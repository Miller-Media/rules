<?php
/**
 * @brief		Rules extension: ContentItems
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
 * @brief	Rules definitions extension: ContentItems
 */
class _ContentItems
{

	/**
	 * @brief	Group events and actions in this extension with other extensions by group name
	 */
	public $group = 'Content Items';

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
			'content_item_created' => array
			( 
				'arguments' => array
				( 
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content\Item' ),
					'values' 	=> array( 'argtype' => 'array' ),
					'container' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Node\Model', 'nullable' => TRUE ),
					'notification' 	=> array( 'argtype' => 'bool' ),
				),		
			),
			'content_item_approved' => array
			( 
				'arguments' => array
				( 
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content\Item' ),
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
				),		
			),
			'content_item_published' => array
			( 
				'arguments' => array
				( 
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content\Item' ),
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
				),		
			),
			'content_item_unpublished' => array
			( 
				'arguments' => array
				( 
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content\Item' ),
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
				),		
			),
			'content_item_moved' => array
			( 
				'arguments' => array
				( 
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content\Item' ),
					'oldContainer' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Node\Model' ),
					'container' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Node\Model' ),
					'link'		=> array( 'argtype' => 'bool' ),
				),		
			),
			'content_item_merging' => array
			( 
				'arguments' => array
				( 
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content\Item' ),
					'items' 	=> array( 'argtype' => 'array', 'class' => '\IPS\Content\Item' ),
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