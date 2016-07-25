<?php
/**
 * @brief		Admin CP Member Form
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Social Suite
 * @subpackage	Rules
 * @since		23 Mar 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\rules\extensions\core\MemberForm;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Admin CP Member Form
 */
class _RulesMember
{
	/**
	 * Action Buttons
	 *
	 * @param	\IPS\Member	$member	The Member
	 * @return	array
	 */
	public function actionButtons( $member )
	{
		return array();
	}

	/**
	 * Process Form
	 *
	 * @param	\IPS\Form\Tabbed		$form	The form
	 * @param	\IPS\Member				$member	Existing Member
	 * @return	void
	 */
	public function process( &$form, $member )
	{
		/* Rules Custom Data */
		foreach ( \IPS\Db::i()->select( '*', 'rules_data', array( 'data_class=? AND data_use_mode IN ( \'public\', \'admin\' )', $member::rulesDataClass() ) ) as $row )
		{
			$data_field = \IPS\rules\Data::constructFromData( $row );
			if ( $data_field->can( 'edit' ) )
			{
				foreach( $data_field->formElements( $member ) as $name => $element )
				{
					$form->add( $element );
				}
			}
		}
		
		/* Rules Custom Logs */
		foreach( \IPS\rules\Log\Custom::roots( 'view', NULL, array( array( 'custom_log_class=? AND custom_log_enabled=1', '-IPS-Member' ) ) ) as $log )
		{
			$tab_title = 'custom_log_' . $log->id;
			\IPS\Member::loggedIn()->language()->words[ 'custom_log_' . $log->id ] = $log->title;
			
			$form->addTab( $tab_title );
			$form->addHtml( (string) $log->logsTable( $member ) );
		}
	}
	
	/**
	 * Save
	 *
	 * @param	array				$values	Values from form
	 * @param	\IPS\Member			$member	The member
	 * @return	void
	 */
	public function save( $values, &$member )
	{
		foreach ( \IPS\Db::i()->select( '*', 'rules_data', array( 'data_class=? AND data_use_mode IN ( \'public\', \'admin\' )', $member::rulesDataClass() ) ) as $row )
		{
			if ( isset ( $values[ 'rules_data_' . $row[ 'data_column_name' ] ] ) )
			{
				$member->setRulesData( $row[ 'data_column_name' ], $values[ 'rules_data_' . $row[ 'data_column_name' ] ] );
				unset( $values[ 'rules_data_' . $row[ 'data_column_name' ] ] );
			}
		}		
	}
}