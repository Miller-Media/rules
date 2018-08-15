<?php
/**
 * @brief		IPS4 Rules
 * @author		Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright		(c) 2014 - Kevin Carwile
 * @package		Rules
 * @since		6 Feb 2015
 */


namespace IPS\rules;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

const TEXT 	= 1;
const EDITOR	= 2;
const TEXTAREA	= 3;
const EMAIL	= 4;
const URL	= 5;
const PHONE	= 6;
const PASSWORD	= 7;
const COLOR	= 8;
const SELECT	= 9;
const RADIO	= 10;

/**
 * Node
 */
class _Data extends \IPS\Node\Model implements \IPS\Node\Permissions
{
	/**
	 * @brief	[ActiveRecord] Multiton Store
	 */
	protected static $multitons;
	
	/**
	 * @brief	[ActiveRecord] Database Table
	 */
	public static $databaseTable = 'rules_data';
	
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'data_';
		
	/**
	 * @brief	[ActiveRecord] Database ID Fields
	 */
	protected static $databaseIdFields = array( 'data_column_name', 'data_key' );
	
	/**
	 * @brief	[Node] Parent ID Database Column
	 */
	public static $databaseColumnParent = NULL;
		
	/**
	 * @brief	[Node] Order Database Column
	 */
	public static $databaseColumnOrder = 'weight';
	
	/**
	 * @brief	[Node] Node Title
	 */
	public static $nodeTitle = 'custom_data';
	
	/**
	 * @brief	[Node] App for permission index
	 */
	public static $permApp = 'rules';
	
	/**
	 * @brief	[Node] Type for permission index
	 */
	public static $permType = 'data_field';
	
	/**
	 * @brief	The map of permission columns
	 */
	public static $permissionMap = array(
		'view'			=> 'view',
		'edit'			=> 2,
	);
	
	/**
	 * @brief	[Node] Prefix string that is automatically prepended to permission matrix language strings
	 */
	public static $permissionLangPrefix = 'rules_';
	
	/**
	 * @brief	Use Modal Forms?
	 */
	public static $modalForms = FALSE;
	
	/**
	 * @brief	Original Data
	 */
	public $originalData = array();
	
	/**
	 *  Disable Copy Button
	 */	
	public $noCopyButton = TRUE;
	
	/**
	 *  Get Title
	 */
	public function get__title()
	{
		return $this->name;
	}
	
	/**
	 * Set Title
	 */
	public function set__title( $val )
	{
		$this->name = $val;
	}
	
	/**
	 * Get Node Description
	 */
	public function get__description()
	{		
		$typeTitle = $this->storedValueTitle();	
		return "<strong>{$this->entityTitle()} Data</strong> / <strong>" . 
			ucfirst( $this->type ) . "</strong>" . ( $typeTitle ? " ({$typeTitle})" : "" ) . " " .
			" <div style='display:inline-block; width:20px'></div> <span style='color:green'><i class='fa fa-key'></i> {$this->column_name}</span>"
			. ( $this->description ? "<br><i class='fa fa-caret-right'></i> " . $this->description : "" );
	}
	
	/**
	 * Get Object Title
	 */
	public function entityTitle()
	{
		$objClass 	= str_replace( '-', '\\', $this->class );
		$objTitle 	= $objClass;
		
		if ( $objClass == '\IPS\Member' ) { 					$objTitle = 'Member'; }
		else if ( is_subclass_of ( $objClass, '\IPS\Content\Item' ) ) { 	$objTitle = \IPS\Member::loggedIn()->language()->get( $objClass::$title ); }
		else if ( is_subclass_of ( $objClass, '\IPS\Node\Model' ) ) { 		$objTitle = \IPS\Member::loggedIn()->language()->get( $objClass::$nodeTitle ); }

		return $objTitle;
	}
	
	/**
	 * Get Stored Value Title
	 */
	public function storedValueTitle()
	{
		$typeTitle = NULL;
	
		if ( in_array( $this->type, array( 'array', 'object' ) ) )
		{
			if ( $this->type_class )
			{
				$typeClass = str_replace( '-', '\\', $this->type_class );
				if ( $typeClass == '\IPS\Member' ) { 					$typeTitle = 'Member'; }
				else if ( is_subclass_of ( $typeClass, '\IPS\Content\Item' ) ) { 	$typeTitle = \IPS\Member::loggedIn()->language()->get( $typeClass::$title ); }
				else if ( is_subclass_of ( $typeClass, '\IPS\Node\Model' ) ) { 		$typeTitle = \IPS\Member::loggedIn()->language()->get( $typeClass::$nodeTitle ); }
			}
		}
		
		return $typeTitle;
	}
	
	/**
	 * Get Description
	 */
	public function get_description()
	{

		return isset( $this->_data[ 'description' ] ) ? $this->_data[ 'description' ] : '';
	}
	
	/**
	 * Set Description
	 */
	public function set_description( $val )
	{
		$this->_data[ 'description' ] = $val;
	}
		
	/**
	 * [Node] Get whether or not this node is enabled
	 *
	 * @note	Return value NULL indicates the node cannot be enabled/disabled
	 * @return	bool|null
	 */
	protected function get__enabled()
	{
		return NULL;
	}

	/**
	 * [Node] Set whether or not this node is enabled
	 *
	 * @param	bool|int	$enabled	Whether to set it enabled or disabled
	 * @return	void
	 */
	protected function set__enabled( $enabled )
	{

	}
	
	/**
	 * Init
	 *
	 * @return	void
	 */
	public function init()
	{
		$this->originalData = $this->_data;
	}
	
	/**
	 * [Node] Get buttons to display in tree
	 * Example code explains return value
	 *
	 * @param	string	$url		Base URL
	 * @param	bool	$subnode	Is this a subnode?
	 * @return	array
	 */
	public function getButtons( $url, $subnode=FALSE )
	{
		$buttons = parent::getButtons( $url, $subnode );
		return $buttons;
	}
	
	/**
	 * [Node] Custom Badge
	 *
	 * @return	NULL|array	Null for no badge, or an array of badge data (0 => CSS class type, 1 => language string, 2 => optional raw HTML to show instead of language string)
	 */
	protected function get__badge()
	{
		if ( 1 )
		{
			switch ( $this->use_mode )
			{
				case 'public':
				
					return array(
						0	=> 'ipsBadge ipsBadge_positive',
						1	=> 'Public',
					);
					
				case 'admin':
				
					return array(
						0	=> 'ipsBadge ipsBadge_warning',
						1	=> 'Administrative',
					);
			}
		}
		
		return NULL;
	}
	
	/**
	 * [Node] Add/Edit Form
	 *
	 * @param	\IPS\Helpers\Form	$form	The form
	 * @return	void
	 */
	public function form( &$form )
	{
		$self = $this;
		$lang = \IPS\Member::loggedIn()->language();
		$configuration = json_decode( $this->configuration, TRUE ) ?: array();
		$wrap_chosen_prefix	= "<div data-controller='rules.admin.ui.chosen'>";
		$wrap_chosen_suffix	= "</div>";
		
		$form->add( new \IPS\Helpers\Form\Text( 'data_name', $this->name, TRUE, array() ) );
		$form->add( new \IPS\Helpers\Form\Text( 'data_description', $this->description, FALSE ) );
		
		if ( $this->id )
		{
			$form->add( new \IPS\Helpers\Form\Text( 'data_column_name', $this->column_name, TRUE, array(), function( $val ) use ( $self )
			{
				$val = mb_strtolower( $val );
				$val = str_replace( ' ', '_', $val );
				$val = preg_replace( '/[^A-Za-z0-9_]/', '', $val );
				$val = preg_replace( '/_{2,}/', '_', $val );
				$val = trim( $val, '_' );
				$val = $val ?: 'data';
				
				if ( $val != trim( mb_strtolower( \IPS\Request::i()->data_column_name ) ) )
				{
					throw new \InvalidArgumentException( 'rules_data_column_invalid' );
				}

				$data_class = \IPS\Request::i()->data_class;
				$this_id = (int) $self->id;
				
				if ( \IPS\Db::i()->select( 'COUNT(*)', 'rules_data', array( 'data_column_name=? AND data_class=? AND data_id!=?', $val, $data_class, $this_id ) )->first() )
				{
					throw new \InvalidArgumentException( 'data_column_not_unique' );
				}
			} ) );
		}
		
		$data_types = array
		(
			'object'	=> 'Object',
			'int' 		=> 'Integer',
			'float'		=> 'Decimal / Float',
			'string' 	=> 'String',
			'bool'		=> 'TRUE / FALSE',
			'array'		=> 'Array (multiple values)',
			'mixed'		=> 'Any Value',
		);
		
		$data_toggles = array
		(
			'object' 	=> array( 'data_use_mode', 'data_type_class', 'data_text_mode_unavailable', 'data_value_default_unusable' ),
			'int'		=> array( 'data_use_mode', 'data_text_mode_unavailable', 'data_value_default_usable' ),
			'float'		=> array( 'data_use_mode', 'data_text_mode_unavailable', 'data_value_default_usable' ),
			'string' 	=> array( 'data_use_mode', 'data_text_mode', 'data_text_mode_wrap', 'data_value_default_usable' ),
			'bool'		=> array( 'data_use_mode', 'data_text_mode_unavailable', 'data_value_default_usable' ),
			'array' 	=> array( 'data_use_mode', 'data_type_class', 'data_text_mode_unavailable', 'data_value_default_unusable' ),
		);
		
		/**
		 * Basic Object Classes
		 */
		$object_classes = array
		(
			'General' => array
			(
				''				=> 'Arbitrary',
				'-IPS-Member'			=> 'Member ( IPS\Member )',
				'-IPS-DateTime'			=> 'A Date/Time ( IPS\DateTime )',
				'-IPS-Http-Url'			=> 'A Url ( IPS\Http\Url )',
				'-IPS-Content'			=> 'Content ( IPS\Content )',
				'-IPS-Content-Item'		=> 'Content Item ( IPS\Content\Item )',
				'-IPS-Content-Comment'		=> 'Content Comment ( IPS\Content\Comment )',
				'-IPS-Content-Review'		=> 'Content Review ( IPS\Content\Review )',
				'-IPS-Node-Model'		=> 'Node ( IPS\Node\Model )',
				'-IPS-Patterns-ActiveRecord'	=> 'Active Record ( IPS\Patterns\ActiveRecord )',
			),
		);
		
		$object_classes_toggles = array();
		$object_classes_containers = array();
		
		$core_key = $lang->get( '__app_core' );
		
		/**
		 * Add additional content types
		 */
		foreach ( \IPS\Application::allExtensions( 'core', 'ContentRouter' ) as $router )
		{
			$appname = '';
			$_object_classes = array();
			foreach ( $router->classes as $contentItemClass )
			{
				if ( is_subclass_of( $contentItemClass, '\IPS\Content\Item' ) )
				{
					/* Set Appname */
					$appname = $appname ?: $lang->addToStack( '__app_' . $contentItemClass::$application );
					if ( $contentItemClass::$application == 'core' )
					{
						$core_key = $appname;
					}
					
					/* Add the content class */
					$_object_classes[ '-' . str_replace( '\\', '-', $contentItemClass ) ] =  ucwords( $lang->checkKeyExists( $contentItemClass::$title ) ? $lang->get( $contentItemClass::$title ) : '' ) . ' ( ' . $contentItemClass . ' )';
					
					$hasNode = FALSE;
					
					/* Add node class */
					if ( isset( $contentItemClass::$containerNodeClass ) and $nodeClass = $contentItemClass::$containerNodeClass )
					{
						$hasNode = TRUE;
						$_object_classes[ '-' . str_replace( '\\', '-', $nodeClass ) ] = $lang->addToStack( $nodeClass::$nodeTitle ) . ' ( ' . $nodeClass . ' )';
						
						$lang->words[ 'containers-' . str_replace( '\\', '-', $nodeClass ) ] = $lang->checkKeyExists( $nodeClass::$nodeTitle ) ? $lang->get( $nodeClass::$nodeTitle ) : $nodeClass::$nodeTitle;
						$object_classes_containers[] = new \IPS\Helpers\Form\Node( 'containers-' . str_replace( '\\', '-', $nodeClass ), isset( $configuration[ 'containers-' . str_replace( '\\', '-', $nodeClass ) ] ) ? $configuration[ 'containers-' . str_replace( '\\', '-', $nodeClass ) ] : 0, FALSE, array( 'class' => $nodeClass, 'multiple' => TRUE, 'subnodes' => FALSE, 'zeroVal' => 'All' ), NULL, NULL, NULL, 'containers-' . str_replace( '\\', '-', $nodeClass ) );
						$object_classes_toggles[ '-' . str_replace( '\\', '-', $contentItemClass ) ] = array( 'containers-' . str_replace( '\\', '-', $nodeClass ) );
						$object_classes_toggles[ '-' . str_replace( '\\', '-', $nodeClass ) ] = array( 'containers-' . str_replace( '\\', '-', $nodeClass ) );
					}
					
					/* Add comment class */
					if ( isset( $contentItemClass::$commentClass ) and $commentClass = $contentItemClass::$commentClass )
					{
						$_object_classes[ '-' . str_replace( '\\', '-', $commentClass ) ] = ucwords( $lang->checkKeyExists( $contentItemClass::$title ) ? $lang->get( $contentItemClass::$title ) . ' Comment' : '' ) . ' ( ' . $commentClass . ' )';
						
						if ( $hasNode )
						{
							$object_classes_toggles[ '-' . str_replace( '\\', '-', $commentClass ) ] = array( 'containers-' . str_replace( '\\', '-', $nodeClass ) );						
						}
					}
					
					/* Add review class */
					if ( isset( $contentItemClass::$reviewClass ) and $reviewClass = $contentItemClass::$reviewClass )
					{
						$_object_classes[ '-' . str_replace( '\\', '-', $reviewClass ) ] = ucwords( $lang->checkKeyExists( $contentItemClass::$title ) ? $lang->get( $contentItemClass::$title ) . ' Review' : '' ) . ' ( ' . $reviewClass . ' )';
						
						if ( $hasNode )
						{
							$object_classes_toggles[ '-' . str_replace( '\\', '-', $reviewClass ) ] = array( 'containers-' . str_replace( '\\', '-', $nodeClass ) );						
						}
					}
					
				}
			}
			
			$object_classes[ $appname ] = array_merge( ( isset( $object_classes[ $appname ] ) ? $object_classes[ $appname ] : [] ), $_object_classes );
		}
		
		$data_classes = array
		(
			$core_key => array
			(
				'-IPS-Member'			=> 'Member ( IPS\Member )',
			),
		);				
		
		$data_classes = array_replace_recursive( $data_classes, $object_classes );
		unset( $data_classes[ 'General' ] );
		
		$column_name = 'data_' . $this->column_name;
		$field_locked = 
		(
			\IPS\Db::i()->checkForTable( $this::getTableName( $this->class ) ) and 
			\IPS\Db::i()->checkForColumn( $this::getTableName( $this->class ), 'data_' . $this->column_name ) and
			\IPS\Db::i()->select( 'COUNT(*)', $this::getTableName( $this->class ), array( $column_name . ' > \'\' ') )->first()
		);
		
		$data_display_options = array
		(
			'automatic'	=> 'Automatic',
			'manual'	=> 'Manual',
		);
		
		$data_use_options = array
		(
			'internal' 	=> 'Internal Use Only',
			'public'	=> 'Public Use',
			'admin'		=> 'Administrative Use',
		);
		
		$data_use_toggles = array
		(
			'public' => array( 'data_tab', 'data_required', 'data_text_mode', 'data_value_default' ),
			'admin' => array( 'data_tab', 'data_required', 'data_text_mode', 'data_value_default' ),
		);
		
		$data_text_modes = array
		(
			TEXT 		=> 'Text Field',
			TEXTAREA 	=> 'Text Area',
			EDITOR 		=> 'Content Editor',
			URL		=> 'Url Input',
			EMAIL		=> 'Email Input',
			PASSWORD	=> 'Password Input',
			SELECT		=> 'Select Box',
			RADIO		=> 'Radio Buttons',
		);
		
		$data_text_mode_toggles = array
		(
			SELECT	=> array( 'data_value_options' ),
			RADIO	=> array( 'data_value_options' ),
		);
		
		$form->add( new \IPS\Helpers\Form\Select( 'data_class', $this->class ?: '-IPS-Member', FALSE, array( 'options' => $data_classes, 'disabled' => $field_locked, 'toggles' => $object_classes_toggles ), NULL, $wrap_chosen_prefix, $wrap_chosen_suffix, 'data_class' ) );
		
		foreach( $object_classes_containers as $nodeSelect )
		{
			$form->add( $nodeSelect );
		}
		
		$form->add( new \IPS\Helpers\Form\Select( 'data_type', $this->type ?: 'int', TRUE, array( 'options' => $data_types, 'toggles' => $data_toggles, 'disabled' => $field_locked ), NULL, $wrap_chosen_prefix, $wrap_chosen_suffix ) );
		$form->add( new \IPS\Helpers\Form\Select( 'data_type_class', $this->type_class ?: '', FALSE, array( 'options' => $object_classes, 'toggles' => array( 'custom' => array( 'data_custom_class' ) ), 'disabled' => $field_locked ), NULL, $wrap_chosen_prefix, $wrap_chosen_suffix, 'data_type_class' ) );
		
		$form->add( new \IPS\Helpers\Form\Radio( 'data_display_mode', $this->display_mode ?: 'automatic', TRUE, array( 'options' => $data_display_options ), NULL, NULL, NULL, 'data_display_mode' ) );
		$form->add( new \IPS\Helpers\Form\Radio( 'data_use_mode', $this->use_mode ?: 'internal', TRUE, array( 'options' => $data_use_options, 'toggles' => $data_use_toggles ), NULL, NULL, NULL, 'data_use_mode' ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'data_required', $this->required ?: 0, TRUE, array(), NULL, NULL, NULL, 'data_required' ) );
		$form->add( new \IPS\Helpers\Form\Radio( 'data_text_mode', $this->text_mode ?: 1, TRUE, array( 'options' => $data_text_modes, 'toggles' => $data_text_mode_toggles ), NULL, "<div id='data_text_mode_wrap'>", "</div><span id='data_text_mode_unavailable' class='ipsMessage ipsMessage_success'>Automatically Configured</span>", 'data_text_mode' ) );
		$form->add( new \IPS\Helpers\Form\Stack( 'data_value_options', json_decode( $this->value_options, TRUE ), FALSE, array( 'stackFieldType' => 'KeyValue' ), NULL, NULL, NULL, 'data_value_options' ) );
		$form->add( new \IPS\Helpers\Form\Textarea( 'data_value_default', $this->value_default, FALSE, array(), NULL, "<div id='data_value_default_usable'>", "</div><span id='data_value_default_unusable' class='ipsMessage ipsMessage_warning'>Unsupported</span>", 'data_value_default' ) );
		
		parent::form( $form );
	}

	/**
	 * Build Editing Form Elements
	 *
	 * @param	object		$hostObj		The item hosting the data ( member/node/content )
	 * @param	array		$values			An array of default values to use if there is no host object
	 */
	public function formElements( $hostObj=NULL, $values=array() )
	{
		$lang 		= \IPS\Member::loggedIn()->language();
		$form_name 	= 'rules_data_' . $this->column_name;
		$form_value 	= $hostObj ? $hostObj->getRulesData( $this->column_name ) : ( array_key_exists( $form_name, (array) $values ) ? $values[ $form_name ] : $this->value_default );
		
		/* Language */
		$lang->words[ $form_name ] 		= $this->name;
		$lang->words[ $form_name . '_desc' ] 	= $this->description;
		
		$formElements = array();
		
		switch ( $this->type )
		{
			case 'int':
				
				$formElements[ $form_name ] = new \IPS\Helpers\Form\Number( $form_name, $form_value, $this->required, array( 'min' => NULL ), NULL, NULL, NULL, $form_name );
				break;
				
			case 'float':
			
				$formElements[ $form_name ] = new \IPS\Helpers\Form\Number( $form_name, $form_value, $this->required, array( 'min' => NULL, 'decimals' => TRUE ), NULL, NULL, NULL, $form_name );
				break;
			
			case 'string':
			
				switch( $this->text_mode )
				{
					case EDITOR:
					
						$formElements[ $form_name ] = new \IPS\Helpers\Form\Editor( $form_name, $form_value, $this->required, array( 'app' => 'rules', 'key' => 'Generic' ) );
						break;
						
					case TEXTAREA:
					
						$formElements[ $form_name ] = new \IPS\Helpers\Form\TextArea( $form_name, $form_value, $this->required, array(), NULL, NULL, NULL, $form_name );
						break;
						
					case URL:
					
						$formElements[ $form_name ] = new \IPS\Helpers\Form\Url( $form_name, new \IPS\Http\Url( $form_value ), $this->required, array(), NULL, NULL, NULL, $form_name );
						break;
						
					case EMAIL:

						$formElements[ $form_name ] = new \IPS\Helpers\Form\Email( $form_name, $form_value, $this->required, array(), NULL, NULL, NULL, $form_name );
						break;
					
					case PASSWORD:

						$formElements[ $form_name ] = new \IPS\Helpers\Form\Password( $form_name, $form_value, $this->required, array(), NULL, NULL, NULL, $form_name );
						break;
						
					case SELECT:
						
						$options = array();
						if ( is_array( $value_options = json_decode( $this->value_options, TRUE ) ) )
						{
							foreach( $value_options as $option )
							{
								$options[ $option[ 'key' ] ] = $option[ 'value' ];
							}
						}
						$formElements[ $form_name ] = new \IPS\Helpers\Form\Select( $form_name, $form_value, $this->required, array( 'options' => $options ), NULL, NULL, NULL, $form_name );
						break;
						
					case RADIO:
						
						$options = array();
						if ( is_array( $value_options = json_decode( $this->value_options, TRUE ) ) )
						{
							foreach( $value_options as $option )
							{
								$options[ $option[ 'key' ] ] = $option[ 'value' ];
							}
						}
						$formElements[ $form_name ] = new \IPS\Helpers\Form\Radio( $form_name, $form_value, $this->required, array( 'options' => $options ), NULL, NULL, NULL, $form_name );
						break;
						
					case TEXT:
					default:
					
						$formElements[ $form_name ] = new \IPS\Helpers\Form\Text( $form_name, $form_value, $this->required, array(), NULL, NULL, NULL, $form_name );
						break;
				}
				break;
				
			case 'bool':
			
				$formElements[ $form_name ] = new \IPS\Helpers\Form\YesNo( $form_name, $form_value, $this->required, array(), NULL, NULL, NULL, $form_name );
				break;
				
			case 'object':
			
				$objectClass = str_replace( '-', '\\', $this->type_class );
				
				/* Node Select */
				if ( is_subclass_of( $objectClass, '\IPS\Node\Model' ) )
				{
					$formElements[ $form_name ] = new \IPS\Helpers\Form\Node( $form_name, $form_value, $this->required, array( 'class' => $objectClass, 'multiple' => FALSE, 'permissionCheck' => 'view' ), NULL, NULL, NULL, $form_name );
				}
				
				/* Content Select */
				else if ( is_subclass_of( $objectClass, '\IPS\Content\Item' ) )
				{
					$formElements[ $form_name ] = new \IPS\rules\Field\Content( $form_name, $form_value, $this->required, array( 'multiple' => 1, 'class' => $objectClass ), NULL, NULL, NULL, $form_name );
				}
				
				/* Member Select */
				else if ( $objectClass == '\IPS\Member' )
				{
					$formElements[ $form_name ] = new \IPS\Helpers\Form\Member( $form_name, $form_value, $this->required, array( 'multiple' => 1 ), NULL, NULL, NULL, $form_name );
				}
				
				/* Date Select */
				else if ( $objectClass == '\IPS\DateTime' )
				{
					$formElements[ $form_name ] = new \IPS\Helpers\Form\Date( $form_name, $form_value, $this->required, array( 'time' => TRUE ), NULL, NULL, NULL, $form_name );
				}
				
				/* Url Input */
				else if ( $objectClass == '\IPS\Http\Url' )
				{
					$formElements[ $form_name ] = new \IPS\Helpers\Form\Url( $form_name, $form_value, $this->required, array(), NULL, NULL, NULL, $form_name );
				}
				
				break;
			
			case 'array':
			
				$objectClass = str_replace( '-', '\\', $this->type_class );

				/* Multiple Node Select */
				if ( is_subclass_of( $objectClass, '\IPS\Node\Model' ) )
				{
					$formElements[ $form_name ] = new \IPS\Helpers\Form\Node( $form_name, $form_value, $this->required, array( 'class' => $objectClass, 'multiple' => TRUE, 'permissionCheck' => 'view' ), NULL, NULL, NULL, $form_name );
				}
				
				/* Multiple Content Select */
				else if ( is_subclass_of( $objectClass, '\IPS\Content\Item' ) )
				{
					$formElements[ $form_name ] = new \IPS\rules\Field\Content( $form_name, $form_value, $this->required, array( 'multiple' => NULL, 'class' => $objectClass ), NULL, NULL, NULL, $form_name );
				}
				
				/* Multiple Member Select */
				else if ( $objectClass == '\IPS\Member' )
				{
					$formElements[ $form_name ] = new \IPS\Helpers\Form\Member( $form_name, $form_value, $this->required, array( 'multiple' => NULL ), NULL, NULL, NULL, $form_name );
				}
				
				/* Multiple Date Select */
				else if ( $objectClass == '\IPS\DateTime' )
				{
					$formElements[ $form_name ] = new \IPS\Helpers\Form\Stack( $form_name, $form_value, $this->required, array( 'stackFieldType' => 'Date', 'time' => FALSE ), NULL, NULL, NULL, $form_name );
				}
				
				/* Multiple Urls */
				else if ( $objectClass == '\IPS\Http\Url' )
				{
					$formElements[ $form_name ] = new \IPS\Helpers\Form\Stack( $form_name, $form_value, $this->required, array( 'stackFieldType' => 'Url' ), NULL, NULL, NULL, $form_name );
				}				

				/* Multiple Arbitrary Values */
				else if ( $objectClass == '' )
				{
					$formElements[ $form_name ] = new \IPS\Helpers\Form\Stack( $form_name, $form_value, $this->required, array(), NULL, NULL, NULL, $form_name );
				}
				
				break;
		}
		
		return $formElements;
	}
	
	/**
	 * Get the value to save from submitted form values
	 *
	 * @param 	array	$values		The values from the form
	 * @return	mixed
	 */
	public function valueFromForm( $values )
	{
		$form_name 	= 'rules_data_' . $this->column_name;
		$form_value 	= isset( $values[ $form_name ] ) ? $values[ $form_name ] : NULL;
		
		if ( $this->type == 'string' )
		{
			$form_value = (string) $form_value;
		}
		
		/**
		 * Induce an object from a saved value
		 * Added to accomodate the IPS gallery image upload process
		 */
		if ( $form_value and $this->type == 'object' and ! is_object( $form_value ) )
		{
			$elements 		= $this->formElements( NULL, $values );
			$form_field 		= $elements[ $form_name ];
			$form_field->value 	= $form_value;
			$form_value 		= $form_field->formatValue();
		}		
		
		return $form_value;
	}
	
	/**
	 * [Node] Save Add/Edit Form
	 *
	 * @param	array	$values	Values from the form
	 * @return	void
	 */
	public function saveForm( $values )
	{
		$values[ 'data_column_name' ] = mb_strtolower( isset( $values[ 'data_column_name' ] ) ? $values[ 'data_column_name' ] : '' );
		$configuration = array();
		
		foreach( $values as $key => $value )
		{
			/**
			 * Save node container selections in configuration array
			 */
			if ( mb_substr( $key, 0, 11 ) == 'containers-' )
			{
				if ( is_array( $values[ $key ] ) )
				{
					foreach( $values[ $key ] as $node )
					{
						$configuration[ $key ][] = $node->_id;
					}
				}
				else
				{
					$configuration[ $key ] = $values[ $key ];
				}
				
				unset( $values[ $key ] );
			}
		}
		
		$values[ 'data_configuration' ] = json_encode( $configuration );
		$values[ 'data_value_options' ] = json_encode( $values[ 'data_value_options' ] );
		
		/* Default values are unsupported for some field types */
		if ( in_array( $values[ 'data_type' ], array( 'object', 'array' ) ) )
		{
			$values[ 'data_value_default' ] = NULL;
		}
		
		parent::saveForm( $values );
	}
	
	/**
	 * [ActiveRecord] Save 
	 */
	public function save()
	{
		/**
		 * Assign a unique key if needed
		 */
		if ( ! $this->key )
		{
			$this->key = md5( uniqid() . mt_rand() );
		}
		
		/**
		 * Work out a column name automatically
		 */
		if ( ! $this->column_name or $this->_new )
		{
			$keyname = mb_strtolower( $this->column_name ?: $this->name );
			$keyname = str_replace( ' ', '_', $keyname );
			$keyname = preg_replace( '/[^a-z0-9_]/', '', $keyname );
			$keyname = preg_replace( '/_{2,}/', '_', $keyname );
			$keyname = trim( $keyname, '_' );
			$keyname = $keyname ?: 'data';
			
			$num = '';
			while ( \IPS\Db::i()->select( 'COUNT(*)', 'rules_data', array( 'data_column_name=? AND data_class=? AND data_id!=?', $keyname . $num, $this->class, (int) $this->id ) )->first() )
			{
				/* Start at 1 */
				if ( $num === '' ) { $num = 1; }
				$num++;
			}
			
			$this->column_name = $keyname . $num;
		}
		
		/**
		 * Make database changes if things have changed
		 */
		if ( ! $this->_new )
		{			
			/**
			 * Moving to a new table, or storing data for a different object class?
			 * 
			 * DROP COLUMN
			 */
			if ( $this->originalData[ 'class' ] != $this->class or $this->originalData[ 'type_class' ] != $this->type_class or $this->originalData[ 'type' ] != $this->type )
			{
				try
				{
					\IPS\Db::i()->dropColumn( $this::getTableName( $this->originalData[ 'class' ] ), 'data_' . $this->originalData[ 'column_name' ] );
				}
				catch ( \IPS\Db\Exception $e ) {}
				
				/* If there are no data fields left for the old class, drop the table too */
				if ( ! \IPS\Db::i()->select( 'COUNT(*)', 'rules_data', array( 'data_class=? AND data_id!=?', $this->originalData[ 'class' ], $this->id ) )->first() )
				{
					try
					{
						\IPS\Db::i()->dropTable( $this::getTableName( $this->originalData[ 'class' ] ) );
					}
					catch ( \IPS\Db\Exception $e ) {}
				}
			}
			
			/**
			 * Just changing the column name?
			 *
			 * CHANGE COLUMN
			 */
			else if ( $this->originalData[ 'column_name' ] != $this->column_name )
			{
				\IPS\Db::i()->changeColumn( $this::getTableName( $this->class ), 'data_' . $this->originalData[ 'column_name' ], $this::columnDefinition( $this->type, $this->type_class, $this->column_name ) );
			}
		}
		
		/**
		 * Create a data table if we don't already have one
		 */
		if ( ! \IPS\Db::i()->checkForTable( $this::getTableName( $this->class ) ) )
		{
			\IPS\Db::i()->createTable( $this::tableDefinition( $this->class ) );
		}

		/**
		 * If we don't have a column for this data... create one
		 */
		if ( ! \IPS\Db::i()->checkForColumn( $this::getTableName( $this->class ), 'data_' . $this->column_name ) )
		{
			\IPS\Db::i()->addColumn( $this::getTableName( $this->class ), $this::columnDefinition( $this->type, $this->type_class, $this->column_name ) );
		}
		
		/**
		 * Update the original data
		 */
		$this->originalData = $this->_data;
		
		parent::save();
		
	}
	
	/**
	 * Check if a class is a concrete active record
	 */
	public static function isConcreteRecord( $class )
	{
		$objectClass = str_replace( '-', '\\', $class );
		try { $reflectedClass = new \ReflectionClass( $objectClass ); } catch( \ReflectionException $e ) { $reflectedClass = NULL; }
		
		return ( $reflectedClass !== NULL and ! $reflectedClass->isAbstract() and is_subclass_of( $objectClass, '\IPS\Patterns\ActiveRecord' ) );
	}
	
	/**
	 * Get Column Definition
	 */
	public static function columnDefinition( $type, $type_class, $column_name )
	{
		$field_decimals = NULL;
		
		switch ( $type )
		{
			case 'object':
							
				if ( ! static::isConcreteRecord( $type_class ) )
				{
					$field_type = 'MEDIUMTEXT';
					$field_length = NULL;
					break;
				}
				
				if ( $type_class == '-IPS-Http-Url' )
				{
					$field_type = 'VARCHAR';
					$field_length = 1028;
					break;
				}
			
			case 'int':
			case 'bool':
			
				$field_type = 'INT';
				$field_length = 20;
				break;
			
			case 'float':
				
				$field_type = 'FLOAT';
				$field_length = 22;
				$field_decimals = 10;
				break;

			case 'array':
			case 'string':
			case 'mixed':
			default:
			
				$field_type = 'MEDIUMTEXT';
				$field_length = NULL;
		}
		
		return array
		( 
			'name' 		=> 'data_' . $column_name, 
			'type' 		=> $field_type, 
			'length' 	=> $field_length, 
			'decimals' 	=> $field_decimals ,
			'allow_null' 	=> TRUE,
			'default' 	=> NULL,
		);
	}
	
	/**
	 * Get Table Definition
	 */
	public static function tableDefinition( $class )
	{
		$table_name = static::getTableName( $class );
		
		return array
		(
			'name' 		=> $table_name,
			'columns' 	=> array
			(
				'entity_id' => array
				(
					'name' => 'entity_id',
					'type' => 'int',
					'allow_null' => FALSE,
					'auto_increment' => FALSE,
					'binary' => FALSE,
					'comment' => '',
					'decimals' => NULL,
					'default' => NULL,
					'length' => 20,
					'unsigned' => FALSE,
					'values' => array(),
					'zerofill' => FALSE,
				),
			),
			'indexes' 	=> array
			(
				'PRIMARY' => array
				(
					'type' => 'primary',
					'name' => 'PRIMARY',
					'length' => array( NULL ),
					'columns' => array( 'entity_id' ),
				),
			),
		);	
	}
	
	/**
	 * Get Table Name
	 */
	public static function getTableName( $class )
	{
		$class = str_replace( '\\', '-', $class );
		$class = trim( $class, '-' );
		$table_suffix = mb_strtolower( $class );
		$table_suffix = str_replace( 'ips-', '', $table_suffix );
		$table_suffix = str_replace( '-', '_', $table_suffix );
		
		return 'rules_data_' . $table_suffix;
	}
	
	/**
	 * Get a display representation of some data
	 *
	 * @param 	mixed		$data		The data to convert into a display value
	 * @return	string				The value to display
	 */
	public static function dataDisplayValue( $data )
	{
		/**
		 * Standard data types
		 */
		if ( is_string( $data ) or is_numeric( $data ) )
		{
			return $data;
		}
		
		/**
		 * Boolean
		 */
		if ( is_bool( $data ) )
		{
			return $data ? "<i class='fa fa-check'></i>" : "<i class='fa fa-times'></i>";		
		}
		
		/**
		 * Arrays
		 */
		if ( is_array( $data ) )
		{
			$values = array();
			foreach( $data as $value )
			{
				$display = static::dataDisplayValue( $value );
				if ( $display !== NULL )
				{
					$values[] = $display;
				}
			}
			
			return implode( ', ', $values );
		}
		
		/**
		 * Objects
		 */
		if ( is_object( $data ) )
		{	
			/* Members */
			if ( $data instanceof \IPS\Member )
			{
				return "<a target='_blank' href='{$data->url()}'>{$data->name}</a>";
			}
			
			/* Content */
			else if ( $data instanceof \IPS\Content )
			{
				$title = "Content";
				
				if ( $data instanceof \IPS\Content\Comment )
				{
					return 'comment';
					if ( $item = $data->item() )
					{
						$title = $item->mapped( 'title' );
					}
				}
				else
				{
					$title = $data->mapped( 'title' );
				}
				
				if ( method_exists( $data, 'url' ) and $data->url() )
				{
					$title = "<a target='_blank' href='{$data->url()}'>{$title}</a>";
				}
				
				return $title;
			}
			
			/* Nodes */
			else if ( $data instanceof \IPS\Node\Model )
			{
				$title = $data->_title;
				if ( method_exists( $data, 'url' ) and $data->url() )
				{
					$title = "<a target='_blank' href='{$data->url()}'>{$title}</a>";
				}
				
				return $title;
			}
			
			/* Unknown */
			else
			{
				$title = "Object (" . get_class( $data ) . ")";
				
				if ( method_exists( $data, '__toString' ) )
				{
					$title = (string) $data;
				}
				else if ( $data->title )
				{
					$title = $data->title;
				}
				
				if ( method_exists( $data, 'url' ) and $data->url() )
				{
					$title = "<a target='_blank' href='{$data->url()}'>{$title}</a>";
				}
				
				return $title;
			}
		}
		
		return NULL;
	}

	/**
	 * Clone
	 */
	public function __clone()
	{
		$this->key = md5( uniqid() . mt_rand() );
		parent::__clone();
	}
	
	/**
	 * [ActiveRecord] Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{		
		$result = parent::delete();
		
		try
		{
			\IPS\Db::i()->dropColumn( $this::getTableName( $this->class ), 'data_' . $this->column_name );
		}
		catch ( \IPS\Db\Exception $e ) { }
		
		/* If there are no data fields left, drop the table too */
		if ( ! \IPS\Db::i()->select( 'COUNT(*)', 'rules_data', array( 'data_class=?', $this->class ) )->first() )
		{
			try
			{
				\IPS\Db::i()->dropTable( $this::getTableName( $this->class ) );
			}
			catch ( \IPS\Db\Exception $e ) { }
		}
		
		return $result;
	}	
	
}