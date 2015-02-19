<?php
/**
 * @brief		IPS4 Rules
 * @author		Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright		(c) 2014 - Kevin Carwile
 * @package		Rules
 * @since		6 Feb 2015
 */


namespace IPS\rules\Rule;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Node
 */
class _Aspect extends \IPS\Node\Model
{
	/**
	 * @brief	[ActiveRecord] Multiton Store
	 */
	protected static $multitons;
	
	/**
	 * @brief	[Node] Sortable?
	 */
	public static $nodeSortable = TRUE;
	
	/**
	 * @brief	[ActiveRecord] Database Table
	 */
	public static $databaseTable = NULL;
	
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = NULL;
		
	/**
	 * @brief	[Node] Order Database Column
	 */
	public static $databaseColumnOrder = NULL;
	
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
	public static $nodeTitle = 'aspect';
	
	/**
	 * @brief	Sub Node Class
	 */
	public static $subnodeClass = TRUE;
	
	/**
	 * @brief	Parent Node ID Column;
	 */
	public static $parentNodeColumnId = NULL;
	
	/**
	 * @brief	Associated Rule
	 */
	public $rule = NULL;
	
	/**
	 * @brief	Aspect Type
	 */
	public $type = NULL;
	
	/**
	 * @brief	Aspect Node Class
	 */
	public $aspectClass = NULL;
	
	/**
	 * Constructor
	 */
	public function __construct( $type, $rule=NULL )
	{
		switch ( $type )
		{
			case 'conditions':
			
				$this->title 		= \IPS\Member::loggedIn()->language()->get( 'conditions' );
				$this->aspectClass 	= '\IPS\rules\Condition';
				break;
				
			case 'actions':
			
				$this->title 		= \IPS\Member::loggedIn()->language()->get( 'actions' );
				$this->aspectClass	= '\IPS\rules\Action';
				break;
		}
		
		$this->type 	= $type;
		$this->rule 	= $rule;
		$this->id	= $this->type . '-' . $this->rule->id;
	}
	
	/**
	 * Load
	 */
	public static function load( $id, $idField = NULL, $extraWhereClause = NULL )
	{
		$parts = explode( '-', $id );
		if ( count( $parts ) == 2 )
		{
			$rule = \IPS\rules\Rule::load( $parts[1] );
			return new \IPS\rules\Rule\Aspect( $parts[0], $rule );
		}
		
		return call_user_func_array( 'parent::load', func_get_args() );
	}
	
	/**
	 * Restrict Options
	 */
	public function canEdit() 	{ return FALSE; }
	public function canDelete() 	{ return FALSE; }
	public function canCopy()	{ return FALSE; }
	
	/**
	 *  Get Title
	 */
	public function get__title()
	{
		return $this->title;
	}
	
	/**
	 * Set Title
	 */
	public function set__title( $val )
	{
		$this->title = $val;
	}

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
		if ( 0 )
		{
			return array(
				0	=> 'ipsBadge ipsBadge_intermediary',
				1	=> 'badge text',
			);
		}
		
		return NULL;
	}
	
	/**
	 * Fetch All Root Nodes
	 *
	 * @param	string|NULL			$permissionCheck	The permission key to check for or NULl to not check permissions
	 * @param	\IPS\Member|NULL	$member				The member to check permissions for or NULL for the currently logged in member
	 * @param	mixed				$where				Additional WHERE clause
	 * @return	array
	 */
	public static function roots( $permissionCheck='view', $member=NULL, $where=array() )
	{
		/**
		 * Roots are disabled because this is just a mock node
		 */
		return array();
	}
	
	/**
	 * [Node] Add/Edit Form
	 *
	 * @param	\IPS\Helpers\Form	$form	The form
	 * @return	void
	 */
	public function form( &$form )
	{
		
	}
	
	/**
	 * [Node] Save Add/Edit Form
	 *
	 * @param	array	$values	Values from the form
	 * @return	void
	 */
	public function saveForm( $values )
	{
		parent::saveForm( $values );
	}

	/**
	 * [Node] Fetch Child Nodes
	 *
	 * @param	string|NULL			$permissionCheck	The permission key to check for or NULL to not check permissions
	 * @param	\IPS\Member|NULL	$member				The member to check permissions for or NULL for the currently logged in member
	 * @param	bool				$subnodes			Include subnodes? NULL to *only* check subnodes
	 * @param	array|NULL			$skip				Children IDs to skip
	 * @param	mixed				$_where				Additional WHERE clause
	 * @return	array
	 */
	public function children( $permissionCheck='view', $member=NULL, $subnodes=TRUE, $skip=null, $where=array() )
	{
		$subnodeClass 		= $this->aspectClass;
		$where[] 		= array( $subnodeClass::$databasePrefix . $subnodeClass::$parentNodeColumnId . '=?', $this->rule->id );
		$children 		= $subnodeClass::roots( NULL, NULL, $where );
		
		foreach( $children as $child )
		{
			$child->id = $this->id . '-' . $child->id;
		}

		return $children;		
	}
	
	/**
	 * Mock Node!! 
	 * 
	 * This is just a utility node, not a real database record
	 */
	public function save() 		{}
	public function delete() 	{}
	public function __clone() 	{}	
	
}