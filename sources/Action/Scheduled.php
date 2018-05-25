<?php
/**
 * @brief		IPS4 Rules
 * @author		Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright		(c) 2014 - Kevin Carwile
 * @package		Rules
 * @since		6 Feb 2015
 */


namespace IPS\rules\Action;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Scheduled Action
 */
class _Scheduled extends \IPS\Patterns\ActiveRecord
{
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'schedule_';
	
	/**
	 * @brief	[ActiveRecord] ID Database Column
	 */
	public static $databaseColumnId = 'id';

	/**
	 * @brief	[ActiveRecord] Database table
	 * @note	This MUST be over-ridden
	 */
	public static $databaseTable	= 'rules_scheduled_actions';
		
	/**
	 * @brief	[ActiveRecord] Database ID Fields
	 */
	protected static $databaseIdFields = array();
	
	/**
	 * @brief	Bitwise keys
	 */
	protected static $bitOptions = array();

	/**
	 * @brief	[ActiveRecord] Multiton Store
	 * @note	This needs to be declared in any child classes as well, only declaring here for editor code-complete/error-check functionality
	 */
	protected static $multitons = array();
	
	/**
	 * Execute the Scheduled Action
	 *
	 * @param	bool	$deleteWhenDone		Delete the scheduled action when complete
	 */
	public function execute( $deleteWhenDone=TRUE )
	{
		if ( $this->queued )
		{
			return;
		}
		else
		{
			$this->queued = time();
			$this->save();
		}
		
		$action_data = json_decode( $this->data, TRUE );
		
		$args = array();
		$event_args = array();

		/**
		 * Standard Scheduled Action
		 */
		if ( $this->action_id )
		{
			foreach ( (array) $action_data[ 'args' ] as $arg )
			{
				$args[] = \IPS\rules\Application::restoreArg( $arg );
			}

			foreach ( (array) $action_data[ 'event_args' ] as $key => $arg )
			{
				$event_args[ $key ] = \IPS\rules\Application::restoreArg( $arg );
			}

			try
			{
				$action = \IPS\rules\Action::load( $this->action_id );
				$action->event()->thread = $this->thread;
				$action->event()->parentThread = $this->parent_thread;
				
				/* Set the root thread for which deferred actions should be executed */
				$action->event()->rootThread = $this->thread;

				if ( isset( $action->definition[ 'callback' ] ) and is_callable( $action->definition[ 'callback' ] ) )
				{
					try
					{
						$result = call_user_func_array( $action->definition[ 'callback' ], array_merge( $args, array( $action->data[ 'configuration' ][ 'data' ], $event_args, $action ) ) );

						if ( $rule = $action->rule() and $rule->debug )
						{
							\IPS\rules\Application::rulesLog( $rule->event(), $rule, $action, $result, 'Evaluated'  );
						}
					}
					catch ( \Exception $e )
					{
						$event = $action->rule() ? $action->rule()->event() : NULL;
						\IPS\rules\Application::rulesLog( $event, $action->rule(), $action, $e->getMessage(), 'Error Exception', 1 );
					}
				}
				else
				{
					if ( $rule = $action->rule() )
					{
						\IPS\rules\Application::rulesLog( $rule->event(), $rule, $action, FALSE, 'Missing Callback', 1  );
					}
				}
			}
			catch ( \OutOfRangeException $e ) { }
		}
		
		/**
		 * Custom Scheduled Actions
		 */
		else if ( $this->custom_id )
		{
			foreach ( (array) $action_data[ 'args' ] as $key => $arg )
			{
				$args[ $key ] = \IPS\rules\Application::restoreArg( $arg );
			}
			
			try
			{
				$action = \IPS\rules\Action\Custom::load( $this->custom_id );
				$event = \IPS\rules\Event::load( 'rules', 'CustomActions', 'custom_action_' . $action->key, TRUE );

				$deleteWhenDone = $action_data[ 'frequency' ] !== 'repeat';			
				
				if ( $bulk_arg = $action_data[ 'bulk_option' ] )
				{
					foreach( $action->children() as $argument )
					{
						if ( $bulk_arg === 'custom_argument_' . $argument->id )
						{
							$bulkClass = $argument->class == 'custom' ? $argument->custom_class : str_replace( '-', '\\', $argument->class );
							$records = iterator_to_array(
								new \IPS\Patterns\ActiveRecordIterator(
									\IPS\Db::i()->select( '*', $bulkClass::$databaseTable, array( $bulkClass::$databasePrefix . $bulkClass::$databaseColumnId . '>?', (int) $action_data[ 'bulk_counter' ] ), $bulkClass::$databasePrefix . $bulkClass::$databaseColumnId . ' ASC', (int) $action_data[ 'bulk_limit' ] ?: 100 ),
									$bulkClass
								)
							);
							break;
						}
					}
					
					if ( $records )
					{
						$idField = $bulkClass::$databaseColumnId;
						foreach ( $records as $record )
						{
							$args[ $bulk_arg ] = $record;
							$action_data[ 'bulk_counter' ] = $record->$idField;
							$this->data = json_encode( $action_data );
							$this->save();
							call_user_func_array( array( $event, 'trigger' ), array_values( $args ) );
						}
						
					}
					
					if ( \IPS\Db::i()->select( 'COUNT(*)', $bulkClass::$databaseTable, array( $bulkClass::$databasePrefix . $bulkClass::$databaseColumnId . '>?', (int) $action_data[ 'bulk_counter' ] ) )->first() == 0 )
					{
						/* Reschedule */
						$next_run = $this->time;
						$action_data[ 'bulk_counter' ] = 0;
						$this->data = json_encode( $action_data );
						
						while ( $next_run <= time() )
						{
							$interval = 	( (int) $action_data[ 'minutes' ] * 60 ) + 
									( (int) $action_data[ 'hours' ] * 60 * 60 ) +
									( (int) $action_data[ 'days' ] * 60 * 60 * 24 ) +
									( (int) $action_data[ 'months' ] * 60 * 60 * 24 * 30 );
									
							/* If zero interval, add 5 minutes to current time and break */
							if ( $interval <= 0 )
							{
								$next_run = time() + ( 60 * 5 );
								break;
							}
							
							$next_run += $interval;
						}
						
						$this->time = $next_run;
					}
					else
					{
						$deleteWhenDone = FALSE;
					}
				}
				else
				{
					call_user_func_array( array( $event, 'trigger' ), array_values( $args ) );
					
					/* Reschedule */
					$next_run = $this->time;
					
					while ( $next_run < time() )
					{
						$interval = 	( (int) $action_data[ 'minutes' ] * 60 ) + 
								( (int) $action_data[ 'hours' ] * 60 * 60 ) +
								( (int) $action_data[ 'days' ] * 60 * 60 * 24 ) +
								( (int) $action_data[ 'months' ] * 60 * 60 * 24 * 30 );
								
						/* If zero interval, add 5 minutes to current time and break */
						if ( $interval <= 0 )
						{
							$next_run = time() + ( 60 * 5 );
							break;
						}
						
						$next_run += $interval;
					}
					
					$this->time = $next_run;
				}
			}
			catch( \OutOfRangeException $e ) { }
		}

		if ( $deleteWhenDone )
		{
			$this->delete();
		}
		else
		{
			$this->queued = 0;
			$this->save();
		}
	}
	
}