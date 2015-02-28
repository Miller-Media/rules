<?php
/**
 * @brief		Rules extension: Comments
 * @package		Rules for IPS Social Suite
 * @since		26 Feb 2015
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
 * @brief	Rules definitions extension: Comments
 */
class _Comments
{

	/**
	 * @brief	Group events and actions in this extension with other extensions by group name
	 */
	public $group = 'Comments';

	/**
	 * Triggerable Events
	 *
	 * Define the events that can be triggered by your application
	 *
	 * @return 	array		Array of event definitions
	 */
	public function events()
	{
		$events = array
		(
			'content_comment_created' => array
			( 
				'arguments' => array
				( 
					'comment' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Content\Comment' ),
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content\Item' ),
					'content' 	=> array( 'argtype' => 'string' ),
					'first' 	=> array( 'argtype' => 'bool' ),
					'guestName' 	=> array( 'argtype' => 'string', 'nullable' => TRUE ),
					'increment' 	=> array( 'argtype' => 'bool' ),
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
					'time' 		=> array( 'argtype' => 'object', 'class' => '\IPS\DateTime', 'nullable' => TRUE ),
				),		
			),
			'content_comment_moved' => array
			( 
				'arguments' => array
				( 
					'comment' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Content\Comment' ),
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content\Item' ),
				),		
			),
		);
		
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