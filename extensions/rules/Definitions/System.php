<?php
/**
 * @brief		Rules extension: System
 * @package		Rules for IPS Social Suite
 * @since		21 Feb 2015
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
 * @brief	Rules definitions extension: System
 */
class _System
{

	/**
	 * @brief	Group events and actions in this extension 
	 */
	public $group = 'System';

	/**
	 * Triggerable Events
	 *
	 * @return 	array		Array of event definitions
	 */
	public function events()
	{
		$events = array
		(
			'record_created' => array
			( 
				'arguments' => array
				( 
					'record' => array( 'argtype' => 'object', 'class' => '\IPS\Patterns\ActiveRecord' ),
				),		
			),
			'record_being_updated' => array
			( 
				'arguments' => array
				( 
					'record' => array( 'argtype' => 'object', 'class' => '\IPS\Patterns\ActiveRecord' ),
					'changed' => array( 'argtype' => 'array' ),
				),		
			),
			'record_being_deleted' => array
			( 
				'arguments' => array
				( 
					'record' => array( 'argtype' => 'object', 'class' => '\IPS\Patterns\ActiveRecord' ),
				),		
			),
			'browser_output' => array
			(
				'arguments' => array
				(
					'output' => array( 'argtype' => 'string' ),
					'status' => array( 'argtype' => 'int' ),
					'type' => array( 'argtype' => 'string' ),
					'headers' => array( 'argtype' => 'array' ),
					'docache' => array( 'argtype' => 'bool' ),
					'iscache' => array( 'argtype' => 'bool'	),
				),
			),
		);
		
		return $events;
	}
	
	/**
	 * Operational Conditions
	 */
	public function conditions()
	{
		$conditions = array
		(
			'execute_php' => array
			(
				'configuration' => array
				(
					'form' => function( &$form, $values, $operation )
					{
						\IPS\Member::loggedIn()->language()->words[ 'rules_System_custom_phpcode_desc' ] = \IPS\Member::loggedIn()->language()->get( 'phpcode_desc_details_vars' ) . \IPS\rules\Application::eventArgInfo( $operation->event() );
						$form->add( new \IPS\Helpers\Form\Codemirror( 'rules_System_custom_phpcode', $values[ 'custom_phpcode' ] ?: "//<?php\n\nreturn TRUE;", FALSE, array( 'mode' => 'php' ), NULL, NULL, NULL, 'rules_System_custom_phpcode' ) );
					},
				),
				'callback' 	=> array( $this, 'executePHP' ),
			),
			'board_status' => array
			(
				'configuration' => array
				(
					'form' => function( $form, $values, $operation )
					{
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_System_board_online_status', $values[ 'rules_System_board_online_status' ], TRUE, array( 'options' => array( 'online' => 'Online', 'offline' => 'Offline' ) ) ) );
					},
				),
				'callback' => array( $this, 'checkBoardStatus' ),
			),
		);
		
		return $conditions;
	}
	
	/**
	 * Operational Actions
	 *
	 * @return 	array		Array of action definitions
	 */
	public function actions()
	{
		$actions = array
		(
			'send_email' => array
			(
				'configuration' => array
				(
					'form' => function( &$form, $values, $action )
					{
						//$form->add( new \IPS\Helpers\Form\YesNo( 'use_html', $values[ 'use_html' ], FALSE ) );
					},
				),
				
				'callback' 	=> array( $this, 'sendEmail' ),
				
				'arguments'	=> array
				(
					'recipients' => array
					(
						'configuration' => array
						(
							'form' => function( $form, $values, $action ) 
							{
								$members = array();
								foreach( (array) $values[ 'rules_System_member_recipients' ] as $member_id )
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
								
								$form->add( new \IPS\Helpers\Form\Member( 'rules_System_member_recipients', $members, TRUE, array( 'multiple' => NULL ), NULL, NULL, NULL, 'rules_System_member_recipients' ) );
								
								return array( 'rules_System_member_recipients' );
							},
							'saveValues' => function( &$values, $action ) 
							{
								$_members = array();
								
								foreach( (array) $values[ 'rules_System_member_recipients' ] as $member )
								{
									$_members[] = $member->member_id;
								}
								
								$values[ 'rules_System_member_recipients' ] = $_members;
							},
							'getArg' => function( $values, $action )
							{
								$members = array();
								foreach( (array) $values[ 'rules_System_member_recipients' ] as $member_id )
								{
									try { $members[] = \IPS\Member::load( $member_id ); }
									catch( \Exception $e ) {}
								}
								return $members;
							},
						),
						'argtypes' => \IPS\rules\Application::getPreset( 'members' ),
						'required'	=> TRUE,
					),
					'message' => array
					(
						'configuration' => array
						(
							'form' => function( $form, $values, $action ) 
							{
								$form->add( new \IPS\Helpers\Form\Codemirror( 'rules_System_email_message', $values[ 'rules_System_email_message' ], FALSE, array(), NULL, NULL, NULL, 'rules_System_email_message' ) );
								return array( 'rules_System_email_message' );
							},
							'saveValues' => function( &$values, $action ) 
							{
							
							},
							'getArg' => function ( $values, $action )
							{
								return $values[ 'rules_System_email_message' ];
							},
						),
						'argtypes' => array( 
							'string' => array(
								'description' => 'The formatted message to send',
							),
						),
						'required' => TRUE,
					),
				),
			),
			'send_notification' => array
			(

			),
			'send_pm' => array
			(
			
			),
			'display_message' => array
			(
				'callback'	=> array( $this, 'displayMessage' ),
				'arguments' 	=> array
				(
					'message' => array
					(
						'configuration' => array
						(
							'form' => function( $form, $values, $action )
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_System_inline_message', $values[ 'rules_System_inline_message' ], FALSE, array(), NULL, NULL, NULL, 'rules_System_inline_message' ) );
								return array( 'rules_System_inline_message' );
							},
							'getArg' => function( $values, $action )
							{
								return $values[ 'rules_System_inline_message' ];
							},
						),
						'required' => TRUE,
						'argtypes' => array
						(
							'string' => array
							(
								'description' => "Message to display to user",
							),							
						),
					),
				),
			),
			'url_redirect' => array
			(
				'callback'	=> array( $this, 'urlRedirect' ),
				'arguments' 	=> array
				(
					'url' => array
					(
						'configuration' => array
						(
							'form' => function( $form, $values, $action )
							{
								$form->add( new \IPS\Helpers\Form\Url( 'rules_System_redirect_url', $values[ 'rules_System_redirect_url' ], TRUE, array(), NULL, NULL, NULL, 'rules_System_redirect_url' ) );
								return array( 'rules_System_redirect_url' );
							},
							'getArg' => function( $values, $action )
							{
								return $values[ 'rules_System_redirect_url' ];
							},
						),
						'required' => TRUE,
						'argtypes' => array
						(
							'string' => array
							(
								'description' => "The url to redirect to",
							),
							'object' => array
							(
								'description' => "An IPS Suite url object",
								'class' => '\IPS\Http\Url',
							),
						),
					),
					'message' => array
					(
						'configuration' => array
						(
							'form' => function( $form, $values, $action )
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_System_redirect_message', $values[ 'rules_System_redirect_message' ], FALSE, array(), NULL, NULL, NULL, 'rules_System_redirect_message' ) );
								return array( 'rules_System_redirect_message' );
							},
							'getArg' => function( $values, $action )
							{
								return $values[ 'rules_System_redirect_message' ];
							},
						),
						'required' => TRUE,
						'argtypes' => array
						(
							'string' => array
							(
								'description' => "Message to display after redirect",
							),
						),
					),
				),
			),
			'execute_php' => array
			(
				'configuration' => array
				(
					'form' => function( &$form, $values, $operation )
					{
						\IPS\Member::loggedIn()->language()->words[ 'rules_System_custom_phpcode_desc' ] = \IPS\Member::loggedIn()->language()->get( 'phpcode_desc_details_vars' ) . \IPS\rules\Application::eventArgInfo( $operation->event() );
						$form->add( new \IPS\Helpers\Form\Codemirror( 'rules_System_custom_phpcode', $values[ 'custom_phpcode' ] ?: "//<?php\n\nreturn TRUE;", FALSE, array( 'mode' => 'php' ), NULL, NULL, NULL, 'rules_System_custom_phpcode' ) );
					},
				),
				'callback' 	=> array( $this, 'executePHP' ),
			),
		);
		
		return $actions;
	}
	
	
	/*** ACTIONS ***/
	
	/**
	 * Send Email Callback
	 */
	public function sendEmail( $recipients, $message, $values )
	{
		return 'email not implemented';
	}
	
	/**
	 * Execute PHP Code
	 */
	public function executePHP( $values, $arg_map, $operation )
	{
		$evaluate = function( $phpcode ) use ( $arg_map, $operation )
		{
			extract( $arg_map );								
			return @eval( $phpcode );
		};
		
		return $evaluate( $values[ 'custom_phpcode' ] );
	}
	
	/**
	 * Display Inline Message
	 */
	public function displayMessage( $message )
	{
		$_SESSION[ 'inlineMessage' ] = $message;
		return 'message set';
	}
	
	/**
	 * Redirect to URL
	 */
	public function urlRedirect( $url, $message )
	{
		if ( $url )
		{
			\IPS\Output::i()->redirect( $url, $message );
		}
	}
	
	/*** CONDITIONS ***/
	
	/**
	 * Check Board Status (Online/Offline)
	 */
	public function checkBoardStatus( $values )
	{
		switch ( $values[ 'rules_System_board_online_status' ] )
		{
			case 'online':
				return \IPS\Settings::i()->site_online;
				break;
			case 'offline':
				return ! \IPS\Settings::i()->site_online;
				break;
			default:
				return FALSE;
		}
	}
	
	
}