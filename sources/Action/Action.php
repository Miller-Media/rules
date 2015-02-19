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
class _Action extends \IPS\Node\Model
{
	/**
	 * @brief	[ActiveRecord] Multiton Store
	 */
	protected static $multitons;
	
	/**
	 * @brief	[ActiveRecord] Database Table
	 */
	public static $databaseTable = 'rules_actions';
	
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'action_';
		
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
	public static $nodeTitle = 'actions';
	
	/**
	 * @brief	[Node] Parent Node Class
	 */
	public static $parentNodeClass = 'IPS\rules\Rule';
	
	/**
	 * @brief	[Node] Parent Node Column
	 */
	public static $parentNodeColumnId = 'rule_id';
	
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
		foreach ( $buttons as $id => &$button )
		{
			$button[ 'link' ] = $button[ 'link' ]->setQueryString( 'aspect', 'actions-' . $this->rule_id );
		}
		
		return $buttons;
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
		$form->add( new \IPS\Helpers\Form\Text( 'action_title', $this->title, TRUE ) );
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
	 * [ActiveRecord] Save 
	 */
	public function save()
	{
		/**
		 * If this condition was created as a subnode, then it's parent
		 * will be empty and the rule_id will be set to our mock container.
		 *
		 * We'll need to parse it and reset it to point to our real rule_id
		 */
		if ( \substr( $this->rule_id, 0, 8 ) == 'actions-' )
		{
			$this->rule_id = \substr( $this->rule_id, 8 );
		}
		
		/**
		 * If this condition was not created as a subnode, then it's parent
		 * will be set to the condition it was created under, and we will
		 * still need to assign the correct rule_id that it belongs to
		 */
		if ( ! $this->rule_id and \IPS\Request::i()->aspect )
		{
			/* Parse cryptic parent id's created by our node controller */
			if ( count( $parts = explode( '-', $this->parent_id ) ) > 1 )
			{
				$this->parent_id = array_pop( $parts );
			}
			$this->rule_id = \substr( \IPS\Request::i()->aspect, 8 );
		}
		
		parent::save();
	}	
		
}