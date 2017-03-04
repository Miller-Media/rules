<?php
/**
 * @brief		Rules conversions: Core
 * @package		Rules for IPS Social Suite
 * @since		20 Mar 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\rules\extensions\rules\Conversions;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Rules conversions extension: Core
 */
class _Core
{

	/**
	 * Global Arguments
	 *
	 * Let rules know about any global arguments that can be used
	 *
	 * @return 	array		Array of global argument definitions
	 */
	public function globalArguments()
	{
		return array
		(
			'site_settings' => array
			(
				'token' => 'site',
				'argtype' => 'object',
				'class' => '\IPS\Settings',
				'getArg' => function()
				{
					return \IPS\Settings::i();
				},
			),
			'logged_in_member' => array
			(
				'token' => 'user',
				'description' => 'the currently logged in user',
				'argtype' => 'object',
				'class' => '\IPS\Member',
				'nullable' => TRUE,
				'getArg' => function()
				{
					return \IPS\Member::loggedIn();
				},
			),
			'current_time' => array
			(
				'token' => 'time',
				'description' => 'the current time',
				'argtype' => 'object',
				'class' => '\IPS\DateTime',
				'getArg' => function()
				{
					return \IPS\DateTime::ts( time() );
				},
			),
			'request_url' => array
			(
				'token' => 'url',
				'description' => 'the request url',
				'argtype' => 'object',
				'class' => '\IPS\Http\Url',
				'getArg' => function()
				{
					return \IPS\Request::i()->url();
				},
			),
		);
	}

	/**
	 * Conversion Map
	 *
	 * Let's rules know how to convert objects into different types of arguments
	 *
	 * @return 	array		Array of conversion definitions
	 */
	public function conversionMap()
	{
		$map = array
		(
			'\IPS\Member' => array
			(
				'Name' => array
				(
					'token' => 'name',
					'description' => 'User name',
					'argtype' => 'string',
					'converter' => function( $member )
					{
						$name = (string) $member->name;
						\IPS\Lang::load( \IPS\Lang::defaultLanguage() )->parseOutputForDisplay( $name );
						return $name;
					},
				),
				'Name Link' => array
				(
					'token' => 'namelink',
					'description' => 'User name linked to profile',
					'argtype' => 'string',
					'converter' => function ( $member )
					{
						return "<a href='" . $member->url() . "' data-ipsHover data-ipsHover-target='" . $member->url()->setQueryString( array( 'do' => 'hovercard' ) ) . "'>{$member->name}</a>";
					},
				),
				'Email' => array
				(
					'token' => 'email',
					'description' => 'User email address',
					'argtype' => 'string',
					'converter' => function( $member )
					{
						return $member->email;
					},
				),
				'Member Title' => array
				(
					'token' => 'title',
					'description' => 'User title',
					'argtype' => 'string',
					'converter' => function( $member )
					{
						return (string) $member->member_title;
					},
				),
				'Content Count' => array
				(
					'token' => 'posts',
					'description' => 'Posts count',
					'argtype' => 'int',
					'converter' => function( $member )
					{
						return (int) $member->real_member_posts;
					},
				),
				'Leaderboard Days Won' => array
				(
					'token' => 'leaderboards_won',
					'description' => 'Number of days won on leaderboard',
					'argtype' => 'int',
					'converter' => function( $member )
					{
						return (int) $member->getReputationDaysWonCount();
					},
				),
				'Reputation' => array
				(
					'token' => 'reputation',
					'description' => 'Reputation points',
					'argtype' => 'int',
					'converter' => function( $member )
					{
						return (int) $member->pp_reputation_points;
					},
				),
				'Warn Level' => array
				(
					'token' => 'warnlevel',
					'description' => 'Warning level',
					'argtype' => 'int',
					'converter' => function( $member )
					{
						return (int) $member->warn_level;
					},
				),				
				'Joined Date' => array
				(
					'token' => 'joined',
					'description' => 'Joined date',
					'argtype' => 'object',
					'class' => '\IPS\DateTime',
					'converter' => function( $member )
					{
						return $member->joined;
					},
					'tokenValue' => function( $date )
					{
						return (string) $date;
					},
				),
				'Birthday' => array
				(
					'token' => 'birthday',
					'description' => 'Birthday',
					'argtype' => 'object',
					'class' => '\IPS\DateTime',
					'nullable' => TRUE,
					'converter' => function( $member )
					{
						return $member->birthday;
					},
					'tokenValue' => function( $date )
					{
						return (string) $date;
					},
				),
				'Age' => array
				(
					'token' => 'age',
					'argtype' => 'int',
					'description' => 'Age',
					'nullable' => TRUE,
					'converter' => function( $member )
					{
						return $member->age();
					},
				),
				'Member ID' => array
				(
					'token' => 'id',
					'description' => 'The member id',
					'argtype' => 'int',
					'converter' => function( $member )
					{
						return $member->member_id;
					},
				),
				'Url' => array
				(
					'token' => 'url',
					'description' => 'The url',
					'tokenValue' => function( $url ) { return (string) $url; },
					'argtype' => 'object',
					'class' => '\IPS\Http\Url',
					'converter' => function( $member )
					{
						return $member->url();
					},
				),
				'Followers' => array
				(
					'argtype' => 'array',
					'class' => '\IPS\Member',
					'converter' => function( $member )
					{
						$members = array();
						foreach ( $member->followers( 3, array( 'immediate', 'daily', 'weekly' ), NULL ) as $follower )
						{
							try
							{
								$members[] = \IPS\Member::load( $follower[ 'follow_member_id' ] );
							}
							catch( \OutOfRangeException $e ) {}
						}
						return $members;
					},
				),
				'Last Activity' => array
				(
					'token' => 'lastactivity',
					'description' => 'Last activity',
					'argtype' => 'object',
					'class' => '\IPS\DateTime',
					'converter' => function( $member )
					{
						return \IPS\DateTime::ts( $member->last_activity );
					},
					'tokenValue' => function( $date )
					{
						return (string) $date;
					},
				),
			),
			'\IPS\Content' => array
			(
				'Title' => array
				(
					'token' => 'title',
					'description' => 'The content title',
					'argtype' => 'string',
					'nullable' => TRUE,
					'converter' => function( $content )
					{
						if ( $content instanceof \IPS\Content\Comment )
						{
							return $content->item()->mapped( 'title' );
						}
						
						return $content->mapped( 'title' );
					},
				),
				'Content' => array
				(
					'token' => 'content',
					'description' => 'The content body',
					'argtype' => 'string',
					'nullable' => TRUE,
					'converter' => function( $content )
					{
						return $content->content();
					},
				),
				'Created Date' => array
				(
					'token' => 'created',
					'argtype' => 'object',
					'class' => '\IPS\DateTime',
					'converter' => function( $content )
					{
						return \IPS\DateTime::ts( $content->mapped( 'date' ) );
					},
				),
				'Updated Date' => array
				(
					'token' => 'updated',
					'argtype' => 'object',
					'class' => '\IPS\DateTime',
					'converter' => function( $content )
					{
						return \IPS\DateTime::ts( $content->mapped( 'updated' ) );
					},
				),
				'Tags' => array
				(
					'argtype' => 'array',
					'converter' => function( $content )
					{
						return (array) $content->tags();
					},
				),
				'Content ID' => array
				(
					'token' => 'id',
					'description' => 'The content ID',
					'argtype' => 'int',
					'converter' => function( $content )
					{
						$idField = $content::$databaseColumnId;
						return $content->$idField;
					},
				),
				'Author' => array
				(
					'argtype' => 'object',
					'class' => '\IPS\Member',
					'converter' => function( $content )
					{
						return $content->author();
					},
				),
				'Author Name' => array
				(
					'token' => 'author:name',
					'description' => 'The author name',
					'argtype' => 'string',
					'converter' => function( $content )
					{
						return $content->author()->name;
					},
				),
				'Author ID' => array
				(
					'token' => 'author:id',
					'description' => 'The author ID',
					'argtype' => 'int',
					'converter' => function( $content )
					{
						return $content->author()->member_id;
					},
				),
				'Url' => array
				(
					'token' => 'url',
					'tokenValue' => function( $url ) { return (string) $url; },
					'description' => 'The url',
					'argtype' => 'object',
					'class' => '\IPS\Http\Url',
					'converter' => function( $content )
					{
						return $content->url();
					},
				),
			),
			'\IPS\Content\Item' => array
			(
				'Container' => array
				(
					'argtype' => 'object',
					'class' => '\IPS\Node\Model',
					'nullable' => TRUE,
					'converter' => function( $item )
					{
						return $item->containerWrapper();
					},
				),
				'Comment Count' => array
				(
					'token' => 'comments',
					'description' => 'Item comment count',
					'argtype' => 'int',
					'converter' => function( $item )
					{
						return (int) $item->mapped( 'num_comments' );
					},
				),
				'Last Post Time' => array
				(
					'token' => 'lastpost',
					'argtype' => 'object',
					'class' => '\IPS\DateTime',
					'converter' => function( $item )
					{
						return \IPS\DateTime::ts( max( $item->mapped( 'last_comment' ), $item->mapped( 'last_review' ) ) );
					},
				),
				'Views' => array
				(
					'token' => 'views',
					'description' => 'Item views count',
					'argtype' => 'int',
					'converter' => function( $item )
					{
						return (int) $item->mapped( 'views' );
					},
				),
				'Followers' => array
				(
					'argtype' => 'array',
					'class' => '\IPS\Member',
					'converter' => function( $item )
					{
						try
						{
							$members = array();
							foreach ( $item->followers( 3, array( 'none', 'immediate', 'daily', 'weekly' ), NULL ) as $follower )
							{
								try
								{
									$members[] = \IPS\Member::load( $follower[ 'follow_member_id' ] );
								}
								catch( \OutOfRangeException $e ) {}
							}
							return $members;
						}
						catch ( \BadMethodCallException $e )
						{
							return array();
						}
					},
				),
				'Author Followers' => array
				(
					'argtype' => 'array',
					'class' => '\IPS\Member',
					'converter' => function( $item )
					{
						$members = array();
						foreach ( $item->author()->followers( 3, array( 'immediate', 'daily', 'weekly' ), NULL ) as $follower )
						{
							try
							{
								$members[] = \IPS\Member::load( $follower[ 'follow_member_id' ] );
							}
							catch( \OutOfRangeException $e ) {}
						}
						return $members;
					},
				),
				'First Comment' => array
				(
					'argtype' => 'object',
					'class' => '\IPS\Content\Comment',
					'converter' => function( $item )
					{
						return $this->comments( 1, 0, 'date', 'asc', NULL, FALSE, NULL, NULL, TRUE );
					},
				),
				'Last Comment' => array
				(
					'argtype' => 'object',
					'class' => '\IPS\Content\Comment',
					'converter' => function( $item )
					{
						return $this->comments( 1, 0, 'date', 'desc', NULL, FALSE, NULL, NULL, TRUE );
					},					
				),
			),
			'\IPS\Node\Model' => array
			(
				'Parent' => array
				(
					'argtype' => 'object',
					'class' => '\IPS\Node\Model',
					'nullable' => TRUE,
					'converter' => function( $node )
					{
						return $node->parent();
					},
				),
				'Root Parent' => array
				(
					'argtype' => 'object',
					'class' => '\IPS\Node\Model',
					'nullable' => TRUE,
					'converter' => function( $node )
					{
						while( $parent = $node->parent() );
						return $parent;
					},
				),
				'Title' => array
				(
					'token' => 'title',
					'description' => 'The node title',
					'argtype' => 'string',
					'converter' => function( $node )
					{
						$title = $node->_title;
						\IPS\Lang::load( \IPS\Lang::defaultLanguage() )->parseOutputForDisplay( $title );						
						return $title;
					},
				),
				'Content Count' => array
				(
					'token' => 'items',
					'description' => 'Total items count',
					'argtype' => 'int',
					'converter' => function( $node )
					{
						return (int) $node->_items;
					},
				),
				'Node ID' => array
				(
					'token' => 'id',
					'description' => 'The node ID',
					'argtype' => 'int',
					'converter' => function( $node )
					{
						return $node->_id;
					},
				),
				'Url' => array
				(
					'token' => 'url',
					'tokenValue' => function( $url ) { return (string) $url; },
					'description' => 'The url',
					'argtype' => 'object',
					'class' => '\IPS\Http\Url',
					'converter' => function( $node )
					{
						return $node->url();
					},
				),
			),
			'\IPS\DateTime' => array
			(
				'Date/Time' => array
				(
					'token' => 'datetime',
					'description' => 'The formatted date/time',
					'argtype' => 'string',
					'converter' => function( $date )
					{
						return (string) $date;
					},
				),
				'Timestamp' => array
				(
					'token' => 'timestamp',
					'description' => 'The unix timestamp',
					'argtype' => 'int',
					'converter' => function( $date )
					{
						return $date->getTimestamp();
					},
				),
				'Year' => array
				(
					'token' => 'year',
					'description' => 'The full year',
					'argtype' => 'int',
					'converter' => function( $date )
					{
						return $date->format( 'Y' );
					},
				),
				'Month' => array
				(
					'token' => 'month',
					'description' => 'The month number',
					'argtype' => 'int',
					'converter' => function( $date )
					{
						return $date->format( 'n' );
					},
				),
				'Day' => array
				(
					'token' => 'day',
					'description' => 'The day of the month',
					'argtype' => 'int',
					'converter' => function( $date )
					{
						return $date->format( 'j' );
					},
				),
				'Hour' => array
				(
					'token' => 'hour',
					'description' => 'The hour of day',
					'argtype' => 'int',
					'converter' => function( $date )
					{
						return $date->format( 'G' );
					},
				),
				'Minute' => array
				(
					'token' => 'minute',
					'description' => 'The minute of hour',
					'argtype' => 'int',
					'converter' => function( $date )
					{
						return $date->format( 'i' );
					},
				),
			),
			'\IPS\Http\Url' => array
			(
				'Url' => array
				(
					'token' => 'url',
					'description' => 'The url address',
					'argtype' => 'string',
					'converter' => function( $url )
					{
						return (string) $url;
					},
				),
				'Params' => array
				(
					'argtype' => 'array',
					'converter' => function( $url )
					{
						return $url->queryString;
					},
				),
			),
			'\IPS\Settings' => array
			(
				'Site Name' => array
				(
					'token' => 'name',
					'description' => 'The site name',
					'argtype' => 'string',
					'converter' => function( $settings )
					{
						return $settings->board_name;
					},
				),
				'Site Url' => array
				(
					'token' => 'url',
					'description' => 'The site url',
					'argtype' => 'object',
					'class' => '\IPS\Http\Url',
					'converter' => function( $settings )
					{
						return new \IPS\Http\Url( $settings->base_url );
					},
					'tokenValue' => function( $url )
					{
						return (string) $url;
					},
				),
			),
		);
		
		$lang = \IPS\Member::loggedIn()->language();
		
		/**
		 * Content Item Conversions
		 */
		foreach ( \IPS\Application::allExtensions( 'core', 'ContentRouter' ) as $router )
		{
			foreach ( $router->classes as $contentItemClass )
			{
				if ( is_subclass_of( $contentItemClass, '\IPS\Content\Item' ) )
				{
					$contentTitle = ucwords( ( $lang->checkKeyExists( $contentItemClass::$title ) ? $lang->get( $contentItemClass::$title ) : $contentItemClass::$title ) );
					
					/**
					 * Add Converters For Content Item
					 */
					if ( isset ( $contentItemClass::$containerNodeClass ) )
					{
						$nodeClass = $contentItemClass::$containerNodeClass;
						$nodeTitle = ucwords( ( $lang->checkKeyExists( $nodeClass::$nodeTitle ) ? $lang->get( $nodeClass::$nodeTitle ) : $nodeClass::$nodeTitle ) );
						$node_type = rtrim( $nodeTitle, "s" );
						$map[ '\\' . ltrim( $contentItemClass, '\\' ) ][ $node_type ] = array
						(
							'argtype' => 'object',
							'class' => '\\' . ltrim( $nodeClass, '\\' ),
							'converter' => function( $item )
							{
								return $item->container();
							},
						);
					}

					/**
					 * Add Converters For Comments
					 */
					if ( isset ( $contentItemClass::$commentClass ) )
					{
						$commentClass = $contentItemClass::$commentClass;
						$map[ '\\' . ltrim( $commentClass, '\\' ) ][ $contentTitle ] = array
						(
							'argtype' => 'object',
							'class' => '\\' . ltrim( $contentItemClass, '\\' ),
							'converter' => function( $comment )
							{
								return $comment->item();
							},
						);
					}
					
					/**
					 * Add Converters For Reviews
					 */
					if ( isset ( $contentItemClass::$reviewClass ) )
					{
						$reviewClass = $contentItemClass::$reviewClass;
						$map[ '\\' . ltrim( $reviewClass, '\\' ) ][ $contentTitle ] = array
						(
							'argtype' => 'object',
							'class' => '\\' . ltrim( $contentItemClass, '\\' ),
							'converter' => function( $review )
							{
								return $review->item();
							},
						);					
					}
				}
			}
		}
		
		/**
		 * Custom Member Profile Fields
		 */
		
		/* Cheap way to make sure profile fields are in the data store */
		\IPS\core\ProfileFields\Field::fieldsForContentView();
		
		foreach ( \IPS\Data\Store::i()->profileFields[ 'fields' ] as $group_id => $fields )
		{
			foreach( $fields as $field_id => $fieldrow )
			{
				$field = \IPS\core\ProfileFields\Field::constructFromData( $fieldrow );
				
				/* resets */
				$argtype = 'mixed';
				$argclass = NULL;
				$token = 'field_' . $field_id;
				$tokenValue = NULL;
				$converter = function( $member ) use ( $field_id )
				{							
					return $member->rulesProfileData( $field_id );
				};
				
				switch( $field->type )
				{
					case 'Text':
					case 'Password':
					case 'Email':
					case 'Codemirror':
					case 'Tel':
					case 'TextArea':
					case 'Color':
					case 'Editor':
					case 'Radio':
					
						$argtype = 'string';
						break;
						
					case 'Checkbox':
					case 'YesNo':
					
						$argtype = 'bool';
						$converter = function( $member ) use ( $field_id )
						{
							if ( ( $fieldData = $member->rulesProfileData( $field_id ) ) !== NULL )
							{
								return (bool) $fieldData;
							}
						};
						$tokenValue = function( $bool )
						{
							return $bool ? "Yes" : "No";
						};
						break;
						
					case 'Date':
					
						$argtype = 'object';
						$argclass = '\IPS\DateTime';
						$converter = function( $member ) use ( $field_id )
						{
							if ( $fieldData = $member->rulesProfileData( $field_id ) )
							{
								return \IPS\DateTime::ts( $fieldData );
							}
						};
						break;
						
					case 'Number':
					case 'Rating':
					
						$argtype = 'float';
						break;
						
					case 'Address':
					
						$token = NULL;
						$argtype = 'object';
						$argclass = '\IPS\GeoLocation';
						$converter = function( $member ) use ( $field_id )
						{
							if ( $fieldData = $member->rulesProfileData( $field_id ) )
							{
								return \IPS\GeoLocation::buildFromJson( $fieldData );
							}
						};
						break;
						
					case 'Member':
					
						$argtype = 'object';
						$argclass = '\IPS\Member';
						$converter = function( $member ) use ( $field_id )
						{
							if ( $fieldData = $member->rulesProfileData( $field_id ) )
							{
								try
								{
									return \IPS\Member::load( $fieldData );
								}
								catch( \OutOfRangeException $e ) { }
							}
						};
						$tokenValue = function( $member )
						{
							return $member->name;
						};
						break;
						
					case 'Url':
					
						$argtype = 'object';
						$argclass = '\IPS\Http\Url';
						$converter = function( $member ) use ( $field_id )
						{
							if ( $fieldData = $member->rulesProfileData( $field_id ) )
							{
								return new \IPS\Http\Url( $fieldData );
							}
						};
						break;
						
					case 'Select':
					
						if ( $field->multiple )
						{
							$argtype = 'array';
							$converter = function( $member ) use ( $field_id )
							{
								if ( $fieldData = $member->rulesProfileData( $field_id ) )
								{
									$values = explode( ',', $fieldData );									
									return $values;
								}
							};
							$tokenValue = function( $array )
							{
								return implode( ', ', $array );
							};
						}
						else
						{
							$argtype = 'string';
							$converter = function( $member ) use ( $field_id )
							{
								return $member->rulesProfileData( $field_id );
							};
						}
						break;
						
					case 'CheckboxSet':

						$options = json_decode( $field->content, TRUE );
						if ( $field->multiple )
						{
							$argtype = 'array';
							$converter = function( $member ) use ( $field_id, $options )
							{
								if ( $fieldData = $member->rulesProfileData( $field_id ) )
								{
									$values = array();
									$_selections = explode( ',', $fieldData );
									foreach( $_selections as $_selection )
									{
										$values[ $_selection ] = $options[ $_selection ];
									}
									
									return $values;
								}
							};
							$tokenValue = function( $array )
							{
								return implode( ', ', $array );
							};
						}
						else
						{
							$argtype = 'string';
							$converter = function( $member ) use ( $field_id, $options )
							{
								if ( $fieldData = $member->rulesProfileData( $field_id ) )
								{
									return $options[ $fieldData ];
								}
							};
						}
						break;
					
				}
				
				$map[ '\IPS\Member' ][ 'core_pfield_' . $field->id ] = array
				(
					'token' => $token,
					'tokenValue' => $tokenValue,
					'argtype' => $argtype,
					'class' => $argclass,
					'description' => $field->_title . ' (profile field)',
					'converter' => $converter,
					'nullable' => TRUE,
				);
			}
		}		
		
		return $map;
	}
	
}