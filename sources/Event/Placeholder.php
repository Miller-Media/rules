<?php
/**
 * @brief		IPS4 Rules
 * @author		Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright		(c) 2014 - Kevin Carwile
 * @package		Rules
 * @since		6 Feb 2015
 */


namespace IPS\rules\Event;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Node
 */
class _Placeholder extends \IPS\rules\Event
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
	 * Placeholder Flag
	 */
	public $placeholder = TRUE;
	
	/**
	 * Constructor
	 *
	 * @param 	string	$app		App that defines the action
	 * @param	string	$class		Extension class where action is defined
	 * @param	string	$key		Action key
	 * @param	bool	$silent		Don't complain if the event cant be loaded, and return a shell object
	 */
	public function __construct( $app, $class, $key )
	{
		$this->app 	= $app;
		$this->class 	= $class;
		$this->key	= $key;
	}
	
	/**
	 * Trigger An Event
	 */
	public function trigger()
	{
		\IPS\rules\Application::rulesLog( $this, NULL, NULL, 'error', 'No event definition available', 1 );
	}
	
	/**
	 * Event Title
	 */
	public function title() 
	{
		return 'Unknown';
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
		return array();
	}
		
}