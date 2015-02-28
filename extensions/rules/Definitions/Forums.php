<?php
/**
 * @brief		Rules extension: Forums
 * @package		Rules for IPS Social Suite
 * @since		27 Feb 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\rules\extensions\rules\Definitions;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Rules definitions extension: Forums
 */
class _Forums
{

	/**
	 * @brief	Group events and actions in this extension with other extensions by group name
	 */
	public $group = 'Forums';

	/**
	 * Triggerable Events
	 *
	 * Define the events that can be triggered by your application
	 *
	 * @return 	array		Array of event definitions
	 */
	public function events()
	{
		$events = array();
		
		if ( class_exists ( '\IPS\forums\SavedAction' ) )
		{
			$lang 	= \IPS\Member::loggedIn()->language();
			$action_definition = array
			( 
				'arguments' => array
				( 
					'action' 	=> array( 'argtype' => 'object', 'class' => '\IPS\forums\SavedAction' ),
					'topic' 	=> array( 'argtype' => 'object', 'class' => '\IPS\forums\Topic' ),
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
				),		
			);
			
			foreach( \IPS\forums\SavedAction::roots( NULL ) as $action )
			{
				$lang->words[ 'rules_Forums_event_saved_action_' . $action->_id ] = sprintf( $lang->get( 'rules_Forums_event_saved_action' ), $action->_title );
				$lang->words[ 'rules_Forums_event_saved_action_' . $action->_id . '_action' ] 	= $lang->get( 'rules_Forums_event_saved_action_action' );
				$lang->words[ 'rules_Forums_event_saved_action_' . $action->_id . '_topic' ] 	= $lang->get( 'rules_Forums_event_saved_action_topic' );
				$lang->words[ 'rules_Forums_event_saved_action_' . $action->_id . '_member' ] 	= $lang->get( 'rules_Forums_event_saved_action_member' );
				$events[ 'saved_action_' . $action->_id ] = $action_definition;
			}		
		}
		
		return $events;
	}
	
	/**
	 * Conditional Operations
	 *
	 * You can define your own conditional operations which can be
	 * added to rules as conditions.
	 *
	 * @return 	array		Array of conditions definitions
	 */
	public function conditions()
	{
		$conditions = array
		(

		);
		
		return $conditions;
	}

	/**
	 * Triggerable Actions
	 *
	 * @return 	array		Array of action definitions
	 */
	public function actions()
	{
		$actions = array
		(

		);
		
		return $actions;
	}
	
}