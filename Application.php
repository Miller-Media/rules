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

const ACTION_STANDARD = 0;
const ACTION_ELSE = 1;

/**
 * Rules Application Class
 */
class _Application extends \IPS\rules\Secure\Application
{

	/**
	 * Preset Operation Argument Types
	 */
	public static $argPresets = NULL;
	
	/**
	 * Preset Operation Argument Types
	 */
	public static $classMap = NULL;
	
	/**
	 * Global Arguments
	 */
	public static $globalArguments = NULL;
	
	/**
	 * @brief	Action Queue
	 */
	public static $actionQueue = array();
	
	/**
	 * @brief	Application Shutdown Flag
	 */
	public static $shutDown = FALSE;
		
	/**
	 * @brief	Interval
	 */
	public $interval = 604800;
	
	/**
	 * [Node] Get Node Icon
	 *
	 * @return	string
	 */
	protected function get__icon()
	{
		return 'legal';
	}
	
	/**
	 * [Node] Custom Badge
	 *
	 * @return	NULL|array	Null for no badge, or an array of badge data (0 => CSS class type, 1 => language string, 2 => optional raw HTML to show instead of language string)
	 */
	public function get__badge()
	{
		if ( $this->isProtected() )
		{
			return array(
				0	=> 'ipsBadge ipsBadge_neutral',
				1	=> 'Lite',
			);
		}
		
		return NULL;
	}	
	
	/**
	 * Application Data
	 */
	public function get_appdata()
	{
		return array( 'ver' => $this->version, 'version' => $this->long_version, 'state' => $this->isProtected(), 'url' => \IPS\Settings::i()->base_url, 'key' => 'rules' );
	}
	
	/**
	 * Argument Definition Presets
	 *
	 * @param	string	$key	The preset key to retrieve
	 * @return	array		The argument preset definition
	 */
	public static function argPreset( $key )
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
					'converter' => function( $id ) 
					{
						try { return array( \IPS\Member::load( $id ) );	}
						catch ( \Exception $e ) { return NULL; }
					},								
				),
				'object' => array
				(
					'description'	=> 'An \IPS\Member Object',
					'class' => '\IPS\Member',
					'converter' => function( $member ) 
					{
						return ( $member instanceof \IPS\Member ) ? array( $member ) : NULL;
					},
				),
				'array'	=> array
				(
					'description' => "Array of member ID's or member objects",
					'class' => '\IPS\Member',
					'converter' => function( $args ) 
					{
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
					'converter' => function( $id ) 
					{
						try { return \IPS\Member::load( $id );	}
						catch ( \Exception $e ) { return array(); }
					},								
				),
				'object' => array
				(
					'description' => 'An \IPS\Member Object',
					'class' => '\IPS\Member',
				),
			),
			
			/**
			 * Date
			 */
			'date' => array
			(
				'object' => array
				(
					'description' => 'An \IPS\DateTime object',
					'class' => '\IPS\DateTime',
				),
			),

			/**
			 * Tags
			 */
			'tags' => array
			(
				'array' => array
				(
					'description' => 'Array Of Tags',
				),
				'string' => array
				( 
					'description' => 'Tag',
					'converter' => function( $string )
					{
						return array( $string );
					},
				),
			),
			
			/**
			 * Url
			 */
			'url' => array
			(
				'object' => array
				(
					'description' => 'An \IPS\Http\Url object',
					'class' => '\IPS\Http\Url',
				),			
			),
			
		);
		
		return static::argPreset( $key );
	}
	
	/**
	 * Configuration Form Presets
	 *
	 * @param	string	$key		The key for the configuration preset to retrieve
	 * @param	string	$field_name	The name of the field
	 * @param	bool	$required	Indicate if the field should be required or not
	 * @param	array	$options	Additional config options
	 * @return	array			The argument preset definition
	 */
	public static function configPreset( $key, $field_name, $required=TRUE, $options=array() )
	{
		switch ( $key )
		{
			/**
			 * Multiple Members
			 */
			case 'members' :
			
				return array
				(
					'form' => function( $form, $values ) use ( $field_name, $required )
					{
						$members = array();
						$fieldValues = isset( $values[ $field_name ] ) ? (array) $values[ $field_name ] : array();						
						foreach( $fieldValues as $member_id )
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
						
						$options[ 'multiple' ] = NULL;
						
						$form->add( new \IPS\Helpers\Form\Member( $field_name, $members, $required, $options, NULL, NULL, NULL, $field_name ) );
						return array( $field_name );
					},
					'saveValues' => function( &$values ) use ( $field_name )
					{	
						$members = array();
						$fieldValues = isset( $values[ $field_name ] ) ? (array) $values[ $field_name ] : array();						
						foreach ( $fieldValues as $member )
						{
							$members[] = $member->member_id;
						}
						$values[ $field_name ] = $members;
					},
					'getArg' => function( $values ) use ( $field_name )
					{
						$members = array();
						$fieldValues = isset( $values[ $field_name ] ) ? (array) $values[ $field_name ] : array();						
						foreach( $fieldValues as $member_id )
						{
							if ( $member_id )
							{
								try { $members[] = \IPS\Member::load( $member_id ); }
								catch( \Exception $e ) {}
							}
						}
						
						return $members;
					},
				);
			
			/**
			 * Single Member
			 */
			case 'member':
			
				return array
				(
					'form' => function( $form, $values ) use ( $field_name, $required )
					{
						$members = array();
						$fieldValues = isset( $values[ $field_name ] ) ? (array) $values[ $field_name ] : array();						
						foreach( $fieldValues as $member_id )
						{
							if ( $member_id )
							{
								try
								{
									$members[] = \IPS\Member::load( $member_id );
								}
								catch ( \Exception $e ) { }
							}
						}
						
						$options[ 'multiple' ] = 1;
						
						$form->add( new \IPS\Helpers\Form\Member( $field_name, $members, $required, $options, NULL, NULL, NULL, $field_name ) );
						return array( $field_name );
					},
					'saveValues' => function( &$values ) use ( $field_name )
					{
						if ( isset( $values[ $field_name ] ) and $values[ $field_name ] instanceof \IPS\Member )
						{
							$values[ $field_name ] = array( $values[ $field_name ]->member_id );
						}
					},
					'getArg' => function( $values ) use ( $field_name )
					{
						$members = array();
						$fieldValues = isset( $values[ $field_name ] ) ? (array) $values[ $field_name ] : array();						
						foreach( $fieldValues as $member_id )
						{
							if ( $member_id )
							{
								try { $members[] = \IPS\Member::load( $member_id ); }
								catch( \Exception $e ) { }
							}
						}
						return array_shift( $members );
					},
				);
				
			/**
			 * Multiple Content Items
			 */
			case 'items' :
			
				return array
				(
					'form' => function( $form, $values ) use ( $field_name, $required, $options )
					{
						$items = array();
						$itemClass = $options[ 'class' ];
						
						if ( ! class_exists( $itemClass ) or ! is_subclass_of( $itemClass, '\IPS\Content\Item' ) )
						{
							return array();
						}
						
						$fieldValues = isset( $values[ $field_name ] ) ? (array) $values[ $field_name ] : array();						
						foreach( $fieldValues as $content_id )
						{
							if ( $content_id )
							{
								try
								{
									$items[] = $itemClass::load( $content_id );
								}
								catch ( \Exception $e ) { }
							}
						}
						
						if ( ! array_key_exists( 'multiple', $options ) ) {
							$options[ 'multiple' ] = NULL;
						}
						
						$form->add( new \IPS\rules\Field\Content( $field_name, $items, $required, $options, NULL, NULL, NULL, $field_name ) );
						return array( $field_name );
					},
					'saveValues' => function( &$values ) use ( $field_name, $options )
					{
						$itemClass = $options[ 'class' ];
						
						if ( ! class_exists( $itemClass ) or ! is_subclass_of( $itemClass, '\IPS\Content\Item' ) )
						{
							$values[ $field_name ] = NULL;
						}
						
						$idField = $itemClass::$databaseColumnId;
						
						$items = array();
						$fieldValues = isset( $values[ $field_name ] ) ? (array) $values[ $field_name ] : array();						
						foreach ( $fieldValues as $content )
						{
							if ( $content instanceof $itemClass )
							{
								$items[] = $content->$idField;
							}
						}
						
						$values[ $field_name ] = $items;
					},
					'getArg' => function( $values ) use ( $field_name, $options )
					{
						$items = array();
						$itemClass = $options[ 'class' ];
						
						if ( ! class_exists( $itemClass ) or ! is_subclass_of( $itemClass, '\IPS\Content\Item' ) )
						{
							return NULL;
						}
					
						$items = array();
						$fieldValues = isset( $values[ $field_name ] ) ? (array) $values[ $field_name ] : array();						
						foreach( $fieldValues as $content_id )
						{
							try 
							{ 
								$items[] = $itemClass::load( $content_id ); 
							}
							catch( \Exception $e ) { }
						}
						
						return $items;
					},
				);
			
			/**
			 * Single Content Item
			 */
			case 'item':
			
				return array
				(
					'form' => function( $form, $values ) use ( $field_name, $required, $options )
					{
						$items = array();
						$itemClass = $options[ 'class' ];
						
						if ( ! class_exists( $itemClass ) or ! is_subclass_of( $itemClass, '\IPS\Content\Item' ) )
						{
							return array();
						}
						
						$fieldValues = isset( $values[ $field_name ] ) ? (array) $values[ $field_name ] : array();						
						foreach( $fieldValues as $content_id )
						{
							if ( $content_id )
							{
								try
								{
									$items[] = $itemClass::load( $content_id );
								}
								catch ( \Exception $e ) { }
							}
						}
						
						$options[ 'multiple' ] = 1;
						
						$form->add( new \IPS\rules\Field\Content( $field_name, $items, $required, $options, NULL, NULL, NULL, $field_name ) );
						return array( $field_name );
					},
					'saveValues' => function( &$values ) use ( $field_name, $options )
					{
						$itemClass = $options[ 'class' ];
						
						if ( ! class_exists( $itemClass ) or ! is_subclass_of( $itemClass, '\IPS\Content\Item' ) )
						{
							$values[ $field_name ] = NULL;
						}
						
						$idField = $itemClass::$databaseColumnId;
						
						if ( isset( $values[ $field_name ] ) and $values[ $field_name ] instanceof $itemClass )
						{
							$values[ $field_name ] = array( $values[ $field_name ]->$idField );
						}
					},
					'getArg' => function( $values ) use ( $field_name, $options )
					{
						$items = array();
						$itemClass = $options[ 'class' ];
						
						if ( ! class_exists( $itemClass ) or ! is_subclass_of( $itemClass, '\IPS\Content\Item' ) )
						{
							return NULL;
						}
					
						$items = array();
						$fieldValues = isset( $values[ $field_name ] ) ? (array) $values[ $field_name ] : array();						
						foreach( $fieldValues as $content_id )
						{
							try 
							{ 
								$items[] = $itemClass::load( $content_id ); 
							}
							catch( \Exception $e ) { }
						}
						
						return array_shift( $items );
					},
				);

			/**
			 * Multiple Nodes
			 */
			case 'nodes' :
			
				return array
				(
					'form' => function( $form, $values ) use ( $field_name, $required, $options )
					{
						$items = array();
						$nodeClass = $options[ 'class' ];
						
						if ( ! class_exists( $nodeClass ) or ! is_subclass_of( $nodeClass, '\IPS\Node\Model' ) )
						{
							return array();
						}
						
						$fieldValues = isset( $values[ $field_name ] ) ? (array) $values[ $field_name ] : array();						
						foreach( $fieldValues as $node_id )
						{
							if ( $node_id )
							{
								try
								{
									$items[] = $nodeClass::load( $node_id );
								}
								catch ( \Exception $e ) { }
							}
						}
						
						$options[ 'multiple' ] = TRUE;
						
						$form->add( new \IPS\Helpers\Form\Node( $field_name, $items, $required, $options, NULL, NULL, NULL, $field_name ) );
						return array( $field_name );
					},
					'saveValues' => function( &$values ) use ( $field_name, $options )
					{
						$nodeClass = $options[ 'class' ];
						
						if ( ! class_exists( $nodeClass ) or ! is_subclass_of( $nodeClass, '\IPS\Node\Model' ) )
						{
							$values[ $field_name ] = NULL;
						}
						
						$idField = $nodeClass::$databaseColumnId;
						
						$items = array();
						$fieldValues = isset( $values[ $field_name ] ) ? (array) $values[ $field_name ] : array();						
						foreach ( $fieldValues as $node )
						{
							if ( $node instanceof $nodeClass )
							{
								$items[] = $node->$idField;
							}
						}
						
						$values[ $field_name ] = $items;
					},
					'getArg' => function( $values ) use ( $field_name, $options )
					{
						$items = array();
						$nodeClass = $options[ 'class' ];
						
						if ( ! class_exists( $nodeClass ) or ! is_subclass_of( $nodeClass, '\IPS\Node\Model' ) )
						{
							return NULL;
						}
					
						$items = array();
						$fieldValues = isset( $values[ $field_name ] ) ? (array) $values[ $field_name ] : array();						
						foreach( $fieldValues as $node_id )
						{
							try 
							{ 
								$items[] = $nodeClass::load( $node_id ); 
							}
							catch( \Exception $e ) { }
						}
						
						return $items;
					},
				);
			
			/**
			 * Single Node
			 */
			case 'node':
			
				return array
				(
					'form' => function( $form, $values ) use ( $field_name, $required, $options )
					{
						$items = array();
						$nodeClass = $options[ 'class' ];
						
						if ( ! class_exists( $nodeClass ) or ! is_subclass_of( $nodeClass, '\IPS\Node\Model' ) )
						{
							return array();
						}
						
						$fieldValues = isset( $values[ $field_name ] ) ? (array) $values[ $field_name ] : array();						
						foreach( $fieldValues as $node_id )
						{
							if ( $node_id )
							{
								try
								{
									$items[] = $nodeClass::load( $node_id );
								}
								catch ( \Exception $e ) { }
							}
						}
						
						$options[ 'multiple' ] = FALSE;
						
						$form->add( new \IPS\Helpers\Form\Node( $field_name, $items, $required, $options, NULL, NULL, NULL, $field_name ) );
						return array( $field_name );
					},
					'saveValues' => function( &$values ) use ( $field_name, $options )
					{
						$nodeClass = $options[ 'class' ];
						
						if ( ! class_exists( $nodeClass ) or ! is_subclass_of( $nodeClass, '\IPS\Node\Model' ) )
						{
							$values[ $field_name ] = NULL;
						}
						
						$idField = $nodeClass::$databaseColumnId;
						
						if ( isset( $values[ $field_name ] ) and $values[ $field_name ] instanceof $nodeClass )
						{
							$values[ $field_name ] = array( $values[ $field_name ]->$idField );
						}
					},
					'getArg' => function( $values ) use ( $field_name, $options )
					{
						$items = array();
						$nodeClass = $options[ 'class' ];
						
						if ( ! class_exists( $nodeClass ) or ! is_subclass_of( $nodeClass, '\IPS\Node\Model' ) )
						{
							return NULL;
						}
					
						$items = array();
						$fieldValues = isset( $values[ $field_name ] ) ? (array) $values[ $field_name ] : array();						
						foreach( $fieldValues as $node_id )
						{
							try 
							{ 
								$items[] = $nodeClass::load( $node_id ); 
							}
							catch( \Exception $e ) { }
						}
						
						return array_shift( $items );
					},
				);
				
			/**
			 * Date
			 */
			case 'date':
			
				return array
				(
					'form' => function( $form, $values, $operation ) use ( $field_name, $required, $options )
					{
						$values[ $field_name ] = isset( $values[ $field_name ] ) ? $values[ $field_name ] : time();
						$form->add( new \IPS\Helpers\Form\Date( $field_name, \IPS\DateTime::ts( $values[ $field_name ] ), $required, $options, NULL, NULL, NULL, $field_name ) );
						return array( $field_name );
					},
					'saveValues' => function( &$values, $operation ) use ( $field_name )
					{	
						if ( isset( $values[ $field_name ] ) and $values[ $field_name ] instanceof \IPS\DateTime )
						{
							$values[ $field_name ] = $values[ $field_name ]->getTimestamp();
						}
					},
					'getArg' => function( $values, $operation ) use ( $field_name )
					{
						return \IPS\DateTime::ts( $values[ $field_name ] );
					},
				);
				
			/**
			 * Tags
			 */
			case 'tags':

				return array
				(
					'form' => function( $form, $values ) use ( $field_name, $required, $options )
					{
						$form->add( new \IPS\Helpers\Form\Stack( $field_name, isset( $values[ $field_name ] ) ? $values[ $field_name ] : array(), $required, $options, NULL, NULL, NULL, $field_name ) );
						return array( $field_name );
					},
					'getArg' => function( $values ) use ( $field_name )
					{
						$fieldValues = isset( $values[ $field_name ] ) ? (array) $values[ $field_name ] : array();						
						return $fieldValues;
					},
				);
				
			/**
			 * Url
			 */
			case 'url':

				return array
				(
					'form' => function( $form, $values, $action ) use ( $field_name, $required, $options )
					{
						$form->add( new \IPS\Helpers\Form\Url( $field_name, isset( $values[ $field_name ] ) ? new \IPS\Http\Url( $values[ $field_name ] ) : NULL, $required, $options, NULL, NULL, NULL, $field_name ) );
						return array( $field_name );
					},
					'saveValues' => function( &$values ) use ( $field_name )
					{
						$values[ $field_name ] = (string) $values[ $field_name ];
					},
					'getArg' => function( $values, $action ) use ( $field_name )
					{
						return new \IPS\Http\Url( $values[ $field_name ] );
					},
				);

			default:
			
				return array();
			
		}
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
		$_operations 		= array();
		$lang			= \IPS\Member::loggedIn()->language();
		$wrap_chosen_prefix	= "<div class='chosen-collapse' data-controller='rules.admin.ui.chosen'>";
		$wrap_chosen_suffix	= "</div>";
		$form->class 		.= " opformForm"; 
		
		if ( ! $operation->_id )
		{
			$form->actionButtons = array( \IPS\Theme::i()->getTemplate( 'forms', 'core', 'global' )->button( 'rules_next', 'submit', null, 'ipsButton ipsButton_primary', array( 'accesskey' => 's' ) ) );
		}
		
		/**
		 * Footprint is used to limit the operations selection to compatible operations
		 */
		if ( $operation->footprint or isset( $operation->definition ) )
		{
			$footprint = $operation->footprint ?: md5( json_encode( $operation->definition[ 'arguments' ] ) );
		}
		
		/**
		 * Operation select options
		 */
		foreach ( \IPS\rules\Application::rulesDefinitions() as $definition_key => $definition )
		{
			foreach ( $definition[ $optype ] as $operation_key => $operation_data )
			{
				/* 
				 * Add to the select list if the operation hasn't been previously configured, 
				 * or if this was the operation which was originally configured, or if this
				 * operation's footprint is the same as the one which is configured (uses the 
				 * same arguments).
				 */
				if 
				( 
					! isset( $footprint ) or 
					( $definition_key === md5( $operation->app . $operation->class ) and $operation_key === $operation->key ) or 
					$footprint == md5( json_encode( isset( $operation_data[ 'arguments' ] ) ? $operation_data[ 'arguments' ] : '' ) ) 
				)
				{
					$group = ( isset( $operation_data[ 'group' ] ) and $operation_data[ 'group' ] ) ? $operation_data[ 'group' ] : $definition[ 'group' ];
					$_operations[ $group ][ $definition_key . '_' . $operation_key ] = $definition[ 'app' ] . '_' . $definition[ 'class' ] . '_' . $optype . '_' . $operation_key;
				}
			}
		}
		
		$lang->words[ 'operation_title' ] = $lang->get( $optype . '_title' );	
		$lang->words[ 'rule_operation_selection' ] = $lang->get( 'rule_' . $optype . '_selection' );
		$form->add( new \IPS\Helpers\Form\Select( 'rule_operation_selection', $operation->id ? md5( $operation->app . $operation->class ) . '_' . $operation->key : NULL, TRUE, array( 'options' => $_operations, 'noDefault' => TRUE ), NULL, $wrap_chosen_prefix, $wrap_chosen_suffix, 'rule_operation_selection' ) );
		$form->add( new \IPS\Helpers\Form\Text( 'operation_title', $operation->title, TRUE ) );
		
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
								
					/* Add the "event argument optional" notice in case it needs to be shown */
					$noticeLang = $arg[ 'required' ] ? $lang->addToStack( 'rules_event_argument_required_notice' ) : $lang->addToStack( 'rules_event_argument_optional_notice' );
					$noticeHtml = 
						"<div id='{$argNameKey}_optional_notice' class='ipsHide' style='margin:10px 0 -30px 0; max-width:550px;'>
							<div class='ipsMessage ipsMessage_warning'>
								{$noticeLang}
							</div>
						</div>
						";

					/**
					 * Argument source selection
					 *
					 * IPS does a validate on input creation, WHY? WHY? WHY?
					 */
					$lang->words[ $argNameKey . '_source' ] = $lang->get( 'source' );
					$form->add( $source_select = new \IPS\Helpers\Form\Select( $argNameKey . '_source', isset( $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_source' ] ) ? $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_source' ] : ( isset( $arg[ 'default' ] ) ? $arg[ 'default' ] : NULL ), TRUE, array( 'options' => array( 'event' => '', 'manual' => '', 'phpcode' => '' ) ), NULL, $wrap_chosen_prefix, $wrap_chosen_suffix, $argNameKey . '_source' ) );
					$source_select->options[ 'options' ] = array();
					
					/**
					 * MANUAL CONFIGURATION
					 *
					 * Does the argument support a manual configuration?
					 */
					if 
					( 
						( isset ( $arg[ 'configuration' ][ 'form' ] ) 	and is_callable( $arg[ 'configuration' ][ 'form' ] ) ) and 
						( isset ( $arg[ 'configuration' ][ 'getArg' ] ) and is_callable( $arg[ 'configuration' ][ 'getArg' ] ) )
					)
					{				
						/**
						 * Add manual configuration form fields from definition
						 *
						 * Note: Callbacks should return an array with the ID's of their
						 * added form fields so we know what to toggle.
						 */
						$_added = call_user_func_array( $arg[ 'configuration' ][ 'form' ], array( $form, isset( $operation->data[ 'configuration' ][ 'data' ] ) ? $operation->data[ 'configuration' ][ 'data' ] : array(), $operation ) );
						
						$source 		= $argNameKey . '_source';
						$eventArg		= $argNameKey . '_eventArg';
						$eventArgNullable	= md5( \IPS\Request::i()->$eventArg ) . '_nullable';
						$useDefault 		= $argNameKey . '_eventArg_useDefault_checkbox';
						$enforce_validation 	=  
						( 
							\IPS\Request::i()->$source == 'manual' or 
							( 
								\IPS\Request::i()->$source == 'event' and 
								(
									\IPS\Request::i()->$useDefault and
									\IPS\Request::i()->$eventArgNullable
								)
							) 
						);
						
						/**
						 * Bypass validation errors on form fields that aren't actually required by our configuration
						 */
						if ( ! $enforce_validation )
						{
							foreach( (array) $_added as $el )
							{
								/* Pick the element from the form */
								$formElement = NULL;
								foreach ( $form->elements as $tab => $elements )
								{
									/* Quick look */
									if ( isset( $elements[ $el ] ) )
									{
										$formElement = $elements[ $el ];
										break;
									}
									
									/* Sometimes, the added html id may not be the same as the element name (i.e. editors) */
									foreach( $elements as $element )
									{
										if 
										( 
											( $element->htmlId and $el == $element->htmlId ) or 
											( ! $element->htmlId and $el == $form->id . '_' . $element->name )
										)
										{
											$formElement = $element;
											break 2;
										}
									}
								}
								
								if ( $formElement )
								{
									$formElement->noError();
								}
							}
						}
						
						$source_select->options[ 'options' ][ 'manual' ] = 'operation_arg_source_manual';
						$source_select->options[ 'toggles' ][ 'manual' ] = $_added;
					}
					
					/**
					 * EVENT ARGUMENTS 
					 *
					 * Can the operation accept variable arguments?
					 * Does the event have arguments to pass?
					 * Are there any arguments to use?
					 */
					$event = $operation->event();
					$_usable_arguments = static::usableEventArguments( $arg, $operation );
					
					if 
					( 
						isset( $arg[ 'argtypes' ] ) and 
						count( $event->data[ 'arguments' ] ) and
						$_usable_arguments
					)
					{
						$source_select->options[ 'options' ][ 'event' ] = 'operation_arg_source_event';
						
						$usable_arguments 	= array();
						$usable_toggles		= array();
						$default_toggle_needed	= FALSE;
						
						/**
						 * Add usable event arguments to our list
						 */
						foreach ( $_usable_arguments as $event_arg_name => $event_argument )
						{
							/* Break apart argument name because it may contain converter details */
							$apart 			= explode( ':', $event_arg_name );
							$_event_arg_name 	= isset( $apart[ 0 ] ) ? $apart[ 0 ] : '';
							$converter_class 	= isset( $apart[ 1 ] ) ? $apart[ 1 ] : '';
							$converter_key 		= isset( $apart[ 2 ] ) ? $apart[ 2 ] : '';
							
							/**
							 * Global Arguments
							 */
							if ( mb_substr( $event_arg_name, 0, 9 ) === '__global_' )
							{
								$eventArgNameKey = $event_arg_name;
								$eventArgNameLang = $_event_arg_name;
							}
							
							/**
							 * Event Specific Arguments
							 */
							else
							{
								$eventArgNameKey = $event->app . '_' . $event->class . '_event_' . $event->key . '_' . $event_arg_name;
								$eventArgNameLang = $event->app . '_' . $event->class . '_event_' . $event->key . '_' . $_event_arg_name;
							}
							
							/* If it will be converted, change the item title to indicate that */
							if ( $converter_class and $converter_key )
							{
								$lang->words[ $eventArgNameKey ] = ( $lang->checkKeyExists( $eventArgNameLang ) ? $lang->get( $eventArgNameLang ) : $eventArgNameLang ) . ' (' . ( $lang->checkKeyExists( 'rules_convert_' . $converter_key ) ? $lang->addToStack( 'rules_convert_' . $converter_key ) : $lang->addToStack( $converter_key ) ) . ')';
							}
							
							$usable_arguments[ $eventArgNameLang ][ $event_arg_name ] = $eventArgNameKey;			
							
							if ( isset( $event_argument[ 'nullable' ] ) and $event_argument[ 'nullable' ] )
							{
								$form->hiddenValues[ md5( $event_arg_name ) . '_nullable' ] = TRUE;
							
								/* The default config YesNo option will be needed */
								$default_toggle_needed = TRUE;
								
								/* a yes/no option will be given to allow the user to choose a default configuration, so just toggle that */
								$usable_toggles[ $event_arg_name ] = array( $argNameKey . '_eventArg_useDefault', $argNameKey . '_optional_notice' );
							}
							else
							{
								$form->hiddenValues[ md5( $event_arg_name ) . '_nullable' ] = FALSE;
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
							
							/* Reduce optgroups with only one choice to a single option */
							$single_options = array();
							foreach( $usable_arguments as $_k => $_v )
							{
								if ( is_array( $_v ) and count( $_v ) == 1 )
								{
									unset( $usable_arguments[ $_k ] );
									foreach( $_v as $__k => $__v )
									{
										$single_options[ $__k ] = $__v;
									}
								}
							}
							
							/* Put back together with single options at top */
							$usable_arguments = array_merge( $single_options, $usable_arguments );
							
							/* Event arg selector */
							$form->add( new \IPS\Helpers\Form\Select( $argNameKey . '_eventArg', isset( $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_eventArg' ] ) ? $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_eventArg' ] : NULL, FALSE, array( 'options' => $usable_arguments, 'toggles' => $usable_toggles ), NULL, $wrap_chosen_prefix, $wrap_chosen_suffix . $noticeHtml, $argNameKey . '_eventArg' ), $argNameKey . '_source' );
							
							/**
							 * Offer default configuration option if available
							 */
							if ( $default_toggle_needed )
							{
								$togglesOn = ( isset( $arg[ 'configuration' ][ 'getArg' ] ) and is_callable( $arg[ 'configuration' ][ 'getArg' ] ) ) ? $source_select->options[ 'toggles' ][ 'manual' ] : array( $argNameKey . '_phpcode' );
								$form->add( new \IPS\Helpers\Form\YesNo( $argNameKey . '_eventArg_useDefault', isset( $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_eventArg_useDefault' ] ) ? $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_eventArg_useDefault' ] : NULL, FALSE, array( 'togglesOn' => $togglesOn ), NULL, NULL, NULL, $argNameKey . '_eventArg_useDefault' ), $argNameKey . '_eventArg' );
							}
							
							$source_select->options[ 'toggles' ][ 'event' ] = array( $argNameKey . '_eventArg' );
						}
					}
					
					/**
					 * PHP CODE
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
										$_arg_list[] = "<strong>{$_type}</strong>" . ( $_type_def[ 'class' ] ? ' (' . implode( ',', (array) $_type_def[ 'class' ] ) . ')' : '' ) . ": {$_type_def[ 'description' ]}";
									}
									else
									{
										$_arg_list[] = "<strong>{$_type}</strong>" . ( $_type_def[ 'class' ] ? ' (' . implode( ',', (array) $_type_def[ 'class' ] ) . ')' : '' );
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
						
						$form->add( new \IPS\Helpers\Form\Codemirror( $argNameKey . '_phpcode', isset( $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_phpcode' ] ) ? $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_phpcode' ] : "//<?php\n\nreturn;", FALSE, array( 'mode' => 'php' ), NULL, NULL, NULL, $argNameKey . '_phpcode' ) );
					}
				}
			}
		}	
	}
	
	/**
	 * Save Operation Form
	 *
	 * @param	\IPS\Node\Model		$operation	The condition or action node
	 * @param	string			$optype		A string representing the type of operation ( conditions/actions )
	 * @param	array			$value		Submitted form values
	 * @param	array			$core_values	An array of value keys that should be allowed to pass through for core processing
	 * @return	array					Values for core processing
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
	 *
	 * @param	array			$arg		The argument definition
	 * @param	\IPS\Node\Model		$operation	The condition or action node
	 * @return	array					An array of additional arguments that can be derived from the event
	 */
	public static function usableEventArguments( $arg, $operation )
	{
		$_usable_arguments = array();
		$event = $operation->event();
		
		if ( isset( $arg[ 'argtypes' ] ) )
		{
			if ( isset( $event->data ) )
			{
				/* Add in global arguments */
				$all_arguments = array_merge( $event->data[ 'arguments' ] ?: array(), static::getGlobalArguments() );
				
				if ( is_array( $all_arguments ) and count( $all_arguments ) )
				{
					/**
					 * Create an array of argtypes that are acceptable as an
					 * operation argument
					 */
					$_types = array();
					foreach ( $arg[ 'argtypes' ] as $type => $typedata )
					{
						$_types[] = is_array( $typedata ) ? $type : $typedata;
					}
						
					/**
					 * For every available event/global argument, see if we can use it
					 * by comparing it to the acceptable argtypes
					 */
					foreach( $all_arguments as $event_arg_name => $event_argument )
					{
						$type_def = array();
						
						/**
						 * Check if the event argument itself is supported
						 */
						if ( in_array( 'mixed', $_types ) or in_array( $event_argument[ 'argtype' ], $_types ) )
						{
							$can_use = TRUE;
							
							/* Our operation argument type definition */
							$type_def = isset( $arg[ 'argtypes' ][ $event_argument[ 'argtype' ] ] ) ? $arg[ 'argtypes' ][ $event_argument[ 'argtype' ] ] : $arg[ 'argtypes' ][ 'mixed' ];
							
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
							}
							
							/* So can we use it or what! */
							if ( $can_use )
							{
								$_usable_arguments[ $event_arg_name ] = $event_argument;
							}
						}
						
						/**
						 * Add in any other arguments that we can derive from the event argument as options also
						 */
						if ( $event_argument[ 'argtype' ] == 'object' and isset( $event_argument[ 'class' ] ) )
						{
							if ( $derivative_arguments = static::classConverters( $event_argument, $type_def ) )
							{
								foreach ( $derivative_arguments as $map_key => $derivative_argument )
								{
									if ( in_array( 'mixed', $_types ) or in_array( $derivative_argument[ 'argtype' ], $_types ) )
									{
										$_usable_arguments[ $event_arg_name . ":" . $map_key ] = $derivative_argument;
									}
								}
							}						
						}				
					}
				}
			}
		}
		
		return $_usable_arguments;
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
			$event 			= $operation->event();
			
			if ( isset( $event->data[ 'arguments' ] ) and count( $event->data[ 'arguments' ] ) )
			{
				foreach ( $event->data[ 'arguments' ] as $event_arg_name => $event_arg )
				{
					$arg_map[ $event_arg_name ] = $args[ $i ];
					$event_arg_index[ $event_arg_name ] = $i++;
				}
			}
			
			try
			{
				if ( isset( $operation->definition[ 'arguments' ] ) and is_array( $operation->definition[ 'arguments' ] ) )
				{
					/* Put together the argument list needed by this operation */
					foreach ( $operation->definition[ 'arguments' ] as $arg_name => $arg )
					{
						$argument_missing 	= FALSE;
						$argNameKey 		= $operation->app . '_' . $operation->class . '_' . $optype . '_' . $operation->key . '_' . $arg_name;
						
						/* Check which source the user has configured for the argument data */
						switch ( $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_source' ] )
						{
							/**
							 * Grab argument from event
							 */
							case 'event':
							
								/**
								 * Determine which argument index to use and if the argument
								 * needs class conversion or not
								 */
								$parts = explode( ':', $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_eventArg' ] );
								$event_arg_name = isset( $parts[ 0 ] ) ? $parts[ 0 ] : NULL;
								$converter_class = isset( $parts[ 1 ] ) ? $parts[ 1 ] : NULL;
								$converter_key = isset( $parts[ 2 ] ) ? $parts[ 2 ] : NULL;
								
								$_operation_arg	= NULL;
								$input_arg 	= NULL;
								$input_arg_type	= NULL;
								
								/**
								 * Get input argument from global arguments
								 */
								if ( mb_substr( $event_arg_name, 0, 9 ) === '__global_' )
								{
									$global_arguments = static::getGlobalArguments();
									if ( isset ( $global_arguments[ $event_arg_name ] ) )
									{
										if ( is_callable( $global_arguments[ $event_arg_name ][ 'getArg' ] ) )
										{
											$input_arg = call_user_func_array( $global_arguments[ $event_arg_name ][ 'getArg' ], array() );
										}
										$input_arg_type = $global_arguments[ $event_arg_name ][ 'argtype' ];
									}
								}
								
								/**
								 * Get input argument from event arguments
								 */
								else
								{
									$_i = $event_arg_index[ $event_arg_name ];
									if ( isset( $_i ) )
									{
										$input_arg = $args[ $_i ];
										$input_arg_type = $event->data[ 'arguments' ][ $event_arg_name ][ 'argtype' ];
									}
								}
								
								/**
								 * Check if argument is present in the event
								 */
								if ( isset ( $input_arg ) )
								{
									/**
									 * If an argument has been chosen which is a "derivative" of an actual event argument,
									 * then we need to pass the event argument to the conversion function to get the
									 * correct derivative value.
									 */
									if ( $converter_class and $converter_key )
									{
										$classConverters = static::getConversions();
										if 
										( 
											isset ( $classConverters[ $converter_class ][ $converter_key ] ) and 
											is_callable( $classConverters[ $converter_class ][ $converter_key ][ 'converter' ] ) 
										)
										{									
											$event_arg 	= call_user_func( $classConverters[ $converter_class ][ $converter_key ][ 'converter' ], $input_arg );
											$event_arg_type	= $classConverters[ $converter_class ][ $converter_key ][ 'argtype' ];
										}
										else
										{
											$event_arg 	= NULL;
											$event_arg_type = NULL;
										}
									}
									else
									{
										$event_arg 	= $input_arg;
										$event_arg_type = $input_arg_type;
									}
									
									/**
									 * Argtypes must be defined to use event arguments
									 */
									if ( is_array( $arg[ 'argtypes' ] ) )
									{
										/* Simple definitions with no processing callbacks */
										if ( in_array( $event_arg_type, $arg[ 'argtypes' ] ) or in_array( 'mixed', $arg[ 'argtypes' ] ) )
										{
											$_operation_arg = $event_arg;
										}
										
										/* Complex definitions, check for processing callbacks */
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
							
							/**
							 * Grab manual entry argument
							 */
							case 'manual':
							
								/**
								 * Arguments received from manual configuration callbacks are not passed through any processing callbacks
								 * because it is expected that the designer of the operation will return an argument that is
								 * already in a state that can be passed directly to the operation callback.
								 */
								if ( isset ( $arg[ 'configuration' ][ 'getArg' ] ) and is_callable( $arg[ 'configuration' ][ 'getArg' ] ) )
								{
									$operation_args[] = call_user_func_array( $arg[ 'configuration' ][ 'getArg' ], array( $operation->data[ 'configuration' ][ 'data' ], $operation ) );
								}
								else
								{
									$argument_missing = TRUE;
								}
								break;
							
							/**
							 * Calculate an argument using PHP
							 */
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
										$type_map = array
										( 
											'integer' 	=> 'int',
											'double'	=> 'float',
											'boolean' 	=> 'bool',
											'string' 	=> 'string',
											'array'		=> 'array',
											'object'	=> 'object',
										);
										
										$php_arg_type = $type_map[ gettype( $argVal ) ];
										
										/* Simple definitions with no value processing callbacks */
										if ( in_array( $php_arg_type, $arg[ 'argtypes' ] ) or in_array( 'mixed', $arg[ 'argtypes' ] ) )
										{
											$operation_args[] = $argVal;
										}
										
										/* Complex definitions, check for value processing callbacks */
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
							$operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_source' ] == 'event' and
							$operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_eventArg_useDefault' ]
						)	
						{
							/**
							 * Get the default value from manual configuration setting
							 */
							if ( isset ( $arg[ 'configuration' ][ 'getArg' ] ) and is_callable( $arg[ 'configuration' ][ 'getArg' ] ) )
							{
								$argVal = call_user_func_array( $arg[ 'configuration' ][ 'getArg' ], array( $operation->data[ 'configuration' ][ 'data' ], $operation ) );
								if ( isset( $argVal ) )
								{
									$argument_missing = FALSE;
									$operation_args[] = $argVal;
								}
							}
							
							/**
							 * Get the default value from phpcode
							 */
							else
							{
								/* Only if we haven't already attempted to get the argument from phpcode */
								if ( $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_source' ] !== 'phpcode' )
								{
									/**
									 * This code is getting a little redundant. I know.
									 */
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
											$type_map = array
											( 
												'integer' 	=> 'int',
												'double'	=> 'float',
												'boolean' 	=> 'bool',
												'string' 	=> 'string',
												'array'		=> 'array',
												'object'	=> 'object',
											);
											
											$php_arg_type = $type_map[ gettype( $argVal ) ];
											
											/* Simple definitions with no processing callbacks */
											if ( in_array( $php_arg_type, $arg[ 'argtypes' ] ) or in_array( 'mixed', $arg[ 'argtypes' ] ) )
											{
												$operation_args[] = $argVal;
												$argument_missing = FALSE;
											}
											
											/* Complex definitions, check for processing callbacks */
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
												$argument_missing = FALSE;
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
												$argument_missing = FALSE;
											}
										}
									}							
								}
							}
						}

						if ( $argument_missing )
						{
							if ( $arg[ 'required' ] )
							{
								/* Operation cannot be invoked because we're missing a required argument */
								if ( $rule = $operation->rule() and $rule->debug )
								{
									static::rulesLog( $event, $operation->rule(), $operation, "No argument available for: " . $arg_name, 'Operation skipped (missing argument)' );
								}
								return NULL;
							}
							else
							{
								$operation_args[] = NULL;
							}
						}
					}
				}
				
				/**
				 * Now that we have our argument list, time to execute the operation callback
				 */
				if ( isset ( $operation->definition[ 'callback' ] ) and is_callable( $operation->definition[ 'callback' ] ) )
				{
					/**
					 * Perform token replacements on string value arguments
					 */
					$tokens = static::getTokens( $event, $arg_map );
					foreach ( $operation_args as &$_operation_arg )
					{
						if ( in_array( gettype( $_operation_arg ), array( 'string' ) ) )
						{
							$_operation_arg = static::replaceTokens( $_operation_arg, $tokens );
						}
					}
					
					try
					{
						/**
						 * Check to see if actions have a future scheduling
						 */
						if ( $operation instanceof \IPS\rules\Action and $operation->schedule_mode )
						{
							$future_time = 0;
							switch ( $operation->schedule_mode )
							{
								/**
								 * Defer to end of rule processing
								 */
								case 1:
									$result = '__suppress__';
									$event->actionStack[] = array
									(
										'action' 	=> $operation,
										'args' 	 	=> $operation_args,
										'event_args' 	=> $arg_map,
										'thread' 	=> $event->thread,
										'parent' 	=> $event->parentThread,
									);
									break;
									
								/**
								 * Set amount of time in the future 
								 */
								case 2:
									$future_time = \strtotime
									( 
										'+' . intval( $operation->schedule_months ) . ' months ' . 
										'+' . intval( $operation->schedule_days ) . ' days ' .
										'+' . intval( $operation->schedule_hours ) . ' hours ' .
										'+' . intval( $operation->schedule_minutes ) . ' minutes '
									);
									break;
									
								/**
								 * On a specific date/time
								 */
								case 3:
									$future_time = $operation->schedule_date;
									break;
									
								/**
								 * On a calculated date
								 */
								case 4:
									$evaluate = function( $phpcode ) use ( $arg_map )
									{
										extract( $arg_map );
										return @eval( $phpcode );
									};
									
									$custom_time = $evaluate( $operation->schedule_customcode );
									
									if ( is_numeric( $custom_time ) )
									{
										$future_time = intval( $custom_time );
									}
									else if ( is_object( $custom_time ) )
									{
										if ( $custom_time instanceof \IPS\DateTime )
										{
											$future_time = $custom_time->getTimestamp();
										}
									}
									else if ( is_string( $custom_time ) )
									{
										$future_time = strtotime( $custom_time );
									}
									break;
									
								/**
								 * At the end of the page load
								 */
								case 5:
								
									if ( ! \IPS\rules\Application::$shutDown )
									{
										$result = '__suppress__';
										static::$actionQueue[] = array
										(
											'event'	=> $event,
											'action' => array
											(
												'action' 	=> $operation,
												'args' 	 	=> $operation_args,
												'event_args' 	=> $arg_map,
												'thread' 	=> $event->thread,
												'parent' 	=> $event->parentThread,
											),
										);
									}
									else
									{
										$result = 'Action skipped. Page shut down already initiated.';
									}
									break;
									
							}
							
							if ( $future_time > time() )
							{
								$thread = $parentThread = NULL;
								
								if ( $rule = $operation->rule() )
								{
									$thread 	= $rule->event()->thread;
									$parentThread 	= $rule->event()->parentThread;
								}
								
								$unique_key = $operation->schedule_key ? static::replaceTokens( $operation->schedule_key, $tokens ) : NULL;
								$result = static::scheduleAction( $operation, $future_time, $operation_args, $arg_map, $thread, $parentThread, $unique_key );
							}
							
						}
					
						/**
						 * If our operation was scheduled, then it will have a result already from the scheduler
						 */
						if ( ! isset ( $result ) )
						{
							$result = call_user_func_array( $operation->definition[ 'callback' ], array_merge( $operation_args, array( $operation->data[ 'configuration' ][ 'data' ], $arg_map, $operation ) ) );					
						}
						
						/**
						 * Conditions have a special setting to invert their result with NOT, so let's check that 
						 */
						if ( $operation instanceof \IPS\rules\Condition and $operation->not )
						{
							$result = ! $result;
						}
						
						if ( $rule = $operation->rule() and $rule->debug and $result !== '__suppress__' )
						{
							static::rulesLog( $rule->event(), $rule, $operation, $result, 'Evaluated' );
						}
						
						return $result;
					}
					catch ( \Exception $e ) 
					{
						/**
						 * Log exceptions that happen during operation execution
						 */
						$event = $operation->rule() ? $operation->rule()->event() : NULL;
						$paths = explode( '/', str_replace( '\\', '/', $e->getFile() ) );
						$file = array_pop( $paths );
						static::rulesLog( $event, $operation->rule(), $operation, $e->getMessage() . '<br>Line: ' . $e->getLine() . ' of ' . $file, 'Operation Callback Exception', 1 );
					}
				}
				else
				{
					if ( $rule = $operation->rule() )
					{
						static::rulesLog( $rule->event(), $rule, $operation, FALSE, 'Missing Callback', 1  );
					}
				}
			}
			catch ( \Exception $e )
			{
				/**
				 * Log exceptions that happen during argument preparation
				 */
				$event = $operation->rule() ? $operation->rule()->event() : NULL;
				$paths = explode( '/', str_replace( '\\', '/', $e->getFile() ) );
				$file = array_pop( $paths );
				static::rulesLog( $event, $operation->rule(), $operation, $e->getMessage() . '<br>Line: ' . $e->getLine() . ' of ' . $file, "Argument Callback Exception ({$arg_name})", 1 );
			}
		}
		else
		{
			/**
			 * Log non-invokable action
			 */
			$event = $operation->rule() ? $operation->rule()->event() : NULL;
			static::rulesLog( $event, $operation->rule(), $operation, FALSE, 'Operation aborted. (Missing Definition)', 1 );		
		}
	}

	/**
	 * @brief	Tokens Cache
	 */
	public static $tokensCache = array();
	
	/**
	 * Build Event Tokens
	 *
	 * @param 	\IPS\rules\Event 	$event 		The rules event
	 * @param	array|NULL		$arg_map	An associative array of the event arguments, if NULL then token/descriptions will be generated
	 * @return	array					An associative array of token/var replacements
	 */
	public static function getTokens( $event, $arg_map=NULL )
	{
		$cache_key = isset ( $arg_map ) ? $event->thread : 'descriptions';
		
		if ( isset ( static::$tokensCache[ $cache_key ] ) )
		{
			return static::$tokensCache[ $cache_key ];
		}
		
		$global_args 		= static::getGlobalArguments();
		$classConverters 	= static::getConversions();
		$replacements 		= array();		
		$string_types 		= array( 'string', 'int', 'float' );
		
		$arg_groups = array
		(
			'event' => $event->data[ 'arguments' ] ?: array(),
			'global' => $global_args,
		);
		
		foreach ( $arg_groups as $group => $all_arguments )
		{
			foreach( $all_arguments as $arg_name => $argument )
			{
				/**
				 * Check if the event argument is string replaceable
				 */
				if ( in_array( $argument[ 'argtype' ], $string_types ) )
				{
					/* Building token values */
					if ( isset ( $arg_map ) )
					{
						$replacements[ '[' . $arg_name . ']' ] = $replacements[ '~' . $arg_name . '~' ] = (string) $arg_map[ $arg_name ];
					}
					/* Building token description */
					else
					{
						$replacements[ '[' . $arg_name . ']' ] = "The value of the '" . $arg_name . "' argument";
					}
				}

				/**
				 * Add in any other arguments that we can derive from the event argument as options also
				 */
				if ( in_array( $argument[ 'argtype' ], array( 'object', 'array' ) ) and isset( $argument[ 'class' ] ) )
				{				
					if ( $derivative_arguments = static::classConverters( $argument ) )
					{
						foreach ( $derivative_arguments as $map_key => $derivative_argument )
						{
							list( $converter_class, $converter_key ) = explode( ':', $map_key );
							
							if ( in_array( $derivative_argument[ 'argtype' ], $string_types ) or isset( $classConverters[ $converter_class ][ $converter_key ][ 'tokenValue' ] ) )
							{
								if 
								( 
									isset ( $classConverters[ $converter_class ][ $converter_key ][ 'token' ] ) and 
									is_callable( $classConverters[ $converter_class ][ $converter_key ][ 'converter' ] ) 
								)
								{
									$input_arg = NULL;
									$arg_name_token = NULL;
									$arg_name_description = NULL;
									$tokenValue = '';
									
									/**
									 * Building Token Values
									 */
									if ( isset ( $arg_map ) )
									{
										switch( $group )
										{
											case 'event':
											
												$input_arg = $arg_map[ $arg_name ];
												$arg_name_token = $arg_name;
												break;
												
											case 'global':
										
												if 
												( 
													isset( $global_args[ $arg_name ] ) and 
													isset( $global_args[ $arg_name ][ 'token' ] ) and
													is_callable( $global_args[ $arg_name ][ 'getArg' ] ) )
												{
													$arg_name_token = 'global:' . $global_args[ $arg_name ][ 'token' ];
													$input_arg = call_user_func( $global_args[ $arg_name ][ 'getArg' ] );
												}
												break;
										}
										
										if ( isset( $arg_name_token ) )
										{
											/* Tokens will only be calculated if needed */
											$tokenValue = new \IPS\rules\Event\Token( $input_arg, $classConverters[ $converter_class ][ $converter_key ] );	
											$replacements[ '[' . $arg_name_token . ":" . $classConverters[ $converter_class ][ $converter_key ][ 'token' ] . ']' ] = $replacements[ '~' . $arg_name_token . ":" . $classConverters[ $converter_class ][ $converter_key ][ 'token' ] . '~' ] = $tokenValue;
										}
									}
									
									/**
									 * Building Token Descriptions
									 */
									else
									{
										switch ( $group )
										{
											case 'event':
												$arg_name_token = $arg_name;
												break;
											
											case 'global':
												if ( 
													isset( $global_args[ $arg_name ] ) and 
													isset( $global_args[ $arg_name ][ 'token' ] )
												)
												{
													$arg_name_token = 'global:' . $global_args[ $arg_name ][ 'token' ];
													$arg_name_description = ( isset( $global_args[ $arg_name ][ 'description' ] ) and $global_args[ $arg_name ][ 'description' ] ) ? ' for ' . $global_args[ $arg_name ][ 'description' ] : '';
												}
												break;
										}
										
										if ( isset( $arg_name_token ) )
										{
											$replacements[ '[' . $arg_name_token . ":" . $classConverters[ $converter_class ][ $converter_key ][ 'token' ] . ']' ] = $classConverters[ $converter_class ][ $converter_key ][ 'description' ] . $arg_name_description;
										}
									}
								}
							}
						}
					}						
				}				
			}
		}
				
		return static::$tokensCache[ $cache_key ] = $replacements;
	}
	
	/**
	 * Replace Tokens
	 * 
	 * @param 	string	$string		The string with possible tokens to replace
	 * @param	array	$replacements	An array of string replacement values
	 * @return	string			The string with tokens replaced
	 */
	public static function replaceTokens( $string, $replacements )
	{
		if ( empty( $replacements ) or ! is_array( $replacements ) )
		{
			return $string;
		}
		
		return strtr( $string, $replacements );
	}

	/**
	 * Get Global Arguments
	 *
	 * @return 	array		Keyed array of global arguments
	 */
	public static function getGlobalArguments()
	{
		if ( isset ( static::$globalArguments ) )
		{
			return static::$globalArguments;
		}
		
		static::$globalArguments = array();
		
		foreach ( static::rulesExtensions( 'Conversions' ) as $app => $classes )
		{
			foreach ( $classes as $class => $ext )
			{
				/**
				 * Global arguments have a special prefix added to their name
				 * so that we can identify them as global arguments later on.
				 */
				$_globalArguments = array();
				foreach ( $ext->globalArguments() as $arg_name => $arg )
				{
					$_globalArguments[ '__global_' . $arg_name ] = $arg;
				}
				static::$globalArguments = array_replace_recursive( static::$globalArguments, $_globalArguments );
			}
		}
		
		return static::$globalArguments;
	}
	
	/**
	 * Check For Class Compliance
	 *
	 * @param	string 		$class		Class to check compliance
	 * @param	string|array	$classes	A classname or array of classnames to validate against
	 * @return	bool				Will return TRUE if $class is the same as or is a subclass of any $classes
	 */
	public static function classCompliant( $class, $classes )
	{
		return parent::classCompliant( $class, $classes );
	}
	
	/**
	 * Class Converters
	 *
	 * Based on the argument provided, returns an array map of alternative arguments that it can
	 * be converted into
	 *
	 * @param	array	$event_argument		The argument definition provided by the event
	 * @param	array	$type_def		The argument definition required by the operation
	 * @return	array				Class converter methods
	 */
	public static function classConverters( $event_argument, $type_def=array() )
	{
		if ( ! isset( $event_argument[ 'class' ] ) )
		{
			return array();
		}
		
		$conversion_arguments	= array();
		$mappings		= array();
		$current_class 		= $event_argument[ 'class' ]; 
		$acceptable_classes 	= isset( $type_def[ 'class' ] ) ? (array) $type_def[ 'class' ] : array();
		
		/**
		 * If the operation argument does not require any specific
		 * class(es) of object, then any class is acceptable
		 */
		if ( empty ( $acceptable_classes ) )
		{
			$acceptable_classes = array( '*' );
		}

		/**
		 * Build a map of all the classes in our converter map that are compliant 
		 * with our event argument, meaning our event argument is the same as or a
		 * subclass of the convertable class
		 */
		foreach ( static::getConversions() as $base_class => $conversions )
		{
			if ( static::classCompliant( $current_class, $base_class ) )
			{
				$mappings[ $base_class ] = $conversions;
			}
		}
		
		/**
		 * For every class that has conversions available and that our event argument is compliant with,
		 * we look at each of the conversion options available and see if any of them convert into a class
		 * that can then be used as an operation argument. 
		 */
		foreach ( $mappings as $base_class => $conversions )
		{
			foreach ( $conversions as $conversion_key => $argument )
			{
				foreach ( $acceptable_classes as $acceptable_class )
				{
					if ( $acceptable_class === '*' or ( isset( $argument[ 'class' ] ) and static::classCompliant( $argument[ 'class' ], $acceptable_class ) ) )
					{
						$conversion_arguments[ $base_class . ':' . $conversion_key ] = $argument;
					}
				}
			}
		}
		
		return $conversion_arguments;
	}
	
	/**
	 * Get Class Conversion Mappings
	 * 
	 * @param 	string|NULL	$class		A specific class to return conversions for, NULL for all
	 * @return	array				Class conversion definitions
	 */
	public static function getConversions( $class=NULL )
	{
		if ( isset ( static::$classMap ) )
		{
			return isset( $class ) ? static::$classMap[ $class ] : static::$classMap;
		}
		
		static::$classMap = array();
		
		foreach ( static::rulesExtensions( 'Conversions' ) as $app => $classes )
		{
			foreach ( $classes as $_class => $ext )
			{
				static::$classMap = array_replace_recursive( static::$classMap, $ext->conversionMap() );
			}
		}
						
		return static::getConversions( $class );		
	}

	/**
	 * Schedule An Action
	 *
	 * @param 	\IPS\rules\Action	$action		The action to schedule
	 * @param	int			$time		The timestamp of when the action is scheduled
	 * @param	array			$args		The arguments to send to the action
	 * @param	array			$event_args	The arguments from the event
	 * @param	string			$thread		The event thread to tie the action back to (for debugging)
	 * @param	string			$parentThread	The events parent thread to tie the action back to (for debugging)
	 * @param	string|NULL		$unique_key	A unique key to identify the action for later updating/removal
	 * @return	mixed					A message to log to the database if debugging is on
	 */
	public static function scheduleAction( $action, $time, $args, $event_args, $thread, $parentThread, $unique_key=NULL )
	{
		/**
		 * Delete existing actions with the same unique key
		 */
		if ( isset( $unique_key ) and trim( $unique_key ) != '' )
		{
			\IPS\Db::i()->delete( 'rules_scheduled_actions', array( 'schedule_unique_key=?', trim( $unique_key ) ) );
		}
		
		$scheduled_action 		= new \IPS\rules\Action\Scheduled;		
		$scheduled_action->time 	= $time;
		$scheduled_action->action_id	= $action->id;
		$scheduled_action->thread	= $thread;
		$scheduled_action->parent_thread = $parentThread;
		$scheduled_action->created 	= time();
		$scheduled_action->unique_key	= trim( $unique_key );
		
		$db_args = array();
		foreach ( $args as $arg )
		{
			$db_args[] = static::storeArg( $arg );
		}
		
		$db_event_args = array();
		foreach ( $event_args as $key => $arg )
		{
			$db_event_args[ $key ] = static::storeArg( $arg );
		}
		
		$scheduled_action->data = json_encode( array
		(
			'args' => $db_args,
			'event_args' => $db_event_args,
		) );
		
		$scheduled_action->save();
		
		return "Action Scheduled (ID#{$scheduled_action->id}): " . \IPS\DateTime::ts( $time );
	}

	/**
	 * Prepare an argument for database storage
	 *
	 * Known objects are stored in a way that they can be easily reconstructed
	 * into original form. All other objects will be cast into stdClass when restored.
	 *
	 * @param 	mixed		$arg		The argument to store
	 * @return	mixed				An argument which can be json encoded
	 */
	public static function storeArg( $arg )
	{
		/* Walk through arrays recursively to store arguments */
		if ( is_array( $arg ) )
		{
			$arg_array = array();
			
			foreach ( $arg as $k => $_arg )
			{
				$arg_array[ $k ] = static::storeArg( $_arg );
			}
			
			return $arg_array;
		}
		
		if ( ! is_object( $arg ) )
		{
			return $arg;
		}
		
		/**
		 * Active Record
		 */
		if ( $arg instanceof \IPS\Patterns\ActiveRecord )
		{
			$idColumn = $arg::$databaseColumnId;
			$dbstore = array( '_obj_class' => '\\' . get_class( $arg ), 'id' => $arg->$idColumn );
		}
		
		/**
		 * DateTime Object
		 */
		else if ( $arg instanceof \IPS\DateTime )
		{
			$dbstore = array( '_obj_class' => '\IPS\DateTime', 'timestamp' => $arg->getTimestamp() );
		}
		
		/**
		 * Url Object
		 */
		else if ( $arg instanceof \IPS\Http\Url )
		{
			$dbstore = array( '_obj_class' => '\IPS\Http\Url', 'url' => (string) $arg );
		}
		
		/**
		 * Generic Object
		 */
		else
		{
			$dbstore = array( '_obj_class' => 'stdClass', 'data' => (array) $arg );
		}
		
		return $dbstore;
	}

	/**
	 * Restore an argument from database storage
	 *
	 * @param 	object		$arg		The argument to restore
	 * @return	mixed				The restored argument
	 */
	public static function restoreArg( $arg )
	{
		if ( ! is_array( $arg ) )
		{
			return $arg;
		}
		
		/* If the array is not a stored object reference, walk through elements recursively to restore values */
		if ( ! isset ( $arg[ '_obj_class' ] ) )
		{
			$arg_array = array();
			
			foreach ( $arg as $k => $_arg )
			{
				$arg_array[ $k ] = static::restoreArg( $_arg );
			}

			return $arg_array;
		}
		
		/**
		 * Generic Object
		 */
		if ( $arg[ '_obj_class' ] == 'stdClass' )
		{
			return (object) $arg[ 'data' ];
		}
		
		/**
		 * DateTime Object
		 */
		else if ( $arg[ '_obj_class' ] == '\IPS\DateTime' )
		{
			return \IPS\DateTime::ts( $arg[ 'timestamp' ] );
		}
		
		/**
		 * Url Object
		 */
		else if ( $arg[ '_obj_class' ] == '\IPS\Http\Url' )
		{
			return new \IPS\Http\Url( $arg[ 'url' ] );
		}
		
		/**
		 * Active Record
		 */
		else
		{
			$class = $arg[ '_obj_class' ];
			try
			{
				return $class::load( $arg[ 'id' ] );
			}
			catch ( \Exception $e )
			{
				return NULL;
			}
		}		
	}

	/**
	 * Recursion Protection
	 */
	public static $locked = FALSE;
	
	/**
	 * Create a Rules Log
	 *
	 * @param	\IPS\rules\Event	$event		The event associated with the log
	 * @param	\IPS\rules\Rule|NULL	$rule		The rule associated with the log
	 * @param	\IPS\rules\Action	$operation	The condition or action associated with the log
	 * @param	mixed			$result		The value returned by the operation or log event
	 * @param	string			$message	The reason for the log
	 * @param	int			$error		The error code, or zero indicating a debug log
	 * @return 	void
	 */
	public static function rulesLog( $event, $rule, $operation, $result, $message='', $error=0 )
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
			$log->rule_parent = is_object ( $rule ) 	? $rule->parent_id		: 0; 
			$log->op_id	= is_object( $operation ) 	? $operation->id		: 0;
			$log->type 	= is_object( $operation ) 	? get_class( $operation )	: NULL;
			$log->result 	= json_encode( $result );
			$log->message 	= $message;
			$log->error	= $error;
			$log->time 	= time();
			
			$log->save();
			
			static::$locked = FALSE;
		}
	}
	
	/**
	 * Get Scheduled Actions
	 *
	 * @param	bool		$priorityCycle		Run priority scheduled actions
	 * @return	array
	 */
	public function scheduledActions( $priorityCycle=FALSE )
	{
		return iterator_to_array( new \IPS\Patterns\ActiveRecordIterator( \IPS\Db::i()->select( '*', 'rules_scheduled_actions', array( 'schedule_time<=? AND schedule_queued<1', time() ), 'schedule_time ASC' ), 'IPS\rules\Action\Scheduled' ) );
	}
	
	/**
	 * Get Event Argument Info
	 *
	 * @param 	\IPS\rules\Event	$event		The constructed event object
	 * @param	string			$_p		Display prefix for the argument names
	 * @return	string					Html info about the event
	 */
	public static function eventArgInfo( $event, $_p='$' )
	{
		if ( ! $event ) 
		{
			return NULL;
		}
	
		$_event_arg_list = array();
		$lang = \IPS\Member::loggedIn()->language();
		
		if ( $event and isset( $event->data[ 'arguments' ] ) and is_array( $event->data[ 'arguments' ] ) )
		{
			foreach ( $event->data[ 'arguments' ] as $_event_arg_name => $_event_arg )
			{
				$eventArgNameKey = $event->app . '_' . $event->class . '_event_' . $event->key . '_' . $_event_arg_name;
				$_event_arg_list[] = "<strong>{$_p}{$_event_arg_name}</strong> - " . ( $lang->checkKeyExists( $eventArgNameKey ) ? \ucfirst( mb_strtolower( $lang->get( $eventArgNameKey ) ) ) : $eventArgNameKey ) . ( ( isset( $_event_arg[ 'nullable' ] ) and $_event_arg[ 'nullable' ] ) ? " ( may be NULL )" : "" );
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
	 * Event HTML Header
	 * 
	 * @param 	\IPS\rules\Event	$event		The event to display the header for
	 * @return	string					The template html
	 */
	public static function eventHeader( $event )
	{
		$tokens = static::getTokens( $event );
		return \IPS\Theme::i()->getTemplate( 'components' )->eventHeader( $event, $tokens );
	}
	
	/**
	 * Rule HTML Header
	 *
	 * @param 	\IPS\rules\Rule		$rule		The rule to display the header for
	 * @return	string					The template html
	 */
	public static function ruleHeader( $rule )
	{
		return \IPS\Theme::i()->getTemplate( 'components' )->ruleHeader( $rule );
	}
	
	/**
	 * Rule Child HTML Notice
	 *
	 * @param 	\IPS\rules\Rule		$rule		The rule parent
	 * @return	string					The template html
	 */
	public static function ruleChild( $rule )
	{
		return \IPS\Theme::i()->getTemplate( 'components' )->ruleChild( $rule );
	}
	
	/**
	 * Get URL
	 *
	 * @return	\IPS\Http\Url
	 */
	public function url()
	{
		$args = func_get_args();
		if ( $args[0] == 'update' )
		{
			return \IPS\Http\Url::external( $this->update_check )->setQueryString( array_merge( $this->appdata, array( 'ips_version' => \IPS\Application::load( 'core' )->version ) ) );
		}

		return parent::url();
	}
	
	/**
	 * Install JSON Data
	 */
	public function installJsonData( $skipMember=FALSE )
	{
		/* Update app version data */
		$versions = $this->getAllVersions();
		$lversions = array_keys( $versions );
		$hversions = array_values( $versions );
		$updates = $this->url( 'update' );
		
		if( count($versions) )
		{
			$ver = array_pop( $hversions );
			$version = array_pop( $lversions );
			$updates = $updates->setQueryString( array( 'ver' => $ver, 'version' => $version, 'installed' => 1 ) );
		}
		
		call_user_func_array( 'parent::installJsonData', func_get_args() );
		
		try { $updates->request()->get(); } catch( \Exception $e ) { }
	}
	
	/**
	 * Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{
		parent::delete();
		$this->url( 'update' )->setQueryString( 'installed', 0 )->request()->get();
	}
	
	/**
	 * Retrieve All Rules Definitions
	 *
	 * @param	string	$type	The type of extension to return
	 * @return 	array		Keyed array of rules extensions objects
	 */
	public static function rulesExtensions( $type )
	{
		static $extensions;
		
		if ( isset ( $extensions ) )
		{
			return $extensions[ $type ];
		}
		
		$apps 		= \IPS\Application::applications();
		$extensions	= array();
		
		foreach( $apps as $application )
		{
			foreach ( $application->extensions( 'rules', 'Definitions' ) as $key => $ext )
			{
				$extensions[ 'Definitions' ][ $application->directory ][ $key ] = $ext;
			}
			
			foreach ( $application->extensions( 'rules', 'Conversions' ) as $key => $ext )
			{
				$extensions[ 'Conversions' ][ $application->directory ][ $key ] = $ext;
			}
			
			foreach ( $application->extensions( 'rules', 'Values' ) as $key => $ext )
			{
				$extensions[ 'Values' ][ $application->directory ][ $key ] = $ext;
			}
		}
		
		return $extensions[ $type ];
	}
	
	/**
	 * Get Map of Rules Definitions
	 *
	 * @param	string		$definition_key		The key of a specific definition to return
	 * @return	mixed					Returns specific definitions, or all if no parameters are given
	 */
	public static function rulesDefinitions( $definition_key=NULL )
	{
		static $definitions;
		
		if ( isset( $definitions ) )
		{
			if ( isset ( $definition_key ) )
			{
				if ( isset ( $definitions[ $definition_key ] ) )
				{
					return $definitions[ $definition_key ];
				}
				else
				{
					return NULL;
				}
			}
			
			return $definitions;
		}
		
		$definitions = array();

		foreach ( static::rulesExtensions( 'Definitions' ) as $app => $classes )
		{
			foreach ( $classes as $class => $ext )
			{
				$definitions[ md5( $app . $class ) ] = array
				( 
					'group'		=> $ext->defaultGroup,
					'app' 		=> $app,
					'class' 	=> $class,
					'events' 	=> method_exists( $ext, 'events' ) 	? $ext->events() 	: array(),
					'conditions'	=> method_exists( $ext, 'conditions' ) 	? $ext->conditions() 	: array(),
					'actions' 	=> method_exists( $ext, 'actions' ) 	? $ext->actions() 	: array(),
				);
			}
		}
		
		return static::rulesDefinitions( $definition_key );
	}
	
	/**
	 * Shutdown Rules: Execute queued actions
	 *
	 * @return	void
	 */ 
	public static function shutDown()
	{
		if ( ! static::$shutDown )
		{
			/* No more actions should be queued from this point forward */
			static::$shutDown = TRUE;
			
			/**
			 * Run end of page queued actions
			 */
			while( $queued = array_shift( static::$actionQueue ) )
			{
				$event = $queued[ 'event' ];
				$action = array( $queued[ 'action' ] );
				
				$event->executeDeferred( $action );
			}
		}
	}
}

register_shutdown_function( function() { \IPS\rules\Application::shutDown(); } );

