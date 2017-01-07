<?php


namespace IPS\rules\modules\admin\rules;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * admin
 */
class _rules extends \IPS\Node\Controller
{
	/**
	 * Node Class
	 */
	protected $nodeClass = '\IPS\rules\Rule';
	
	/**
	 * @brief	If true, root cannot be turned into sub-items, and other items cannot be turned into roots
	 */
	protected $protectRoots = TRUE;
	
	/**
	 * @brief	If true, will prevent any item from being moved out of its current parent, only allowing them to be reordered within their current parent
	 */
	protected $lockParents = TRUE;
	
	/**
	 * Title can contain HTML?
	 */
	public $_titleHtml = TRUE;
	
	/**
	 * Description can contain HTML?
	 */
	public $_descriptionHtml = TRUE;
	
	/**
	 * Constructor
	 *
	 * @param	\IPS\Http\Url|NULL	$url		The base URL for this controller or NULL to calculate automatically
	 * @return	void
	 */
	public function __construct( $url=NULL )
	{
		parent::__construct( $url );
	}
	
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'rules_manage' );
		
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'chosen.jquery.js', 'rules', 'interface' ) );	
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'chosen.css', 'rules', 'admin' ) );
		
		/* Javascript Controller */
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'admin_ui.js', 'rules', 'admin' ) );
		
		parent::execute();
	}
		
	/**
	 * Manage
	 */
	protected function manage()
	{
		if ( ! \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rulesets" ) );
		}
		
		parent::manage();
	}
	
	/**
	 * Add/Edit Form
	 *
	 * @return void
	 */
	protected function form()
	{	
		$rule 	= NULL;
		$parent = NULL;
		
		\IPS\Output::i()->sidebar[ 'actions' ][ 'manageall' ] = array(
			'icon'	=> 'caret-left',
			'link'	=> \IPS\Http\Url::internal( 'app=rules&module=rules&controller=rulesets' ),
			'title'	=> 'rules_manage_all_rules',
			'data' => array( ),
		);
		
		if ( \IPS\Request::i()->id )
		{
			$rule = \IPS\rules\Rule::load( \IPS\Request::i()->id );
			\IPS\Output::i()->output .= \IPS\rules\Application::eventHeader( $rule->event() );
		}
		
		if ( $rule and $rule->parent() )
		{
			$parent = $rule->parent();
		}
		else if ( \IPS\Request::i()->parent )
		{
			$parent = \IPS\rules\Rule::load( \IPS\Request::i()->parent );
			\IPS\Output::i()->output .= \IPS\rules\Application::eventHeader( $parent->event() );
		}
		
		if ( $parent )
		{
			\IPS\Output::i()->output .= \IPS\rules\Application::ruleChild( $parent );
		}
		
		parent::form();		
		\IPS\Output::i()->title = 'Rule: ' . \IPS\Output::i()->title;
	}
	
	/**
	 * Redirect after save
	 *
	 * @param	\IPS\Node\Model	$old	A clone of the node as it was before or NULL if this is a creation
	 * @param	\IPS\Node\Model	$node	The node now
	 * @return	void
	 */
	protected function _afterSave( \IPS\Node\Model $old = NULL, \IPS\Node\Model $new, $lastUsedTab = false )
	{
		if( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->json( array() );
		}
		else
		{
			if ( $old == NULL )
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rules&do=form&id={$new->id}&tab=conditions" ) );
			}
			else
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rulesets" ), 'saved' );
			}
		}
	}
	
	/**
	 * Get Root Rows
	 *
	 * @return	array
	 */
	public function _getRoots()
	{
		$nodeClass = $this->nodeClass;
		$rows = array();
		foreach( $nodeClass::roots( NULL, NULL, array( array( 'rule_ruleset_id=0' ) ) ) as $node )
		{
			$rows[ $node->_id ] = $this->_getRow( $node );
		}
		
		return $rows;
	}

	/**
	 * Get Root Buttons
	 *
	 * @return	array
	 */
	public function _getRootButtons()
	{
		/**
		 * Disabled since adding this controller inline with the rulesets controller
		 */
		return array();
		
		$buttons = parent::_getRootButtons();
		
		$buttons[ 'import' ]  = array(
			'icon'	=> 'upload',
			'title'	=> 'import',
			'link'	=> \IPS\Http\Url::internal( $this->url->setQueryString( array( 'do' => 'import' ) ) ),
			'data'	=> array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'import' ) )
		);
		
		return $buttons;
	}
	
	/**
	 * Get Single Row
	 *
	 * @param	mixed	$id		May be ID number (or key) or an \IPS\Node\Model object
	 * @param	bool	$root	Format this as the root node?
	 * @param	bool	$noSort	If TRUE, sort options will be disabled (used for search results)
	 * @return	string
	 */
	public function _getRow( $id, $root=FALSE, $noSort=FALSE )
	{
		$nodeClass = $this->nodeClass;
		if ( $id instanceof \IPS\Node\Model )
		{
			$node = $id;
		}
		else
		{
			try
			{
				$node = $nodeClass::load( $id );
			}
			catch( \OutOfRangeException $e )
			{
				\IPS\Output::i()->error( 'node_error', '2S101/P', 404, '' );
			}
		}
		
		$id = ( $node instanceof $nodeClass ) ? $node->_id :  "s.{$node->_id}";
		$class = get_class( $node );
		
		$buttons = $node->getButtons( $this->url, !( $node instanceof $this->nodeClass ) );
		if ( isset( \IPS\Request::i()->searchResult ) and isset( $buttons['edit'] ) )
		{
			$buttons['edit']['link'] = $buttons['edit']['link']->setQueryString( 'searchResult', \IPS\Request::i()->searchResult );
		}
		
		$title = $node->_title;
		
		if ( $node instanceof \IPS\rules\Rule )
		{
			if ( $node->hasChildren() )
			{
				$title = "<span class='ipsBadge ipsBadge_warning'>Rule Group</span> " . $title;
			}
			else
			{
				$title = "<span class='ipsBadge ipsBadge_neutral'>Rule</span> " . $title;
			}
		}
		
		return \IPS\Theme::i()->getTemplate( 'trees', 'core' )->row(
			$this->url,
			$id,
			$title,
			$node->childrenCount( NULL ),
			$buttons,
			$node->_description,
			$node->_icon ? $node->_icon : NULL,
			( $noSort === FALSE and $class::$nodeSortable and $node->canEdit() ) ? $node->_position : NULL,
			$root,
			$node->_enabled,
			( $node->_locked or !$node->canEdit() ),
			( ( $node instanceof \IPS\Node\Model ) ? $node->_badge : $this->_getRowBadge( $node ) ),
			$this->_titleHtml,
			$this->_descriptionHtml,
			$node->canAdd()
		);
	}
	
}
