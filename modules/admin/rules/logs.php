<?php


namespace IPS\rules\modules\admin\rules;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * logs
 */
class _logs extends \IPS\Dispatcher\Controller
{	
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'logs_manage' );
		parent::execute();
	}
	
	/**
	 * Manage
	 *
	 * @return	void
	 */
	protected function manage()
	{		
		/* Create the table */
		$controllerUrl 	= \IPS\Http\Url::internal( "app=rules&module=rules&controller=rulesets&do=viewlog" );
		$table = new \IPS\Helpers\Table\Db( 'rules_logs', \IPS\Http\Url::internal( 'app=rules&module=rules&controller=logs' ), array( 'error>0 OR ( op_id=0 AND rule_parent=0 )' ) );
		$table->include 	= array( 'type', 'app', 'key', 'message', 'result', 'time' );
		$table->langPrefix 	= 'rules_logs_table_';
		$table->parsers 	= array
		(
			'app' => function( $val, $row )
			{
				$event = \IPS\rules\Event::load( $row[ 'app' ], $row[ 'class' ], $row[ 'key' ], TRUE );
				return $event->title();
			},
			'key' => function( $val, $row )
			{
				if ( $row[ 'rule_id' ] )
				{
					try
					{
						$rule = \IPS\rules\Rule::load( $row[ 'rule_id' ] );
						return "<a href='" . \IPS\Http\Url::internal( "app=rules&module=rules&controller=rules&id={$rule->id}&do=form" ) . "'>{$rule->title}</a>";
					}
					catch( \OutOfRangeException $e ) {}
				}
			},
			'time'	=> function( $val )
			{
				return (string) \IPS\DateTime::ts( $val );
			},
			'type' => function( $val, $row )
			{
				return $row[ 'error' ] ? "<span style='color:red'><i class='fa fa-warning'></i> Error Log</span>" : "<i class='fa fa-bug'></i> Rule Debug Log";
			},
			'result' => function( $val )
			{
				return json_decode( $val );
			},
		);				
		$table->sortBy = 'id';
		$table->sortDirection = 'desc';
		$table->rowButtons = function( $row ) use ( $controllerUrl )
		{	
			$buttons = array();
			
			if ( $row[ 'rule_id' ] )
			{
				$buttons[ 'view' ] = array(
					'icon'		=> 'search',
					'title'		=> 'View Log Info',
					'id'		=> "{$row['id']}-view",
					'link'		=> $controllerUrl->setQueryString( array( 'logid' => $row[ 'id' ] ) ),
					'data'		=> array( 'ipsDialog' => '' ),
				);
			}
			
			return $buttons;
		};
		$table->noSort = array( 'id', 'key', 'type', 'message', 'result' );
		
		\IPS\Output::i()->sidebar[ 'actions' ][ 'flush' ] = array(
			'icon'	=> 'trash',
			'link'	=> \IPS\Http\Url::internal( 'app=rules&module=rules&controller=logs&do=flushlogs' ),
			'title'	=> 'rules_flush_logs',
			'data' => array( 'confirm' => '' ),
		);
		
		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack( 'rules_logs' );
		\IPS\Output::i()->output	= \IPS\Theme::i()->getTemplate( 'global', 'core' )->block( 'title', (string) $table );
	}
	
	protected function flushlogs()
	{
		$db = \IPS\Db::i();
		$db->delete( 'rules_logs' );
		$db->query( "ALTER TABLE `{$db->prefix}rules_logs` AUTO_INCREMENT = 1" );
		\IPS\Output::i()->redirect( $this->url->setQueryString( 'do', NULL ) );
	}
}