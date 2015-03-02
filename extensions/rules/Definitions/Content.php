<?php
/**
 * @brief		Rules extension: Content
 * @package		Rules for IPS Social Suite
 * @since		25 Feb 2015
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
 * @brief	Rules definitions extension: Content
 */
class _Content
{

	/**
	 * @brief	Group events and actions in this extension with other extensions by group name
	 */
	public $group = 'Content';

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
			'content_pinned' => array
			( 
				'arguments' => array
				( 
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content' ),
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
				),		
			),
			'content_unpinned' => array
			( 
				'arguments' => array
				( 
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content' ),
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
				),		
			),
			'content_featured' => array
			( 
				'arguments' => array
				( 
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content' ),
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
				),		
			),
			'content_unfeatured' => array
			( 
				'arguments' => array
				( 
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content' ),
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
				),		
			),
			'content_locked' => array
			( 
				'arguments' => array
				( 
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content' ),
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
				),		
			),
			'content_unlocked' => array
			( 
				'arguments' => array
				( 
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content' ),
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
				),		
			),
			'content_hidden' => array
			( 
				'arguments' => array
				( 
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content' ),
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
					'reason' 	=> array( 'argtype' => 'string', 'nullable' => TRUE ),
				),		
			),
			'content_unhidden' => array
			( 
				'arguments' => array
				( 
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content' ),
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
				),		
			),
			'content_reported' => array
			( 
				'arguments' => array
				( 
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content' ),
					'message' 	=> array( 'argtype' => 'string' ),
				),		
			),
			'content_author_changed' => array
			( 
				'arguments' => array
				( 
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content' ),
					'oldMember' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
					'newMember' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
				),		
			),
			'content_deleted' => array
			( 
				'arguments' => array
				( 
					'item' 		=> array( 'argtype' => 'object', 'class' => '\IPS\Content' ),
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
			'content_type' => array
			(
				'callback' 	=> array( $this, 'contentType' ),				
				'configuration' => array
				(
					'form' => function( $form, $values, $condition )
					{
						$lang 		= \IPS\Member::loggedIn()->language();
						$options 	= array();
						
						/**
						 * Build list of available content types
						 */
						foreach ( \IPS\Application::allExtensions( 'core', 'ContentRouter' ) as $router )
						{
							foreach ( $router->classes as $contentItemClass )
							{
								$options[ $contentItemClass ] = $lang->addToStack( '__app_' . $contentItemClass::$application ) . ' / ' . ucwords( $lang->get( $contentItemClass::$title ) );
							}
						}
						
						$form->add( new \IPS\Helpers\Form\CheckboxSet( 'rules_Content_content_types', $values[ 'rules_Content_content_types' ], TRUE, array( 'options' => $options ), NULL, NULL, NULL, 'rules_Content_content_types' ) );
						return array( 'rules_Content_content_types' );
					},
				),
				
				'arguments'	=> array
				(
					'content' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Content Object',
								'class' => '\IPS\Content',						
							),						
						),
						'required'	=> TRUE,
					),	
				),				
			),
			'content_status' => array
			(
				'callback' 	=> array( $this, 'contentStatus' ),				
				'configuration' => array
				(
					'form' => function( $form, $values, $condition )
					{
						$lang 		= \IPS\Member::loggedIn()->language();
						$options 	= array();
						
						/**
						 * Build list of statuses
						 */
						$statuses = array(
							'pinned'	=> 'mod_confirm_pin',
							'unpinned'	=> 'mod_confirm_unpin',
							'featured'	=> 'mod_confirm_feature',
							'unfeatured'	=> 'mod_confirm_unfeature',
							'hidden'	=> 'mod_confirm_hide',
							'unhidden'	=> 'mod_confirm_unhide',
							'locked'	=> 'mod_confirm_lock',
							'unlocked'	=> 'mod_confirm_unlock',
							'published'	=> 'Published',
						);
						
						$form->add( new \IPS\Helpers\Form\CheckboxSet( 'rules_Content_content_statuses', $values[ 'rules_Content_content_statuses' ], TRUE, array( 'options' => $statuses ), NULL, NULL, NULL, 'rules_Content_content_statuses' ) );
						return array( 'rules_Content_content_statuses' );
					},
				),
				
				'arguments'	=> array
				(
					'content' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Content Object',
								'class' => '\IPS\Content',						
							),						
						),
						'required'	=> TRUE,
					),	
				),				
			),
			'content_author' => array
			(
				'callback' 	=> array( $this, 'contentAuthor' ),				
				'configuration' => array
				(
					'form' => function( $form, $values ) 
					{
						$members = array();
						foreach( (array) $values[ 'rules_Content_author_members' ] as $member_id )
						{
							if ( $member_id )
							{
								try
								{
									$members[] = \IPS\Member::load( $member_id );
								}
								catch ( \Exception $e ) {}
							}
						}
						
						$form->add( new \IPS\Helpers\Form\Member( 'rules_Content_author_members', $members, TRUE, array( 'multiple' => NULL ), NULL, NULL, NULL, 'rules_Content_author_members' ) );
					},
					'saveValues' => function( &$values ) 
					{
						$_members = array();
						
						foreach( (array) $values[ 'rules_Content_author_members' ] as $member )
						{
							$_members[] = $member->member_id;
						}
						
						$values[ 'rules_Content_author_members' ] = $_members;
					},
				),				
				'arguments'	=> array
				(
					'content' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Content Object',
								'class' => '\IPS\Content',						
							),						
						),
						'required'	=> TRUE,
					),	
				),				
			),
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
			'pin_content' => array
			(
				'callback' 	=> array( $this, 'pinContent' ),				
				'arguments'	=> array
				(
					'content' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Content Object',
								'class' => '\IPS\Content',						
							),						
						),
						'required'	=> TRUE,
					),	
				),
			),
			'unpin_content' => array
			(
				'callback' 	=> array( $this, 'unpinContent' ),				
				'arguments'	=> array
				(
					'content' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Content Object',
								'class' => '\IPS\Content',						
							),						
						),
						'required'	=> TRUE,
					),	
				),
			),
			'feature_content' => array
			(
				'callback' 	=> array( $this, 'featureContent' ),				
				'arguments'	=> array
				(
					'content' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Content Object',
								'class' => '\IPS\Content',						
							),						
						),
						'required'	=> TRUE,
					),	
				),
			),
			'unfeature_content' => array
			(
				'callback' 	=> array( $this, 'unfeatureContent' ),				
				'arguments'	=> array
				(
					'content' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Content Object',
								'class' => '\IPS\Content',						
							),						
						),
						'required'	=> TRUE,
					),	
				),
			),
			'lock_content' => array
			(
				'callback' 	=> array( $this, 'lockContent' ),				
				'arguments'	=> array
				(
					'content' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Content Object',
								'class' => '\IPS\Content',						
							),						
						),
						'required'	=> TRUE,
					),	
				),
			),
			'unlock_content' => array
			(
				'callback' 	=> array( $this, 'unlockContent' ),				
				'arguments'	=> array
				(
					'content' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Content Object',
								'class' => '\IPS\Content',						
							),						
						),
						'required'	=> TRUE,
					),	
				),
			),
			'hide_content' => array
			(
				'callback' 	=> array( $this, 'hideContent' ),				
				'arguments'	=> array
				(
					'content' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Content Object',
								'class' => '\IPS\Content',						
							),						
						),
						'required' => TRUE,
					),
					'member' => array
					(
						'argtypes' => \IPS\rules\Application::getPreset( 'member' ),
						'required' => TRUE,
						'configuration' => array
						(
							'form' => function( $form, $values, $action ) 
							{
								$members = array();
								foreach( (array) $values[ 'rules_Content_member' ] as $member_id )
								{
									if ( $member_id )
									{
										try
										{
											$members[] = \IPS\Member::load( $member_id );
										}
										catch ( \Exception $e ) {}
									}
								}
								
								$form->add( new \IPS\Helpers\Form\Member( 'rules_Content_member', $members, FALSE, array( 'multiple' => 1 ), NULL, NULL, NULL, 'rules_Content_member' ) );
								return array( 'rules_Content_member' );
							},
							'saveValues' => function( &$values, $action ) 
							{
								$_members = array();
								
								foreach( (array) $values[ 'rules_Content_member' ] as $member )
								{
									$_members[] = $member->member_id;
								}
								
								$values[ 'rules_Content_member' ] = $_members;
							},
							'getArg' => function( $values, $action )
							{
								$members = array();
								foreach( (array) $values[ 'rules_Content_member' ] as $member_id )
								{
									try { $members[] = \IPS\Member::load( $member_id ); }
									catch( \Exception $e ) {}
								}
								$member = array_shift( $members );
								return $member;
							},
						),
					),
					'reason' => array
					(
						'argtypes' => array( 'string' => array( 'description' => 'The reason for hiding the content' ) ),
						'required' => FALSE,
						'configuration' => array
						(
							'form' => function( $form, $values, $condition )
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Content_reason', $values[ 'rules_Content_reason' ], FALSE, array(), NULL, NULL, NULL, 'rules_Content_reason' ) );
								return array( 'rules_Content_reason' );
							},
						),
					),
				),
			),
			'unhide_content' => array
			(
				'callback' 	=> array( $this, 'unhideContent' ),				
				'arguments'	=> array
				(
					'content' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Content Object',
								'class' => '\IPS\Content',						
							),						
						),
						'required' => TRUE,
					),
					'member' => array
					(
						'argtypes' => \IPS\rules\Application::getPreset( 'member' ),
						'required' => FALSE,
						'configuration' => array
						(
							'form' => function( $form, $values, $action ) 
							{
								$members = array();
								foreach( (array) $values[ 'rules_Content_member' ] as $member_id )
								{
									if ( $member_id )
									{
										try
										{
											$members[] = \IPS\Member::load( $member_id );
										}
										catch ( \Exception $e ) {}
									}
								}
								
								$form->add( new \IPS\Helpers\Form\Member( 'rules_Content_member', $members, TRUE, array( 'multiple' => 1 ), NULL, NULL, NULL, 'rules_Content_member' ) );
								return array( 'rules_Content_member' );
							},
							'saveValues' => function( &$values, $action ) 
							{
								$_members = array();
								
								foreach( (array) $values[ 'rules_Content_member' ] as $member )
								{
									$_members[] = $member->member_id;
								}
								
								$values[ 'rules_Content_member' ] = $_members;
							},
							'getArg' => function( $values, $action )
							{
								$members = array();
								foreach( (array) $values[ 'rules_Content_member' ] as $member_id )
								{
									try { $members[] = \IPS\Member::load( $member_id ); }
									catch( \Exception $e ) {}
								}
								$member = array_shift( $members );
								return $member;
							},
						),
					),
				),
			),
			'delete_content' => array
			(
				'callback' 	=> array( $this, 'deleteContent' ),				
				'arguments'	=> array
				(
					'content' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Content Object',
								'class' => '\IPS\Content',						
							),						
						),
						'required'	=> TRUE,
					),	
				),
			),
			'change_author' => array
			(
				'callback' 	=> array( $this, 'changeAuthor' ),				
				'arguments'	=> array
				(
					'content' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Content Object',
								'class' => '\IPS\Content',						
							),						
						),
						'required'	=> TRUE,
					),
					'author' => array
					(
						'argtypes' => \IPS\rules\Application::getPreset( 'member' ),
						'required' 	=> TRUE,
					),
				),
			),
		);
		
		return $actions;
	}
	
	/**
	 * Check Content Types
	 */
	public function contentType( $content, $values )
	{
		if ( ! is_object( $content ) )
		{
			return FALSE;
		}
		return in_array( get_class( $content ), (array) $values[ 'rules_Content_content_types' ] );
	}
	
	/**
	 * Check Content Status
	 */
	public function contentStatus( $content, $values )
	{
		if ( ! is_object( $content ) or ! ( $content instanceof \IPS\Content ) )
		{
			return FALSE;
		}
		
		$hasStatus = TRUE;
		
		foreach ( (array) $values[ 'rules_Content_content_statuses' ] as $status )
		{
			if ( ! $hasStatus ) break;
			
			switch ( $status )
			{
				case 'pinned':
					$hasStatus = $content->mapped( 'pinned' );
					break;
					
				case 'unpinned':
					$hasStatus = isset( $content::$databaseColumnMap[ 'pinned' ] ) and ! $content->mapped( 'pinned' );
					break;
					
				case 'featured':
					$hasStatus = $content->mapped( 'featured' );
					break;
					
				case 'unfeatured':
					$hasStatus = isset( $content::$databaseColumnMap[ 'featured' ] ) and ! $content->mapped( 'featured' );
					break;
					
				case 'hidden':
					$hasStatus = $content->hidden();
					break;
					
				case 'unhidden':
					$hasStatus = ! $content->hidden();
					break;
					
				case 'locked':
					$hasStatus = $content->mapped( 'locked' ) or $content->mapped( 'status' ) == 'closed';
					break;
					
				case 'unlocked':
					$hasStatus = ! ( $content->mapped( 'locked' ) or $content->mapped( 'status' ) == 'closed' );
					break;
					
				case 'published':
					$hasStatus = ! $content->isFutureDate() and ! $content->hidden();
					break;
			}
		}
		
		return $hasStatus;
	}
	
	/**
	 * Content Has Author
	 */
	public function contentAuthor( $content, $values )
	{
		if ( ! is_object( $content ) or ! ( $content instanceof \IPS\Content ) )
		{
			return FALSE;
		}
				
		return in_array( $content->author()->member_id, (array) $values[ 'rules_Content_author_members' ] );
	}
	
	/**
	 * Pin Content
	 */
	public function pinContent( $content )
	{
		if ( ! is_object( $content ) )
		{
			return 'content is not an object';
		}
		
		if ( ! ( $content instanceof \IPS\Content ) )
		{
			return 'not a content class: ' . get_class( $content );
		}
		
		if ( $pinned = $content::$databaseColumnMap[ 'pinned' ] )
		{
			$content->$pinned = TRUE;
			$content->save();
			return 'content pinned';
		}
		
		return 'content not pinnable';
	}

	/**
	 * UnPin Content
	 */
	public function unpinContent( $content )
	{
		if ( ! is_object( $content ) )
		{
			return 'content is not an object';
		}
		
		if ( ! ( $content instanceof \IPS\Content ) )
		{
			return 'not a content class: ' . get_class( $content );
		}
		
		if ( $pinned = $content::$databaseColumnMap[ 'pinned' ] )
		{
			$content->$pinned = FALSE;
			$content->save();
			return 'content unpinned';
		}
		
		return 'content not pinnable';
	}
	
	/**
	 * Feature Content
	 */
	public function featureContent( $content )
	{
		if ( ! is_object( $content ) )
		{
			return 'content is not an object';
		}
		
		if ( ! ( $content instanceof \IPS\Content ) )
		{
			return 'not a content class: ' . get_class( $content );
		}
		
		if ( $featured = $content::$databaseColumnMap[ 'featured' ] )
		{
			$content->$featured = TRUE;
			$content->save();
			return 'content featured';
		}
		
		return 'content not featurable';
	}

	/**
	 * Unfeature Content
	 */
	public function unfeatureContent( $content )
	{
		if ( ! is_object( $content ) )
		{
			return 'content is not an object';
		}
		
		if ( ! ( $content instanceof \IPS\Content ) )
		{
			return 'not a content class: ' . get_class( $content );
		}
		
		
		if ( $featured = $content::$databaseColumnMap[ 'featured' ] )
		{
			$content->$featured = FALSE;
			$content->save();
			return 'content unfeatured';
		}
		
		return 'content not featurable';
	}
		
	/**
	 * Lock Content
	 */
	public function lockContent( $content )
	{
		if ( ! is_object( $content ) )
		{
			return 'content is not an object';
		}
		
		if ( ! ( $content instanceof \IPS\Content ) )
		{
			return 'not a content class: ' . get_class( $content );
		}
		
		if ( $locked = $content::$databaseColumnMap[ 'locked' ] )
		{
			$content->$locked = TRUE;
			$content->save();
			return 'content locked';
		}
		else if ( $status = $content::$databaseColumnMap[ 'status' ] )
		{
			$content->$status = 'closed';
			$content->save();
			return 'content locked';		
		}
		
		return 'content not lockable';
	}

	/**
	 * Unlock Content
	 */
	public function unlockContent( $content )
	{
		if ( ! is_object( $content ) )
		{
			return 'content is not an object';
		}
		
		if ( ! ( $content instanceof \IPS\Content ) )
		{
			return 'not a content class: ' . get_class( $content );
		}
		
		if ( $locked = $content::$databaseColumnMap[ 'locked' ] )
		{
			$content->$locked = FALSE;
			$content->save();
			return 'content unlocked';
		}
		else if ( $status = $content::$databaseColumnMap[ 'status' ] )
		{
			$content->$status = 'open';
			$content->save();
			return 'content unlocked';		
		}
		
		return 'content not lockable';
	}
		
	/**
	 * Hide Content
	 */
	public function hideContent( $content, $member, $reason )
	{
		if ( ! is_object( $content ) )
		{
			return 'content is not an object';
		}
		
		if ( ! ( $content instanceof \IPS\Content ) )
		{
			return 'not a content class: ' . get_class( $content );
		}
		
		$content->hide( $member, $reason );
		return 'content hidden';
	}

	/**
	 * Unhide Content
	 */
	public function unhideContent( $content, $member )
	{
		if ( ! is_object( $content ) )
		{
			return 'content is not an object';
		}
		
		if ( ! ( $content instanceof \IPS\Content ) )
		{
			return 'not a content class: ' . get_class( $content );
		}
		
		$content->unhide( $member );
		return 'content unhidden';
	}
		
	/**
	 * Delete Content
	 */
	public function deleteContent( $content )
	{
		if ( ! is_object( $content ) )
		{
			return 'content is not an object';
		}
		
		if ( ! ( $content instanceof \IPS\Content ) )
		{
			return 'not a content class: ' . get_class( $content );
		}
		
		$content->$pinned = FALSE;
		$content->delete();
		return 'content deleted';
	}
	
	/**
	 * Change Author
	 */
	public function changeAuthor( $content, $author )
	{
		if ( ! is_object( $content ) )
		{
			return 'content is not an object';
		}
		
		if ( ! ( $content instanceof \IPS\Content ) )
		{
			return 'not a content class: ' . get_class( $content );
		}
		
		if ( ! ( $author instanceof \IPS\Member ) )
		{
			return 'invalid new author';
		}
		
		if ( $author = $content::$databaseColumnMap[ 'author' ] )
		{
			$content->$author = $author->member_id;
			$content->save();
			return 'author changed';
		}
		
		return "can't change content author";
	}
		
}