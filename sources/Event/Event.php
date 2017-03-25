<?php
/**
 * @brief		IPS4 Rules
 * @author		Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright		(c) 2014 - Kevin Carwile
 * @package		Rules
 * @since		6 Feb 2015
 */


namespace IPS\rules;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Node
 */
class _Event
{

	/**
	 * @brief	App
	 */
	public $app = NULL;
	
	/**
	 * @brief	Class
	 */
	public $class = NULL;
	
	/**
	 * @brief	Action Key
	 */
	public $key = NULL;
	
	/**
	 * @brief	Event Data
	 */
	public $data = NULL;
	
	/**
	 * @brief	Deferred Action Stack
	 */
	public $actionStack = array();
	
	/**
	 * Multiton Cache
	 */
	public static $multitons = array();
	
	/**
	 * Placeholder Flag
	 */
	public $placeholder = FALSE;
	
	/**
	 * API Response Params
	 */
	public $apiResponse = array();
	
	/**
	 * Event Loader
	 * 
	 * @param 	string	$app		App that defines the action
	 * @param	string	$class		Extension class where action is defined
	 * @param	string	$key		Action key
	 * @param	bool	$forced		Load event regardless of if there are any rules attached to it
	 * @return	\IPS\rules\Event	Return a rules event object
	 */
	public static function load( $app='null', $class='null', $key='null', $forced=FALSE )
	{
		if ( isset ( static::$multitons[ $app ][ $class ][ $key ] ) )
		{
			return static::$multitons[ $app ][ $class ][ $key ];
		}
		
		if ( $forced or static::hasRules( $app, $class, $key ) )
		{
			try
			{
				return static::$multitons[ $app ][ $class ][ $key ] = new \IPS\rules\Event( $app, $class, $key );
			}
			catch ( \BadMethodCallException $e )
			{
				/* Return a placeholder event */
				return static::$multitons[ $app ][ $class ][ $key ] = new \IPS\rules\Event\Placeholder( $app, $class, $key );
			}
		}
		else
		{
			/* Return a placeholder event */
			return new \IPS\rules\Event\Placeholder( $app, $class, $key, FALSE );
		}
	}
	
	/**
	 * Events Cache
	 */
	protected static $eventsCache = array();
	
	/**
	 * Constructor
	 *
	 * @param 	string	$app		App that defines the action
	 * @param	string	$class		Extension class where action is defined
	 * @param	string	$key		Action key
	 */
	public function __construct( $app, $class, $key )
	{
		$this->app 	= $app;
		$this->class 	= $class;
		$this->key	= $key;
		
		$extClass = '\IPS\\' . $app . '\extensions\rules\Definitions\\' . $class;
		if ( class_exists( $extClass ) )
		{
			if ( isset( static::$eventsCache[ $app ][ $class ] ) )
			{
				$events = static::$eventsCache[ $app ][ $class ];
			}
			else
			{
				$ext = new $extClass;
				$events = static::$eventsCache[ $app ][ $class ] = method_exists( $ext, 'events' ) ? $ext->events() : array();
			}
			
			if ( isset ( $events[ $key ] ) )
			{
				$this->data = $events[ $key ];
				static::$multitons[ $this->app ][ $this->class ][ $this->key ] = $this;
			}
			else
			{
				throw new \BadMethodCallException( \IPS\Member::loggedIn()->language()->get( 'rules_event_not_found' ) );
			}
		}
		else
		{
			throw new \BadMethodCallException( \IPS\Member::loggedIn()->language()->get( 'rules_event_not_found' ) );
		}
	}
	
	/**
	 * Root Thread ID
	 *
	 * This is the thread for which deferred actions should be executed
	 */
	public $rootThread = NULL;
		
	/**
	 * Thread ID
	 */
	public $thread = NULL;
	
	/**
	 * Parent Thread ID
	 */
	public $parentThread = NULL;
		
	/**
	 * Recursion Protection
	 */
	public $locked = FALSE;

	/**
	 * Trigger An Event
	 */
	public function trigger()
	{
		if ( ! $this->locked )
		{
			/* Don't do this during an upgrade */
			if( \IPS\Dispatcher::hasInstance() AND \IPS\Dispatcher::i()->controllerLocation === 'setup' )
			{
				return;
			}
			
			/**
			 * Give each new event triggered a unique thread id so
			 * logs can be tied back to the event that generated them
			 */
			$parentThread = $this->parentThread;
			$this->parentThread = $this->thread;
			$this->thread = md5( uniqid() . mt_rand() );
			
			foreach ( $this->rules() as $rule )
			{
				if ( ! $rule->ruleset() or $rule->ruleset()->enabled )
				{
					if ( $rule->enabled )
					{
						$result = call_user_func_array( array( $rule, 'invoke' ), func_get_args() );
						
						if ( $rule->debug )
						{
							\IPS\rules\Application::rulesLog( $this, $rule, NULL, $result, 'Rule evaluated' );
						}
					}
					else
					{
						if ( $rule->debug )
						{
							\IPS\rules\Application::rulesLog( $this, $rule, NULL, '--', 'Rule not evaluated (disabled)' );
						}
					}
				}
				else
				{
					if ( $rule->debug )
					{
						\IPS\rules\Application::rulesLog( $this, $rule, NULL, '--', 'Rule not evaluated (rule set disabled)' );
					}				
				}
			}
			
			$this->thread = $this->parentThread;
			$this->parentThread = $parentThread;
			
			/** 
			 * Deferred Actions
			 *
			 * Only execute deferred actions at the root thread level
			 */
			if ( $this->thread === $this->rootThread )
			{
				$actions = $this->actionStack;
				$this->actionStack = array();
				$this->executeDeferred( $actions );
			}			
		}
	}
	
	/**
	 * Execute Deferred
	 *
	 * @param	array		$actions		Deferred actions to execute
	 * @return	void
	 */
	public function executeDeferred( $actions )
	{
		$this->locked = TRUE;
		
		while ( $deferred = array_shift( $actions ) )
		{
			$action 		= $deferred[ 'action' ];
			$this->thread 		= isset( $deferred[ 'thread' ] ) ? $deferred[ 'thread' ] : NULL;
			$this->parentThread 	= isset( $deferred[ 'parentThread' ] ) ? $deferred[ 'parentThread' ] : NULL;
			
			/**
			 * Execute the action
			 */					
			try
			{
				$action->locked = TRUE;
				
				$result = call_user_func_array( $action->definition[ 'callback' ], array_merge( $deferred[ 'args' ], array( $action->data[ 'configuration' ][ 'data' ], $deferred[ 'event_args' ], $action ) ) );					
				
				$action->locked = FALSE;
				
				if ( $rule = $action->rule() and $rule->debug )
				{
					\IPS\rules\Application::rulesLog( $this, $rule, $action, $result, 'Evaluated' );
				}
			}
			catch( \Exception $e )
			{
				/**
				 * Log Exceptions
				 */
				$paths = explode( '/', str_replace( '\\', '/', $e->getFile() ) );
				$file = array_pop( $paths );
				\IPS\rules\Application::rulesLog( $this, $action->rule(), $action, $e->getMessage() . '<br>Line: ' . $e->getLine() . ' of ' . $file, 'Operation Callback Exception', 1 );
			}
		}
		
		$this->locked = FALSE;
		
		/* Reset threads */
		$this->thread = $this->parentThread = $this->rootThread = NULL;	
	}
	
	/**
	 * Get Event Title
	 */
	public function title()
	{
		$lang = \IPS\Member::loggedIn()->language();
		
		if ( $lang->checkKeyExists( $this->app . '_' . $this->class . '_event_' . $this->key ) )
		{
			return $lang->get( $this->app . '_' . $this->class . '_event_' . $this->key );
		}
		
		return 'Untitled ( ' . $this->app . ' / ' . $this->class . ' / ' . $this->key . ' )';
	}
	
	/**
	 * @brief 	Cache for rules
	 */
	protected $rulesCache = NULL;
	
	/**
	 * Get rules attached to this event
	 */
	public function rules()
	{
		if ( isset( $this->rulesCache ) )
		{
			return $this->rulesCache;
		}
		
		try
		{
			return $this->rulesCache = \IPS\rules\Rule::roots( NULL, NULL, array( array( 'rule_event_app=? AND rule_event_class=? AND rule_event_key=?', $this->app, $this->class, $this->key ) ) );
		}
		catch ( \Exception $e )
		{
			/* Uninstalled */
			return $this->rulesCache = array();
		}
	}
	
	/* hasRules Cache */
	public static $hasRules = array();
		
	/**
	 * Check if rules are attached to an event
	 *
	 * @param 	string	$app		App that defines the action
	 * @param	string	$class		Extension class where action is defined
	 * @param	string	$key		Action key
	 * @param	bool	$enabled	Whether to only count enabled rules
	 * @return	bool
	 */
	public static function hasRules( $app, $class, $key, $enabled=TRUE )
	{	
		if ( isset( static::$hasRules[ $app ][ $class ][ $key ][ (int) $enabled ] ) )
		{
			return static::$hasRules[ $app ][ $class ][ $key ][ (int) $enabled ];
		}
		
		try
		{
			return static::$hasRules[ $app ][ $class ][ $key ][ (int) $enabled ] = (bool) \IPS\rules\Rule::roots( NULL, NULL, array( array( 'rule_event_app=? AND rule_event_class=? AND rule_event_key=? AND rule_enabled=1', $app, $class, $key ) ) );
		}
		catch( \Exception $e )
		{
			/* Uninstalled */
			return static::$hasRules[ $app ][ $class ][ $key ][ (int) $enabled ] = FALSE;
		}
	}
		
}