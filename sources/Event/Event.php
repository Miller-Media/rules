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
class _Node extends \IPS\Node\Model
{
	/**
	 * @brief	[ActiveRecord] Multiton Store
	 */
	protected static $multitons;
	
	/**
	 * @brief	[ActiveRecord] Database Table
	 */
	public static $databaseTable = 'libraries_libraries';
	
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'library_';
		
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
	public static $nodeTitle = 'libraries';
		
	/**
	 * @brief	[Node] ACP Restrictions
	 * @code
	 	array(
	 		'app'		=> 'core',				// The application key which holds the restrictrions
	 		'module'	=> 'foo',				// The module key which holds the restrictions
	 		'map'		=> array(				// [Optional] The key for each restriction - can alternatively use "prefix"
	 			'add'			=> 'foo_add',
	 			'edit'			=> 'foo_edit',
	 			'permissions'	=> 'foo_perms',
	 			'delete'		=> 'foo_delete'
	 		),
	 		'all'		=> 'foo_manage',		// [Optional] The key to use for any restriction not provided in the map (only needed if not providing all 4)
	 		'prefix'	=> 'foo_',				// [Optional] Rather than specifying each  key in the map, you can specify a prefix, and it will automatically look for restrictions with the key "[prefix]_add/edit/permissions/delete"
	 * @encode
	 */
	protected static $restrictions = array(
		'app'		=> 'app',
		'module'	=> 'module',
		'prefix'	=> 'categories_'
	);
	
	/**
	 * @brief	[Node] App for permission index
	 */
	public static $permApp = 'app';
	
	/**
	 * @brief	[Node] Type for permission index
	 */
	public static $permType = 'nodetype_category';
	
	/**
	 * @brief	The map of permission columns
	 */
	public static $permissionMap = array(
		'view'			=> 'view',
		//'read'		=> 2,
		//'add'			=> 3,
		//'reply'		=> 4,
		//'rate'		=> 5,
		//'review'		=> 6,
	);
	
	/**
	 * @brief	Database Column Map
	 */
	public static $databaseColumnMap = array(
		//'cover_photo'			=> 'cover_photo',
		//'cover_photo_offset'		=> 'cover_offset',
	);

	/**
	 * @brief	Bitwise values for category_bitoptions field
	 */
	public static $bitOptions = array(
		'bitoptions' => array(
			'bitoptions' => array(
			)
		)
	);

	/**
	 * @brief	[Node] Prefix string that is automatically prepended to permission matrix language strings
	 */
	public static $permissionLangPrefix = 'nodetype_';

	/**
	 * @brief	[Node] Title prefix.  If specified, will look for a language key with "{$key}_title" as the key
	 */
	public static $titleLangPrefix = 'nodetype_category_';
	
	/**
	 * @brief	[Node] Moderator Permission
	 */
	public static $modPerm = 'nodetype_categories';

	/**
	 * @brief	Content Item Class
	 */
	public static $contentItemClass = 'IPS\app\Content';
	
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
		
}