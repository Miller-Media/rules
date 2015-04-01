<?php


namespace IPS\rules\modules\admin\rules;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * schedule
 */
class _schedule extends \IPS\Dispatcher\Controller
{	
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
	 *
	 * @return	void
	 */
	protected function manage()
	{
		$self = $this;
		
		/* Create the table */
		$table = new \IPS\Helpers\Table\Db( 'rules_scheduled_actions', \IPS\Http\Url::internal( 'app=rules&module=rules&controller=schedule' ) );
		$table->include 	= array( 'schedule_time', 'schedule_action_id', 'schedule_thread', 'schedule_unique_key', 'schedule_created' );
		$table->langPrefix 	= 'rules_scheduled_';
		$table->parsers 	= array
		(
			'schedule_time'	=> function( $val )
			{
				return (string) \IPS\DateTime::ts( $val );
			},
			'schedule_created' => function( $val )
			{
				return (string) \IPS\DateTime::ts( $val );
			},
			'schedule_action_id' => function( $val, $row )
			{
				try
				{
					$action = \IPS\rules\Action::load( $val );
					return "<a href='" . \IPS\Http\Url::internal( "app=rules&module=rules&controller=actions&id={$val}&do=form" ) . "'>{$action->title}</a>";
				}
				catch ( \OutOfRangeException $e )
				{
					return "Action Missing ( deleted )";
				}
			},
			'schedule_thread' => function( $val, $row )
			{
				try
				{
					$action = \IPS\rules\Action::load( $row[ 'schedule_action_id' ] );
					if ( $rule = $action->rule() )
					{
						return "<a href='" . \IPS\Http\Url::internal( "app=rules&module=rules&controller=rules&id={$rule->id}&do=form&tab=actions" ) . "'>{$rule->title}</a>";
					}
				}
				catch( \OutOfRangeException $e ) 
				{
					return "No Rule Associated";
				}
			},
			'schedule_unique_key' => function( $val, $row )
			{
				if ( ! $val )
				{
					return "--";
				}
				
				return $val;
			},
		);				
		$table->sortBy = 'schedule_time';
		if ( ! \IPS\Request::i()->sortdirection )
		{
			$table->sortDirection = 'asc';
		}	
		
		$table->rowButtons = function( $row ) use ( $self )
		{	
			$buttons = array();
			$action = NULL;
			$rule = NULL;
			
			try
			{
				$action = \IPS\rules\Action::load( $row[ 'schedule_action_id' ] );
				$rule = $action->rule();
			}
			catch( \OutOfRangeException $e ) {}
			
			$buttons[ 'edit' ] = array
			(
				'icon' => 'pencil',
				'title' => 'edit',
				'link' => $self->url->setQueryString( array( 'do' => 'edit', 'id' => $row[ 'schedule_id' ] ) ),
			);
						
			$buttons[ 'execute' ] = array
			(
				'icon' => 'caret-square-o-right',
				'title' => 'Execute Now',
				'link' => $self->url->setQueryString( array( 'do' => 'executeAction', 'id' => $row[ 'schedule_id' ] ) ),
				'data' => array( 'confirm' => '' ),
			);
			
			$buttons[ 'delete' ] = array
			(
				'icon' => 'trash',
				'title' => 'Delete Action',
				'link' => $self->url->setQueryString( array( 'do' => 'delete', 'id' => $row[ 'schedule_id' ] ) ),
				'data' => array( 'confirm' => '' ),
			);
						
			if ( $rule )
			{
				try
				{
					$logid = \IPS\Db::i()->select( 'id', 'rules_logs', array( 'op_id=0 AND rule_parent=0 AND rule_id=? AND thread=?', $rule->id, $row[ 'schedule_thread' ] ) )->first();
					$buttons[ 'debug' ] = array(
						'icon'		=> 'bug',
						'title'		=> 'View Debug Log',
						'id'		=> "{$row['schedule_id']}-debug",
						'link'		=> \IPS\Http\Url::internal( "app=rules&module=rules&controller=rulesets&do=viewlog" )->setQueryString( array( 'logid' => $logid ) ),
						'data'		=> array( 'ipsDialog' => '' ),
					);
				}
				catch ( \UnderflowException $e ) {}
			}
			
			return $buttons;
		};
		$table->noSort = array( 'schedule_action_id', 'schedule_thread' );
		

		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack( 'rules_scheduled_actions' );
		\IPS\Output::i()->output	= \IPS\Theme::i()->getTemplate( 'global', 'core' )->block( 'title', (string) $table );
	}
	
	/**
	 * Edit Scheduled Action
	 *
	 * @return	void
	 */
	protected function edit()
	{
		try
		{
			$scheduled_action = \IPS\rules\Action\Scheduled::load( \IPS\Request::i()->id );
		}
		catch ( \OutOfRangeException $e )
		{
			\IPS\Output::i()->error( 'node_error', '2RS22/A', 403 );
		}
	
		$form = new \IPS\Helpers\Form( 'rules_edit_scheduled_action' );
		$form->add( new \IPS\Helpers\Form\Date( 'rules_scheduled_date', \IPS\DateTime::ts( $scheduled_action->time ), TRUE, array( 'time' => TRUE ) ) );
		
		if ( $values = $form->values() )
		{
			$scheduled_action->time = $values[ 'rules_scheduled_date' ]->getTimestamp();
			$scheduled_action->save();
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=schedule" ), 'rules_scheduled_action_updated' );
		}
	
		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack( 'rules_editing_scheduled_action' );
		\IPS\Output::i()->output 	= $form;
	}
	
	
	/**
	 * Execute Scheduled Action
	 *
	 * @return	void
	 */
	protected function executeAction()
	{
		try
		{
			$scheduled_action = \IPS\rules\Action\Scheduled::load( \IPS\Request::i()->id );
		}
		catch ( \OutOfRangeException $e )
		{
			\IPS\Output::i()->error( 'node_error', '2RS22/B', 403 );
		}
	
		$scheduled_action->execute();
		
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=schedule" ), 'rules_scheduled_action_executed' );
	}	
	
	/**
	 * Execute Scheduled Action
	 *
	 * @return	void
	 */
	protected function delete()
	{
		try
		{
			$scheduled_action = \IPS\rules\Action\Scheduled::load( \IPS\Request::i()->id );
		}
		catch ( \OutOfRangeException $e )
		{
			\IPS\Output::i()->error( 'node_error', '2RS22/C', 403 );
		}
	
		$scheduled_action->delete();
		
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=schedule" ), 'rules_scheduled_action_deleted' );
	}
	
}