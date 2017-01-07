<?php


namespace IPS\rules\modules\admin\rules;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * actions
 */
class _actions extends \IPS\Node\Controller
{
	/**
	 * Node Class
	 */
	protected $nodeClass = '\IPS\rules\Action';
	
	/**
	 * @brief	Associated Rule
	 */
	protected $rule = NULL;
	
	/**
	 * @brief	Action mode
	 */
	protected $actionMode = NULL;
	
	/**
	 * Constructor
	 *
	 * @param	\IPS\Http\Url|NULL	$url		The base URL for this controller or NULL to calculate automatically
	 * @return	void
	 */
	public function __construct( $url=NULL, $rule=NULL, $action_mode=NULL )
	{
		if ( isset ( $rule ) )
		{
			$this->rule = $rule;
		}
		
		$this->actionMode = $action_mode;
		
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
		
		/* Javascript Controllers */
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'admin_ui.js', 'rules', 'admin' ) );
				
		if ( ! isset( $this->rule ) and \IPS\Request::i()->do != 'enableToggle' )
		{
			if ( \IPS\Request::i()->id )
			{
				try
				{
					$action = \IPS\rules\Action::load( \IPS\Request::i()->id );
					$rule_id = $action->rule_id;
				}
				catch ( \OutOfRangeException $e ) 
				{
					if ( \IPS\Request::i()->rule )
					{
						$rule_id = \IPS\Request::i()->rule;
						\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rules&do=form&id={$rule_id}&tab=actions" ) );
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
					\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rules&do=form&id={$rule_id}&tab=actions" ) );
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
		\IPS\Output::i()->title = 'Action: ' . \IPS\Output::i()->title;
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
		
		$where = array( 'action_rule_id=?', $this->rule->id );
		
		if ( $this->actionMode !== NULL )
		{
			$where = array( 'action_rule_id=? AND action_else=?', $this->rule->id, $this->actionMode );
		}
		
		foreach( $nodeClass::roots( NULL, NULL, array( $where ) ) as $node )
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
		
		if ( $nodeClass::canAddRoot() and ! $this->actionMode )
		{
			return array( 'add' => array(
				'icon'	=> 'plus',
				'title'	=> 'rules_add_action',
				'link'	=> $this->url->setQueryString( array( 'do' => 'form', 'rule' => $this->rule->id ) ),
				'data'	=> ( $nodeClass::$modalForms ? array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'rules_add_action' ) ) : array() )
				) );
		}
		
		return array();
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
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=actions&do=form&id={$new->id}&rule={$new->rule_id}" ) );
			}
			else
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rules&do=form&id={$new->rule_id}&tab=actions" ), 'saved' );
			}
		}
	}
			
}