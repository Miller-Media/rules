<?php


namespace IPS\rules\modules\admin\rules;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

const LOCKED_TIMEOUT = 900;

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
		
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'chosen.jquery.js', 'rules', 'interface' ) );	
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'chosen.css', 'rules', 'admin' ) );
		
		/* Javascript Controller */
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'admin_ui.js', 'rules', 'admin' ) );
		
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
		
		\IPS\Output::i()->sidebar[ 'actions' ][ 'documentation' ] = array(
			'icon'	=> 'file',
			'link'	=> \IPS\Http\Url::external( 'http://www.ipsguru.net/documentation/rules/general/scheduling' ),
			'title'	=> 'rules_documentation',
			'target' => '_blank',
			'data' => array(),
		);		
		
		/* Create the table */
		$table = new \IPS\Helpers\Table\Db( 'rules_scheduled_actions', \IPS\Http\Url::internal( 'app=rules&module=rules&controller=schedule' ) );
		$table->include 	= array( 'schedule_time', 'schedule_action_id', 'schedule_thread', 'schedule_created', 'schedule_unique_key', 'schedule_parent' );
		$table->langPrefix 	= 'rules_scheduled_';
		
		$table->filters = array
		(
			'schedule_filter_keyphrases'	=> 'schedule_unique_key!=\'\'',
			'schedule_filter_manual'	=> 'schedule_custom_id>0',
			'schedule_filter_automated'	=> 'schedule_action_id>0',
		);
		
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
				if ( $val )
				{
					try
					{
						$action = \IPS\rules\Action::load( $val );
						return "<a href='" . \IPS\Http\Url::internal( "app=rules&module=rules&controller=actions&id={$val}&do=form" ) . "'>{$action->title}</a>";
					}
					catch ( \OutOfRangeException $e ) { }
				}
				else if ( $row[ 'schedule_custom_id' ] )
				{
					try
					{
						$action = \IPS\rules\Action\Custom::load( $row[ 'schedule_custom_id' ] );
						$scheduled_action = \IPS\rules\Action\Scheduled::constructFromData( $row );
						$action_data = json_decode( $scheduled_action->data, TRUE );
						return "<a href='" . \IPS\Http\Url::internal( "app=rules&module=rules&controller=custom&do=form&id={$action->custom_id}" ) . "'>{$action->title}</a> (" . ( $action_data[ 'bulk_option' ] ? \IPS\Member::loggedIn()->language()->addToStack( 'rules_bulk' ) . " " . $action_data[ 'bulk_limit' ] . " / " : "" ) . ( $action_data[ 'frequency' ] == 'repeat' ? \IPS\Member::loggedIn()->language()->addToStack( 'rules_recurring' ) : \IPS\Member::loggedIn()->language()->addToStack( 'rules_onetime' ) ) . ")";
					}
					catch ( \OutOfRangeException $e ) { }
				}
				
				return "Unknown";
			},
			'schedule_thread' => function( $val, $row )
			{
				if ( $row[ 'schedule_action_id' ] )
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
						return "Automation Rule (removed)";
					}
				}
				else if ( $row[ 'schedule_custom_id' ] )
				{
					return "Manual Entry";
				}
			},
			'schedule_parent' => function( $val, $row )
			{
				if ( $row[ 'schedule_queued' ] )
				{
					if ( $row[ 'schedule_queued' ] < time() - \IPS\rules\modules\admin\rules\LOCKED_TIMEOUT )
					{
						$status = "<span style='color:red' data-ipsTooltip title='" . \IPS\Member::loggedIn()->language()->addToStack( 'schedule_locked' ) . " since " . (string) \IPS\DateTime::ts( $row[ 'schedule_queued' ] ) . "'><i class='fa fa-circle'></i></span>";
					}
					else
					{
						$status = "<span style='color:green' data-ipsTooltip title='" . \IPS\Member::loggedIn()->language()->addToStack( 'schedule_running' ) . " since " . (string) \IPS\DateTime::ts( $row[ 'schedule_queued' ] ) . "'><i class='fa fa-circle'></i></span>";
					}
				}
				else
				{
					$status = "<span style='color:#ccc' data-ipsTooltip title='" . \IPS\Member::loggedIn()->language()->addToStack( 'schedule_pending' ) . "'><i class='fa fa-circle'></i></span>";
				}
				
				if ( $row[ 'schedule_custom_id' ] )
				{
					try
					{
						$action = \IPS\rules\Action\Custom::load( $row[ 'schedule_custom_id' ] );
						$scheduled_action = \IPS\rules\Action\Scheduled::constructFromData( $row );
						$action_data = json_decode( $scheduled_action->data, TRUE );
						
						if ( $bulk_arg = $action_data[ 'bulk_option' ] )
						{
							foreach( $action->children() as $argument )
							{
								if ( $bulk_arg === 'custom_argument_' . $argument->id )
								{
									$bulkClass = $argument->class == 'custom' ? $argument->custom_class : str_replace( '-', '\\', $argument->class );
									$total = \IPS\Db::i()->select( 'COUNT(*)', $bulkClass::$databaseTable )->first();
									$completed = \IPS\Db::i()->select( 'COUNT(*)', $bulkClass::$databaseTable, array( $bulkClass::$databasePrefix . $bulkClass::$databaseColumnId . '<=?', (int) $action_data[ 'bulk_counter' ] ) )->first();
									
									if ( $completed == 0 )
									{
										$status .= " Waiting to bulk process <strong>{$total}</strong> records";
									}
									else
									{
										$status .= " <strong>{$completed}</strong> of <strong>{$total}</strong> total records (" . ( (int) ( 100 * ( $completed / $total ) ) ) . "%)";
									}
									break;
								}
							}
						}
					}
					catch ( \OutOfRangeException $e ) { }
				}
				
				return $status;
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

		$table->sortBy = \IPS\Request::i()->sortby ?: 'schedule_time';
		$table->sortDirection = \IPS\Request::i()->sortdirection ?: 'asc';
		
		$table->rowButtons = function( $row ) use ( $self )
		{	
			$buttons = array();
			$action = NULL;
			$rule = NULL;
			
			$scheduled_action = \IPS\rules\Action\Scheduled::constructFromData( $row );
			$action_data = json_decode( $scheduled_action->data, TRUE ) ?: array();
			
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
				'title' => 'rules_execute',
				'link' => $self->url->setQueryString( array( 'do' => 'executeAction', 'id' => $row[ 'schedule_id' ] ) ),
				'data' => array( 'confirm' => '' ),
			);
			
			if ( $scheduled_action->queued and $scheduled_action->queued < time() - \IPS\rules\modules\admin\rules\LOCKED_TIMEOUT )
			{
				$buttons[ 'unlock' ] = array
				(
					'icon' => 'unlock',
					'title' => 'rules_unlock_action',
					'link' => $self->url->setQueryString( array( 'do' => 'unlockAction', 'id' => $row[ 'schedule_id' ] ) ),
				);
			}
			
			if ( $action_data[ 'bulk_option' ] and $action_data[ 'bulk_counter' ] )
			{
				$buttons[ 'reset' ] = array
				(
					'icon' => 'refresh',
					'title' => 'rules_reset_bulk',
					'link' => $self->url->setQueryString( array( 'do' => 'resetAction', 'id' => $row[ 'schedule_id' ] ) ),
					'data' => array( 'confirm' => '' ),
				);			
			}
			
			$buttons[ 'delete' ] = array
			(
				'icon' => 'trash',
				'title' => 'delete',
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
						'title'		=> 'rules_view_debug',
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
	 * Schedule Custom Actions
	 *
	 */
	protected function newSchedule()
	{
		$this->_customScheduleForm();
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
			\IPS\Output::i()->error( 'node_error', '2RS22/B', 403 );
		}
		
		/* Standard Schedule Form */
		if ( $scheduled_action->action_id )
		{
			$this->_standardScheduleForm( $scheduled_action );
		}
		
		/* Custom Schedule Form */
		else if ( $scheduled_action->custom_id )
		{
			$this->_customScheduleForm( $scheduled_action );
		}	
	}
	
	/**
	 * Custom Scheduling Form
	 *
	 * @param	\IPS\rules\Action\Scheduled|NULL	$scheduled_action	The scheduled action or NULL if creating a new one
	 * @return	void
	 */
	protected function _customScheduleForm( $scheduled_action=NULL )
	{
		$custom_id = isset( $scheduled_action ) ? $scheduled_action->custom_id : \IPS\Request::i()->custom_id;
		try
		{
			$customAction = \IPS\rules\Action\Custom::load( $custom_id );
		}
		catch( \OutOfRangeException $e )
		{
			\IPS\Output::i()->error( 'node_error', '2RS22/A', 403 );
		}
		
	
		$frequency_options = array
		(
			'once' 		=> 'rules_onetime',
			'repeat' 	=> 'rules_recurring',
		);
		
		$lang 		= \IPS\Member::loggedIn()->language();
		$action_data 	= isset( $scheduled_action ) ? ( json_decode( $scheduled_action->data, TRUE ) ?: array() ) : array();
		$action_args 	= array();
		$action_data_args = isset( $action_data[ 'args' ] ) ? (array) $action_data[ 'args' ] : array();
		
		foreach ( $action_data_args as $key => $arg )
		{
			$action_args[ $key ] = \IPS\rules\Application::restoreArg( $arg );
		}
		
		$form = new \IPS\Helpers\Form( 'rules_custom_schedule' );
		$form->addHtml( '<div class="ipsMessage ipsMessage_info"><h2 style="margin:0">' . $customAction->title . '</h2><p>' . $customAction->description . '</p></div>' );
		
		$form->add( new \IPS\Helpers\Form\Date( 'rules_scheduled_date', \IPS\DateTime::ts( ( isset( $scheduled_action ) ? $scheduled_action->time : strtotime( 'now +1 hour' ) ) ), TRUE, array( 'time' => TRUE ) ) );
		$form->add( new \IPS\Helpers\Form\Radio( 'rules_schedule_custom_frequency', isset( $action_data[ 'frequency' ] ) ? $action_data[ 'frequency' ] : NULL, FALSE, array( 'options' => $frequency_options, 'toggles' => array( 'repeat' => array( 'action_schedule_repeats', 'action_schedule_minutes', 'action_schedule_hours', 'action_schedule_days', 'action_schedule_months' ) ) ) ) );
		$form->addHtml( '<div class="ipsFieldRow"><div id="action_schedule_repeats" class="ipsFieldRow_content ipsMessage ipsMessage_warning">' . $lang->addToStack( 'rules_repeats' ) . '</div></div>' );
		$form->add( new \IPS\Helpers\Form\Number( 'action_schedule_minutes', isset( $action_data[ 'minutes' ] ) ? $action_data[ 'minutes' ] : 0, FALSE, array(), NULL, NULL, NULL, 'action_schedule_minutes' ) );
		$form->add( new \IPS\Helpers\Form\Number( 'action_schedule_hours', isset( $action_data[ 'hours' ] ) ? $action_data[ 'hours' ] : 0, FALSE, array(), NULL, NULL, NULL, 'action_schedule_hours' ) );
		$form->add( new \IPS\Helpers\Form\Number( 'action_schedule_days', isset( $action_data[ 'days' ] ) ? $action_data[ 'days' ] : 0, FALSE, array(), NULL, NULL, NULL, 'action_schedule_days' ) );
		$form->add( new \IPS\Helpers\Form\Number( 'action_schedule_months', isset( $action_data[ 'months' ] ) ? $action_data[ 'months' ] : 0, FALSE, array(), NULL, NULL, NULL, 'action_schedule_months' ) );
				
		$argument_inputs 	= array();
		$bulk_options 		= array();
		$bulk_options_toggles 	= array();
		
		foreach( $customAction->children() as $argument )
		{
			$form_name = 'custom_argument_' . $argument->id;
			$form_value = isset( $action_args[ 'custom_argument_' . $argument->id ] ) ? $action_args[ 'custom_argument_' . $argument->id ] : NULL;
			$form_input = NULL;
			
			$lang->words[ $form_name ] 		= $argument->name;
			$lang->words[ $form_name . '_desc' ] 	= $argument->description;
			
			switch ( $argument->type )
			{
				case 'int': 	$form_input = new \IPS\Helpers\Form\Number( $form_name, $form_value, $argument->required, array( 'min' => NULL ), NULL, NULL, NULL, $form_name ); break;			
				case 'float':	$form_input = new \IPS\Helpers\Form\Number( $form_name, $form_value, $argument->required, array( 'min' => NULL, 'decimals' => TRUE ), NULL, NULL, NULL, $form_name ); break;
				case 'string':	$form_input = new \IPS\Helpers\Form\TextArea( $form_name, $form_value, $argument->required, array(), NULL, NULL, NULL, $form_name ); break;
				case 'bool':	$form_input = new \IPS\Helpers\Form\YesNo( $form_name, $form_value, $argument->required, array(), NULL, NULL, NULL, $form_name ); break;
				case 'object':
				
					$objectClass = $argument->class == 'custom' ? $argument->custom_class : str_replace( '-', '\\', $argument->class );
					
					/* Node Select */
					if ( is_subclass_of( $objectClass, '\IPS\Node\Model' ) )
					{
						$form_input = new \IPS\Helpers\Form\Node( $form_name, $form_value, $argument->required, array( 'class' => $objectClass, 'multiple' => FALSE, 'permissionCheck' => 'view' ), NULL, NULL, NULL, $form_name );
						$bulk_options[ $form_name ] = $argument->name;
					}
					
					/* Content Select */
					else if ( is_subclass_of( $objectClass, '\IPS\Content\Item' ) )
					{
						$form_input = new \IPS\rules\Field\Content( $form_name, $form_value, $argument->required, array( 'multiple' => 1, 'class' => $objectClass ), NULL, NULL, NULL, $form_name );
						$bulk_options[ $form_name ] = $argument->name;
					}
					
					/* Member Select */
					else if ( $objectClass == '\IPS\Member' )
					{
						$form_input = new \IPS\Helpers\Form\Member( $form_name, $form_value, $argument->required, array( 'multiple' => 1 ), NULL, NULL, NULL, $form_name );
						$bulk_options[ $form_name ] = $argument->name;
					}
					
					/* Date Select */
					else if ( $objectClass == '\IPS\DateTime' )
					{
						$form_input = new \IPS\Helpers\Form\Date( $form_name, $form_value, $argument->required, array( 'time' => TRUE ), NULL, NULL, NULL, $form_name );
					}
					
					/* Url Input */
					else if ( $objectClass == '\IPS\Http\Url' )
					{
						$form_input = new \IPS\Helpers\Form\Url( $form_name, $form_value, $argument->required, array(), NULL, NULL, NULL, $form_name );
					}
					
					break;
				
				case 'array':
				
					$objectClass = $argument->class == 'custom' ? $argument->custom_class : str_replace( '-', '\\', $argument->class );

					/* Multiple Node Select */
					if ( is_subclass_of( $objectClass, '\IPS\Node\Model' ) )
					{
						$form_input = new \IPS\Helpers\Form\Node( $form_name, $form_value, $argument->required, array( 'class' => $objectClass, 'multiple' => TRUE, 'permissionCheck' => 'view' ), NULL, NULL, NULL, $form_name );
					}
					
					/* Multiple Content Select */
					else if ( is_subclass_of( $objectClass, '\IPS\Content\Item' ) )
					{
						$form_input = new \IPS\rules\Field\Content( $form_name, $form_value, $argument->required, array( 'multiple' => NULL, 'class' => $objectClass ), NULL, NULL, NULL, $form_name );
					}
					
					/* Multiple Member Select */
					else if ( $objectClass == '\IPS\Member' )
					{
						$form_input = new \IPS\Helpers\Form\Member( $form_name, $form_value, $argument->required, array( 'multiple' => NULL ), NULL, NULL, NULL, $form_name );
					}
					
					/* Multiple Date Select */
					else if ( $objectClass == '\IPS\DateTime' )
					{
						$form_input = new \IPS\Helpers\Form\Stack( $form_name, $form_value, $argument->required, array( 'stackFieldType' => 'Date', 'time' => FALSE ), NULL, NULL, NULL, $form_name );
					}
					
					/* Multiple Urls */
					else if ( $objectClass == '\IPS\Http\Url' )
					{
						$form_input = new \IPS\Helpers\Form\Stack( $form_name, $form_value, $argument->required, array( 'stackFieldType' => 'Url' ), NULL, NULL, NULL, $form_name );
					}				

					/* Multiple Arbitrary Values */
					else if ( $objectClass == '' )
					{
						$form_input = new \IPS\Helpers\Form\Stack( $form_name, $form_value, $argument->required, array(), NULL, NULL, NULL, $form_name );
					}
					
					break;
			}
			
			if ( $form_input )
			{
				if ( \IPS\Request::i()->rules_schedule_custom_bulk === $form_name )
				{
					$form_input->noError();
				}
				
				$argument_inputs[] = $form_input;
			}
		}
		
		if ( $bulk_options )
		{
			$bulk_options_keys = array_keys( $bulk_options );
			$bulk_options_toggles[ '' ] = $bulk_options_keys;
			
			/* Each bulk option should toggle on every argument but itself */
			foreach( $bulk_options_keys as $key )
			{
				$bulk_options_toggles[ $key ] = array_filter( $bulk_options_keys, function( $val ) use ( $key ) { return $key !== $val; } );
				$bulk_options_toggles[ $key ][] = 'rules_schedule_bulk_limit';
			}
			
			$form->addHeader( 'rules_bulk_options' );
			$form->add( new \IPS\Helpers\Form\Select( 'rules_schedule_custom_bulk', isset( $action_data[ 'bulk_option' ] ) ? $action_data[ 'bulk_option' ] : NULL, FALSE, array( 'options' => array_merge( array( '' => 'None' ), $bulk_options ), 'toggles' => $bulk_options_toggles ), NULL, "<div data-controller='rules.admin.ui.chosen'>", "</div>", 'rules_schedule_custom_bulk' ) );
			$form->add( $bulk_limit = new \IPS\Helpers\Form\Number( 'rules_schedule_bulk_limit', isset( $action_data[ 'bulk_limit' ] ) ? $action_data[ 'bulk_limit' ] : 500, TRUE, array( 'min' => 1 ), NULL, NULL, NULL, 'rules_schedule_bulk_limit' ) );
			
			if ( \IPS\Request::i()->rules_schedule_custom_bulk === '' )
			{
				$bulk_limit->noError();
			}
		}
		
		$form->addHeader( 'rules_fixed_arguments' );
		
		foreach( $argument_inputs as $input )
		{
			$form->add( $input );
		}
		
		if ( $values = $form->values() )
		{
			if ( ! isset( $scheduled_action ) )
			{
				$scheduled_action = new \IPS\rules\Action\Scheduled;
				$scheduled_action->created = time();
				$scheduled_action->action_id = 0;
				$scheduled_action->custom_id = $custom_id;
			}

			$scheduled_action->time = $values[ 'rules_scheduled_date' ]->getTimestamp();
			
			$action_data[ 'frequency' ] 	= $values[ 'rules_schedule_custom_frequency' ];
			$action_data[ 'minutes' ]	= $values[ 'action_schedule_minutes' ];
			$action_data[ 'hours' ]		= $values[ 'action_schedule_hours' ];
			$action_data[ 'days' ]		= $values[ 'action_schedule_days' ];
			$action_data[ 'months' ]	= $values[ 'action_schedule_months' ];
			
			$action_args = array();
			foreach( $customAction->children() as $argument )
			{
				if ( $values[ 'rules_schedule_custom_bulk' ] === 'custom_argument_' . $argument->id )
				{
					$action_args[ 'custom_argument_' . $argument->id ] = \IPS\rules\Application::storeArg( NULL );
					
					/* Set the counter to zero when creating bulk option for first time, or if bulk argument has been changed */
					if ( ! isset( $action_data[ 'bulk_counter' ] ) or $action_data[ 'bulk_option' ] !== $values[ 'rules_schedule_custom_bulk' ] )
					{
						$action_data[ 'bulk_counter' ] = 0;
					}
				}
				else
				{
					$action_args[ 'custom_argument_' . $argument->id ] = \IPS\rules\Application::storeArg( $values[ 'custom_argument_' . $argument->id ] );
				}
			}
			$action_data[ 'args' ] = $action_args;
			$action_data[ 'bulk_option' ] = $values[ 'rules_schedule_custom_bulk' ];
			$action_data[ 'bulk_limit' ] = $values[ 'rules_schedule_bulk_limit' ];
			
			$scheduled_action->data = json_encode( $action_data );
			$scheduled_action->save();
			
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=schedule&filter=schedule_filter_manual" ) );
		}
		
		\IPS\Output::i()->title 	= $lang->addToStack( 'rules_schedule_custom_action' );
		\IPS\Output::i()->output 	= $form;		
	}
	
	/**
	 * Standard Scheduling Form
	 *
	 * @param 	\IPS\rules\Action\Scheduled	$scheduled_action	The scheduled action
	 * @return 	void
	 */
	protected function _standardScheduleForm( $scheduled_action )
	{
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
			\IPS\Output::i()->error( 'node_error', '2RS22/C', 403 );
		}
		
		/* Set future scheduled dates to now */
		if ( $scheduled_action->time > time() )
		{
			$scheduled_action->time = time();
			$scheduled_action->save();
		}
		
		$scheduled_action->execute();
		
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=schedule" ), 'rules_scheduled_action_executed' );
	}
	
	/**
	 * Unlock frozen actions
	 */
	protected function unlockAction()
	{
		try
		{
			$scheduled_action = \IPS\rules\Action\Scheduled::load( \IPS\Request::i()->id );
		}
		catch ( \OutOfRangeException $e )
		{
			\IPS\Output::i()->error( 'node_error', '2RS22/C', 403 );
		}
		
		if ( $scheduled_action->queued < time() - ( 60 * 15 ) )
		{	
			$scheduled_action->queued = 0;
			$scheduled_action->save();
		}
		
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=schedule&filter=schedule_filter_manual" ) );
		
	}
	
	/**
	 * Reset bulk actions
	 */
	protected function resetAction()
	{
		try
		{
			$scheduled_action = \IPS\rules\Action\Scheduled::load( \IPS\Request::i()->id );
		}
		catch ( \OutOfRangeException $e )
		{
			\IPS\Output::i()->error( 'node_error', '2RS22/C', 403 );
		}
		
		$action_data = json_decode( $scheduled_action->data, TRUE );
		
		if ( $action_data[ 'bulk_option' ] and $action_data[ 'bulk_counter' ] )
		{	
			$action_data[ 'bulk_counter' ] = 0;
			$scheduled_action->data = json_encode( $action_data );
			$scheduled_action->save();
		}
		
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=schedule&filter=schedule_filter_manual" ) );
		
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
			\IPS\Output::i()->error( 'node_error', '2RS22/D', 403 );
		}
	
		$scheduled_action->delete();
		
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=rules&module=rules&controller=schedule" ), 'rules_scheduled_action_deleted' );
	}
	
}