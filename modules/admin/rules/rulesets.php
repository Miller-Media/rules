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
		
		parent::execute();
	}
	
	
	/**
	 * Manage
	 */
	protected function manage()
	{
		\IPS\Output::i()->sidebar[ 'actions' ][ 'exportall' ] = array(
			'icon'	=> 'download',
			'link'	=> \IPS\Http\Url::internal( 'app=rules&module=rules&controller=rules&do=exportAll' ),
			'title'	=> 'rules_export_all',
			'data' => array( 'confirm' => '' ),
		);
		
		\IPS\Output::i()->output .= "<style> #tree_search { display:none; } </style>";
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
		}
		
		if ( $parent )
		{
			\IPS\Output::i()->output .= \IPS\rules\Application::ruleChild( $parent );
		}
		
		parent::form();		
	}
	
	/**
	 * Redirect after save
	 *
	 * @param	\IPS\Node\Model	$old	A clone of the node as it was before or NULL if this is a creation
	 * @param	\IPS\Node\Model	$node	The node now
	 * @return	void
	 */
	protected function _afterSave( \IPS\Node\Model $old = NULL, \IPS\Node\Model $new )
	{
		if( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->json( array() );
		}
		else
		{
			if ( \IPS\Request::i()->subnode )
			{
				if ( $old == NULL )
				{
					\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rulesets&subnode=1&do=form&id={$new->id}&tab=conditions" ) );
				}
			}
		}
		
		parent::_afterSave( $old, $new );
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
		$event = \IPS\rules\Event::load( $log[ 'app' ], $log[ 'class' ], $log[ 'key' ] );
		
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
					return $operation->title;
				}
				catch ( \OutOfRangeException $e )
				{
					return 'Unknown Condition (deleted)';
				}
			},
			'result' => function( $val )
			{
				return $val;
			},
		);				
		$conditions->sortBy = 'id';
		$conditions->sortDirection = 'asc';
		$conditions->noSort = array( 'op_id', 'message', 'result' );
		
		$actions		= new \IPS\Helpers\Table\Db( 'rules_logs', $this->url, array( 'thread=? AND type=? AND rule_id=?', $log[ 'thread' ], 'IPS\rules\Action', $log[ 'rule_id' ] ) );
		$actions->include 	= array( 'op_id', 'message', 'result' );
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
				return $val;
			},
		);				
		$actions->sortBy = 'id';
		$actions->sortDirection = 'asc';
		$actions->noSort = array( 'op_id', 'message', 'result' );
		
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
						'link'		=> $self->url->setQueryString( array( 'do' => 'form', 'id' => $row[ 'rule_id' ], 'tab' => 'debug_console' ) ),
					);
				}
			}
			catch ( \OutOfRangeException $e ) {}
			
			return $buttons;
		};
		$subrules->noSort = array( 'rule_id', 'message', 'result' );
				
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'views' )->logdetails( $log, $event, $rule, $conditions, $actions, $subrules );
	}
	
	/**
	 * Export Rule(set)
	 */
	protected function export()
	{
		try
		{
			$rule = \IPS\rules\Rule::load( \IPS\Request::i()->rule );
		}
		catch( \OutOfRangeException $e )
		{
			\IPS\Output::i()->error( 'invalid_rule', '2RL01/B', 403 );
		}
		
		$xml = \IPS\Xml\SimpleXML::create( 'rules' );
		$this->_addRuleExport( $rule, $xml );
		
		\IPS\Output::i()->sendOutput( $xml->asXML(), 200, 'application/xml', array( "Content-Disposition" => \IPS\Output::getContentDisposition( 'attachment', \IPS\Http\Url::seoTitle( $rule->title ) . '.xml' ) ) );
	}
	
	/**
	 * Export Rule(set)
	 */
	protected function exportAll()
	{
	
		$xml = \IPS\Xml\SimpleXML::create( 'rules' );
		
		foreach ( \IPS\rules\Rule::roots( NULL ) as $rule )
		{
			$this->_addRuleExport( $rule, $xml );
		}
		
		\IPS\Output::i()->sendOutput( $xml->asXML(), 200, 'application/xml', array( "Content-Disposition" => \IPS\Output::getContentDisposition( 'attachment', 'all-rules.xml' ) ) );
	}
	
	/**
	 * Build Rule Nodes
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
		
		$conditionsNode = $ruleNode->addChild( 'conditions' );
		foreach ( $rule->conditions() as $condition )
		{
			$this->_addConditionExport( $condition, $conditionsNode );
		}
		
		$actionsNode = $ruleNode->addChild( 'actions' );
		foreach ( $rule->actions() as $action )
		{
			$this->_addActionExport( $action, $actionsNode );
		}
		
		$subrulesNode = $ruleNode->addChild( 'rules' );
		foreach ( $rule->children() as $subrule )
		{
			$this->_addRuleExport( $subrule, $subrulesNode );
		}
	}
	
	/**
	 * Build Condition Nodes
	 */
	protected function _addConditionExport( $condition, $xml )
	{
		$conditionNode = $xml->addChild( 'condition' );
		
		$conditionNode->addAttribute( 'title', 		$condition->title );
		$conditionNode->addAttribute( 'weight', 	$condition->weight );
		$conditionNode->addAttribute( 'rule', 		$condition->rule_id );
		$conditionNode->addAttribute( 'app', 		$condition->app );
		$conditionNode->addAttribute( 'class', 		$condition->class );
		$conditionNode->addAttribute( 'key', 		$condition->key );
		$conditionNode->addAttribute( 'enabled',	$condition->enabled );
		$conditionNode->addAttribute( 'compare', 	$condition->group_compare );
		$conditionNode->addAttribute( 'not',		$condition->not );
		
		$conditionNode->addChild( 'data', json_encode( $condition->data ) );
		
		$subconditions = $conditionNode->addChild( 'conditions' );
		foreach( $condition->children() as $_condition )
		{
			$this->_addConditionExport( $_condition, $subconditions );
		}
	}
	
	/**
	 * Build Condition Nodes
	 */
	protected function _addActionExport( $action, $xml )
	{
		$actionNode = $xml->addChild( 'action' );
		
		$actionNode->addAttribute( 'title', 		$action->title );
		$actionNode->addAttribute( 'weight', 		$action->weight );
		$actionNode->addAttribute( 'rule', 		$action->rule_id );
		$actionNode->addAttribute( 'app', 		$action->app );
		$actionNode->addAttribute( 'class', 		$action->class );
		$actionNode->addAttribute( 'key', 		$action->key );
		$actionNode->addAttribute( 'enabled',		$action->enabled );
		$actionNode->addAttribute( 'description', 	$action->description );
		
		$actionNode->addChild( 'data', json_encode( $action->data ) );
	}
	
	/**
	 * Get Root Buttons
	 *
	 * @return	array
	 */
	public function _getRootButtons()
	{
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
								
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=rules&module=rules&controller=rules&do=doImport&file=' . urlencode( $tempFile ) . '&key=' . md5_file( $tempFile ) . ( isset( \IPS\Request::i()->id ) ? '&id=' . \IPS\Request::i()->id : '' ) ) );
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
		if ( ! file_exists( \IPS\Request::i()->file ) or md5_file( \IPS\Request::i()->file ) !== \IPS\Request::i()->key )
		{
			\IPS\Output::i()->error( 'generic_error', '2RI00/B', 500, '' );
		}
		
		if ( ! ( $import = \simplexml_load_file( \IPS\Request::i()->file, 'SimpleXMLElement', LIBXML_NOCDATA ) ) )
		{
			\IPS\Output::i()->error( 'xml_upload_invalid', '2RI00/C', 403, '' );
		}
				
		foreach ( $import->rule as $ruleXML )
		{
			$rule = $this->_constructNewRule( $ruleXML, 0 );
		}
		
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rules" ), 'rules_imported' );
		
	}
	
	protected function _constructNewRule( $ruleXML, $parent_id )
	{
		$rule = new \IPS\rules\Rule;
		
		$rule->parent_id	= (int) 	$parent_id;
		$rule->title 		= (string) 	$ruleXML[ 'title' ];
		$rule->weight 		= (int) 	$ruleXML[ 'weight' ];
		$rule->event_app 	= (string) 	$ruleXML[ 'app' ];
		$rule->event_class 	= (string) 	$ruleXML[ 'class' ];
		$rule->event_key	= (string) 	$ruleXML[ 'key' ];
		$rule->base_compare	= (string) 	$ruleXML[ 'compare' ];
		$rule->enabled		= (int) 	$ruleXML[ 'enabled' ];
		$rule->debug		= (int) 	$ruleXML[ 'debug' ];
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
		
		if ( $ruleXML->rules->rule )
		{
			foreach ( $ruleXML->rules->rule as $_ruleXML )
			{
				$this->_constructNewRule( $_ruleXML, $rule->id );
			}
		}
		
		return $rule;
	}
	
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
		$condition->data 		= json_decode( (string) $conditionXML->data );
		$condition->save();
		
		if ( $conditionXML->conditions->condition )
		{
			foreach ( $conditionXML->conditions->condition as $_conditionXML )
			{
				$this->_constructNewCondition( $_conditionXML, $condition->id, $rule_id );
			}
		}
	}

	protected function _constructNewAction( $actionXML, $rule_id )
	{
		$action = new \IPS\rules\Action;
		
		$action->rule_id 	= $rule_id;
		$action->title 		= (string) 	$actionXML[ 'title' ];
		$action->weight 	= (int) 	$actionXML[ 'weight' ];
		$action->app		= (string) 	$actionXML[ 'app' ];
		$action->class		= (string) 	$actionXML[ 'class' ];
		$action->key		= (string) 	$actionXML[ 'key' ];
		$action->enabled	= (int) 	$actionXML[ 'enabled' ];
		$action->data 		= json_decode( (string) $actionXML->data );
		$action->save();
	}
	
}