//<?php

abstract class rules_hook_ipsContent extends _HOOK_CLASS_
{

	/**
	 * Hide
	 *
	 * @param	\IPS\Member|NULL|FALSE	$member	The member doing the action (NULL for currently logged in member, FALSE for no member)
	 * @param	string					$reason	Reason
	 * @return	void
	 */
	public function hide( $member, $reason=NULL )
	{
		$result = call_user_func_array( 'parent::hide', func_get_args() );
		
		\IPS\rules\Event::load( 'rules', 'Content', 'content_hidden' )->trigger( $this, $member ?: \IPS\Member::loggedIn(), $reason );
		
		$classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_hidden_' . md5( get_class( $this ) ) );
		if ( ! $classEvent->placeholder )
		{
			$classEvent->trigger( $this, $member ?: \IPS\Member::loggedIn(), $reason );
		}

		return $result;
	}

	/**
	 * Unhide
	 *
	 * @param	\IPS\Member|NULL	$member	The member doing the action (NULL for currently logged in member)
	 * @return	void
	 */
	public function unhide( $member )
	{
		$result = call_user_func_array( 'parent::unhide', func_get_args() );
		
		\IPS\rules\Event::load( 'rules', 'Content', 'content_unhidden' )->trigger( $this, $member ?: \IPS\Member::loggedIn() );
		
		$classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_unhidden_' . md5( get_class( $this ) ) );
		if ( ! $classEvent->placeholder )
		{
			$classEvent->trigger( $this, $member ?: \IPS\Member::loggedIn() );
		}

		return $result;
	}

	/**
	 * Change Author
	 *
	 * @param	\IPS\Member	$newAuthor	The new author
	 * @return	void
	 */
	public function changeAuthor( \IPS\Member $newAuthor )
	{
		$oldAuthor = $this->author();
		$result = call_user_func_array( 'parent::changeAuthor', func_get_args() );
		
		\IPS\rules\Event::load( 'rules', 'Content', 'content_author_changed' )->trigger( $this, $oldAuthor, $newAuthor );
		
		$classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_author_changed_' . md5( get_class( $this ) ) );
		if ( ! $classEvent->placeholder )
		{
			$classEvent->trigger( $this, $oldAuthor, $newAuthor );
		}

		return $result;
	}

	/**
	 * Report
	 *
	 * @param	string	$reportContent	Report content message from member
	 * @return	\\IPS\core\Reports\Report
	 * @throws	\UnexpectedValueException	If there is a permission error - you should only call this method after checking canReport
	 */
	public function report( $reportContent )
	{
		$result = call_user_func_array( 'parent::report', func_get_args() );
		
		\IPS\rules\Event::load( 'rules', 'Content', 'content_reported' )->trigger( $this, $reportContent );
		
		$classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_reported_' . md5( get_class( $this ) ) );
		if ( ! $classEvent->placeholder )
		{
			$classEvent->trigger( $this, $reportContent );
		}

		return $result;
	}

	/**
	 * Give reputation
	 *
	 * @param	int					$type	1 for positive, -1 for negative
	 * @param	\IPS\Member|NULL	$member	The member to check for (NULL for currently logged in member)
	 * @return	void
	 * @throws	\DomainException|\BadMethodCallException
	 */
	public function giveReputation( $type, \IPS\Member $member=NULL )
	{
		$result = call_user_func_array( 'parent::giveReputation', func_get_args() );
		
		\IPS\rules\Event::load( 'rules', 'Members', 'reputation_given' )->trigger( $this->author(), $member ?: \IPS\Member::loggedIn(), $this, $type );
		
		return $result;
	}
	
	/**
	 * Do Moderator Action
	 *
	 * @param	string				$action	The action
	 * @param	\IPS\Member|NULL	$member	The member doing the action (NULL for currently logged in member)
	 * @param	string|NULL			$reason	Reason (for hides)
	 * @return	void
	 * @throws	\OutOfRangeException|\InvalidArgumentException|\RuntimeException
	 */
	public function modAction( $action, \IPS\Member $member = NULL, $reason = NULL )
	{
		$result = call_user_func_array( 'parent::modAction', func_get_args() );
		$member = $member ?: \IPS\Member::loggedIn();

		$this->modActionEvent( $action, $member );
		
		return $result;
	}
	
	/**
	 * Trigger moderation action events
	 */
	public function modActionEvent( $action, $member=NULL )
	{
		$member = $member ?: new \IPS\Member;
	
		switch ( $action )
		{
			case 'approve'	: \IPS\rules\Event::load( 'rules', 'Content', 'content_approved' 	)->trigger( $this, $member ); break;
			case 'pin'	: \IPS\rules\Event::load( 'rules', 'Content', 'content_pinned' 		)->trigger( $this, $member ); break;
			case 'unpin'	: \IPS\rules\Event::load( 'rules', 'Content', 'content_unpinned' 	)->trigger( $this, $member ); break;
			case 'feature'	: \IPS\rules\Event::load( 'rules', 'Content', 'content_featured' 	)->trigger( $this, $member ); break;
			case 'unfeature': \IPS\rules\Event::load( 'rules', 'Content', 'content_unfeatured' 	)->trigger( $this, $member ); break;
			case 'lock'	: \IPS\rules\Event::load( 'rules', 'Content', 'content_locked' 		)->trigger( $this, $member ); break;
			case 'unlock'	: \IPS\rules\Event::load( 'rules', 'Content', 'content_unlocked' 	)->trigger( $this, $member ); break;			
		}
		
		switch ( $action )
		{
			case 'approve'	: $classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_approved_' . 	md5( get_class( $this ) ) ); break;
			case 'pin'	: $classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_pinned_' . 	md5( get_class( $this ) ) ); break;
			case 'unpin'	: $classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_unpinned_' . 	md5( get_class( $this ) ) ); break;
			case 'feature'	: $classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_featured_' . 	md5( get_class( $this ) ) ); break;
			case 'unfeature': $classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_unfeatured_' . 	md5( get_class( $this ) ) ); break;
			case 'lock'	: $classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_locked_' . 	md5( get_class( $this ) ) ); break;
			case 'unlock'	: $classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_unlocked_' . 	md5( get_class( $this ) ) ); break;			
		}

		if ( isset( $classEvent ) and ! $classEvent->placeholder )
		{
			$classEvent->trigger( $this, $member );
		}	
	}
	
	/**
	 * Save Changed Columns
	 *
	 * @return	void
	 */
	public function save()
	{
		if ( $this->_new )
		{
			$result = call_user_func_array( 'parent::save', func_get_args() );
			
			\IPS\rules\Event::load( 'rules', 'Content', 'content_created' )->trigger( $this );
			\IPS\rules\Event::load( 'rules', 'Content', 'content_updated' )->trigger( $this, $this->_data, TRUE );
			
			$createdEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_created_' . md5( get_class( $this ) ) );
			if ( ! $createdEvent->placeholder )
			{
				$createdEvent->trigger( $this );
			}
			
			$updatedEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_updated_' . md5( get_class( $this ) ) );
			if ( ! $updatedEvent->placeholder )
			{
				$updatedEvent->trigger( $this, $this->_data, TRUE );
			}
		}
		else
		{
			$changed 		= $this->changed;
			$rulesDataChanged 	= $this->rulesDataChanged;
			
			$result = call_user_func_array( 'parent::save', func_get_args() );
			
			if ( ! empty( $changed ) or $rulesDataChanged )
			{
				\IPS\rules\Event::load( 'rules', 'Content', 'content_updated' )->trigger( $this, $changed, FALSE );
				
				$updatedEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_updated_' . md5( get_class( $this ) ) );
				if ( ! $updatedEvent->placeholder )
				{
					$updatedEvent->trigger( $this, $changed, FALSE );
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{
		$result = call_user_func_array( 'parent::delete', func_get_args() );
		
		\IPS\rules\Event::load( 'rules', 'Content', 'content_deleted' )->trigger( $this );
		
		$classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_deleted_' . md5( get_class( $this ) ) );
		if ( ! $classEvent->placeholder )
		{
			$classEvent->trigger( $this );
		}

		return $result;
	}
	
	/**
	 * Returns the content
	 *
	 * @return	string
	 */
	public function content()
	{
		if ( \IPS\Request::i()->do !== 'edit' )
		{
			if ( is_subclass_of( get_called_class(), '\IPS\Content\Comment' ) )
			{
				if ( $item = $this->item() and ! $item::$databaseColumnMap[ 'content' ] and $item->rulesDataFields() )
				{
					return \IPS\Theme::i()->getTemplate( 'components', 'rules', 'front' )->contentDataDisplay( $item, parent::content() );
				}
			}
			
			if ( is_subclass_of( get_called_class(), '\IPS\Content\Item' ) )
			{
				if ( $this->rulesDataFields() )
				{
					return \IPS\Theme::i()->getTemplate( 'components', 'rules', 'front' )->contentDataDisplay( $this, parent::content() );
				}
			}
		}
		
		return parent::content();
	}
	
	
}