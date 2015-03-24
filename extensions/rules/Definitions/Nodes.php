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
		$lang = \IPS\Member::loggedIn()->language();
	
		$node_events = array
		(
			'node_created' => array
			( 
				'arguments' => array
				( 
					'node' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Node\Model' ),
				),		
			),
			'node_updated' => array
			( 
				'arguments' => array
				( 
					'node' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Node\Model' ),
					'changed'	=> array( 'argtype' => 'array' ),
				),		
			),
			'node_deleted' => array
			( 
				'arguments' => array
				( 
					'node' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Node\Model' ),
				),		
			),
		);
		
		$app_events = array();
		$data = array
		(
			'node_events' => $node_events,
			'lang' => $lang,
		);
		
		foreach ( \IPS\Application::allExtensions( 'core', 'ContentRouter' ) as $router )
		{
			foreach ( $router->classes as $contentItemClass )
			{
				$content_type = ucwords( $lang->get( $contentItemClass::$title ) );
				$group = 'Content: ' . ( $lang->checkKeyExists( '__app_' . $contentItemClass::$application ) ? $lang->get( '__app_' . $contentItemClass::$application ) : $contentItemClass::$application );
				
				//$this->buildEvents( $app_events, $contentItemClass, $content_type, $group, $data );
				
				if ( isset ( $contentItemClass::$commentClass ) )
				{
					$commentClass = $contentItemClass::$commentClass;
					//$this->buildEvents( $app_events, $commentClass, $content_type . ' Comment', $group, $data );
				}
				
				if ( isset ( $contentItemClass::$reviewClass ) )
				{
					$reviewClass = $contentItemClass::$reviewClass;
					//$this->buildEvents( $app_events, $reviewClass, $content_type . ' Review', $group, $data );
				}
			}
		}		
		
		return array_merge( $node_events, $app_events );
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