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
	public $defaultGroup = 'Global';

	/**
	 * Triggerable Events
	 *
	 * @return 	array		Array of event definitions
	 */
	public function events()
	{
		$events = array
		(
			'record_updated' => array
			( 
				'arguments' => array
				( 
					'record' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Patterns\ActiveRecord' ),
					'changed' 	=> array( 'argtype' => 'array' ),
					'new' 		=> array( 'argtype' => 'bool' ),
				),		
			),
			'record_copied' => array
			( 
				'arguments' => array
				( 
					'old_record' => array( 'argtype' => 'object', 'class' => '\IPS\Patterns\ActiveRecord' ),
					'new_record' => array( 'argtype' => 'object', 'class' => '\IPS\Patterns\ActiveRecord' ),
				),		
			),
			'record_deleted' => array
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
			'compare_numbers' => array
			(
				'callback' 	=> array( $this, 'compareNumbers' ),
				'configuration' => array
				(
					'form' => function( $form, $values, $condition )
					{
						$compare_options = array(
							'>' 	=> 'Number 1 is greater than Number 2',
							'<' 	=> 'Number 1 is less than Number 2',
							'=='	=> 'Number 1 is equal to Number 2',
							'!='	=> 'Number 1 is not equal to Number 2',
							'>='	=> 'Number 1 is greater than or equal to Number 2',
							'<='	=> 'Number 1 is less than or equal to Number 2'
						);
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Comparisons_type', isset( $values[ 'rules_Comparisons_type' ] ) ? $values[ 'rules_Comparisons_type' ] : NULL, TRUE, array( 'options' => $compare_options ), NULL, NULL, NULL, 'rules_Comparisons_type' ) );
					},
				),
				'arguments'	=> array
				(
					'number1' => array
					(
						'argtypes' => array
						(
							'int' 	=> array( 'description' => 'a value to use as number 1' ),
							'float' => array( 'description' => 'a value to use as number 1' ),
						),				
						'required'	=> TRUE,
						'configuration' => array
						(
							'form'		=> function( $form, $values, $condition ) 
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Comparisons_number1', isset( $values[ 'rules_Comparisons_number1' ] ) ? $values[ 'rules_Comparisons_number1' ] : NULL, TRUE, array(), NULL, NULL, NULL, 'rules_Comparisons_number1' ) );
								return array( 'rules_Comparisons_number1' );
							},
							'saveValues' => function( &$values, $condition )
							{
								settype( $values[ 'rules_Comparisons_number1' ], 'float' ); 
							},
							'getArg'	=> function( $values, $condition )
							{
								return $values[ 'rules_Comparisons_number1' ];
							},
						),
					),
					'number2' => array
					(
						'default' => 'manual',
						'argtypes' => array
						(
							'int' 	=> array( 'description' => 'a value to use as number 2' ),
							'float' => array( 'description' => 'a value to use as number 2' ),
						),				
						'required'	=> TRUE,
						'configuration' => array
						(
							'form'		=> function( $form, $values, $condition ) 
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Comparisons_number2', isset( $values[ 'rules_Comparisons_number2' ] ) ? $values[ 'rules_Comparisons_number2' ] : NULL, TRUE, array(), NULL, NULL, NULL, 'rules_Comparisons_number2' ) );
								return array( 'rules_Comparisons_number2' );
							},
							'saveValues' => function( &$values, $condition )
							{
								settype( $values[ 'rules_Comparisons_number2' ], 'float' ); 
							},
							'getArg'	=> function( $values, $condition )
							{
								return $values[ 'rules_Comparisons_number2' ];
							},
						),
					),	
				),				
			),
			'compare_strings' => array
			(
				'callback' 	=> array( $this, 'compareStrings' ),
				'configuration' => array
				(
					'form' => function( $form, $values, $condition )
					{
						$compare_options = array(
							'contains' 	=> 'String 1 contains String 2',
							'startswith'	=> 'String 1 starts with String 2',
							'endswith'	=> 'String 1 ends with String 2',
							'equals'	=> 'String 1 is the same as String 2',
						);
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Comparisons_type', isset( $values[ 'rules_Comparisons_type' ] ) ? $values[ 'rules_Comparisons_type' ] : NULL, TRUE, array( 'options' => $compare_options ), NULL, NULL, NULL, 'rules_Comparisons_type' ) );
					},
				),
				'arguments'	=> array
				(
					'string1' => array
					(
						'argtypes' => array
						(
							'string' => array( 'description' => 'the value to use as string 1' ),
						),				
						'required'	=> TRUE,
						'configuration' => array
						(
							'form' => function( $form, $values, $condition ) 
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Comparisons_string1', isset( $values[ 'rules_Comparisons_string1' ] ) ? $values[ 'rules_Comparisons_string1' ] : NULL, TRUE, array(), NULL, NULL, NULL, 'rules_Comparisons_string1' ) );
								return array( 'rules_Comparisons_string1' );
							},
							'getArg' => function( $values, $condition )
							{
								return $values[ 'rules_Comparisons_string1' ];
							},
						),
					),	
					'string2' => array
					(
						'default' => 'manual',
						'argtypes' => array
						(
							'string' => array( 'description' => 'the value to use as string 2' ),
						),				
						'required'	=> TRUE,
						'configuration' => array
						(
							'form' => function( $form, $values, $condition ) 
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Comparisons_string2', isset( $values[ 'rules_Comparisons_string2' ] ) ? $values[ 'rules_Comparisons_string2' ] : NULL, TRUE, array(), NULL, NULL, NULL, 'rules_Comparisons_string2' ) );
								return array( 'rules_Comparisons_string2' );
							},
							'getArg' => function( $values, $condition )
							{
								return $values[ 'rules_Comparisons_string2' ];
							},
						),
					),	
				),				
			),
			'compare_truth' => array
			(
				'callback' 	=> array( $this, 'compareTruth' ),
				'configuration' => array
				(
					'form' => function( $form, $values, $condition )
					{
						$compare_options = array
						(
							'true' 		=> 'Value is TRUE',
							'false'		=> 'Value is FALSE',
							'truthy'	=> 'Value is TRUE or equivalent to TRUE (any non-empty string/array, number not 0)',
							'falsey'	=> 'Value is FALSE or equivalent to FALSE (including NULL, 0, empty string/array)',
							'null'		=> 'Value is NULL',
							'notnull'	=> 'Value is NOT NULL',
						);
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Comparisons_type', isset( $values[ 'rules_Comparisons_type' ] ) ? $values[ 'rules_Comparisons_type' ] : NULL, TRUE, array( 'options' => $compare_options ), NULL, NULL, NULL, 'rules_Comparisons_type' ) );
					},
				),
				'arguments'	=> array
				(
					'value' => array
					(
						'argtypes' => array
						(
							'mixed' => array( 'description' => 'the value to compare' ),
						),				
						'required'	=> FALSE,
						'configuration' => array
						(
							'form' => function( $form, $values, $condition ) 
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Comparisons_value', isset( $values[ 'rules_Comparisons_value' ] ) ? $values[ 'rules_Comparisons_value' ] : NULL, TRUE, array(), NULL, NULL, NULL, 'rules_Comparisons_value' ) );
								return array( 'rules_Comparisons_value' );
							},
							'getArg' => function( $values, $condition )
							{
								return $values[ 'rules_Comparisons_value' ];
							},
						),
					),
				),
			),
			'compare_type' => array
			(
				'callback' 	=> array( $this, 'compareType' ),
				
				'configuration' => array
				(
					'form' => function( $form, $values, $condition )
					{
						$compare_options = array
						(
							'boolean'	=> 'Value is a Boolean (TRUE/FALSE)',
							'string' 	=> 'Value is a String',
							'integer'	=> 'Value is a Integer',
							'double'	=> 'Value is a Float (Decimal)',
							'array'		=> 'Value is an Array',
							'object'	=> 'Value is an Object',
							'NULL'		=> 'Value is NULL',
						);
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Comparisons_type', isset( $values[ 'rules_Comparisons_type' ] ) ? $values[ 'rules_Comparisons_type' ] : NULL, TRUE, array( 'options' => $compare_options ), NULL, NULL, NULL, 'rules_Comparisons_type' ) );
					},
				),
				'arguments'	=> array
				(
					'value' => array
					(
						'argtypes' => array
						(
							'mixed' => array( 'description' => 'the value to compare type' ),
						),				
						'required'	=> TRUE,
						'configuration' => array
						(
							'form' => function( $form, $values, $condition ) 
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Comparisons_value', isset( $values[ 'rules_Comparisons_value' ] ) ? $values[ 'rules_Comparisons_value' ] : NULL, TRUE, array(), NULL, NULL, NULL, 'rules_Comparisons_value' ) );
								return array( 'rules_Comparisons_value' );
							},
							'getArg' => function( $values, $condition )
							{
								return $values[ 'rules_Comparisons_value' ];
							},
						),
					),
				),				
			),
			'compare_array' => array
			(
				'callback' 	=> array( $this, 'compareArray' ),
				
				'configuration' => array
				(
					'form' => function( $form, $values, $condition )
					{
						$compare_options = array
						(
							'lengthgreater'	=> 'Array length is greater than',
							'lengthless' 	=> 'Array length is less than',
							'lengthequal'	=> 'Array length is equal to',
							'containskey'	=> 'Array contains a specific key',
							'containsvalue' => 'Array contains a specific value',
						);
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Comparisons_type', isset( $values[ 'rules_Comparisons_type' ] ) ? $values[ 'rules_Comparisons_type' ] : NULL, TRUE, array( 'options' => $compare_options ), NULL, NULL, NULL, 'rules_Comparisons_type' ) );
					},
				),
				'arguments' => array
				(
					'array' => array
					(
						'argtypes' => array
						(
							'array' 	=> array( 'description' => 'an array to compare' ),
						),				
						'required'	=> TRUE,
					),
					'value' => array
					(
						'default' => 'manual',
						'argtypes' => array
						(
							'mixed' => array( 'description' => 'a value to compare array with' ),
						),				
						'required'	=> TRUE,
						'configuration' => array
						(
							'form' => function( $form, $values, $condition ) 
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Comparisons_value', isset( $values[ 'rules_Comparisons_value' ] ) ? $values[ 'rules_Comparisons_value' ] : NULL, TRUE, array(), NULL, NULL, NULL, 'rules_Comparisons_value' ) );
								return array( 'rules_Comparisons_value' );
							},
							'getArg' => function( $values, $condition )
							{
								return $values[ 'rules_Comparisons_value' ];
							},
						),
					),	
				),				
			),
			'compare_dates' => array
			(
				'callback' 	=> array( $this, 'compareDates' ),
				'configuration' => array
				(
					'form' => function( $form, $values, $condition )
					{
						$date_compare_options = array
						(
							'<' => 'Date 1 is before Date 2',
							'>' => 'Date 1 is after Date 2',
							'=' => 'Date 1 and Date 2 are on the same day',
							'?' => 'Date 1 and Date 2 are within a certain amount of time of each other',
						);
						
						$date_toggles = array
						(
							'?' => array( 'rules_Content_attribute_compare_minutes', 'rules_Content_attribute_compare_hours', 'rules_Content_attribute_compare_days', 'rules_Content_attribute_compare_months', 'rules_Content_attribute_compare_years' ),
						);
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Comparisons_type', isset( $values[ 'rules_Comparisons_type' ] ) ? $values[ 'rules_Comparisons_type' ] : NULL, TRUE, array( 'options' => $date_compare_options, 'toggles' => $date_toggles ), NULL, NULL, NULL, 'rules_Comparisons_type' ) );
						$form->add( new \IPS\Helpers\Form\Number( 'rules_Content_attribute_compare_minutes', isset( $values[ 'rules_Content_attribute_compare_minutes' ] ) ? $values[ 'rules_Content_attribute_compare_minutes' ] : 0, TRUE, array(), NULL, NULL, NULL, 'rules_Content_attribute_compare_minutes' ) );
						$form->add( new \IPS\Helpers\Form\Number( 'rules_Content_attribute_compare_hours', isset( $values[ 'rules_Content_attribute_compare_hours' ] ) ? $values[ 'rules_Content_attribute_compare_hours' ] : 0, TRUE, array(), NULL, NULL, NULL, 'rules_Content_attribute_compare_hours' ) );
						$form->add( new \IPS\Helpers\Form\Number( 'rules_Content_attribute_compare_days', isset( $values[ 'rules_Content_attribute_compare_days' ] ) ? $values[ 'rules_Content_attribute_compare_days' ] : 0, TRUE, array(), NULL, NULL, NULL, 'rules_Content_attribute_compare_days' ) );
						$form->add( new \IPS\Helpers\Form\Number( 'rules_Content_attribute_compare_months', isset( $values[ 'rules_Content_attribute_compare_months' ] ) ? $values[ 'rules_Content_attribute_compare_months' ] : 0, TRUE, array(), NULL, NULL, NULL, 'rules_Content_attribute_compare_months' ) );
						$form->add( new \IPS\Helpers\Form\Number( 'rules_Content_attribute_compare_years', isset( $values[ 'rules_Content_attribute_compare_years' ] ) ? $values[ 'rules_Content_attribute_compare_years' ] : 0, TRUE, array(), NULL, NULL, NULL, 'rules_Content_attribute_compare_years' ) );
					},
				),
				'arguments'	=> array
				(
					'date1' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'date' ),		
						'configuration' => \IPS\rules\Application::configPreset( 'date', 'rules_Comparisons_date1', TRUE, array( 'time' => TRUE ) ),
						'required'	=> TRUE,
					),	
					'date2' => array
					(
						'default'	=> 'manual',
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'date' ),		
						'configuration' => \IPS\rules\Application::configPreset( 'date', 'rules_Comparisons_date2', TRUE, array( 'time' => TRUE ) ),
						'required'	=> TRUE,
					),
				),
			),
			'compare_objects' => array
			(
				'callback' 	=> array( $this, 'compareObjects' ),
				
				'configuration' => array
				(
					'form' => function( $form, $values, $condition )
					{
						$compare_options = array(
							'isclass' 	=> 'Object has the same class as Value',
							'issubclass'	=> 'Object is a subclass of Value',
							'equal' 	=> 'Object and Value are the same object',
						);
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Comparisons_type', isset( $values[ 'rules_Comparisons_type' ] ) ? $values[ 'rules_Comparisons_type' ] : NULL, TRUE, array( 'options' => $compare_options ), NULL, NULL, NULL, 'rules_Comparisons_type' ) );
					},
				),
				
				'arguments' => array
				(
					'object' => array
					(
						'argtypes' => array
						(
							'object' 	=> array( 'description' => 'the object to compare' ),
						),				
						'required'	=> TRUE,
					),
					'value' => array
					(
						'default' => 'manual',
						'argtypes' => array
						(
							'string' => array( 'description' => 'a classname to compare' ),
							'object' => array( 'description' => 'an object to compare' ),
						),				
						'required'	=> TRUE,
						'configuration' => array
						(
							'form' => function( $form, $values, $condition ) 
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Comparisons_value', isset( $values[ 'rules_Comparisons_value' ] ) ? $values[ 'rules_Comparisons_value' ] : NULL, TRUE, array(), NULL, NULL, NULL, 'rules_Comparisons_value' ) );
								\IPS\Member::loggedIn()->language()->words[ 'rules_Comparisons_value_desc' ] = "Enter an object classname";
								return array( 'rules_Comparisons_value' );
							},
							'getArg' => function( $values, $condition )
							{
								return $values[ 'rules_Comparisons_value' ];
							},
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
						$form->add( new \IPS\Helpers\Form\Codemirror( 'rules_System_custom_phpcode', isset( $values[ 'rules_System_custom_phpcode' ] ) ? $values[ 'rules_System_custom_phpcode' ] : "//<?php\n\nreturn TRUE;", FALSE, array( 'mode' => 'php' ), NULL, NULL, NULL, 'rules_System_custom_phpcode' ) );
					},
				),
				'callback' 	=> array( $this, 'executePHP' ),
			),
			'scheduled_action' => array
			(
				'callback' 	=> array( $this, 'checkActionSchedule' ),
				'configuration' => array
				(
					'form' => function( $form, $values )
					{
						$schedule_options = array
						(
							'exact' 	=> 'Exact',
							'contains' 	=> 'Contains',
						);
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_System_schedule_mode', isset( $values[ 'rules_System_schedule_mode' ] ) ? $values[ 'rules_System_schedule_mode' ] : 'exact', TRUE, array( 'options' => $schedule_options ) ) );
					},
				),
				'arguments' 	=> array
				(
					'key' => array
					(
						'default' => 'manual',
						'configuration' => array
						(
							'form' => function( $form, $values, $action )
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_System_schedule_key', isset( $values[ 'rules_System_schedule_key' ] ) ? $values[ 'rules_System_schedule_key' ] : NULL, FALSE, array(), NULL, NULL, NULL, 'rules_System_schedule_key' ) );
								return array( 'rules_System_schedule_key' );
							},
							'getArg' => function( $values, $action )
							{
								return $values[ 'rules_System_schedule_key' ];
							},
						),
						'argtypes' => array
						(
							'string' => array
							(
								'description' => "Unschedule actions assigned to this keyphrase",
							),
						),
					),
				),
			),
			'board_status' => array
			(
				'configuration' => array
				(
					'form' => function( $form, $values, $operation )
					{
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_System_board_online_status', isset( $values[ 'rules_System_board_online_status' ] ) ? $values[ 'rules_System_board_online_status' ] : NULL, TRUE, array( 'options' => array( 'online' => 'Online', 'offline' => 'Offline' ) ) ) );
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
			'set_api_response' => array
			(
				'callback' => array( $this, 'setApiResponse' ),
				'configuration' => array
				(
					'form' => function( &$form, $values, $operation )
					{
						$response_types = array(
							'int' => 'int',
							'string' => 'string',
							'float' => 'float',
							'datetime' => 'datetime',
							'bool' => 'bool',
							'object' => 'object',
						);				
					
						$form->add( new \IPS\Helpers\Form\Text( 'rules_api_response_key', $values[ 'rules_api_response_key' ], TRUE, array(), NULL, NULL, NULL, 'rules_api_response_key' ) );
						$form->add( new \IPS\Helpers\Form\Text( 'rules_api_response_description', $values[ 'rules_api_response_description' ], FALSE, array(), NULL, NULL, NULL, 'rules_api_response_description' ) );
						$form->add( new \IPS\Helpers\Form\Select( 'rules_api_response_type', $values[ 'rules_api_response_type' ], TRUE, array( 'options' => $response_types ) ) );
					},
				),
				'arguments' => array
				(
					'value' => array
					( 
						'argtypes' => array( 'mixed' ),
						'configuration' => array
						(
							'form' => function( $form, $values )
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_System_api_response_value', isset( $values[ 'rules_System_api_response_value' ] ) ? $values[ 'rules_System_api_response_value' ] : NULL, TRUE, array(), NULL, NULL, NULL, 'rules_System_api_response_value' ) );
								return array( 'rules_System_api_response_value' );
							},
							'getArg' => function( $values )
							{
								return $values[ 'rules_System_api_response_value' ];
							},
						),
					),
				),
			),
			'send_email' => array
			(
				'callback' 	=> array( $this, 'sendEmail' ),	
				'arguments'	=> array
				(
					'recipients' => array
					(
						'argtypes' => \IPS\rules\Application::argPreset( 'members' ),
						'configuration'	=> \IPS\rules\Application::configPreset( 'members', 'rules_choose_members' ),
						'required'	=> TRUE,
					),
					'bcc_recipients' => array
					(
						'default' => 'manual',
						'argtypes' => \IPS\rules\Application::argPreset( 'members' ),
						'configuration'	=> \IPS\rules\Application::configPreset( 'members', 'rules_choose_members2', FALSE ),
						'required'	=> FALSE,
					),
					'subject' => array
					(
						'default' => 'manual',
						'argtypes' => array( 'string' ),
						'configuration' => array
						(
							'form' => function( $form, $values )
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_System_message_subject', isset( $values[ 'rules_System_message_subject' ] ) ? $values[ 'rules_System_message_subject' ] : NULL, TRUE, array(), NULL, NULL, NULL, 'rules_System_message_subject' ) );
								return array( 'rules_System_message_subject' );
							},
							'getArg' => function( $values )
							{
								return $values[ 'rules_System_message_subject' ];
							},
						),
						'required' => TRUE,
					),
					'message' => array
					(
						'default' => 'manual',
						'configuration' => array
						(
							'form' => function( $form, $values, $action ) 
							{
								$form->add( new \IPS\Helpers\Form\Editor( 'rules_System_email_message', isset( $values[ 'rules_System_email_message' ] ) ? $values[ 'rules_System_email_message' ] : NULL, FALSE, array( 'app' => 'rules', 'key' => 'Generic' ) ) );
								return array( $form->id . '_rules_System_email_message' );
							},
							'saveValues' => function( &$values, $action ) 
							{
							
							},
							'getArg' => function ( $values, $action )
							{
								return $values[ 'rules_System_email_message' ];
							},
						),
						'argtypes' => array
						( 
							'string' => array
							(
								'description' => 'The formatted message to send. HTML is allowed.',
							),
						),
						'required' => TRUE,
					),
				),
			),
			'create_conversation' => array
			(
				'callback' 	=> array( $this, 'createConversation' ),	
				'configuration' => array
				(
					'form' => function( &$form, $values, $operation )
					{
						$participation_modes = array
						(
							0 => 'rules_participation_all',
							1 => 'rules_participation_individual',
						);
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_participation_mode', isset( $values[ 'rules_participation_mode' ] ) ? (int) $values[ 'rules_participation_mode' ] : 0, TRUE, array( 'options' => $participation_modes ), NULL, NULL, NULL, 'rules_participation_mode' ) );
						$form->add( new \IPS\Helpers\Form\YesNo( 'rules_conversation_join_creator', isset( $values[ 'rules_conversation_join_creator' ] ) ? $values[ 'rules_conversation_join_creator' ] : TRUE, TRUE ) );
					},
				),
				'arguments'	=> array
				(
					'creator' => array
					(
						'argtypes' => \IPS\rules\Application::argPreset( 'member' ),
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required'	=> TRUE,
					),
					'participants' => array
					(
						'argtypes' => \IPS\rules\Application::argPreset( 'members' ),
						'configuration'	=> \IPS\rules\Application::configPreset( 'members', 'rules_choose_members' ),
						'required'	=> TRUE,
					),
					'subject' => array
					(
						'default' => 'manual',
						'argtypes' => array( 'string' ),
						'configuration' => array
						(
							'form' => function( $form, $values )
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_System_message_subject', isset( $values[ 'rules_System_message_subject' ] ) ? $values[ 'rules_System_message_subject' ] : NULL, TRUE, array(), NULL, NULL, NULL, 'rules_System_message_subject' ) );
								return array( 'rules_System_message_subject' );
							},
							'getArg' => function( $values )
							{
								return $values[ 'rules_System_message_subject' ];
							},
						),
						'required' => TRUE,
					),
					'message' => array
					(
						'default' => 'manual',
						'configuration' => array
						(
							'form' => function( $form, $values, $action ) 
							{
								$form->add( new \IPS\Helpers\Form\Editor( 'rules_System_message_body', isset( $values[ 'rules_System_message_body' ] ) ? $values[ 'rules_System_message_body' ] : NULL, TRUE, array( 'app' => 'rules', 'key' => 'Generic' ) ) );
								return array( $form->id . '_rules_System_message_body' );
							},
							'saveValues' => function( &$values, $action ) 
							{
							
							},
							'getArg' => function ( $values, $action )
							{
								return $values[ 'rules_System_message_body' ];
							},
						),
						'argtypes' => array
						( 
							'string' => array
							(
								'description' => 'The message content',
							),
						),
						'required' => TRUE,
					),
				),
			),
			'create_notification' => array
			(
				'callback' 	=> array( $this, 'createInlineNotifications' ),	
				'arguments'	=> array
				(
					'recipients' => array
					(
						'argtypes' => \IPS\rules\Application::argPreset( 'members' ),
						'configuration'	=> \IPS\rules\Application::configPreset( 'members', 'rules_choose_members' ),
						'required'	=> TRUE,
					),
					'title' => array
					(
						'default' => 'manual',
						'argtypes' => array( 'string' => array( 'description' => 'Notification title' ) ),
						'configuration' => array
						(
							'form' => function( $form, $values )
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_System_message_subject', isset( $values[ 'rules_System_message_subject' ] ) ? $values[ 'rules_System_message_subject' ] : NULL, TRUE, array(), NULL, NULL, NULL, 'rules_System_message_subject' ) );
								return array( 'rules_System_message_subject' );
							},
							'getArg' => function( $values )
							{
								return $values[ 'rules_System_message_subject' ];
							},
						),
						'required' => TRUE,
					),
					'url' => array
					(
						'argtypes' => \IPS\rules\Application::argPreset( 'url' ),
						'configuration' => \IPS\rules\Application::configPreset( 'url', 'rules_System_url', FALSE ),
						'required' => FALSE,
					),
					'content' => array
					(
						'default' => 'manual',
						'configuration' => array
						(
							'form' => function( $form, $values, $action ) 
							{
								$form->add( new \IPS\Helpers\Form\Editor( 'rules_System_message_body', isset( $values[ 'rules_System_message_body' ] ) ? $values[ 'rules_System_message_body' ] : NULL, TRUE, array( 'app' => 'rules', 'key' => 'Generic' ) ) );
								return array( $form->id . '_rules_System_message_body' );
							},
							'saveValues' => function( &$values, $action ) 
							{
							
							},
							'getArg' => function ( $values, $action )
							{
								return $values[ 'rules_System_message_body' ];
							},
						),
						'argtypes' => array
						( 
							'string' => array
							(
								'description' => 'The message content',
							),
						),
						'required' => FALSE,
					),
					'author' => array
					(
						'default' 	=> 'manual',
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ),
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member', FALSE ),
						'required'	=> FALSE,
					),
				),
			),			
			'display_message' => array
			(
				'callback'	=> array( $this, 'displayMessage' ),
				'arguments' 	=> array
				(
					'message' => array
					(
						'default' => 'manual',
						'configuration' => array
						(
							'form' => function( $form, $values, $action )
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_System_inline_message', isset( $values[ 'rules_System_inline_message' ] ) ? $values[ 'rules_System_inline_message' ] : NULL, FALSE, array(), NULL, NULL, NULL, 'rules_System_inline_message' ) );
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
						'argtypes' => \IPS\rules\Application::argPreset( 'url' ),
						'configuration' => \IPS\rules\Application::configPreset( 'url', 'rules_System_url', TRUE ),
						'required' => TRUE,
					),
					'message' => array
					(
						'argtypes' => array
						(
							'string' => array
							(
								'description' => "Message to display after redirect",
							),
						),
						'configuration' => array
						(
							'form' => function( $form, $values, $action )
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_System_redirect_message', isset( $values[ 'rules_System_redirect_message' ] ) ? $values[ 'rules_System_redirect_message' ] : NULL, FALSE, array(), NULL, NULL, NULL, 'rules_System_redirect_message' ) );
								return array( 'rules_System_redirect_message' );
							},
							'getArg' => function( $values, $action )
							{
								return $values[ 'rules_System_redirect_message' ];
							},
						),
						'required' => FALSE,
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
						$form->add( new \IPS\Helpers\Form\Codemirror( 'rules_System_custom_phpcode', isset( $values[ 'rules_System_custom_phpcode' ] ) ? $values[ 'rules_System_custom_phpcode' ] : "//<?php\n\nreturn \"action complete\";", FALSE, array( 'mode' => 'php' ), NULL, NULL, NULL, 'rules_System_custom_phpcode' ) );
					},
				),
				'callback' 	=> array( $this, 'executePHP' ),
			),
			'unschedule_action' => array
			(
				'callback' 	=> array( $this, 'unscheduleAction' ),
				'configuration' => array
				(
					'form' => function( $form, $values )
					{
						$unschedule_options = array
						(
							'exact' 	=> 'Exact',
							'contains' 	=> 'Contains',
						);
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_System_unschedule_mode', isset( $values[ 'rules_System_unschedule_mode' ] ) ? $values[ 'rules_System_unschedule_mode' ] : 'exact', TRUE, array( 'options' => $unschedule_options ) ) );
					},
				),
				'arguments' 	=> array
				(
					'key' => array
					(
						'default' => 'manual',
						'configuration' => array
						(
							'form' => function( $form, $values, $action )
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_System_unschedule_key', isset( $values[ 'rules_System_unschedule_key' ] ) ? $values[ 'rules_System_unschedule_key' ] : NULL, FALSE, array(), NULL, NULL, NULL, 'rules_System_unschedule_key' ) );
								return array( 'rules_System_unschedule_key' );
							},
							'getArg' => function( $values, $action )
							{
								return $values[ 'rules_System_unschedule_key' ];
							},
						),
						'argtypes' => array
						(
							'string' => array
							(
								'description' => "Unschedule actions assigned to this keyphrase",
							),
						),
					),
				),
			),
		);
		
		return $actions;
	}
	
	
	/*** ACTIONS ***/
	
	/**
	 * Set API Response Value
	 */
	public function setApiResponse( $value, $values, $arg_map, $action )
	{
		$response_key = $values[ 'rules_api_response_key' ];
		if ( ! $response_key ) 
		{
			return "no response key to set";
		}
		
		$action->event()->apiResponse[ $response_key ] = $value;
		return "api response key set: " . $response_key;
	}
	
	/**
	 * Send Email Callback
	 */
	public function sendEmail( $recipients, $bcc_recipients, $subject, $message, $values )
	{
		if ( ! is_array( $recipients ) )
		{
			$recipients = array( $recipients );
		}
		
		if ( ! is_array( $bcc_recipients ) )
		{
			$bcc_recipients = array( $bcc_recipients );
		}
		
		if ( empty ( $recipients ) )
		{
			return "no recipients";
		}
		
		$to = array();
		foreach( $recipients as $recipient )
		{
			if ( $recipient->email )
			{
				$to[] = $recipient;
			}
		}
		
		$bcc = array();
		foreach( $bcc_recipients as $recipient )
		{
			if ( $recipient->email )
			{
				$bcc[] = $recipient;
			}
		}
		
		$email = \IPS\Email::buildFromContent( $subject, $message, NULL, 'transactional' );
		$email->send( $to, array(), $bcc );
		
		return "email sent";
	}
	
	/**
	 * Create Conversation
	 */
	public function createConversation( $creator, $participants, $subject, $message, $values )
	{
		if ( ! ( $creator instanceof \IPS\Member ) )
		{
			throw new \UnexpectedValueException( "invalid member as creator" );
		}
		
		if ( empty ( $participants ) )
		{
			return "no participants specified";
		}
		
		if ( ! is_array( $participants ) )
		{
			$participants = array( $participants );
		}
		
		if ( $values[ 'rules_participation_mode' ] == 1 )
		{
			/* Create a separate conversation with all participants */
			foreach( $participants as $participant )
			{
				$conversation = \IPS\core\Messenger\Conversation::createItem( $creator, $creator->ip_address, \IPS\DateTime::ts( time() ) );
				$conversation->title = $subject;
				$conversation->is_system = TRUE;
				$conversation->save();
				
				$_message = \IPS\core\Messenger\Message::create( $conversation, $message, TRUE, NULL, FALSE, $creator );
				$conversation->first_msg_id = $_message->id;
				
				if ( $values[ 'rules_conversation_join_creator' ] or ! isset( $values[ 'rules_conversation_join_creator' ] ) )
				{
					$conversation->authorize( $creator );
				}
				
				$conversation->authorize( $participant );				
				$conversation->save();
			}
			
			return "individual conversations started with " . count( $participants ) . " participants";
		}
		else
		{
			/* Create a single conversation and include all participants */
			$conversation = \IPS\core\Messenger\Conversation::createItem( $creator, $creator->ip_address, \IPS\DateTime::ts( time() ) );
			$conversation->title = $subject;
			$conversation->is_system = TRUE;
			$conversation->save();
			
			$_message = \IPS\core\Messenger\Message::create( $conversation, $message, TRUE, NULL, FALSE, $creator );
			$conversation->first_msg_id = $_message->id;
			
			if ( $values[ 'rules_conversation_join_creator' ] or ! isset( $values[ 'rules_conversation_join_creator' ] ) )
			{
				$conversation->authorize( $creator );
			}
			
			foreach ( $participants as $participant )
			{
				$conversation->authorize( $participant );
			}
			
			$conversation->save();
			
			return "conversation started with " . $conversation->activeParticipants . " participants";
		}
	}
	
	/**
	 * Send Notifications
	 */
	public function createInlineNotifications( $recipients, $title, $url, $content, $author, $values, $args, $action )
	{		
		if ( empty ( $recipients ) )
		{
			return "no notification recipients";
		}
		
		if ( ! is_array( $recipients ) )
		{
			$recipients = array( $recipients );
		}
		
		if ( $author instanceof \IPS\Member )
		{
			$author = $author->member_id;
		}
		
		$count = 0;
		
		foreach ( $recipients as $recipient )
		{
			if ( ! ( $recipient instanceof \IPS\Member ) or ! $recipient->member_id )
			{
				continue;
			}
			
			$count++;
			
			/**
			 * Update existing unread notifications from this action
			 */
			try
			{
				$notification = \IPS\Notification\Inline::constructFromData( \IPS\Db::i()->select( '*', 'core_notifications', array( 'notification_key=? AND item_class=? AND item_id=? AND member=? AND read_time IS NULL', 'rules_notification', get_class( $action ), $action->id, $recipient->member_id ) )->first() );				
				$notification->member_data = array
				(
					'title' 	=> $title,
					'url' 		=> (string) $url,
					'content' 	=> $content,
					'author' 	=> $author,
				);
				$notification->updated_time = time();
				$notification->save();
				
				continue;
			}
			catch ( \UnderflowException $e ) { }
			
			/**
			 * Otherwise, create a new notification
			 */
			$notification 			= new \IPS\Notification\Inline;
			$notification->member 		= $recipient;
			$notification->item 		= $action;
			$notification->notification_app = \IPS\Application::load( 'rules' );
			$notification->notification_key = 'rules_notifications';
			$notification->member_data 	= array
			(
				'title' 	=> $title,
				'url' 		=> (string) $url,
				'content' 	=> $content,
				'author' 	=> $author,
			);
			$notification->save();
		}
		
		return "inline notifications sent to {$count} members";
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
		
		return $evaluate( $values[ 'rules_System_custom_phpcode' ] );
	}
	
	/**
	 * Unschedule Action
	 */
	public function unscheduleAction( $unique_key, $values )
	{
		if ( isset ( $unique_key ) and trim( $unique_key ) != '' )
		{
			switch ( $values[ 'rules_System_unschedule_mode' ] )
			{
				case 'contains':
				
					if ( $count = \IPS\Db::i()->select( 'COUNT(*)', 'rules_scheduled_actions', array( 'schedule_unique_key LIKE ?', '%' . trim( $unique_key ) . '%' ) )->first() )
					{
						\IPS\Db::i()->delete( 'rules_scheduled_actions', array( 'schedule_unique_key LIKE ?', '%' . trim( $unique_key ) . '%' ) );
						return "{$count} scheduled actions deleted";
					}
					else
					{
						return "no scheduled actions to delete";
					}
				
				case 'exact':
				default:
				
					if ( $count = \IPS\Db::i()->select( 'COUNT(*)', 'rules_scheduled_actions', array( 'schedule_unique_key=?', trim( $unique_key ) ) )->first() )
					{
						$schedule_id = \IPS\Db::i()->select( 'schedule_id', 'rules_scheduled_actions', array( 'schedule_unique_key=?', trim( $unique_key ) ) )->first();
						\IPS\Db::i()->delete( 'rules_scheduled_actions', array( 'schedule_unique_key=?', trim( $unique_key ) ) );
						return "{$count} scheduled action deleted (ID#{$schedule_id})";
					}
					else
					{
						return "no scheduled action to delete";
					}
			}
		}
		else
		{
			return "empty keyphrase. no action taken";
		}
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
	 * Check if an action is scheduled
	 */
	public function checkActionSchedule( $unique_key, $values )
	{
		if ( isset ( $unique_key ) and trim( $unique_key ) != '' )
		{
			switch ( $values[ 'rules_System_schedule_mode' ] )
			{
				case 'contains':
				
					if ( $count = \IPS\Db::i()->select( 'COUNT(*)', 'rules_scheduled_actions', array( 'schedule_unique_key LIKE ?', '%' . trim( $unique_key ) . '%' ) )->first() )
					{
						return TRUE;
					}
					break;
				
				case 'exact':
				default:
				
					if ( $count = \IPS\Db::i()->select( 'COUNT(*)', 'rules_scheduled_actions', array( 'schedule_unique_key=?', trim( $unique_key ) ) )->first() )
					{
						return TRUE;
					}
					break;
			}
		}

		return FALSE;
	}

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
	
	/**
	 * Compare Two Numbers
	 */
	public function compareNumbers( $number1, $number2, $values )
	{
		switch( $values[ 'rules_Comparisons_type' ] )
		{
			case '<':
				return $number1 < $number2;
			case '>':
				return $number1 > $number2;
			case '==':
				return $number1 == $number2;
			case '!=':
				return $number1 != $number2;
			case '>=':
				return $number1 >= $number2;
			case '<=':
				return $number1 <= $number2;
			default:
				return FALSE;
		}
	}
	
	/**
	 * Compare Two Strings
	 */
	public function compareStrings( $string1, $string2, $values )
	{
		switch( $values[ 'rules_Comparisons_type' ] )
		{
			case 'contains':
				return mb_strpos( $string1, $string2 ) !== FALSE;
			case 'startswith':
				return mb_substr( $string1, 0, mb_strlen( $string2 ) ) == $string2;
			case 'endswith':
				return mb_substr( $string1, mb_strlen( $string2 ) * -1 ) == $string2;
			case 'equals':
				return $string1 == $string2;
			default:
				return FALSE;
		}
	}
	
	/**
	 * Compare Truth Value
	 */
	public function compareTruth( $value, $values )
	{		
		switch ( $values[ 'rules_Comparisons_type' ] )
		{
			case 'true'	:	return $value === TRUE;
			case 'false'	:	return $value === FALSE;
			case 'truthy'	:	return (bool) $value;
			case 'falsey'	:	return ! ( (bool) $value );
			case 'null'	:	return $value === NULL;
			case 'notnull'	:	return $value !== NULL;
			default		:	return FALSE;
		}
	}
	
	/**
	 * Compare Value Type
	 */
	public function compareType( $value, $values )
	{
		$type = gettype( $value );		
		return $type === $values[ 'rules_Comparisons_type' ];
	}
	
	/**
	 * Compare Dates
	 */
	public function compareDates( $date1, $date2, $values )
	{
		if ( ! ( ( $date1 instanceof \IPS\DateTime ) and ( $date2 instanceof \IPS\DateTime ) ) )
		{
			return FALSE;
		}
		
		switch ( $values[ 'rules_Comparisons_type' ] )
		{
			case '?':
				$value =  intval( $values[ 'rules_Content_attribute_compare_minutes' ] 	) * 60
					+ intval( $values[ 'rules_Content_attribute_compare_hours' ] 	) * ( 60 * 60 )
					+ intval( $values[ 'rules_Content_attribute_compare_days' ] 	) * ( 60 * 60 * 24 )
					+ intval( $values[ 'rules_Content_attribute_compare_months' ] 	) * ( 60 * 60 * 24 * 30 )
					+ intval( $values[ 'rules_Content_attribute_compare_years' ] 	) * ( 60 * 60 * 24 * 365 );
					
				return abs( $date1->getTimestamp() - $date2->getTimestamp() ) < $value;
				
			case '>':
				return $date1->getTimestamp() > $date2->getTimestamp();
				
			case '<':
				return $date1->getTimestamp() < $date2->getTimestamp();
				
			case '=':
				return (
					$date1->format( 'Y' ) == $date2->format( 'Y' ) and
					$date1->format( 'm' ) == $date2->format( 'm' ) and
					$date1->format( 'd' ) == $date2->format( 'd' )
				);
		}
	}
	
	/**
	 * Object Comparison
	 */
	public function compareObjects( $object, $value, $values )
	{
		if ( ! is_object( $object ) )
		{
			return FALSE;
		}
	
		switch ( $values[ 'rules_Comparisons_type' ] )
		{
			case 'equal':
					
				return $object === $value;
				
			case 'isclass':
				
				if ( is_object ( $value ) )
				{
					$value = get_class( $value );
				}

				return get_class( $object ) == ltrim( $value, '\\' );
				
			case 'issubclass':
			
				if ( is_object ( $value ) )
				{
					$value = get_class( $value );
				}
				
				return is_subclass_of( $object, $value );
				
			default:
				return FALSE;
		}
	}
	
	/**
	 * Array Comparison
	 */
	public function compareArray( $array, $value, $values )
	{
		if ( ! is_array( $array ) )
		{
			return FALSE;
		}
			
		switch ( $values[ 'rules_Comparisons_type' ] )
		{
			case 'lengthgreater':
					
				return count( $array ) > (int) $value;
				
			case 'lengthless':
				
				return count( $array ) < (int) $value;
				
			case 'lengthequal':
				
				return count( $array ) == (int) $value;
				
			case 'containskey':
			
				return in_array( $value, array_keys( $array ), TRUE );
				
			case 'containsvalue':
			
				/**
				 * Loop the array so we can check any value (including objects)
				 */
				foreach ( $array as $k => $v )
				{
					if ( $v === $value )
					{
						return TRUE;
					}
				}
				
			default:
				return FALSE;
		}		
	}	
}