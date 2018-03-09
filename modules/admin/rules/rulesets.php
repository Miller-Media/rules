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
class _rulesets extends \IPS\Node\Controller
{
	/**
	 * Node Class
	 */
	protected $nodeClass = '\IPS\rules\Rule\Ruleset';
	
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
		\IPS\Output::i()->sidebar[ 'actions' ][ 'exportall' ] = array(
			'icon'	=> 'download',
			'link'	=> \IPS\Http\Url::internal( 'app=rules&module=rules&controller=rulesets&do=exportAll' ),
			'title'	=> 'rules_export_all',
			'data' => array( 'confirm' => '' ),
		);
		
		\IPS\Output::i()->sidebar[ 'actions' ][ 'overview' ] = array(
			'icon'	=> 'info',
			'link'	=> \IPS\Http\Url::internal( 'app=rules&module=rules&controller=rulesets&do=overview' ),
			'title'	=> 'rules_overview',
			'data' => array( 'rules-overview' => true, 'ipsDialog' => true, 'ipsDialog-size' => 'medium', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'rules_welcome' ) ),
		);
		
		\IPS\Output::i()->sidebar[ 'actions' ][ 'documentation' ] = array(
			'icon'	=> 'file',
			'link'	=> \IPS\Http\Url::external( 'http://www.ipsguru.net/documentation/rules' ),
			'title'	=> 'rules_documentation',
			'target' => '_blank',
			'data' => array(),
		);		
		
		/* Suppress "No Results" message since we show two trees back to back and it looks awkward */
		\IPS\Member::loggedIn()->language()->words[ 'no_results' ] = '';
		
		parent::manage();
		
		$rulesClass		= '\IPS\rules\Rule';
		$rulesController 	= new \IPS\rules\modules\admin\rules\rules( NULL );
		$rules 			= new \IPS\Helpers\Tree\Tree( 
						\IPS\Http\Url::internal( "app=rules&module=rules&controller=rules" ),
						$rulesClass::$nodeTitle, 
						array( $rulesController, '_getRoots' ), 
						array( $rulesController, '_getRow' ), 
						array( $rulesController, '_getRowParentId' ), 
						array( $rulesController, '_getChildren' ), 
						array( $rulesController, '_getRootButtons' )
					);
		
		if ( ! \IPS\Request::i()->isAjax() )
		{
			$rules_empty	= ! ( 
						\IPS\Db::i()->select( 'COUNT(*)', 'rules_rules' )->first() or
						\IPS\Db::i()->select( 'COUNT(*)', 'rules_rulesets' )->first()
					);
					
			$dim		= $rules_empty ? "-dim" : "";
			$bgimage 	= rtrim( \IPS\Http\Url::baseUrl(), '/' ) . "/applications/rules/img/rules-bg{$dim}.png";
			
			if ( $rules_empty )
			{
				$title = \IPS\Member::loggedIn()->language()->addToStack( 'rules_welcome' );
				$overview_url = \IPS\Http\Url::internal( "app=rules&module=rules&controller=rulesets&do=overview" );
				\IPS\Output::i()->output .= "
					<style>
					  .acpBlock { display: none; }
					</style>
					<a href='{$overview_url}' style='width:220px; position:absolute; left:50%; top:50%; margin-left:-110px;' data-ipsDialog data-ipsDialog-size='medium' data-ipsDialog-title='{$title}' class='ipsButton ipsButton_large ipsButton_positive'><i class='fa fa-graduation-cap'></i> &nbsp;&nbsp;Learn About Rules</a>
				";
			}
			
			\IPS\Output::i()->output .= (string) $rules;
			
			\IPS\Output::i()->output .= "
			  <style> 
			    #tree_search { display:none; }
			    #acpMainArea {
			      background:url(\"{$bgimage}\") no-repeat top left rgba( 255, 255, 255, 0.5 );
			      background-size:100% auto;
			    }
				.acpBlock {
				  border-radius: 0px;
				}
				.acpBlock + .acpBlock {
				  border-top: 1px solid transparent;
				}
			    .ipsTree {
			      background-color:#fff;
			    }
			  </style>
			";
		}
		
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
			if ( \IPS\Request::i()->subnode )
			{
				$rule = \IPS\rules\Rule::load( \IPS\Request::i()->id );
				\IPS\Output::i()->output .= \IPS\rules\Application::eventHeader( $rule->event() );
			}
		}
		
		if ( $rule and $rule->parent() )
		{
			$parent = $rule->parent();
		}
		else if ( \IPS\Request::i()->parent and \IPS\Request::i()->subnode == 0 )
		{
			/**
			 * Setting nodeClass to \rules\Rule because otherwise the logic in the parent::form()
			 * gives us the form for a new rule set instead of a new rule
			 */
			$this->nodeClass = '\IPS\rules\Rule';
			$parent = \IPS\rules\Rule::load( \IPS\Request::i()->parent );
			\IPS\Output::i()->output .= \IPS\rules\Application::eventHeader( $parent->event() );
		}
		
		if ( $parent )
		{
			\IPS\Output::i()->output .= \IPS\rules\Application::ruleChild( $parent );
		}
		
		parent::form();
		
		if ( \IPS\Request::i()->subnode == 1 )
		{
			\IPS\Output::i()->title = 'Rule: ' . \IPS\Output::i()->title;
		}		
	}
	
	/**
	 * Get Child Rows
	 *
	 * Modified because _getChildren() from stock node controller doesn't account for
	 * the ID having an "s." prefix for subnodes
	 *
	 * @param	int|string	$id		Row ID
	 * @return	array
	 */
	public function _getChildren( $id )
	{
		$rows = array();

		$nodeClass = $this->nodeClass;
		if ( mb_substr( $id, 0, 2 ) == 's.' )
		{
			$nodeClass = $nodeClass::$subnodeClass;
			$id = mb_substr( $id, 2 );
		}

		try
		{
			$node	= $nodeClass::load( $id );
		}
		catch( \OutOfRangeException $e )
		{
			\IPS\Output::i()->error( 'node_error', '2S101/R', 404, '' );
		}

		foreach ( $node->children( NULL ) as $child )
		{
			$id = ( $child instanceof $this->nodeClass ? '' : 's.' ) . $child->_id;
			$rows[ $id ] = $this->_getRow( $child );
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
		$buttons = array();
		
		if ( $nodeClass::canAddRoot() )
		{
			$buttons[ 'add' ] = array(
				'icon'	=> 'legal',
				'title'	=> 'rulesets_add',
				'link'	=> $this->url->setQueryString( 'do', 'form' ),
				'data'	=> ( $nodeClass::$modalForms ? array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('add') ) : array() )
			);
		}
		
		$buttons[ 'add_rule' ] = array(
			'icon' => 'plus',
			'title' => 'rules_add_rule',
			'link' => $this->url->setQueryString( array( 'do' => 'form', 'subnode' => 1 ) ),
		);
	
		$buttons[ 'import' ]  = array
		(
			'icon'	=> 'upload',
			'title'	=> 'import',
			'link'	=> $this->url->setQueryString( array( 'do' => 'import' ) ),
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
				\IPS\Output::i()->error( 'node_error', '2R101/E', 404, '' );
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
		else if ( $node instanceof \IPS\rules\Rule\Ruleset )
		{
			$title = "<span class='ipsBadge ipsBadge_positive'>Rule Set</span> " . $title;
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
			if ( isset ( \IPS\Request::i()->subnode ) )
			{
				if ( $old == NULL )
				{
					\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rulesets&subnode=1&do=form&id={$new->id}&tab=conditions" ) );
				}
			}
		}
		
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rulesets" ), 'saved' );
	}
	
	/**
	 * Reorder
	 *
	 * @return	void
	 */
	protected function reorder()
	{	
		/* Init */
		$nodeClass = $this->nodeClass;
		
		/* Normalise AJAX vs non-AJAX */
		if( isset( \IPS\Request::i()->ajax_order ) )
		{
			$order = array();
			$position = array();
			foreach( \IPS\Request::i()->ajax_order as $id => $parent )
			{
				if ( !isset( $order[ $parent ] ) )
				{
					$order[ $parent ] = array();
					$position[ $parent ] = 1;
				}
				$order[ $parent ][ $id ] = $position[ $parent ]++;
			}
		}
		/* Non-AJAX way */
		else
		{
			$order = array( \IPS\Request::i()->root ?: 'null' => \IPS\Request::i()->order );
		}

		/* Okay, now order */
		foreach( $order as $parent => $nodes )
		{
			foreach ( $nodes as $id => $position )
			{
				/* Load Node */
				try
				{
					if ( mb_substr( $id, 0, 2 ) === 's.' )
					{
						$node = call_user_func( array( $nodeClass::$subnodeClass, 'load' ), mb_substr( $id, 2 ) );
						$parentColumn = $node::$parentNodeColumnId;
					}
					else
					{
						$node = $nodeClass::load( $id );
						$parentColumn = $node::$databaseColumnParent;
					}
				}
				catch ( \OutOfRangeException $e )
				{
					\IPS\Output::i()->error( 'node_error', '3S101/B', 404, '' );
				}
				$orderColumn = $node::$databaseColumnOrder;
				
				/* Check permission */
				if( !$node->canEdit() )
				{
					continue;
				}
				if( !$node::$nodeSortable or $orderColumn === NULL )
				{
					continue;
				}
								
				/* Do it */
				if ( $parentColumn )
				{
					$node->$parentColumn = ( $parent === 'null' ) ? 0 : is_numeric( $parent ) ? $parent : $nodeClass::$databaseColumnParentRootValue;
				}
				$node->$orderColumn = $position;
				$node->save();
			}
			
		}
				
		/* Log */
		\IPS\Session::i()->log( 'acplog__node_reorder', array( $this->title => TRUE ), TRUE );
				
		/* If this is an AJAX request, just respond */
		if( \IPS\Request::i()->isAjax() )
		{
			return;
		}
		/* Otherwise, redirect */
		else
		{
			\IPS\Output::i()->redirect( $this->url->setQueryString( array( 'root' => \IPS\Request::i()->root ) ) );
		}
		\IPS\Output::i()->sendOutput();
	}

	/**
	 * Rules Overview Help Page
	 */
	protected function overview()
	{
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'rules_welcome' );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'help' )->overview();
	}
	
	/**
	 * Enable Debugging
	 */
	protected function debugEnable()
	{
		$rules = array();
		
		if ( \IPS\Request::i()->setid )
		{
			try
			{
				$ruleset = \IPS\rules\Rule\Ruleset::load( \IPS\Request::i()->setid );
				$rules = $ruleset->children();
			}
			catch( \OutOfRangeException $e ) { }
		}
		
		else if ( \IPS\Request::i()->id )
		{
			try
			{
				$rule = \IPS\rules\Rule::load( \IPS\Request::i()->id );
				$rules = array( $rule );
			}
			catch( \OutOfRangeException $e ) { }
		}

		$enableRecursive = function( $rule ) use ( &$enableRecursive )
		{
			$rule->debug = 1;
			$rule->save();
			
			foreach ( $rule->children() as $_rule )
			{
				$enableRecursive( $_rule );
			}
		};
		
		foreach( $rules as $rule )
		{		
			$enableRecursive( $rule );
		}
		
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rulesets" ), 'Debugging Enabled' );
	}
	
	/**
	 * Disable Debugging
	 */
	protected function debugDisable()
	{
		$rules = array();
		
		if ( \IPS\Request::i()->setid )
		{
			try
			{
				$ruleset = \IPS\rules\Rule\Ruleset::load( \IPS\Request::i()->setid );
				$rules = $ruleset->children();
			}
			catch( \OutOfRangeException $e ) { }
		}
		
		else if ( \IPS\Request::i()->id )
		{
			try
			{
				$rule = \IPS\rules\Rule::load( \IPS\Request::i()->id );
				$rules = array( $rule );
			}
			catch( \OutOfRangeException $e ) { }
		}

		$disableRecursive = function( $rule ) use ( &$disableRecursive )
		{
			$rule->debug = 0;
			$rule->save();
			
			foreach ( $rule->children() as $_rule )
			{
				$disableRecursive( $_rule );
			}
		};
		
		foreach( $rules as $rule )
		{		
			$disableRecursive( $rule );
		}
		
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rulesets" ), 'Debugging Disabled' );
	}

	/**
	 * View Rules Log Info
	 */
	protected function viewlog()
	{
		try
		{
			$log = \IPS\Db::i()->select( '*', 'rules_logs', array( 'id=?', \IPS\Request::i()->logid ) )->first();
		}
		catch( \UnderflowException $e )
		{
			\IPS\Output::i()->error( 'Log Not Found', '2RL01/A', 403 );
		}
		
		$self = $this;
		$event = \IPS\rules\Event::load( $log[ 'app' ], $log[ 'class' ], $log[ 'key' ], TRUE );
		
		$rule = NULL;
		try
		{
			$rule = \IPS\rules\Rule::load( $log[ 'rule_id' ] );
		}
		catch( \OutOfRangeException $e ) {}
		
		$conditions		= new \IPS\Helpers\Table\Db( 'rules_logs', $this->url, array( 'thread=? AND type=? AND rule_id=?', $log[ 'thread' ], 'IPS\rules\Condition', $log[ 'rule_id' ] ) );
		$conditions->include 	= array( 'op_id', 'message', 'result' );
		$conditions->langPrefix = 'rules_conditions_table_';
		$conditions->parsers 	= array
		(
			'op_id'	=> function( $val )
			{
				try
				{
					$operation = \IPS\rules\Condition::load( $val );
					return ( $operation->not ? "<span class='ipsBadge ipsBadge_warning'>NOT</span> " : "" ) .  $operation->title;
				}
				catch ( \OutOfRangeException $e )
				{
					return 'Unknown Condition (deleted)';
				}
			},
			'result' => function( $val )
			{
				if ( $json_val = json_decode( $val ) or $val == 'false' )
				{
					if ( ! is_bool ( $json_val ) )
					{
						return "<pre style='font-family:inherit'>" . print_r( $json_val, true ) . "</pre>";
					}
					else
					{
						return $json_val ? '<span style="color:green">TRUE</span>' : '<span style="color:red">FALSE</span>';
					}
				}				
				return $val;
			},
		);				
		$conditions->sortBy = 'id';
		$conditions->sortDirection = 'asc';
		$conditions->noSort = array( 'op_id', 'message', 'result' );
		$conditions->limit = 1000;
		
		$actions		= new \IPS\Helpers\Table\Db( 'rules_logs', $this->url, array( 'thread=? AND type=? AND rule_id=?', $log[ 'thread' ], 'IPS\rules\Action', $log[ 'rule_id' ] ) );
		$actions->include 	= array( 'op_id', 'message', 'result', 'time' );
		$actions->langPrefix 	= 'rules_actions_table_';
		$actions->parsers 	= array
		(
			'op_id'	=> function( $val )
			{
				try
				{
					$operation = \IPS\rules\Action::load( $val );
					return $operation->title;
				}
				catch ( \OutOfRangeException $e )
				{
					return 'Unknown Action (deleted)';
				}
			},
			'result' => function( $val )
			{
				if ( $json_val = json_decode( $val ) or $val == 'false' )
				{
					if ( ! is_bool ( $json_val ) )
					{
						return "<pre style='font-family:inherit'>" . print_r( $json_val, true ) . "</pre>";
					}
					else
					{
						return $json_val ? '<span style="color:green">TRUE</span>' : '<span style="color:red">FALSE</span>';
					}
				}				
				return $val;
			},
			'time'	=> function( $val )
			{
				return (string) \IPS\DateTime::ts( $val );
			},
		);				
		$actions->sortBy = 'id';
		$actions->sortDirection = 'asc';
		$actions->noSort = array( 'op_id', 'message', 'result' );
		$actions->limit = 1000;
		
		$subrules		= new \IPS\Helpers\Table\Db( 'rules_logs', $this->url, array( 'thread=? AND op_id=0 AND rule_parent=?', $log[ 'thread' ], $log[ 'rule_id' ] ) );
		$subrules->include 	= array( 'rule_id', 'message', 'result' );
		$subrules->langPrefix 	= 'rules_subrules_table_';
		$subrules->parsers 	= array
		(
			'rule_id' => function( $val )
			{
				try
				{
					$rule = \IPS\rules\Rule::load( $val );
					return $rule->title;
				}
				catch ( \OutOfRangeException $e )
				{
					return 'Unknown Rule (deleted)';
				}
			},
			'result' => function( $val )
			{
				if ( $json_val = json_decode( $val ) or $val == 'false' )
				{
					if ( ! is_bool ( $json_val ) )
					{
						return "<pre style='font-family:inherit'>" . print_r( $json_val, true ) . "</pre>";
					}
					else
					{
						return $json_val ? '<span style="color:green">TRUE</span>' : '<span style="color:red">FALSE</span>';
					}
				}				
				return $val;
			},
		);				
		$subrules->sortBy = 'id';
		$subrules->sortDirection = 'asc';
		$subrules->rowButtons = function( $row ) use ( $self )
		{	
			$buttons = array();
			try
			{
				$rule = \IPS\rules\Rule::load( $row[ 'rule_id' ] );
				if ( $rule->debug )
				{
					$buttons[ 'debug' ] = array(
						'icon'		=> 'bug',
						'title'		=> 'View Debug Console',
						'id'		=> "{$row['id']}-view",
						'link'		=> $self->url->setQueryString( array( 'controller' => 'rules', 'do' => 'form', 'id' => $row[ 'rule_id' ], 'tab' => 'debug_console' ) ),
					);
				}
			}
			catch ( \OutOfRangeException $e ) {}
			
			return $buttons;
		};
		$subrules->noSort = array( 'rule_id', 'message', 'result' );
		$subrules->limit = 1000;
		
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'views' )->logdetails( $log, $event, $rule, $conditions, $actions, $subrules );
	}
	
	/**
	 * View Rule Overview
	 */
	protected function viewOverview()
	{
		/**
		 * Overview A Single Rule(group)
		 */
		if ( \IPS\Request::i()->rule )
		{
			try
			{
				$rule = \IPS\rules\Rule::load( \IPS\Request::i()->rule );
			}
			catch( \OutOfRangeException $e )
			{
				\IPS\Output::i()->error( 'invalid_rule', '2RL01/B', 403 );
			}
			
			\IPS\Output::i()->title	= "Overview for rule: " . $rule->title;
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'views' )->rules( array( $rule ) );
		}
		
		/**
		 * Overview A Whole Ruleset
		 */
		else
		{
			try
			{
				$set = \IPS\rules\Rule\Ruleset::load( \IPS\Request::i()->ruleset );
			}
			catch ( \OutOfRangeException $e )
			{
				\IPS\Output::i()->error( 'invalid_rule', '2RL01/B', 403 );
			}
			
			\IPS\Output::i()->title = "Overview for ruleset: " . $set->title;
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'views' )->rules( $set->children() );
		}	
	}
	
	/**
	 * Export Rule(set)
	 */
	protected function export()
	{
		/**
		 * Export A Single Rule(group)
		 */
		if ( \IPS\Request::i()->rule )
		{
			try
			{
				$rule = \IPS\rules\Rule::load( \IPS\Request::i()->rule );
			}
			catch( \OutOfRangeException $e )
			{
				\IPS\Output::i()->error( 'invalid_rule', '2RL01/B', 403 );
			}
			
			$title 		= $rule->title;
			$xml 		= \IPS\Xml\SimpleXML::create( 'ruledata' );
			$rulesets 	= $xml->addChild( 'rulesets' );
			$rules 		= $xml->addChild( 'rules' );
			$customActions 	= $xml->addChild( 'customActions' );
			$customData	= $xml->addChild( 'customData' );
			
			$results = $this->_addRuleExport( $rule, $rules );
			
			$custom_actions = $results[ 'custom_actions' ];
			$custom_data 	= $results[ 'custom_data' ];
			
			foreach ( $custom_actions as $custom_action )
			{
				$this->_addCustomActionExport( $custom_action, $customActions );
			}
			
			foreach ( $custom_data as $data )
			{
				$this->_addCustomDataExport( $data, $customData );
			}

		}
		
		/**
		 * Export A Whole Ruleset
		 */
		else
		{
			try
			{
				$set = \IPS\rules\Rule\Ruleset::load( \IPS\Request::i()->ruleset );
			}
			catch ( \OutOfRangeException $e )
			{
				\IPS\Output::i()->error( 'invalid_rule', '2RL01/B', 403 );
			}
			
			$title 		= $set->title;
			$xml 		= \IPS\Xml\SimpleXML::create( 'ruledata' );
			$rulesets 	= $xml->addChild( 'rulesets' );
			$rules 		= $xml->addChild( 'rules' );
			$customActions 	= $xml->addChild( 'customActions' );
			$customData	= $xml->addChild( 'customData' );
			
			$results = $this->_addRulesetExport( $set, $rulesets );
			
			$custom_actions = $results[ 'custom_actions' ];
			$custom_data 	= $results[ 'custom_data' ];
			
			foreach ( $custom_actions as $custom_action )
			{
				$this->_addCustomActionExport( $custom_action, $customActions );
			}

			foreach ( $custom_data as $data )
			{
				$this->_addCustomDataExport( $data, $customData );
			}

		}
		
		\IPS\Output::i()->sendOutput( $xml->asXML(), 200, 'application/xml', array( "Content-Disposition" => \IPS\Output::getContentDisposition( 'attachment', \IPS\Http\Url::seoTitle( $title ) . '.xml' ) ) );
	}
	
	/**
	 * Export All Rule(set)s
	 */
	protected function exportAll()
	{
		$xml = \IPS\Xml\SimpleXML::create( 'ruledata' );
		
		$rulesets 	= $xml->addChild( 'rulesets' );
		$rules 		= $xml->addChild( 'rules' );
		$customActions 	= $xml->addChild( 'customActions' );
		$customData	= $xml->addChild( 'customData' );
		
		$custom_actions	= array();
		$custom_data 	= array();
		
		foreach ( \IPS\rules\Rule\Ruleset::roots( NULL ) as $ruleset )
		{
			$results = $this->_addRulesetExport( $ruleset, $rulesets );
			
			$custom_actions = array_merge( $custom_actions, $results[ 'custom_actions' ] );
			$custom_data = array_merge( $custom_data, $results[ 'custom_data' ] );
		}
		
		foreach ( \IPS\rules\Rule::roots( NULL, NULL, array( array( 'rule_ruleset_id=0' ) ) ) as $rule )
		{
			$results = $this->_addRuleExport( $rule, $rules );
			
			$custom_actions = array_merge( $custom_actions, $results[ 'custom_actions' ] );
			$custom_data = array_merge( $custom_data, $results[ 'custom_data' ] );
		}
		
		foreach ( \IPS\rules\Action\Custom::roots( NULL ) as $custom_action )
		{
			$this->_addCustomActionExport( $custom_action, $customActions );
		}
		
		foreach ( \IPS\rules\Data::roots( NULL ) as $custom_data )
		{
			$this->_addCustomDataExport( $custom_data, $customData );
		}
		
		\IPS\Output::i()->sendOutput( $xml->asXML(), 200, 'application/xml', array( "Content-Disposition" => \IPS\Output::getContentDisposition( 'attachment', \IPS\Http\Url::seoTitle( 'rules-export-' . \IPS\DateTime::ts( time() ) ) . '.xml' ) ) );
	}
	
	/**
	 * Build Rule Nodes
	 *
	 * @param 	array			$rules		Rule to export
	 * @param	\IPS\Xml\SimpleXML	$xml		XML object
	 */
	protected function _addRulesetExport( $ruleset, $xml )
	{	
		$rulesetNode = $xml->addChild( 'ruleset' );
		$rulesetNode->addAttribute( 'title', 	$ruleset->title );
		$rulesetNode->addAttribute( 'weight', 	$ruleset->weight );
		$rulesetNode->addAttribute( 'enabled', 	$ruleset->enabled );
		$rulesetNode->addAttribute( 'created', 	$ruleset->created_time );
		$rulesetNode->addAttribute( 'creator', 	$ruleset->creator );
		
		$rulesetNode->addChild( 'description', 	$ruleset->description );
		$custom_actions = array();
		$custom_data 	= array();
		
		$rulesNode = $rulesetNode->addChild( 'rules' );
		foreach ( $ruleset->children() as $rule )
		{
			$results = $this->_addRuleExport( $rule, $rulesNode );
			
			$custom_actions = array_merge( $custom_actions, $results[ 'custom_actions' ] );
			$custom_data = array_merge( $custom_data, $results[ 'custom_data' ] );
		}
		
		return array
		( 
			'custom_actions' => $custom_actions,
			'custom_data' => $custom_data,
		);
	}

	/**
	 * Export Rule Nodes
	 *
	 * @param 	array			$rules		Rule to export
	 * @param	\IPS\Xml\SimpleXML	$xml		XML object
	 */
	protected function _addRuleExport( $rule, $xml )
	{			
		$ruleNode = $xml->addChild( 'rule' );
		$ruleNode->addAttribute( 'title', 	$rule->title );
		$ruleNode->addAttribute( 'weight', 	$rule->weight );
		$ruleNode->addAttribute( 'enabled', 	$rule->enabled );
		$ruleNode->addAttribute( 'app', 	$rule->event_app );
		$ruleNode->addAttribute( 'class', 	$rule->event_class );
		$ruleNode->addAttribute( 'key',		$rule->event_key );
		$ruleNode->addAttribute( 'compare',	$rule->base_compare );
		$ruleNode->addAttribute( 'debug',	FALSE );
		
		$custom_actions = array();
		$custom_data 	= array();
		
		$conditionsNode = $ruleNode->addChild( 'conditions' );
		foreach ( $rule->conditions() as $condition )
		{
			$results = $this->_addConditionExport( $condition, $conditionsNode );
			
			$custom_actions = array_merge( $custom_actions, $results[ 'custom_actions' ] );
			$custom_data = array_merge( $custom_data, $results[ 'custom_data' ] );
		}
		
		$actionsNode = $ruleNode->addChild( 'actions' );
		foreach ( $rule->actions( \IPS\rules\ACTION_STANDARD ) as $action )
		{
			$results = $this->_addActionExport( $action, $actionsNode );
			
			$custom_actions = array_merge( $custom_actions, $results[ 'custom_actions' ] );
			$custom_data = array_merge( $custom_data, $results[ 'custom_data' ] );
		}
		
		$elseActionsNode = $ruleNode->addChild( 'elseActions' );
		foreach ( $rule->actions( \IPS\rules\ACTION_ELSE ) as $action )
		{
			$results = $this->_addActionExport( $action, $elseActionsNode );
			
			$custom_actions = array_merge( $custom_actions, $results[ 'custom_actions' ] );
			$custom_data = array_merge( $custom_data, $results[ 'custom_data' ] );
		}
		
		$subrulesNode = $ruleNode->addChild( 'rules' );
		foreach ( $rule->children() as $subrule )
		{
			$results = $this->_addRuleExport( $subrule, $subrulesNode );
			
			$custom_actions = array_merge( $custom_actions, $results[ 'custom_actions' ] );
			$custom_data = array_merge( $custom_data, $results[ 'custom_data' ] );
		}
		
		/**
		 * Export any custom ACTION that this rule is triggered by
		 */
		if 
		( 
			$rule->parent_id == 0 and
			$rule->event_app == 'rules' and
			$rule->event_class == 'CustomActions'
		)
		{
			$custom_action_key = mb_substr( $rule->event_key, \strlen( 'custom_action_' ) );
			try
			{
				$custom_action = \IPS\rules\Action\Custom::load( $custom_action_key, 'custom_action_key' );
				$custom_actions[ $custom_action_key ] = $custom_action;
			}
			catch ( \OutOfRangeException $e ) { }
		}
			
		/**
		 * Export any custom DATA that this rule is triggered by
		 */
		if 
		( 
			$rule->parent_id == 0 and
			$rule->event_app == 'rules' and
			$rule->event_class == 'CustomData'
		)
		{
			$custom_data_key = mb_substr( $rule->event_key, \strlen( 'updated_' ) );
			try
			{
				$data_field = \IPS\rules\Data::load( $custom_data_key, 'data_key' );
				$custom_data[ $custom_data_key ] = $data_field;
			}
			catch ( \OutOfRangeException $e ) { }
		}

		return array
		( 
			'custom_actions' => $custom_actions,
			'custom_data' => $custom_data,
		);
	}
	
	/**
	 * Export Condition Nodes
	 *
	 * @param 	\IPS\rules\Condition	$condition	Condition to export
	 * @param	\IPS\Xml\SimpleXML	$xml		XML object
	 */
	protected function _addConditionExport( $condition, $xml )
	{
		$conditionNode = $xml->addChild( 'condition' );
		
		$custom_actions = array();
		$custom_data = array();
		
		$conditionNode->addAttribute( 'title', 		$condition->title );
		$conditionNode->addAttribute( 'weight', 	$condition->weight );
		$conditionNode->addAttribute( 'rule', 		$condition->rule_id );
		$conditionNode->addAttribute( 'app', 		$condition->app );
		$conditionNode->addAttribute( 'class', 		$condition->class );
		$conditionNode->addAttribute( 'key', 		$condition->key );
		$conditionNode->addAttribute( 'enabled',	$condition->enabled );
		$conditionNode->addAttribute( 'compare', 	$condition->group_compare );
		$conditionNode->addAttribute( 'not',		$condition->not );
		$conditionNode->addAttribute( 'recursion',	$condition->enable_recursion );
		$conditionNode->addAttribute( 'footprint',	$condition->footprint );
		
		$conditionNode->addChild( 'data', json_encode( $condition->data ) );
		
		$subconditions = $conditionNode->addChild( 'conditions' );
		foreach( $condition->children() as $_condition )
		{
			$results = $this->_addConditionExport( $_condition, $subconditions );
			
			$custom_actions = array_merge( $custom_actions, $results[ 'custom_actions' ] );
			$custom_data = array_merge( $custom_data, $results[ 'custom_data' ] );
		}
		
		/**
		 * Attempt to look for usage of custom data as event arguments
		 */
		if ( isset ( $condition->data[ 'configuration' ][ 'data' ] ) and is_array( $condition->data[ 'configuration' ][ 'data' ] ) )
		{
			foreach ( $condition->data[ 'configuration' ][ 'data' ] as $k => $v )
			{
				if ( \substr( $k, -9 ) == '_eventArg' and \substr( \substr( $v, -55 ), 0, 23 ) == 'custom_data_conversion_' )
				{
					$custom_data_key = \substr( $v, -32 );
					try
					{
						$data_field = \IPS\rules\Data::load( $custom_data_key, 'data_key' );
						$custom_data[ $custom_data_key ] = $data_field;
					}
					catch ( \OutOfRangeException $e ) {}
				}
			}
		}
		
		return array
		( 
			'custom_actions' => $custom_actions,
			'custom_data' => $custom_data,
		);
	}
	
	/**
	 * Export Action Nodes
	 *
	 * @param 	\IPS\rules\Action	$action		Action to export
	 * @param	\IPS\Xml\SimpleXML	$xml		XML object
	 */
	protected function _addActionExport( $action, $xml )
	{
		$actionNode 	= $xml->addChild( 'action' );
		$custom_actions = array();
		$custom_data	= array();
		
		$actionNode->addAttribute( 'title', 		$action->title );
		$actionNode->addAttribute( 'weight', 		$action->weight );
		$actionNode->addAttribute( 'rule', 		$action->rule_id );
		$actionNode->addAttribute( 'app', 		$action->app );
		$actionNode->addAttribute( 'class', 		$action->class );
		$actionNode->addAttribute( 'key', 		$action->key );
		$actionNode->addAttribute( 'enabled',		$action->enabled );
		$actionNode->addAttribute( 'description', 	$action->description );
		$actionNode->addAttribute( 'schedule_mode',	$action->schedule_mode );
		$actionNode->addAttribute( 'schedule_date', 	$action->schedule_date );
		$actionNode->addAttribute( 'schedule_minutes', 	$action->schedule_minutes );
		$actionNode->addAttribute( 'schedule_hours', 	$action->schedule_hours );
		$actionNode->addAttribute( 'schedule_days',	$action->schedule_days );
		$actionNode->addAttribute( 'schedule_months',	$action->schedule_months );
		$actionNode->addAttribute( 'schedule_key', 	$action->schedule_key );
		$actionNode->addAttribute( 'recursion',		$action->enable_recursion );
		$actionNode->addAttribute( 'footprint',		$action->footprint );
		$actionNode->addAttribute( 'else',		$action->else );
		
		$actionNode->addChild( 'schedule_customcode', $action->schedule_customcode );
		$actionNode->addChild( 'data', json_encode( $action->data ) );
		
		/**
		 * Export any custom action that this action wants to trigger
		 */
		if 
		( 
			$action->app == 'rules' and
			$action->class == 'CustomActions'
		)
		{
			$custom_action_key = mb_substr( $action->key, \strlen( 'custom_action_' ) );
			try
			{
				$custom_action = \IPS\rules\Action\Custom::load( $custom_action_key, 'custom_action_key' );
				$custom_actions[ $custom_action_key ] = $custom_action;
			}
			catch ( \OutOfRangeException $e ) {}
		}
			
		/**
		 * Export any custom data field that this action wants to update
		 */
		if 
		( 
			$action->app == 'rules' and
			$action->class == 'CustomData'
		)
		{
			$custom_data_key = mb_substr( $action->key, \strlen( 'set_' ) );
			try
			{
				$data_field = \IPS\rules\Data::load( $custom_data_key, 'data_key' );
				$custom_data[ $custom_data_key ] = $data_field;
			}
			catch ( \OutOfRangeException $e ) {}
		}

		return array
		( 
			'custom_actions' => $custom_actions,
			'custom_data' => $custom_data,
		);
	}
	
	/**
	 * Export Custom Actions
	 *
	 * @param 	\IPS\rules\Action\Custom	$action		Custom action to export
	 * @param	\IPS\Xml\SimpleXML		$xml		XML object
	 */
	protected function _addCustomActionExport( $action, $xml )
	{
		$actionNode = $xml->addChild( 'action' );

		$actionNode->addAttribute( 'title', 		$action->title );
		$actionNode->addAttribute( 'weight', 		$action->weight );
		$actionNode->addAttribute( 'description',	$action->description );
		$actionNode->addAttribute( 'key', 		$action->key );
		
		$argumentsNode = $actionNode->addChild( 'arguments' );
		foreach ( $action->children() as $argument )
		{
			$this->_addArgumentExport( $argument, $argumentsNode );
		}
	}
	
	/**
	 * Export Custom Action Arguments
	 *
	 * @param 	\IPS\rules\Action\Argument	$argument	Argument to export
	 * @param	\IPS\Xml\SimpleXML		$xml		XML object
	 */
	protected function _addArgumentExport( $argument, $xml )
	{
		$argumentNode = $xml->addChild( 'argument' );
		
		$argumentNode->addAttribute( 'name',		$argument->name );
		$argumentNode->addAttribute( 'type',		$argument->type );
		$argumentNode->addAttribute( 'class',		$argument->class );
		$argumentNode->addAttribute( 'required',	$argument->required );
		$argumentNode->addAttribute( 'weight',		$argument->weight );
		$argumentNode->addAttribute( 'custom_class',	$argument->custom_class );
		$argumentNode->addAttribute( 'description',	$argument->description );
		$argumentNode->addAttribute( 'varname',		$argument->varname );
	}

	/**
	 * Export Custom Data Fields
	 *
	 * @param 	\IPS\rules\Data		$data		Custom data field to export
	 * @param	\IPS\Xml\SimpleXML	$xml		XML object
	 */
	protected function _addCustomDataExport( $data, $xml )
	{
		$dataNode = $xml->addChild( 'data' );

		$dataNode->addAttribute( 'name', 		$data->name );
		$dataNode->addAttribute( 'class', 		$data->class );
		$dataNode->addAttribute( 'column_name', 	$data->column_name );
		$dataNode->addAttribute( 'type', 		$data->type );
		$dataNode->addAttribute( 'type_class', 		$data->type_class );
		$dataNode->addAttribute( 'weight', 		$data->weight );
		$dataNode->addAttribute( 'tab', 		$data->tab );
		$dataNode->addAttribute( 'use_mode', 		$data->use_mode );
		$dataNode->addAttribute( 'display_mode',	$data->display_mode );
		$dataNode->addAttribute( 'required', 		$data->required );
		$dataNode->addAttribute( 'description',		$data->description );
		$dataNode->addAttribute( 'key', 		$data->key );
		$dataNode->addAttribute( 'text_mode',		$data->text_mode );
		$dataNode->addAttribute( 'setting_key',		$data->setting_key );
		$dataNode->addAttribute( 'value_default',	$data->value_default );
		
		$dataNode->addChild( 'value_options', $data->value_options );
		$dataNode->addChild( 'configuration', $data->configuration );
	}
	
	/**
	 * Import Form
	 *
	 * @return	void
	 */
	public function import()
	{		
		if ( \IPS\NO_WRITES )
		{
			\IPS\Output::i()->error( 'no_writes', '1RI00/A', 403, '' );
		}

		$form = new \IPS\Helpers\Form( NULL, 'import' );
		
		if ( isset( \IPS\Request::i()->id ) )
		{
			$form->hiddenValues['id'] = \IPS\Request::i()->id;
		}
		
		$form->add( new \IPS\Helpers\Form\Upload( 'rules_import', NULL, TRUE, array( 'allowedFileTypes' => array( 'xml' ), 'temporary' => TRUE ) ) );
		
		if ( $values = $form->values() )
		{
			$tempFile = tempnam( \IPS\TEMP_DIRECTORY, 'IPS' );
			move_uploaded_file( $values[ 'rules_import' ], $tempFile );
			
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=rules&module=rules&controller=rulesets&do=doImport&file=' . urlencode( $tempFile ) . '&key=' . md5_file( $tempFile ) . ( isset( \IPS\Request::i()->id ) ? '&id=' . \IPS\Request::i()->id : '' ) ) );
		}
		
		/* Display */
		\IPS\Output::i()->output = $form;
	}
	
	/**
	 * Import
	 *
	 * @return	void
	 */
	public function doImport()
	{
		if ( ! file_exists( urldecode( \IPS\Request::i()->file ) ) or md5_file( urldecode( \IPS\Request::i()->file ) ) != \IPS\Request::i()->key )
		{
			\IPS\Output::i()->error( 'generic_error', '2RI00/B', 500, '' );
		}
		
		if ( ! ( $import = \simplexml_load_file( urldecode( \IPS\Request::i()->file ), 'SimpleXMLElement', LIBXML_NOCDATA ) ) )
		{
			\IPS\Output::i()->error( 'xml_upload_invalid', '2RI00/C', 403, '' );
		}
		
		/**
		 * @LITE VERSION: Prevent broken imports
		 */
		if ( \IPS\Application::load( 'rules' )->isProtected() )
		{
			if ( \IPS\Db::i()->select( 'COUNT(*)', 'rules_rules' )->first() + ( $importCount = $this->_importRulesCount( $import ) ) > \IPS\rules\Secure\RULELIMIT )
			{
				\IPS\Output::i()->error( 'Lite version restricted to a maximum of ' . \IPS\rules\Secure\RULELIMIT . ' rules. This import contains ' . $importCount . ' rules.', 'RULES', 200, '' );
				exit;
			}
		}
			
		/**
		 * Import Rulesets
		 */
		if ( $import->rulesets->ruleset )
		{
			foreach ( $import->rulesets->ruleset as $rulesetXML )
			{
				$this->_constructNewRuleset( $rulesetXML );
			}
		}
		
		/**
		 * Import Independent Rules
		 */
		if ( $import->rules->rule )
		{
			foreach ( $import->rules->rule as $ruleXML )
			{
				$this->_constructNewRule( $ruleXML, 0, 0 );
			}
		}
		
		/**
		 * Import Custom Actions
		 */
		if ( $import->customActions->action )
		{
			foreach ( $import->customActions->action as $actionXML )
			{
				$this->_constructNewCustomAction( $actionXML );
			}
		}
		
		/**
		 * Import Custom Data Fields
		 */
		if ( $import->customData->data )
		{
			foreach ( $import->customData->data as $dataXML )
			{
				$this->_constructNewCustomData( $dataXML );
			}
		}
		
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rulesets" ), 'rules_imported' );
		
	}
	
	/**
	 * Count total import rules
	 */
	protected function _importRulesCount( $import )
	{
		$count = 0;
		
		if ( $import->rulesets->ruleset )
		{
			foreach ( $import->rulesets->ruleset as $ruleset )
			{
				if ( $ruleset->rules->rule )
				{
					foreach ( $ruleset->rules->rule as $rule )
					{
						$count += $this->_rulesCountRecursive( $rule );
					}
				}
			}
		}
		
		if ( $import->rules->rule )
		{
			foreach ( $import->rules->rule as $rule )
			{
				$count += $this->_rulesCountRecursive( $rule );
			}
		}
		
		return $count;
	}
	
	/**
	 * Count rule and sub-rules
	 */
	protected function _rulesCountRecursive( $rule )
	{
		$count = 1;
		if ( $rule->rules->rule )
		{
			foreach ( $rule->rules->rule as $_rule )
			{
				$count += $this->_rulesCountRecursive( $_rule );
			}
		}
		
		return $count;
	}
	
	/**
	 * Create A Ruleset From XML
	 */
	protected function _constructNewRuleset( $rulesetXML )
	{
		$ruleset = new \IPS\rules\Rule\Ruleset;
		
		$ruleset->title 	= (string) 	$rulesetXML[ 'title' ];
		$ruleset->weight 	= (int) 	$rulesetXML[ 'weight' ];
		$ruleset->enabled	= (int) 	$rulesetXML[ 'enabled' ];
		$ruleset->creator	= (string) 	$rulesetXML[ 'creator' ];
		$ruleset->created_time	= (int)		$rulesetXML[ 'created' ];
		$ruleset->description	= (string)	$rulesetXML->description;
		$ruleset->imported_time	= time();
		$ruleset->save();
		
		if ( $rulesetXML->rules->rule )
		{
			foreach ( $rulesetXML->rules->rule as $ruleXML )
			{
				$this->_constructNewRule( $ruleXML, 0, $ruleset->id );
			}
		}
		
		return $ruleset;
	}

	/**
	 * Create A Rule From XML
	 */
	protected function _constructNewRule( $ruleXML, $parent_id, $ruleset_id )
	{
		$rule = new \IPS\rules\Rule;
		
		$rule->parent_id	= (int) 	$parent_id;
		$rule->ruleset_id	= (int)		$ruleset_id;
		$rule->title 		= (string) 	$ruleXML[ 'title' ];
		$rule->weight 		= (int) 	$ruleXML[ 'weight' ];
		$rule->event_app 	= (string) 	$ruleXML[ 'app' ];
		$rule->event_class 	= (string) 	$ruleXML[ 'class' ];
		$rule->event_key	= (string) 	$ruleXML[ 'key' ];
		$rule->base_compare	= (string) 	$ruleXML[ 'compare' ];
		$rule->enabled		= (int) 	$ruleXML[ 'enabled' ];
		$rule->debug		= (int) 	$ruleXML[ 'debug' ];
		$rule->event_footprint	= (string)	$ruleXML[ 'footprint' ];
		$rule->imported_time	= time();
		$rule->save();
		
		if ( $ruleXML->conditions->condition )
		{
			foreach ( $ruleXML->conditions->condition as $conditionXML )
			{
				$this->_constructNewCondition( $conditionXML, 0, $rule->id );
			}
		}
		
		if ( $ruleXML->actions->action )
		{
			foreach ( $ruleXML->actions->action as $actionXML )
			{
				$this->_constructNewAction( $actionXML, $rule->id );
			}
		}
		
		if ( $ruleXML->elseActions->action )
		{
			foreach ( $ruleXML->elseActions->action as $actionXML )
			{
				$this->_constructNewAction( $actionXML, $rule->id );
			}
		}
		
		if ( $ruleXML->rules->rule )
		{
			foreach ( $ruleXML->rules->rule as $_ruleXML )
			{
				$this->_constructNewRule( $_ruleXML, $rule->id, $ruleset_id );
			}
		}
		
		return $rule;
	}
	
	/**
	 * Create A Condition From XML
	 */
	protected function _constructNewCondition( $conditionXML, $parent_id, $rule_id )
	{
		$condition = new \IPS\rules\Condition;
		
		$condition->rule_id 		= $rule_id;
		$condition->parent_id		= $parent_id;
		$condition->title 		= (string) 	$conditionXML[ 'title' ];
		$condition->weight 		= (int) 	$conditionXML[ 'weight' ];
		$condition->app			= (string) 	$conditionXML[ 'app' ];
		$condition->class		= (string) 	$conditionXML[ 'class' ];
		$condition->key			= (string) 	$conditionXML[ 'key' ];
		$condition->enabled		= (int) 	$conditionXML[ 'enabled' ];
		$condition->group_compare 	= (string) 	$conditionXML[ 'compare' ];
		$condition->not			= (string) 	$conditionXML[ 'not' ];
		$condition->enable_recursion = (int) $conditionXML[ 'recursion' ];
		$condition->footprint		= (string)	$conditionXML[ 'footprint' ];
		$condition->data 		= json_decode( (string) $conditionXML->data );
		$condition->save();
		
		if ( $conditionXML->conditions->condition )
		{
			foreach ( $conditionXML->conditions->condition as $_conditionXML )
			{
				$this->_constructNewCondition( $_conditionXML, $condition->id, $rule_id );
			}
		}
		
		return $condition;
	}

	/**
	 * Create An Action From XML
	 */
	protected function _constructNewAction( $actionXML, $rule_id )
	{
		$action = new \IPS\rules\Action;
		
		$action->rule_id 		= $rule_id;
		$action->title 			= (string) 	$actionXML[ 'title' ];
		$action->weight 		= (int) 	$actionXML[ 'weight' ];
		$action->app			= (string) 	$actionXML[ 'app' ];
		$action->class			= (string) 	$actionXML[ 'class' ];
		$action->key			= (string) 	$actionXML[ 'key' ];
		$action->enabled		= (int) 	$actionXML[ 'enabled' ];
		$action->schedule_mode		= (int)		$actionXML[ 'schedule_mode' ];
		$action->schedule_minutes 	= (int)		$actionXML[ 'schedule_minutes' ];
		$action->schedule_hours 	= (int)		$actionXML[ 'schedule_hours' ];
		$action->schedule_days 		= (int)		$actionXML[ 'schedule_days' ];
		$action->schedule_months 	= (int)		$actionXML[ 'schedule_months' ];
		$action->schedule_date		= (int)		$actionXML[ 'schedule_date' ];
		$action->schedule_key		= (string)	$actionXML[ 'schedule_key' ];
		$action->enable_recursion   = (int)  $actionXML[ 'recursion' ];
		$action->footprint		= (string)	$actionXML[ 'footprint' ];
		$action->else			= (int)		$actionXML[ 'else' ];
		
		$action->schedule_customcode	= (string)	$actionXML->schedule_customcode;
		$action->data 			= json_decode( (string) $actionXML->data );
		$action->save();
		
		return $action;
	}
	
	/**
	 * Create A Custom Action From XML
	 */
	protected function _constructNewCustomAction( $actionXML )
	{
		/**
		 * Delete previous version of this custom action if it exists
		 */
		try
		{
			$custom_action = \IPS\rules\Action\Custom::load( (string) $actionXML[ 'key' ], 'custom_action_key' );
			$custom_action->delete();
		}
		catch ( \OutOfRangeException $e ) {}
		
		$action = new \IPS\rules\Action\Custom;
		
		$action->title 			= (string) 	$actionXML[ 'title' ];
		$action->weight 		= (int) 	$actionXML[ 'weight' ];
		$action->description		= (string) 	$actionXML[ 'description' ];
		$action->key			= (string)	$actionXML[ 'key' ];
		$action->save();
		
		if ( $actionXML->arguments->argument )
		{
			foreach ( $actionXML->arguments->argument as $argumentXML )
			{
				$this->_constructNewArgument( $argumentXML, $action->id );
			}
		}
		
		return $action;
	}	

	/**
	 * Create An Argument From XML
	 */
	protected function _constructNewArgument( $argumentXML, $parent_id )
	{
		$argument = new \IPS\rules\Action\Argument;
		
		$argument->parent_id		= $parent_id;
		$argument->name 		= (string) 	$argumentXML[ 'name' ];
		$argument->type 		= (string) 	$argumentXML[ 'type' ];
		$argument->class		= (string) 	$argumentXML[ 'class' ];
		$argument->required		= (int) 	$argumentXML[ 'required' ];
		$argument->weight		= (int)		$argumentXML[ 'weight' ];
		$argument->custom_class		= (string)	$argumentXML[ 'custom_class' ];
		$argument->description		= (string)	$argumentXML[ 'description' ];
		$argument->varname		= (string)	$argumentXML[ 'varname' ];
		$argument->save();
		
		return $argument;
	}
	
	/**
	 * Create Custom Data Field From XML
	 */
	protected function _constructNewCustomData( $dataXML )
	{
		/**
		 * Look for existing custom data field with this key
		 */
		try
		{
			$data_field = \IPS\rules\Data::load( (string) $dataXML[ 'key' ], 'data_key' );
			return $data_field;
		}
		catch ( \OutOfRangeException $e ) {}
		
		$data_field = new \IPS\rules\Data;
		
		$data_field->key		= (string)	$dataXML[ 'key' ];
		$data_field->name 		= (string) 	$dataXML[ 'name' ];
		$data_field->class		= (string) 	$dataXML[ 'class' ];
		$data_field->type 		= (string) 	$dataXML[ 'type' ];
		$data_field->type_class		= (string)	$dataXML[ 'type_class' ];
		$data_field->column_name	= (string)	$dataXML[ 'column_name' ];
		$data_field->weight		= (int)		$dataXML[ 'weight' ];
		$data_field->required		= (int) 	$dataXML[ 'required' ];
		$data_field->description	= (string)	$dataXML[ 'description' ];
		$data_field->tab		= (string)	$dataXML[ 'tab' ];
		$data_field->use_mode		= (string)	$dataXML[ 'use_mode' ];
		$data_field->display_mode	= (string) 	$dataXML[ 'display_mode' ] ?: 'automatic';
		$data_field->text_mode		= (int)		$dataXML[ 'text_mode' ];
		$data_field->setting_key	= (string)	$dataXML[ 'setting_key' ];
		$data_field->value_default	= (string)	$dataXML[ 'value_default' ];
		
		$data_field->value_options	= (string)	$dataXML->value_options;
		$data_field->configuration	= (string)	$dataXML->configuration;
		
		$data_field->save();
		
		return $data_field;
	}		
	
}
