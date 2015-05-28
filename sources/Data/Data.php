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
	 * @brief	Original Key
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

		return $this->_data[ 'description' ];
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
			'object' 	=> array( 'data_use_mode', 'data_type_class' ),
			'int'		=> array( 'data_use_mode' ),
			'float'		=> array( 'data_use_mode' ),
			'string' 	=> array( 'data_use_mode', 'data_text_mode', 'data_text_mode_wrap' ),
			'bool'		=> array( 'data_use_mode' ),
			'array' 	=> array( 'data_use_mode', 'data_type_class' ),
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
				'-IPS-Content'			=> 'Content ( IPS\Content )',
				'-IPS-Content-Item'		=> 'Content Item ( IPS\Content\Item )',
				'-IPS-Content-Comment'		=> 'Content Comment ( IPS\Content\Comment )',
				'-IPS-Content-Review'		=> 'Content Review ( IPS\Content\Review )',
				'-IPS-Node-Model'		=> 'Node ( IPS\Node\Model )',
				'-IPS-Patterns-ActiveRecord'	=> 'Active Record ( IPS\Patterns\ActiveRecord )',
			),
		);
		
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
				/* Set Appname */
				$appname = $appname ?: $lang->addToStack( '__app_' . $contentItemClass::$application );
				if ( $contentItemClass::$application == 'core' )
				{
					$core_key = $appname;
				}
				
				/* Add the content class */
				$_object_classes[ '-' . str_replace( '\\', '-', $contentItemClass ) ] =  ucwords( $lang->checkKeyExists( $contentItemClass::$title ) ? $lang->get( $contentItemClass::$title ) : '' ) . ' ( ' . $contentItemClass . ' )';
				
				/* Add node class */
				if ( isset( $contentItemClass::$containerNodeClass ) and $nodeClass = $contentItemClass::$containerNodeClass )
				{
					$_object_classes[ '-' . str_replace( '\\', '-', $nodeClass ) ] = $lang->addToStack( $nodeClass::$nodeTitle ) . ' ( ' . $nodeClass . ' )';
				}
			}
			
			$object_classes[ $appname ] = $_object_classes;
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
		
		$form->add( new \IPS\Helpers\Form\Select( 'data_class', $this->class ?: '-IPS-Member', FALSE, array( 'options' => $data_classes, 'disabled' => $field_locked ), NULL, $wrap_chosen_prefix, $wrap_chosen_suffix, 'data_class' ) );
		$form->add( new \IPS\Helpers\Form\Select( 'data_type', $this->type ?: 'string', TRUE, array( 'options' => $data_types, 'toggles' => $data_toggles, 'disabled' => $field_locked ), NULL, $wrap_chosen_prefix, $wrap_chosen_suffix ) );
		$form->add( new \IPS\Helpers\Form\Select( 'data_type_class', $this->type_class ?: '', FALSE, array( 'options' => $object_classes, 'toggles' => array( 'custom' => array( 'data_custom_class' ) ), 'disabled' => $field_locked ), NULL, $wrap_chosen_prefix, $wrap_chosen_suffix, 'data_type_class' ) );
		
		$data_use_options = array
		(
			'internal' 	=> 'Internal Use Only',
			'public'	=> 'Public Use',
			'admin'		=> 'Administrative Use',
		);
		
		$data_use_toggles = array
		(
			'public' => array( 'data_tab', 'data_required', 'data_text_mode' ),
			'admin' => array( 'data_tab', 'data_required', 'data_text_mode' ),
		);
		
		$data_text_modes = array
		(
			1 => 'Text Field',
			2 => 'Editor',
		);
		
		$form->add( new \IPS\Helpers\Form\Radio( 'data_use_mode', $this->use_mode ?: 'internal', TRUE, array( 'options' => $data_use_options, 'toggles' => $data_use_toggles ), NULL, NULL, NULL, 'data_use_mode' ) );
		//$form->add( new \IPS\Helpers\Form\Text( 'data_tab', $this->tab, FALSE, array(), NULL, NULL, NULL, 'data_tab' ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'data_required', $this->required, TRUE, array(), NULL, NULL, NULL, 'data_required' ) );
		$form->add( new \IPS\Helpers\Form\Radio( 'data_text_mode', $this->text_mode ?: 1, TRUE, array( 'options' => $data_text_modes ), NULL, "<div id='data_text_mode_wrap'>", "</div>", 'data_text_mode' ) );
		
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
		$form_value 	= $hostObj ? $hostObj->getRulesData( $this->column_name ) : $values[ $form_name ];
		
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
					case 2:
					
						$formElements[ $form_name ] = new \IPS\Helpers\Form\Editor( $form_name, $form_value, $this->required, array( 'app' => 'rules', 'key' => 'Generic' ) );
						break;
						
					case 1:
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
					// @TODO:
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
					// @TODO:
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
	 */
	public function valueFromForm( $values )
	{
		$form_name = 'rules_data_' . $this->column_name;
		return $values[ $form_name ];
	}
	
	/**
	 * Recursion Protection
	 */
	public $locked = FALSE;
	
	/**
	 * [Node] Save Add/Edit Form
	 *
	 * @param	array	$values	Values from the form
	 * @return	void
	 */
	public function saveForm( $values )
	{
		$values[ 'data_column_name' ] = mb_strtolower( $values[ 'data_column_name' ] );		
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
	 * Get Column Definition
	 */
	public static function columnDefinition( $type, $type_class, $column_name )
	{
		$field_decimals = NULL;
		
		switch ( $type )
		{
			case 'object':
			
				if ( ! $type_class )
				{
					$field_type = 'MEDIUMTEXT';
					$field_length = NULL;
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
				$field_decimals = 2;
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
	 * [ActiveRecord] Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{		
		parent::delete();
		
		try
		{
			\IPS\Db::i()->dropColumn( $this::getTableName( $this->class ), 'data_' . $this->column_name );
		}
		catch ( \IPS\Db\Exception $e ) {}
		
		/* If there are no data fields left, drop the table too */
		if ( ! \IPS\Db::i()->select( 'COUNT(*)', 'rules_data', array( 'data_class=?', $this->class ) )->first() )
		{
			try
			{
				\IPS\Db::i()->dropTable( $this::getTableName( $this->class ) );
			}
			catch ( \IPS\Db\Exception $e ) {}
		}
				
	}	
	
}