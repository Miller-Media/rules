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
class _Ruleset extends \IPS\Node\Model
{
	/**
	 * @brief	[ActiveRecord] Multiton Store
	 */
	protected static $multitons;
	
	/**
	 * @brief	[ActiveRecord] Database Table
	 */
	public static $databaseTable = 'rules_rulesets';
	
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'ruleset_';
		
	/**
	 * @brief	[Node] Order Database Column
	 */
	public static $databaseColumnOrder = 'weight';
	
	/**
	 * @brief	[ActiveRecord] Database ID Fields
	 */
	protected static $databaseIdFields = array();
	
	/**
	 * @brief	[Node] Node Title
	 */
	public static $nodeTitle = 'rulesets';
	
	/**
	 * @brief	Sub Node Class
	 */
	public static $subnodeClass = '\IPS\rules\Rule';
	
	/**
	 *  Disable Copy Button
	 */	
	public $noCopyButton = TRUE;
	
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
	 * Get Description
	 */
	public function get__description()
	{
		return $this->description;
	}
	
	/**
	 * [Node] Get content table description 
	 *
	 * @return	string
	 */
	protected function get_description()
	{
		return isset( $this->_data[ 'description' ] ) ? $this->_data[ 'description' ] : '';
	}

	/**
	 * [Node] Get whether or not this node is enabled
	 *
	 * @note	Return value NULL indicates the node cannot be enabled/disabled
	 * @return	bool|null
	 */
	protected function get__enabled()
	{
		return $this->enabled;
	}

	/**
	 * [Node] Set whether or not this node is enabled
	 *
	 * @param	bool|int	$enabled	Whether to set it enabled or disabled
	 * @return	void
	 */
	protected function set__enabled( $enabled )
	{
		$this->enabled = $enabled;
	}
	
	/**
	 * [Node] Custom Badge
	 *
	 * @return	NULL|array	Null for no badge, or an array of badge data (0 => CSS class type, 1 => language string, 2 => optional raw HTML to show instead of language string)
	 */
	protected function get__badge()
	{
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
		$form->add( new \IPS\Helpers\Form\Text( 'ruleset_title', $this->title, TRUE ) );
		$form->add( new \IPS\Helpers\Form\TextArea( 'ruleset_description', $this->description, FALSE ) );
		$form->add( new \IPS\Helpers\Form\Text( 'ruleset_creator', $this->creator, FALSE ) );
		
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
		parent::saveForm( $values );
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
		
		unset( $buttons[ 'copy' ] );
		$buttons[ 'export' ] = array
		(
			'icon' => 'download',
			'title' => 'rules_export_rule_set',
			'link' => $url->setQueryString( array( 'controller' => 'rulesets', 'do' => 'export', 'ruleset' => $this->id ) ),		
		);
		
		$buttons[ 'overview' ] = array
		(
			'icon' => 'list',
			'title' => 'rules_view_overview',
			'link' => $url->setQueryString( array( 'controller' => 'rulesets', 'do' => 'viewOverview', 'ruleset' => $this->id ) ),
			'data' => array( 'ipsDialog' => '', 'ipsDialog-title' => 'Ruleset Overview' ),
		);
		
		$buttons[ 'debug_disable' ] = array
		(
			'icon'		=> 'bug',
			'title'		=> 'Disable Debugging',
			'id'		=> "{$this->id}-debug-disable",
			'link'		=> $url->setQueryString( array( 'controller' => 'rulesets', 'do' => 'debugDisable', 'setid' => $this->id ) ),
		);
		
		$buttons[ 'debug_enable' ] = array
		(
			'icon'		=> 'bug',
			'title'		=> 'Enable Debugging',
			'id'		=> "{$this->id}-debug-enable",
			'link'		=> $url->setQueryString( array( 'controller' => 'rulesets', 'do' => 'debugEnable', 'setid' => $this->id ) ),
		);		
		
		return $buttons;
	}
	 
	/**
	 * Form to delete or move content
	 *
	 * @param	bool	$showMoveToChildren	If TRUE, will show "move to children" even if there are no children
	 * @return	\IPS\Helpers\Form
	 */
	public function deleteOrMoveForm( $showMoveToChildren=FALSE )
	{
		$form = new \IPS\Helpers\Form( 'delete_custom_action', 'rules_confirm_delete' );
		$form->hiddenValues[ 'node_move_children' ] = 0;
		return $form;
	}
	
	/**
	 * [ActiveRecord] Save Changed Columns
	 *
	 * @return	void
	 */
	public function save()
	{
		if ( ! $this->id and ! $this->created_time )
		{
			$this->created_time = time();
		}
		
		parent::save();
	}

	/**
	 * [ActiveRecord] Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{		
		return parent::delete();
	}

}