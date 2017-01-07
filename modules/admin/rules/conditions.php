<?php

namespace IPS\rules\modules\admin\rules;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * conditions
 */
class _conditions extends \IPS\Node\Controller
{
	/**
	 * Title can contain HTML?
	 */
	public $_titleHtml = TRUE;
	
	/**
	 * Node Class
	 */
	protected $nodeClass = '\IPS\rules\Condition';
	
	/**
	 * @brief 	Associated Rule
	 */
	protected $rule = NULL;
	
	/**
	 * Constructor
	 *
	 * @param	\IPS\Http\Url|NULL	$url		The base URL for this controller or NULL to calculate automatically
	 * @return	void
	 */
	public function __construct( $url=NULL, $rule=NULL )
	{
		if ( isset ( $rule ) )
		{
			$this->rule = $rule;
		}
		
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

		if ( ! isset( $this->rule ) and \IPS\Request::i()->do != 'enableToggle' )
		{
			if ( \IPS\Request::i()->id )
			{
				try
				{
					$condition = \IPS\rules\Condition::load( \IPS\Request::i()->id );
					$rule_id = $condition->rule_id;
				}
				catch ( \OutOfRangeException $e ) 
				{
					if ( \IPS\Request::i()->rule )
					{
						$rule_id = \IPS\Request::i()->rule;
						\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rules&do=form&id={$rule_id}&tab=conditions" ) );
					}
				}
			}
			else
			{
				$rule_id = \IPS\Request::i()->rule;
			}
			
			try
			{
				$rule = \IPS\rules\Rule::load( $rule_id );
				$this->rule = $rule;
				$this->url = $this->url->setQueryString( 'rule', $this->rule->id );
			}
			catch ( \Exception $e )
			{
				if ( \IPS\Request::i()->root )
				{
					$rule_id = \IPS\Request::i()->root;
					\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rules&do=form&id={$rule_id}&tab=conditions" ) );
				}
				\IPS\Output::i()->error( 'invalid_rule', '2R001/A', 403, '' );
			}
		}
		
		parent::execute();
	}
	
	/**
	 * Manage
	 *
	 * @return	void
	 */
	protected function manage()
	{
		if ( isset ( $this->rule ) )
		{
			if ( ! \IPS\Request::i()->isAjax() )
			{
				\IPS\Output::i()->output .= "
					<h3>Rule: {$this->rule->title}</h3>
					<a href='" . \IPS\Http\Url::internal( "app=rules&module=rules&controller=rules&do=form&id={$this->rule->id}&tab=conditions" ) . "' class='ipsButton ipsButton_small ipsButton_positive'><i class='fa fa-arrow-left'></i> &nbsp;Back To Rule</a>";
			}
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
		\IPS\Output::i()->output .= \IPS\rules\Application::eventHeader( $this->rule->event() );
		\IPS\Output::i()->output .= \IPS\rules\Application::ruleHeader( $this->rule );
		\IPS\Output::i()->sidebar[ 'actions' ][ 'editrule' ] = array(
			'icon'	=> 'caret-left',
			'link'	=> \IPS\Http\Url::internal( 'app=rules&module=rules&controller=rules&do=form&id=' . $this->rule->id ),
			'title'	=> 'rules_manage_rule',
			'data' => array( ),
		);
		parent::form();		
		\IPS\Output::i()->title = 'Condition: ' . \IPS\Output::i()->title;
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
		foreach( $nodeClass::roots( NULL, NULL, array( array( 'condition_rule_id=?', $this->rule->id ) ) ) as $node )
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
		$nodeClass = $this->nodeClass;
		
		if ( $nodeClass::canAddRoot() )
		{
			return array( 'add' => array(
				'icon'	=> 'filter',
				'title'	=> 'rules_add_condition',
				'link'	=> $this->url->setQueryString( array( 'do' => 'form', 'rule' => $this->rule->id ) ),
				'data'	=> ( $nodeClass::$modalForms ? array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'rules_add_condition' ) ) : array() )
				) );
		}
		return array();
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
										
		return \IPS\Theme::i()->getTemplate( 'trees', 'core' )->row(
			$this->url,
			$id,
			( $node->not ? "<span class='ipsBadge ipsBadge_warning'>NOT</span> " : "" ) . $node->_title,
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
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=conditions&do=form&id={$new->id}&rule={$new->rule_id}" ) );
			}
			else
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rules&do=form&id={$new->rule_id}&tab=conditions" ), 'saved' );
			}
		}
	}
	
}