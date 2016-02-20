<?php
/**
 * @brief		Rules extension: Nodes
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
 * @brief	Rules definitions extension: Nodes
 */
class _Nodes
{

	/**
	 * @brief	Group events and actions in this extension with other extensions by group name
	 */
	public $defaultGroup = 'Nodes';

	/**
	 * Triggerable Events
	 *
	 * Define the events that can be triggered by your application
	 *
	 * @return 	array		Array of event definitions
	 */
	public function events()
	{
		return array();
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
		return array();
	}

	/**
	 * Triggerable Actions
	 *
	 * @return 	array		Array of action definitions
	 */
	public function actions()
	{
		return array();
	}
	
}