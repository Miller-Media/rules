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
		
		if( \IPS\Application::appIsEnabled( 'forums', TRUE ) )
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
			
			$events[ 'best_answer_set' ] = array
			(
				'arguments' => array
				(
					
				),
			);
			
			$events[ 'best_answer_unset' ] = array
			(
				'arguments' => array
				(
				
				),
			);
			
			$events[ 'topic_archived' ] = array
			(
				'arguments' => array
				(
				
				),
			);
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
		$conditions = array();
		
		if( \IPS\Application::appIsEnabled( 'forums', TRUE ) )
		{
			$conditions = array
			(
				'topic_in_forum' => array
				(
					'callback'	=> array( $this, 'topicInForum' ),
					'configuration' => array
					(
						'form' => function( $form, $values, $action )
						{
							$form->add( new \IPS\Helpers\Form\Node( 'rules_Forums_topic_forums', $values[ 'rules_Forums_topic_forums' ], TRUE, array( 'class' => '\IPS\forums\Forum', 'multiple' => TRUE ), NULL, NULL, NULL, 'rules_Forums_topic_forums' ) );
						},
						'saveValues' => function( &$values, $action )
						{
							if ( is_array ( $values[ 'rules_Forums_topic_forums' ] ) )
							{
									$values[ 'rules_Forums_topic_forums' ] = array_keys( $values[ 'rules_Forums_topic_forums' ] );
							}
						},
					),
					'arguments' 	=> array
					(
						'topic' => array
						(
							'required' => TRUE,
							'argtypes' => array
							(
								'object' => array
								(
									'description' => "Topic to check",
									'class' => '\IPS\forums\Topic',
								),							
							),
						),
					),
				),
				'topic_archived' => array
				(
				
				),
			);
		}
		
		return $conditions;
	}

	/**
	 * Triggerable Actions
	 *
	 * @return 	array		Array of action definitions
	 */
	public function actions()
	{
		$actions = array();
		
		if( \IPS\Application::appIsEnabled( 'forums', TRUE ) )
		{
			$actions = array
			(
				'move_topic' => array
				(				
					'callback'	=> array( $this, 'moveTopic' ),
					'configuration' => array
					(
						'form' => function( $form, $values, $action )
						{
							$form->add( new \IPS\Helpers\Form\Node( 'rules_Forums_topic_forum', $values[ 'rules_Forums_topic_forum' ], TRUE, array( 'class' => '\IPS\forums\Forum' ), NULL, NULL, NULL, 'rules_Forums_topic_forum' ) );
							$form->add( new \IPS\Helpers\Form\YesNo( 'rules_Forums_topic_link', $values[ 'rules_Forums_topic_link' ], FALSE, array(), NULL, NULL, NULL, 'rules_Forums_topic_link' ) );
						},
						'saveValues' => function( &$values, $action )
						{
							if ( is_object ( $values[ 'rules_Forums_topic_forum' ] ) )
							{
								$values[ 'rules_Forums_topic_forum' ] = $values[ 'rules_Forums_topic_forum' ]->_id;
							}
						},
					),					
					'arguments' => array
					(
						'topic' => array
						(
							'required' => TRUE,
							'argtypes' => array
							(
								'object' => array
								(
									'description' => "Topic to move",
									'class' => '\IPS\forums\Topic',
								),							
							),
						),
					),
				),
			);
		}
		
		return $actions;
	}
	
	/**
	 * Check If Topic Is In Forum
	 */
	public function topicInForum( $topic, $values )
	{
		if ( ! ( is_object ( $topic ) ) )
		{
			return FALSE;
		}
	
		if ( ! ( $topic instanceof \IPS\forums\Topic ) )
		{
			return FALSE;
		}
		
		if ( $container = $topic->containerWrapper( TRUE ) )
		{
			return in_array( $container->id, (array) $values[ 'rules_Forums_topic_forums' ] );
		}
		
		return FALSE;	
	}
	
	/**
	 * Move Topic
	 */
	public function moveTopic( $topic, $values )
	{
		if ( ! ( is_object ( $topic ) ) )
		{
			return 'topic is not an object';
		}
	
		if ( ! ( $topic instanceof \IPS\forums\Topic ) )
		{
			return 'not a valid topic class: ' . get_class( $topic );
		}
		
		try
		{
			$forum = \IPS\forums\Forum::load( $values[ 'rules_Forums_topic_forum' ] );
			$topic->move( $forum, $values[ 'rules_Forums_topic_link' ] );
			return 'topic moved';
		}
		catch ( \OutOfRangeException $e )
		{
			return "cant move topic, forum doesn't exist";
		}
	}
	
}