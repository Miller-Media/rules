//<?php

class rules_hook_modCoreMembersProfile extends _HOOK_CLASS_
{

	/**
	 * Edit Profile
	 *
	 * @return	void
	 */
	protected function edit()
	{
		/* Do we have permission? */
		if ( !\IPS\Member::loggedIn()->modPermission('can_modify_profiles') and ( \IPS\Member::loggedIn()->member_id !== $this->member->member_id or !$this->member->group['g_edit_profile'] ) )
		{
			\IPS\Output::i()->error( 'no_permission_edit_profile', '2S147/1', 403, '' );
		}
		
		/* Build the form */
		$form = new \IPS\Helpers\Form;
		
		/* The basics */
		$form->addtab( 'profile_edit_basic_tab', 'user');
		$form->addHeader( 'profile_edit_basic_header' );
		if( \IPS\Settings::i()->post_titlechange != -1 and ( isset( \IPS\Settings::i()->post_titlechange ) and $this->member->member_posts >= \IPS\Settings::i()->post_titlechange ) )
		{
			$form->add( new \IPS\Helpers\Form\Text( 'member_title', $this->member->member_title, FALSE, array( 'maxLength' => 64 ) ) );
		}
		
		$form->add( new \IPS\Helpers\Form\Custom( 'bday', array( 'year' => $this->member->bday_year, 'month' => $this->member->bday_month, 'day' => $this->member->bday_day ), FALSE, array( 'getHtml' => function( $element )
		{			
			return strtr( \IPS\Member::loggedIn()->language()->preferredDateFormat(), array(
				'dd'	=> \IPS\Theme::i()->getTemplate( 'members', 'core', 'global' )->bdayForm_day( $element->name, $element->value, $element->error ),
				'mm'	=> \IPS\Theme::i()->getTemplate( 'members', 'core', 'global' )->bdayForm_month( $element->name, $element->value, $element->error ),
				'yy'	=> \IPS\Theme::i()->getTemplate( 'members', 'core', 'global' )->bdayForm_year( $element->name, $element->value, $element->error ),
				'yyyy'	=> \IPS\Theme::i()->getTemplate( 'members', 'core', 'global' )->bdayForm_year( $element->name, $element->value, $element->error ),
			) );
		} ) ) );

		/* Profile fields */
		try
		{
			$values = \IPS\Db::i()->select( '*', 'core_pfields_content', array( 'member_id=?', $this->member->member_id ) )->first();
		}
		catch( \UnderflowException $e )
		{
			$values	= array();
		}
		
		foreach ( \IPS\core\ProfileFields\Field::fields( $values, \IPS\core\ProfileFields\PROFILE ) as $group => $fields )
		{
			$form->addHeader( "core_pfieldgroups_{$group}" );
			foreach ( $fields as $field )
			{
				$form->add( $field );
			}
		}
		
		/**
		 * Rules Data Fields
		 */
		foreach ( \IPS\Db::i()->select( '*', 'rules_data', array( 'data_class=? AND data_use_mode IN ( \'public\', \'admin\' )', \IPS\Member::rulesDataClass() ) ) as $row )
		{
			if ( $row[ 'data_use_mode' ] == 'public' or \IPS\Member::loggedIn()->modPermission( 'can_modify_profiles' ) )
			{
				$data_field = \IPS\rules\Data::constructFromData( $row );
				if ( $data_field->can( 'edit' ) )
				{
					if ( ! isset( $_rules_header ) )
					{
						$_rules_header = TRUE and $form->addHeader( "rules_profile_data_header" );
					}
					
					foreach( $data_field->formElements( $this->member ) as $name => $element )
					{
						$form->add( $element );
					}
				}
			}
		}		

		/* Moderator stuff */		
		if ( \IPS\Member::loggedIn()->modPermission('can_modify_profiles') AND \IPS\Member::loggedIn()->member_id != $this->member->member_id )
		{
			$form->add( new \IPS\Helpers\Form\Editor( 'signature',  $this->member->signature, FALSE, array( 'app' => 'core', 'key' => 'Signatures', 'autoSaveKey' => "frontsig-" . $this->member->member_id, 'attachIds' => array(  $this->member->member_id ) ) ) );

			$form->addTab( 'profile_edit_moderation', 'times' );
			
			if ( $this->member->mod_posts !== 0 )
			{
				$form->add( new \IPS\Helpers\Form\YesNo( 'remove_mod_posts', NULL, FALSE ) );
			}
			
			if ( $this->member->restrict_post !== 0 )
			{
				$form->add( new \IPS\Helpers\Form\YesNo( 'remove_restrict_post', NULL, FALSE ) );
			}
			
			if ( $this->member->temp_ban !== 0 )
			{
				$form->add( new \IPS\Helpers\Form\YesNo( 'remove_ban', NULL, FALSE ) );
			}
		}
		
		/* Handle the submission */
		if ( $values = $form->values() )
		{
			if( ( \IPS\Settings::i()->post_titlechange == -1 or ( isset( \IPS\Settings::i()->post_titlechange ) and $this->member->member_posts >= \IPS\Settings::i()->post_titlechange ) ) AND isset( $values['member_title'] ) )
			{
				$this->member->member_title = $values['member_title'];
			}

			if ( $values['bday'] and $values['bday']['day'] and $values['bday']['month'] )
			{
				$this->member->bday_day		= $values['bday']['day'];
				$this->member->bday_month	= $values['bday']['month'];
				$this->member->bday_year	= $values['bday']['year'];
			}
			else
			{
				$this->member->bday_day = NULL;
				$this->member->bday_month = NULL;
				$this->member->bday_year = NULL;
			}
			
			/* Profile Fields */
			try
			{
				$profileFields = \IPS\Db::i()->select( '*', 'core_pfields_content', array( 'member_id=?', $this->member->member_id ) )->first();
			}
			catch( \UnderflowException $e )
			{
				$profileFields = array();
			}
			
			/* If the row only contains one column (eg. member_id) then the result of the query is a string, we do not want this */
			if ( !is_array( $profileFields ) )
			{
				$profileFields = array();
			}

			$profileFields['member_id'] = $this->member->member_id;

			foreach ( \IPS\core\ProfileFields\Field::fields( $profileFields, \IPS\core\ProfileFields\PROFILE ) as $group => $fields )
			{
				foreach ( $fields as $id => $field )
				{
					$profileFields[ "field_{$id}" ] = $field::stringValue( $values[ $field->name ] );

					if ( $fields instanceof \IPS\Helpers\Form\Editor )
					{
						$field->claimAttachments( $this->id );
					}
				}
			}

			/**
			 * Save Custom Rules Data
			 */
			foreach ( \IPS\Db::i()->select( '*', 'rules_data', array( 'data_class=? AND data_use_mode IN ( \'public\' )', \IPS\Member::rulesDataClass() ) ) as $row )
			{
				if ( isset ( $values[ 'rules_data_' . $row[ 'data_column_name' ] ] ) )
				{
					$this->member->setRulesData( $row[ 'data_column_name' ], $values[ 'rules_data_' . $row[ 'data_column_name' ] ] );
					unset( $values[ 'rules_data_' . $row[ 'data_column_name' ] ] );
				}
			}

			/* Moderator stuff */			
			if ( \IPS\Member::loggedIn()->modPermission('can_modify_profiles') AND \IPS\Member::loggedIn()->member_id != $this->member->member_id)
			{
				if ( isset( $values['remove_mod_posts'] ) AND $values['remove_mod_posts'] )
				{
					$this->member->mod_posts = 0;
				}
				
				if ( isset( $values['remove_restrict_post'] ) AND $values['remove_restrict_post'] )
				{
					$this->member->restrict_post = 0;
				}
				
				if ( isset( $values['remove_ban'] ) AND $values['remove_ban'] )
				{
					$this->member->temp_ban = 0;
				}

				if ( isset( $values['signature'] ) )
				{
					$this->member->signature = $values['signature'];
				}
			}

			/* Save */
			$this->member->save();
			\IPS\Db::i()->replace( 'core_pfields_content', $profileFields );

			\IPS\Output::i()->redirect( $this->member->url() );
		}
		
		/* Set Session Location */
		\IPS\Session::i()->setLocation( $this->member->url(), array(), 'loc_editing_profile', array( $this->member->name => FALSE ) );
		
		\IPS\Output::i()->output = $form->customTemplate( array( call_user_func_array( array( \IPS\Theme::i(), 'getTemplate' ), array( 'forms', 'core' ) ), 'popupTemplate' ) );
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'editing_profile', FALSE, array( 'sprintf' => array( $this->member->name ) ) );
		\IPS\Output::i()->breadcrumb[] = array( NULL, \IPS\Member::loggedIn()->language()->addToStack( 'editing_profile', FALSE, array( 'sprintf' => array( $this->member->name ) ) ) );
	}

}