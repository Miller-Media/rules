<?php
/**
 * @brief		Rules extension: Comparisons
 * @package		Rules for IPS Social Suite
 * @since		24 Feb 2015
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
 * @brief	Rules definitions extension: Comparisons
 */
class _Comparisons
{

	/**
	 * @brief	Group events and actions in this extension with other extensions by group name
	 */
	public $group = 'Comparisons';

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
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Comparisons_type', $values[ 'rules_Comparisons_type' ], TRUE, array( 'options' => $compare_options ), NULL, NULL, NULL, 'rules_Comparisons_type' ) );
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
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Comparisons_number1', $values[ 'rules_Comparisons_number1' ], TRUE, array(), NULL, NULL, NULL, 'rules_Comparisons_number1' ) );
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
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Comparisons_number2', $values[ 'rules_Comparisons_number2' ], TRUE, array(), NULL, NULL, NULL, 'rules_Comparisons_number2' ) );
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
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Comparisons_type', $values[ 'rules_Comparisons_type' ], TRUE, array( 'options' => $compare_options ), NULL, NULL, NULL, 'rules_Comparisons_type' ) );
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
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Comparisons_string1', $values[ 'rules_Comparisons_string1' ], TRUE, array(), NULL, NULL, NULL, 'rules_Comparisons_string1' ) );
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
						'argtypes' => array
						(
							'string' => array( 'description' => 'the value to use as string 2' ),
						),				
						'required'	=> TRUE,
						'configuration' => array
						(
							'form' => function( $form, $values, $condition ) 
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Comparisons_string2', $values[ 'rules_Comparisons_string2' ], TRUE, array(), NULL, NULL, NULL, 'rules_Comparisons_string2' ) );
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
						$compare_options = array(
							'true' 		=> 'Value is TRUE',
							'false'		=> 'Value is FALSE',
							'truthy'	=> 'Value is TRUE or equivalent to TRUE (any non-empty string/array, number not 0)',
							'falsey'	=> 'Value is FALSE or equivalent to FALSE (including NULL, 0, empty string/array)',
							'null'		=> 'Value is NULL',
							'notnull'	=> 'Value is NOT NULL',
						);
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Comparisons_type', $values[ 'rules_Comparisons_type' ], TRUE, array( 'options' => $compare_options ), NULL, NULL, NULL, 'rules_Comparisons_type' ) );
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
						'required'	=> TRUE,
						'configuration' => array
						(
							'form' => function( $form, $values, $condition ) 
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Comparisons_value', $values[ 'rules_Comparisons_value' ], TRUE, array(), NULL, NULL, NULL, 'rules_Comparisons_value' ) );
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
						$compare_options = array(
							'boolean'	=> 'Value is a Boolean (TRUE/FALSE)',
							'string' 	=> 'Value is a String',
							'integer'	=> 'Value is a Integer',
							'double'	=> 'Value is a Float (Decimal)',
							'array'		=> 'Value is an Array',
							'object'	=> 'Value is an Object',
							'NULL'		=> 'Value is NULL',
						);
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Comparisons_type', $values[ 'rules_Comparisons_type' ], TRUE, array( 'options' => $compare_options ), NULL, NULL, NULL, 'rules_Comparisons_type' ) );
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
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Comparisons_value', $values[ 'rules_Comparisons_value' ], TRUE, array(), NULL, NULL, NULL, 'rules_Comparisons_value' ) );
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
			'compare_objects' => array
			(
				'callback' 	=> array( $this, 'compareObjects' ),
				
				'configuration' => array
				(
					'form' => function( $form, $values, $condition )
					{
						$compare_options = array(
							'equal' 	=> 'Object and Value are the same object',
							'isclass' 	=> 'Object is the class of Value',
							'issubclass'	=> 'Object is a subclass of Value',
						);
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Comparisons_type', $values[ 'rules_Comparisons_type' ], TRUE, array( 'options' => $compare_options ), NULL, NULL, NULL, 'rules_Comparisons_type' ) );
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
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Comparisons_value', $values[ 'rules_Comparisons_value' ], TRUE, array(), NULL, NULL, NULL, 'rules_Comparisons_value' ) );
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
			'compare_array' => array
			(
				'callback' 	=> array( $this, 'compareArray' ),
				
				'configuration' => array
				(
					'form' => function( $form, $values, $condition )
					{
						$compare_options = array(
							'lengthgreater'	=> 'Array length is greater than Value',
							'lengthless' 	=> 'Array length is less than Value',
							'lengthequal'	=> 'Array length is equal to Value',
							'containskey'	=> 'Array contains key with Value',
							'containsvalue' => 'Array contains value with Value',
						);
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Comparisons_type', $values[ 'rules_Comparisons_type' ], TRUE, array( 'options' => $compare_options ), NULL, NULL, NULL, 'rules_Comparisons_type' ) );
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
						'argtypes' => array
						(
							'string' => array( 'description' => 'a value to compare array with' ),
							'array' => array( 'description' => 'an array to compare' ),
						),				
						'required'	=> TRUE,
						'configuration' => array
						(
							'form' => function( $form, $values, $condition ) 
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Comparisons_value', $values[ 'rules_Comparisons_value' ], TRUE, array(), NULL, NULL, NULL, 'rules_Comparisons_value' ) );
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
			case 'true':
				return $value === TRUE;
			case 'false':
				return $value === FALSE;
			case 'truthy':
			case 'falsey':
				return $value ? TRUE : FALSE;
			case 'null':
				return $value === NULL;
			case 'notnull':
				return $value !== NULL;
			default:
				return FALSE;
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
		
		$valueLength = is_array( $value ) ? count( $value ) : intval( $value );
	
		switch ( $values[ 'rules_Comparisons_type' ] )
		{
			case 'lengthgreater':
					
				return count( $array ) > $valueLength;
				
			case 'lengthless':
				
				return count( $array ) < $valueLength;
				
			case 'lengthequal':
				
				return count( $array ) == $valueLength;
				
			case 'containskey':
			
				return in_array( $value, array_keys( $array ) );
				
			case 'containsvalue':
			
				return in_array( $value, $array );
				
			default:
				return FALSE;
		}		
	}
	
}