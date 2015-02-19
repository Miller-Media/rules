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
class _Rule extends \IPS\Node\Model
{
	/**
	 * @brief	[ActiveRecord] Multiton Store
	 */
	protected static $multitons;
	
	/**
	 * @brief	[ActiveRecord] Database Table
	 */
	public static $databaseTable = 'rules_rules';
	
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'rule_';
		
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
	public static $databaseColumnParent = 'parent_id';
	
	/**
	 * @brief	[Node] Node Title
	 */
	public static $nodeTitle = 'rules';
	
	/**
	 * @brief	Sub Node Class
	 */
	public static $subnodeClass = NULL;
	
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
	 * [Node] Add/Edit Form
	 *
	 * @param	\IPS\Helpers\Form	$form	The form
	 * @return	void
	 */
	public function form( &$form )
	{
		$form->add( new \IPS\Helpers\Form\Text( 'rule_title', $this->title, TRUE ) );
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
	public function children( $permissionCheck='view', $member=NULL, $subnodes=TRUE, $skip=null, $_where=array() )
	{
		$children = parent::children( $permissionCheck, $member, $subnodes, $skip, $_where );
		
		/**
		 * Add aspect containers as children here since they aren't actually
		 * database records that can be loaded
		 */
		$children[] = new \IPS\rules\Rule\Aspect( 'conditions',	$this );
		$children[] = new \IPS\rules\Rule\Aspect( 'actions', 	$this );
		
		return $children;		
	}
		
}