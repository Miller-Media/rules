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
	 * Add/Edit Form
	 *
	 * @return void
	 */
	protected function form()
	{	
		if ( \IPS\Request::i()->id )
		{
			$rule = \IPS\rules\Rule::load( \IPS\Request::i()->id );
			\IPS\Output::i()->output .= \IPS\rules\Application::eventHeader( $rule->event() );
		}
		
		if ( \IPS\Request::i()->parent or ( $rule and $rule->parent() ) )
		{
			\IPS\Output::i()->output .= \IPS\rules\Application::ruleChild( $rule );
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
			if ( $old == NULL )
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rules&do=form&id={$new->id}&tab=conditions" ) );
			}
			else
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=rules" ), 'saved' );
			}
		}
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
		
		$conditions		= new \IPS\Helpers\Table\Db( 'rules_logs', $this->url, array( 'thread=? AND type=?', $log[ 'thread' ], 'IPS\rules\Condition' ) );
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
		
		$actions		= new \IPS\Helpers\Table\Db( 'rules_logs', $this->url, array( 'thread=? AND type=?', $log[ 'thread' ], 'IPS\rules\Action' ) );
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
		
		$subrules		= new \IPS\Helpers\Table\Db( 'rules_logs', $this->url, array( 'thread=? AND op_id=0 AND id!=?', $log[ 'thread' ], $log[ 'id' ] ) );
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
			
			$buttons[ 'debug' ] = array(
				'icon'		=> 'bug',
				'title'		=> 'Debug',
				'id'		=> "{$row['id']}-view",
				'link'		=> $self->url->setQueryString( array( 'do' => 'form', 'id' => $row[ 'rule_id' ], 'tab' => 'debug_console' ) ),
			);
			
			return $buttons;
		};
		$subrules->noSort = array( 'rule_id', 'message', 'result' );
				
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'views' )->logdetails( $log, $event, $rule, $conditions, $actions, $subrules );
	}
	
	
}