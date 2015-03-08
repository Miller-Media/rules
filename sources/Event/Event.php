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
	 * Multiton Cache
	 */
	public static $multitons = array();
	
	/**
	 * Placeholder Flag
	 */
	public $placeholder = FALSE;
	
	/**
	 * Event Loader
	 * 
	 * @param 	string	$app		App that defines the action
	 * @param	string	$class		Extension class where action is defined
	 * @param	string	$key		Action key
	 * @return	\IPS\rules\Event	Return a rules event object
	 */
	public static function load( $app='null', $class='null', $key='null' )
	{
		if ( isset ( static::$multitons[ $app ][ $class ][ $key ] ) )
		{
			return static::$multitons[ $app ][ $class ][ $key ];
		}
		
		try
		{
			$event = new \IPS\rules\Event( $app, $class, $key );
			return static::$multitons[ $app ][ $class ][ $key ] = $event;
		}
		catch ( \BadMethodCallException $e )
		{
			/**
			 * Return a placeholder event
			 */
			$event = new \IPS\rules\Event\Placeholder( $app, $class, $key );
			return static::$multitons[ $app ][ $class ][ $key ] = $event;
		}
	}
	
	/**
	 * Extension Cache
	 */
	public static $extensions = array();
	
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
			$ext = isset ( static::$extensions[ $app ][ $class ] ) ? static::$extensions[ $app ][ $class ] : new $extClass;
			$events = $ext->events();
			
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
		}
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
		
		return '';
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
		
		return $this->rulesCache = \IPS\rules\Rule::roots( NULL, NULL, array( array( 'rule_event_app=? AND rule_event_class=? AND rule_event_key=?', $this->app, $this->class, $this->key ) ) );
	}
		
}