<?php
/**
 * @brief		Rules Application Class
 * @author		<a href='https://www.linkedin.com/in/kevincarwile'>Kevin Carwile</a>
 * @copyright	(c) 2015 Kevin Carwile
 * @package		IPS Social Suite
 * @subpackage	Rules
 * @since		07 Feb 2015
 * @version		
 */
 
namespace IPS\rules;

/**
 * Rules Application Class
 */
class _Application extends \IPS\Application
{

	/**
	 * Preset Operation Argument Types
	 */
	public static $argPresets = NULL;
	
	/**
	 * Thread ID For Logging
	 */
	public static $threadId = NULL;
	
	/**
	 * Argument Definition Presets
	 *
	 * @param	string	$key	The preset key to retrieve
	 * @return	array		The argument preset definition
	 */
	public static function getPreset( $key )
	{
		if ( isset ( static::$argPresets ) )
		{
			return static::$argPresets[ $key ];
		}
		
		static::$argPresets = array
		(
			/**
			 * Multiple Members Argument
			 */
			'members' => array
			(
				'int'	=> array
				(
					'description' => 'Member ID',
					'class' => '\IPS\Member',
					'converter' => function( $id ) {
						try { return array( \IPS\Member::load( $id ) );	}
						catch ( \Exception $e ) { return NULL; }
					},								
				),
				'object' => array
				(
					'description'	=> 'Member Object',
					'class' => '\IPS\Member',
					'converter' => function( $member ) {
						return ( $member instanceof \IPS\Member ) ? array( $member ) : NULL;
					},
				),
				'array'	=> array
				(
					'description' => "Array of member ID's or member objects",
					'class' => '\IPS\Member',
					'converter' => function( $args ) {
						$_members = array();
						foreach( $args as $arg ) 
						{
							if ( is_object( $arg ) and $arg instanceof \IPS\Member )
							{
								$_members[] = $arg;
							}
							else if ( is_int( $arg ) )
							{
								try { $_members[] = \IPS\Member::load( $arg ); }
								catch( \Exception $e ) {}
							}
						}
						return $_members ?: NULL;
					},
				),
			),
			
			/**
			 * Single Member Argument
			 */
			'member' => array
			(
				'int'	=> array
				(
					'description' => 'Member ID',
					'class' => '\IPS\Member',
					'converter' => function( $id ) {
						try { return \IPS\Member::load( $id );	}
						catch ( \Exception $e ) { return array(); }
					},								
				),
				'object' => array
				(
					'description' => 'Member Object',
					'class' => '\IPS\Member',
				),
			),		
			
		);
		
		return static::getPreset( $key );
	}
	
	/**
	 * Build Operation Form ( Condition / Action )
	 *
	 * @param	\IPS\Helpers\Form	$form		The form to build
	 * @param	\IPS\Node\Model		$operation	The condition or action node
	 * @param	string			$optype		A string representing the type of operation ( conditions/actions )
	 */
	public static function opform( $form, $operation, $optype )
	{
		$_operations 	= array();
		$lang		= \IPS\Member::loggedIn()->language();
		
		$lang->words[ 'operation_title' ] = $lang->get( $optype . '_title' );
		$form->add( new \IPS\Helpers\Form\Text( 'operation_title', $operation->title, TRUE ) );
		
		if ( ! $operation->id or ! $operation->key )
		{
			foreach ( \IPS\rules\Application::rulesDefinitions() as $definition_key => $definition )
			{
				foreach ( $definition[ $optype ] as $operation_key => $operation_data )
				{				
					$_operations[ $definition[ 'group' ] ][ $definition_key . '_' . $operation_key ] = $definition[ 'app' ] . '_' . $definition[ 'class' ] . '_' . $optype . '_' . $operation_key;
				}
			}
		}
		else
		{
			$_operations[ md5( $operation->app . $operation->class ) . '_' . $operation->key ] = $operation->app . '_' . $operation->class . '_' . $optype . '_' . $operation->key;
		}
		
		$lang->words[ 'rule_operation_selection' ] = $lang->get( 'rule_' . $optype . '_selection' );
		$form->add( new \IPS\Helpers\Form\Select( 'rule_operation_selection', $operation->id ? md5( $operation->app . $operation->class ) . '_' . $operation->key : NULL, TRUE, array( 'options' => $_operations, 'noDefault' => TRUE ), NULL, NULL, NULL, 'rule_operation_selection' ) );
		
		if ( $operation->definition )
		{		
			/**
			 * Add operation level configuration options if defined 
			 */
			if ( isset ( $operation->definition[ 'configuration' ][ 'form' ] ) and is_callable( $operation->definition[ 'configuration' ][ 'form' ] ) )
			{
				$callback = $operation->definition[ 'configuration' ][ 'form' ];
				$callback( $form, $operation->data[ 'configuration' ][ 'data' ], $operation );
			}
		
			/**
			 * Add argument level configurations if this operation takes arguments
			 */
			if ( isset( $operation->definition[ 'arguments' ] ) and is_array( $operation->definition[ 'arguments' ] ) )
			{
				foreach ( $operation->definition[ 'arguments' ] as $arg_name => $arg )
				{
					$argNameKey 	= $operation->app . '_' . $operation->class . '_' . $optype . '_' . $operation->key . '_' . $arg_name;
					
					$form->addHeader( $argNameKey );
								
					/**
					 * Argument source selection
					 *
					 * IPS does a validate on input creation, WHY? WHY? WHY?
					 */
					$lang->words[ $argNameKey . '_source' ] = $lang->get( 'source' );
					$form->add( $source_select = new \IPS\Helpers\Form\Select( $argNameKey . '_source', $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_source' ], TRUE, array( 'options' => array( 'event' => '', 'manual' => '', 'phpcode' => '', 'template' => '' ) ), NULL, NULL, NULL, $argNameKey . '_source' ) );
					$source_select->options[ 'options' ] = array();
					
					/**
					 * Can the argument be configured manually
					 */
					if ( isset ( $arg[ 'configuration' ][ 'form' ] ) and is_callable( $arg[ 'configuration' ][ 'form' ] ) )
					{				
						/**
						 * Add manual configuration form fields from definition
						 *
						 * Note: Callbacks should return an array with the ID's of their
						 * added form fields so we know what to toggle.
						 */
						$_added = call_user_func_array( $arg[ 'configuration' ][ 'form' ], array( $form, $operation->data[ 'configuration' ][ 'data' ], $operation ) );
						
						$source 		= $argNameKey . '_source';
						$useDefault 		= $argNameKey . '_eventArg_useDefault';
						$bypass_validation 	= \IPS\Request::i()->$source == 'manual' or ( \IPS\Request::i()->$source == 'event' and \IPS\Request::i()->$useDefault );
						
						/**
						 * Bypass validation errors on form fields that aren't actually required by our configuration
						 */
						foreach( $_added as $el )
						{
							/* Pick the element from the form */
							$formElement = NULL;
							foreach ( $form->elements as $tab => $elements )
							{
								if ( isset( $elements[ $el ] ) )
								{
									$formElement = $elements[ $el ];
									break;
								}
							}
							
							if ( $bypass_validation )
							{
								$formElement->error = NULL;
							}
						}
						
						$source_select->options[ 'options' ][ 'manual' ] = 'operation_arg_source_manual';
						$source_select->options[ 'toggles' ][ 'manual' ] = $_added;
					}
					
					/**
					 * Can the operation accept variable arguments?
					 * Does the event have arguments to pass?
					 * Are any of the arguments usable?
					 */
					if ( isset( $arg[ 'argtypes' ] ) and $event = $operation->event() and $_usable_arguments = static::usableEventArguments( $arg, $operation ) )
					{
						if ( count( $event->data[ 'arguments' ] ) )
						{
							$source_select->options[ 'options' ][ 'event' ] = 'operation_arg_source_event';
											
							$usable_arguments 	= array();
							$usable_toggles		= array();
							
							/* Compile a list of usable arguments */
							foreach( $_usable_arguments as $event_arg_name => $event_argument )
							{
								$eventArgNameKey = $event->app . '_' . $event->class . '_event_' . $event->key . '_' . $event_arg_name;
								$usable_arguments[ $event_arg_name ] = $eventArgNameKey;
								
								if ( isset( $event_argument[ 'nullable' ] ) and $event_argument[ 'nullable' ] )
								{
									/* Toggle on manual settings to use in case the event argument is empty */
									if ( $arg[ 'required' ] )
									{
										/* toggle the manual configuration form by necessity because it's required */
										$usable_toggles[ $event_arg_name ] = $source_select->options[ 'toggles' ][ 'manual' ];
										$form->hiddenValues[ $argNameKey . '_eventArg_useDefault' ] = TRUE;
									}
									else
									{
										/* a yes/no option will be given to allow the user to choose a default configuration, so just toggle that */
										$usable_toggles[ $event_arg_name ][] = $argNameKey . '_eventArg_useDefault';
									}
								}
							}
							
							/**
							 * Add event argument select box if we have usable arguments
							 */
							if ( count( $usable_arguments ) )
							{
								$lang->words[ $argNameKey . '_eventArg' ] 		= $lang->get( 'use_event_argument' );
								$lang->words[ $argNameKey . '_eventArg_useDefault' ] 	= $lang->get( 'use_event_argument_default' );
								$lang->words[ $argNameKey . '_eventArg_useDefault_desc']= $lang->get( 'use_event_argument_default_desc' );
								
								/* Event arg selector */
								$form->add( new \IPS\Helpers\Form\Select( $argNameKey . '_eventArg', $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_eventArg' ], FALSE, array( 'options' => $usable_arguments, 'toggles' => $usable_toggles ), NULL, NULL, NULL, $argNameKey . '_eventArg' ), $argNameKey . '_source' );
								
								/**
								 * Add option to use a default configuration if this argument is not required
								 */
								if ( ! $arg[ 'required' ] )
								{
									$form->add( new \IPS\Helpers\Form\YesNo( $argNameKey . '_eventArg_useDefault', $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_eventArg_useDefault' ], FALSE, array( 'togglesOn' => $source_select->options[ 'toggles' ][ 'manual' ] ), NULL, NULL, NULL, $argNameKey . '_eventArg_useDefault' ), $argNameKey . '_eventArg' );
								}
								
								$source_select->options[ 'toggles' ][ 'event' ] = array( $argNameKey . '_eventArg' );
							}
						}
					}
					
					/**
					 * Are we allowed to use PHP Code?
					 *
					 * @TODO: implement group permissions, allow by default for now
					 */
					if ( isset( $arg[ 'argtypes' ] ) )
					{
						/**
						 * Compile argtype info
						 */
						$_arg_list 	= array();
						
						if ( is_array( $arg[ 'argtypes' ] ) )
						{
							foreach( $arg[ 'argtypes' ] as $_type => $_type_def )
							{
								if ( is_array( $_type_def ) )
								{
									if ( isset ( $_type_def[ 'description' ] ) )
									{
										$_arg_list[] = "<strong>{$_type}</strong>: {$_type_def[ 'description' ]}";
									}
									else
									{
										$_arg_list[] = "<strong>{$_type}</strong>";
									}
								}
								else
								{
									$_arg_list[] = "<strong>{$_type_def}</strong>";
								}
							}
						}
										
						$_arg_list_info = "<ul><li>" . implode( '</li><li>', $_arg_list ) . "</li></ul>";
					
						$lang->words[ $argNameKey . '_phpcode' ] 	= $lang->get( 'phpcode' );
						$lang->words[ $argNameKey . '_phpcode_desc' ] 	= 
							$lang->get( 'phpcode_desc' ) . "<br><br>" . 
							$lang->get( 'phpcode_desc_details' ) . 
							$_arg_list_info . "<br>" .
							$lang->get( 'phpcode_desc_details_vars' ) . 
							static::eventArgInfo( $operation->event() );
						
						$source_select->options[ 'options' ][ 'phpcode' ] = 'operation_arg_source_phpcode';
						$source_select->options[ 'toggles' ][ 'phpcode' ] = array( $argNameKey . '_phpcode' );
						
						$form->add( new \IPS\Helpers\Form\Codemirror( $argNameKey . '_phpcode', $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_phpcode' ] ?: "//<?php\n\nreturn;", FALSE, array( 'mode' => 'php' ), NULL, NULL, NULL, $argNameKey . '_phpcode' ) );
					}
					
				}
			}
		}	
	}
	
	/**
	 * Save Operation Form
	 */
	public static function opformSave( $operation, $optype, $values, $core_values=array() )
	{
		list( $definition_key, $operation_key ) = explode( '_', $values[ 'rule_operation_selection' ], 2 );
		unset( $values[ 'rule_operation_selection' ] );
		
		if ( $definition = \IPS\rules\Application::rulesDefinitions( $definition_key ) )
		{
			$operation->app		= $definition[ 'app' ];
			$operation->class	= $definition[ 'class' ];
			$operation->key 	= $operation_key;
		}
		
		$operation->title = $values[ 'operation_title' ];
		unset( $values[ 'operation_title' ] );
		
		if ( $operation->definition and isset( $operation->definition[ 'arguments' ] ) and is_array( $operation->definition[ 'arguments' ] ) )
		{
			if ( isset ( $operation->definition[ 'configuration' ][ 'saveValues' ] ) and is_callable( $operation->definition[ 'configuration' ][ 'saveValues' ] ) )
			{
				$callback = $operation->definition[ 'configuration' ][ 'saveValues' ];
				$callback( $values, $operation );
			}
			
			foreach ( $operation->definition[ 'arguments' ] as $arg_name => $arg )
			{
				$argNameKey = $operation->app . '_' . $operation->class . '_' . $optype . '_' . $operation->key . '_' . $arg_name;
				
				if ( isset ( $arg[ 'configuration' ][ 'saveValues' ] ) and is_callable( $arg[ 'configuration' ][ 'saveValues' ] ) )
				{
					/**
					 * Call the saveValues callback in the definition to 
					 * process the submitted form values
					 */
					$callback = $arg[ 'configuration' ][ 'saveValues' ];
					$callback( $values, $operation );
				}
			}
		}

		/**
		 * Process Values
		 *
		 * Any configuration values that are not part of our active record
		 * get saved for use by our operation later
		 */
		foreach ( $values as $key => $val )
		{
			if ( ! in_array( $key, $core_values ) )
			{
				$operation->data[ 'configuration' ][ 'data' ][ $key ] = $values[ $key ];
				unset( $values[ $key ] );
			}
		}
		
		return $values;
	}
	
	/**
	 * Get Usable Event Arguments
	 */
	public static function usableEventArguments( $arg, $operation )
	{
		$_usable_arguments = array();
		
		if ( isset( $arg[ 'argtypes' ] ) and $event = $operation->event() )
		{
			if ( isset( $event->data[ 'arguments' ] ) and count( $event->data[ 'arguments' ] ) )
			{
				/* What types of arguments are acceptable? */
				$_types = array();
				foreach ( $arg[ 'argtypes' ] as $type => $typedata )
				{
					$_types[] = is_array( $typedata ) ? $type : $typedata;
				}
								
				/* For each event argument, see if we can use it */
				foreach( $event->data[ 'arguments' ] as $event_arg_name => $event_argument )
				{
					/* Check if the argtype is supported */
					if ( in_array( 'mixed', $_types ) or in_array( $event_argument[ 'argtype' ], $_types ) )
					{
						$can_use = TRUE;
						
						/* Our operation argument type definition */
						$type_def = $arg[ 'argtypes' ][ $event_argument[ 'argtype' ] ] ?: $arg[ 'argtypes' ][ 'mixed' ];
						
						/* If it's not an array, then it doesn't have any special needs */
						if ( is_array( $type_def ) and ! empty ( $type_def ) )
						{
							/* If a special class of argument is required, see if the event argument is compliant */
							if ( isset( $type_def[ 'class' ] ) )
							{
								if ( ! isset( $event_argument[ 'class' ] ) or ! static::classCompliant( $event_argument[ 'class' ], $type_def[ 'class' ] ) )
								{
									$can_use = FALSE;
								}
							}
							
							/* If the argument needs to represent a special property, check for compliance */
							if ( isset( $type_def[ 'property' ] ) )
							{
								if ( ! isset( $event_argument[ 'property' ] ) or ! in_array( $event_argument[ 'property' ], (array) $type_def[ 'property' ] ) )
								{
									$can_use = FALSE;
								}
							}
						}
						
						/* So can we use it or what! */
						if ( $can_use )
						{
							$_usable_arguments[ $event_arg_name ] = $event_argument;
						}
					}
				}
			}
		}
		
		return $_usable_arguments;
	}
	
	/**
	 * Check For Class Compliance
	 *
	 * @param	string 		$class		Class to check compliance
	 * @param	string|array	$classes	A classname or array of classnames to validate against
	 * @return	bool
	 */
	public static function classCompliant( $class, $classes )
	{
		$compliant = FALSE;
		
		foreach ( (array) $classes as $_class )
		{
			if ( $_class === $class )
			{
				$compliant = TRUE;
				break;
			}
			
			if ( is_subclass_of( $class, $_class ) )
			{
				$compliant = TRUE;
				break;
			}
		}
		
		return $compliant;
	}
	
	/**
	 * Invoke An Operation
	 *
	 * @param	\IPS\Node\Model		$operation	A condition/action object to evaluate
	 * @param	string			$optype		The type of operation which the operation is (actions/conditions)
	 * @param	array			$args		The arguments the operation was invoked with
	 * @return	mixed
	 */
	public static function opInvoke( $operation, $optype, $args )
	{
		if ( isset ( $operation->definition ) )
		{	
			$arg_map		= array();
			$operation_args 	= array();
			$event_arg_index 	= array();
			$i			= 0;
			
			if ( $event = $operation->event() )
			{
				if ( isset( $event->data[ 'arguments' ] ) and count( $event->data[ 'arguments' ] ) )
				{
					foreach ( $event->data[ 'arguments' ] as $event_arg_name => $event_arg )
					{
						$arg_map[ $event_arg_name ] = $args[ $i ];
						$event_arg_index[ $event_arg_name ] = $i++;
					}
				}
			}
			
			if ( isset( $operation->definition[ 'arguments' ] ) and is_array( $operation->definition[ 'arguments' ] ) )
			{
				foreach ( $operation->definition[ 'arguments' ] as $arg_name => $arg )
				{
					$argument_missing 	= FALSE;
					$argNameKey 		= $operation->app . '_' . $operation->class . '_' . $optype . '_' . $operation->key . '_' . $arg_name;
					
					switch ( $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_source' ] )
					{
						case 'event':
						
							/**
							 * Determine which argument index to use
							 */
							$event_arg_name = $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_eventArg' ];
							$_i 		= $event_arg_index[ $event_arg_name ];
							$_operation_arg 	= NULL;
							
							/**
							 * Check if argument is present in the event
							 */
							if ( isset ( $_i ) and isset( $args[ $_i ] ) )
							{
								$event_arg 	= $args[ $_i ];
								$event_arg_type = $event->data[ 'arguments' ][ $event_arg_name ][ 'argtype' ];
								
								/**
								 * Argtypes must be set to use event arguments
								 */
								if ( is_array( $arg[ 'argtypes' ] ) )
								{
									/* Simple definitions with no converters */
									if ( in_array( $event_arg_type, $arg[ 'argtypes' ] ) or in_array( 'mixed', $arg[ 'argtypes' ] ) )
									{
										$_operation_arg = $event_arg;
									}
									
									/* Complex definitions, check for converters */
									else if ( isset( $arg[ 'argtypes' ][ $event_arg_type ] ) )
									{
										if ( isset ( $arg[ 'argtypes' ][ $event_arg_type ][ 'converter' ] ) and is_callable( $arg[ 'argtypes' ][ $event_arg_type ][ 'converter' ] ) )
										{
											$_operation_arg = call_user_func_array( $arg[ 'argtypes' ][ $event_arg_type ][ 'converter' ], array( $event_arg, $operation->data[ 'configuration' ][ 'data' ] ) );
										}
										else
										{
											$_operation_arg = $event_arg;
										}
									}
									else if ( isset( $arg[ 'argtypes' ][ 'mixed' ] ) )
									{
										if ( isset ( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ] ) and is_callable( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ] ) )
										{
											$_operation_arg = call_user_func_array( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ], array( $event_arg, $operation->data[ 'configuration' ][ 'data' ] ) );
										}
										else
										{
											$_operation_arg = $event_arg;
										}
									
									}
								}
							}
							
							/**
							 * After all that, check if we have an argument to pass
							 */
							if ( isset( $_operation_arg ) )
							{
								$operation_args[] = $_operation_arg;
							}
							else
							{
								$argument_missing = TRUE;
							}			
							break;
						
						case 'manual':
						
							if ( isset ( $arg[ 'configuration' ][ 'getArg' ] ) and is_callable( $arg[ 'configuration' ][ 'getArg' ] ) )
							{
								$operation_args[] = call_user_func_array( $arg[ 'configuration' ][ 'getArg' ], array( $operation->data[ 'configuration' ][ 'data' ], $operation ) );
							}
							else
							{
								$argument_missing = TRUE;
							}
							break;
							
						case 'phpcode':
						
							$evaluate = function( $phpcode ) use ( $arg_map )
							{
								extract( $arg_map );								
								return @eval( $phpcode );
							};
							
							$argVal = $evaluate( $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_phpcode' ] );
							
							if ( isset( $argVal ) )
							{
								if ( is_array( $arg[ 'argtypes' ] ) )
								{
									$type_map = array( 
										'integer' 	=> 'int',
										'double'	=> 'float',
										'boolean' 	=> 'bool',
										'string' 	=> 'string',
										'array'		=> 'array',
										'object'	=> 'object',
									);
									
									$php_arg_type = $type_map[ gettype( $argVal ) ];
									
									/* Simple definitions with no converters */
									if ( in_array( $php_arg_type, $arg[ 'argtypes' ] ) or in_array( 'mixed', $arg[ 'argtypes' ] ) )
									{
										$operation_args[] = $argVal;
									}
									
									/* Complex definitions, check for converters */
									else if ( isset( $arg[ 'argtypes' ][ $php_arg_type ] ) )
									{
										if ( isset ( $arg[ 'argtypes' ][ $php_arg_type ][ 'converter' ] ) and is_callable( $arg[ 'argtypes' ][ $php_arg_type ][ 'converter' ] ) )
										{
											$operation_args[] = call_user_func_array( $arg[ 'argtypes' ][ $php_arg_type ][ 'converter' ], array( $argVal, $operation->data[ 'configuration' ][ 'data' ] ) );
										}
										else
										{
											$operation_args[] = $argVal;
										}
									}
									else if ( isset( $arg[ 'argtypes' ][ 'mixed' ] ) )
									{
										if ( isset ( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ] ) and is_callable( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ] ) )
										{
											$operation_args[] = call_user_func_array( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ], array( $argVal, $operation->data[ 'configuration' ][ 'data' ] ) );
										}
										else
										{
											$operation_args[] = $argVal;
										}
									
									}
									else
									{
										$argument_missing = TRUE;
									}
								}
								else
								{
									/**
									 * The argument cannot be processed because argtypes aren't supported
									 */
									$argument_missing = TRUE;
								}
								
							}
							else
							{
								$argument_missing = TRUE;
							}
							break;
							
						default:
						
							$argument_missing = TRUE;
					}
					
					/**
					 * If we haven't obtained a usable argument, use the manual default configuration if applicable
					 */
					if 
					( 
						$argument_missing and 
						$operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_source' ] !== 'manual' and
						isset ( $arg[ 'configuration' ][ 'getArg' ] ) and 
						is_callable( $arg[ 'configuration' ][ 'getArg' ] )
					)
					{
						if ( $arg[ 'required' ] or $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_eventArg_useDefault' ] )
						{
							$argVal = call_user_func_array( $arg[ 'configuration' ][ 'getArg' ], array( $operation->data[ 'configuration' ][ 'data' ], $operation ) );
							if ( isset( $argVal ) )
							{
								$argument_missing = FALSE;
								$operation_args[] = $argVal;
							}
						}
					}

					if ( $argument_missing )
					{
						if ( $arg[ 'required' ] )
						{
							/* operation cannot be invoked because we're missing required arguments */
							return NULL;
						}
						else
						{
							$operation_args[] = NULL;
						}
					}
					
				}
			}
						
			if ( isset ( $operation->definition[ 'callback' ] ) and is_callable( $operation->definition[ 'callback' ] ) )
			{
				try
				{
					$result = call_user_func_array( $operation->definition[ 'callback' ], array_merge( $operation_args, array( $operation->data[ 'configuration' ][ 'data' ], $arg_map, $operation ) ) );					
					
					if ( $rule = $operation->rule() and $rule->debug )
					{
						static::rulesLog( $rule->event(), $rule, $operation, $result, 'Evaluated'  );
					}
					
					return $result;
				}
				catch ( \Exception $e ) 
				{
					/**
					 * Log Exceptions
					 */
					$event = $operation->rule() ? $operation->rule()->event() : NULL;
					static::rulesLog( $event, $operation->rule(), $operation, $e->getMessage(), 'Error Exception' );
				}
			}
			else
			{
				if ( $rule = $operation->rule() )
				{
					static::rulesLog( $rule->event(), $rule, $operation, FALSE, 'Missing Callback'  );
				}
			}
		}	
	}
	
	/**
	 * Recursion Protection
	 */
	public static $locked = FALSE;
	
	/**
	 * Log Rules Info
	 */
	public static function rulesLog( $event, $rule, $operation, $result, $message='' )
	{
		if ( ! static::$locked )
		{
			static::$locked = TRUE;
			
			$log 		= new \IPS\rules\Log;
			$log->thread 	= is_object( $event ) 		? $event->thread		: NULL;
			$log->parent	= is_object( $event )		? $event->parentThread		: NULL;
			$log->app 	= is_object( $event ) 		? $event->app 			: NULL;
			$log->class 	= is_object( $event ) 		? $event->class 		: NULL;
			$log->key 	= is_object( $event ) 		? $event->key			: NULL;
			$log->rule_id	= is_object( $rule )		? $rule->id			: 0;
			$log->op_id	= is_object( $operation ) 	? $operation->id		: 0;
			$log->type 	= is_object( $operation ) 	? get_class( $operation )	: NULL;
			$log->result 	= json_encode( $result );
			$log->message 	= $message;
			$log->time 	= time();
			
			$log->save();
			
			static::$locked = FALSE;
		}
	}
	
	
	/**
	 * Get Event Argument Info
	 */
	public static function eventArgInfo( $event, $_p='$' )
	{
		$_event_arg_list = array();
		$lang = \IPS\Member::loggedIn()->language();
		
		if ( $event and isset( $event->data[ 'arguments' ] ) and is_array( $event->data[ 'arguments' ] ) )
		{
			foreach ( $event->data[ 'arguments' ] as $_event_arg_name => $_event_arg )
			{
				$eventArgNameKey = $event->app . '_' . $event->class . '_event_' . $event->key . '_' . $_event_arg_name;
				$_event_arg_list[] = "<strong>{$_p}{$_event_arg_name}</strong> - " . $lang->get( $eventArgNameKey ) . ( ( isset( $_event_arg[ 'nullable' ] ) and $_event_arg[ 'nullable' ] ) ? " ( may be NULL )" : "" );
			}
		}
		
		if ( ! count( $_event_arg_list ) )
		{
			$_event_arg_list[] = "No Data Associated";
		}
		
		$_event_arg_list_info = "<ul><li>" . implode( '</li><li>', $_event_arg_list ) . "</li></ul>";
		
		return $_event_arg_list_info;
	}
	
	/**
	 * Rule HTML Header
	 */
	public static function eventHeader( $event )
	{	
		return \IPS\Theme::i()->getTemplate( 'components' )->eventHeader( $event );
	}
	
	/**
	 * Rule HTML Header
	 */
	public static function ruleHeader( $rule )
	{
		return \IPS\Theme::i()->getTemplate( 'components' )->ruleHeader( $rule );
	}
	
	public static function ruleChild( $rule )
	{
		return \IPS\Theme::i()->getTemplate( 'components' )->ruleChild( $rule );
	}
	
	/**
	 * Retrieve All Rules Definitions
	 *
	 * @return 	array		Keyed array of rules extensions objects
	 */
	public static function rulesExtensions()
	{
		static $extensions;
		
		if ( isset ( $extensions ) )
		{
			return $extensions;
		}
		
		$apps 		= \IPS\Application::applications();
		$extensions	= array();
		
		foreach( $apps as $application )
		{
			foreach ( $application->extensions( 'rules', 'Definitions' ) as $key => $ext )
			{
				$extensions[ $application->directory ][ $key ] = $ext;
			}
		}

		return $extensions;
	}
	
	/**
	 * Get Map of Rules Definitions
	 *
	 * @param	string		$definition_key		The key of a specific definition to return
	 * @param	string		$property		A property to return for the given event_key
	 * @return	mixed					Returns specific definitions, or all if no parameters are given
	 */
	public static function rulesDefinitions( $definition_key=NULL, $property=NULL )
	{
		static $definitions;
		
		if ( isset( $definitions ) )
		{
			if ( isset ( $definition_key ) )
			{
				if ( isset ( $definitions[ $definition_key ] ) )
				{
					if ( isset ( $property ) )
					{
						return $definitions[ $definition_key ][ $property ];
					}
					else
					{
						return $definitions[ $definition_key ];
					}
				}
				else
				{
					return NULL;
				}
			}
			
			return $definitions;
		}
		
		$definitions = array();
		
		foreach ( static::rulesExtensions() as $app => $classes )
		{
			foreach ( $classes as $class => $ext )
			{
				$definitions[ md5( $app . $class ) ] = array( 
					'group'		=> $ext->group,
					'app' 		=> $app,
					'class' 	=> $class,
					'events' 	=> $ext->events(),
					'actions' 	=> $ext->actions(),
					'conditions'	=> $ext->conditions(),
				);
			}
		}
		
		return static::rulesDefinitions( $definition_key, $property );
	}

}