<?php
/**
 * @brief		IPS4 Rules
 * @author		Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright		(c) 2014 - Kevin Carwile
 * @package		Rules
 * @since		6 Feb 2015
 */


namespace IPS\rules\Action;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Node
 */
class _Argument extends \IPS\Node\Model
{
	/**
	 * @brief	[ActiveRecord] Multiton Store
	 */
	protected static $multitons;
	
	/**
	 * @brief	[ActiveRecord] Database Table
	 */
	public static $databaseTable = 'rules_arguments';
	
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'argument_';
		
	/**
	 * @brief	[Node] Order Database Column
	 */
	public static $databaseColumnOrder = 'weight';
	
	/**
	 * @brief	[ActiveRecord] Database ID Fields
	 */
	protected static $databaseIdFields = array();
	
	/**
	 * @brief	[Node] Parent ID Database Column
	 */
	public static $databaseColumnParent = NULL;
	
	/**
	 * @brief	[Node] Node Title
	 */
	public static $nodeTitle = 'custom_action_arguments';
	
	/**
	 * @brief	Use Modal Forms?
	 */
	public static $modalForms = FALSE;
	
	/** 
	 * @brief	Reserved variable names
	 */
	public static $reservedWords = array();
	
	/**
	 * @brief	Indicates if machine name needs to be unique to the argument class
	 */
	public static $uniqueToClass = FALSE;
	
	/**
	 * @brief	Parent Node Class
	 */
	public static $parentNodeClass = '\IPS\rules\Action\Custom';
	
	/**
	 * @brief	Parent Node ID
	 */
	public static $parentNodeColumnId = 'parent_id';
	
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
		return $this->description;
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
	 * Get Data
	 */ 
	public $data = array();
	
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
	 * @brief	Action Definition
	 */
	public $definition = NULL;
		
	/**
	 * Init
	 *
	 * @return	void
	 */
	public function init()
	{

	}
	
	/**
	 * [Node] Custom Badge
	 *
	 * @return	NULL|array	Null for no badge, or an array of badge data (0 => CSS class type, 1 => language string, 2 => optional raw HTML to show instead of language string)
	 */
	protected function get__badge()
	{
		return array(
			0	=> 'ipsBadge ipsBadge_positive',
			1	=> $this->type,
		);
		
		return NULL;
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
		
		$form->add( new \IPS\Helpers\Form\Text( 'argument_name', $this->name, TRUE, array() ) );
		
		if ( $this->id )
		{
			$reservedWords = static::$reservedWords;
			$uniqueToClass = static::$uniqueToClass;
			$databaseTable = static::$databaseTable;
			
			$form->add( new \IPS\Helpers\Form\Text( 'argument_varname', $this->varname, TRUE, array(), function( $val ) use ( $self, $reservedWords, $uniqueToClass, $databaseTable )
			{
				$val = str_replace( ' ', '_', $val );
				$val = preg_replace( '/[^A-Za-z0-9_]/', '', $val );
				$val = preg_replace( '/_{2,}/', '_', $val );
				$val = trim( $val, '_' );
				$val = $val ?: 'arg';
				
				if ( $val != trim( \IPS\Request::i()->argument_varname ) )
				{
					throw new \InvalidArgumentException( 'argument_name_invalid' );
				}
				
				/* Check reserved words */
				if ( in_array( $val, $reservedWords ) )
				{
					throw new \InvalidArgumentException( 'This name is reserved' );
				}
				
				if ( $self->id )
				{
					$parent_id = $self->parent_id;
					$this_id = $self->id;
				}
				else
				{
					$parent_id = \IPS\Request::i()->parent;
					$this_id = 0;
				}
				
				/* Check uniqueness to parent */
				if ( \IPS\Db::i()->select( 'COUNT(*)', $databaseTable, array( 'argument_varname=? AND argument_parent_id=? AND argument_id!=?', $val, $parent_id, $this_id ) )->first() )
				{
					throw new \InvalidArgumentException( 'argument_not_unique' );
				}
				
				/* Check uniqueness to class */
				if ( $uniqueToClass and \IPS\Db::i()->select( 'COUNT(*)', $databaseTable, array( 'argument_varname=? AND argument_class=? AND argument_id!=?', $val, $self->class, $this_id ) )->first() )
				{
					throw new \InvalidArgumentException( 'argument_not_unique_to_class' );					
				}
				
			} ) );
		}
		
		$form->add( new \IPS\Helpers\Form\Text( 'argument_description', $this->description, TRUE ) );
		
		$argument_types = array
		(
			'object'	=> 'Object',
			'int' 		=> 'Integer',
			'string' 	=> 'String',
			'array'		=> 'Array',
			'float'		=> 'Decimal / Float',
			'bool'		=> 'TRUE / FALSE',
			'mixed'		=> 'Mixed Values',
		);
		
		/**
		 * Basic Object Classes
		 */
		$object_classes = array
		(
			'General' => array
			(
				''				=> 'Arbitrary',
				'-IPS-Member'			=> 'Any Member ( IPS\Member )',
				'-IPS-Content'			=> 'Any Content ( IPS\Content )',
				'-IPS-DateTime'			=> 'A Date/Time ( IPS\DateTime )',
				'-IPS-Http-Url'			=> 'A Url ( IPS\Http\Url )',
				'-IPS-Content-Item'		=> 'Content Item ( IPS\Content\Item )',
				'-IPS-Content-Comment'		=> 'Content Comment ( IPS\Content\Comment )',
				'-IPS-Content-Review'		=> 'Content Review ( IPS\Content\Review )',
				'-IPS-Node-Model'		=> 'Content Container ( IPS\Node\Model )',
				'-IPS-Patterns-ActiveRecord'	=> 'Any Active Record ( IPS\Patterns\ActiveRecord )',
				'custom'			=> 'Custom Object Class',
			),
		);
		
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
					
					/* Add the content class */
					$_object_classes[ '-' . str_replace( '\\', '-', $contentItemClass ) ] =  ucwords( $lang->checkKeyExists( $contentItemClass::$title ) ? $lang->get( $contentItemClass::$title ) : '' ) . ' ( ' . $contentItemClass . ' )';
					
					/* Add node class */
					if ( isset( $contentItemClass::$containerNodeClass ) and $nodeClass = $contentItemClass::$containerNodeClass )
					{
						$_object_classes[ '-' . str_replace( '\\', '-', $nodeClass ) ] = $lang->addToStack( $nodeClass::$nodeTitle ) . ' ( ' . $nodeClass . ' )';
					}
				}
			}
			
			$object_classes[ $appname ] = array_merge( ( isset( $object_classes[ $appname ] ) ? $object_classes[ $appname ] : [] ), $_object_classes );
		}
		
		$form->add( new \IPS\Helpers\Form\Select( 'argument_type', $this->type, TRUE, array( 'options' => $argument_types, 'toggles' => array( 'object' => array( 'argument_class' ), 'array' => array( 'argument_class' ) ) ), NULL, $wrap_chosen_prefix, $wrap_chosen_suffix ) );
		$form->add( new \IPS\Helpers\Form\Select( 'argument_class', $this->class, FALSE, array( 'options' => $object_classes, 'toggles' => array( 'custom' => array( 'argument_custom_class' ) ) ), NULL, $wrap_chosen_prefix, $wrap_chosen_suffix, 'argument_class' ) );
		$form->add( new \IPS\Helpers\Form\Text( 'argument_custom_class', $this->custom_class, FALSE, array(), function( $val ) 
		{
			if ( $val )
			{
				if ( ! class_exists( $val ) )
				{
					throw new \InvalidArgumentException( 'Class does not exist' );
				}
			}
		}, NULL, NULL, 'argument_custom_class' ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'argument_required', $this->required !== NULL ? $this->required : TRUE, FALSE ) );
		
		parent::form( $form );
	}
		
	/**
	 * [Node] Save Add/Edit Form
	 *
	 * @param	array	$values	Values from the form
	 * @return	void
	 */
	public function saveForm( $values )
	{
		/**
		 * Work out a machine name for newly added arguments automatically
		 */
		if ( ! isset( $values[ 'argument_varname' ] ) or ! $values[ 'argument_varname' ] )
		{
			/* Standardize variable name */
			$varname = mb_strtolower( $values[ 'argument_name' ] );
			$varname = str_replace( ' ', '_', $varname );
			$varname = preg_replace( '/[^a-z0-9_]/', '', $varname );
			$varname = preg_replace( '/_{2,}/', '_', $varname );
			$varname = trim( $varname, '_' );
			$varname = $varname ?: 'arg';
			
			/* Check reserved words */
			if ( in_array( $varname, static::$reservedWords ) )
			{
				$varname = "log_" . $varname;
			}
			
			if ( $this->id )
			{
				$parent_id = $this->parent_id;
				$this_id = $this->id;
			}
			else
			{
				$parent_id = \IPS\Request::i()->parent;
				$this_id = 0;
			}
			
			$uniqueness = static::$uniqueToClass ? '' : ' AND argument_parent_id=' . $parent_id;			
			$num = '';
			
			while ( \IPS\Db::i()->select( 'COUNT(*)', static::$databaseTable, array( 'argument_varname=?' . $uniqueness . ' AND argument_id!=?', $varname . $num, $this_id ) )->first() )
			{
				/* Count up from 1 */
				$num = $num ? $num + 1 : 1;
			}
			
			$values[ 'argument_varname' ] = $varname . $num;
		}
		
		parent::saveForm( $values );
	}

}