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
class _Action extends \IPS\Node\Model
{
	/**
	 * @brief	[ActiveRecord] Multiton Store
	 */
	protected static $multitons;
	
	/**
	 * @brief	[ActiveRecord] Database Table
	 */
	public static $databaseTable = 'rules_actions';
	
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'action_';
		
	/**
	 * @brief	[Node] Order Database Column
	 */
	public static $databaseColumnOrder = 'weight';
	
	/**
	 * @brief	[ActiveRecord] Database ID Fields
	 */
	protected static $databaseIdFields = array();
	
	/**
	 * @brief	[Node] Parent ID Database Column
	 */
	public static $databaseColumnParent = NULL;
	
	/**
	 * @brief	[Node] Node Title
	 */
	public static $nodeTitle = 'actions';
	
	/**
	 * @brief	[Node] Parent Node Class
	 */
	//public static $parentNodeClass = 'IPS\rules\Rule';
	
	/**
	 * @brief	[Node] Parent Node Column
	 */
	//public static $parentNodeColumnId = 'rule_id';
	
	/**
	 * @brief	Use Modal Forms?
	 */
	public static $modalForms = FALSE;
	
	/**
	 *  Disable Copy Button
	 */	
	public $noCopyButton = TRUE;
	
	/**
	 *  Get Title
	 */
	public function get__title()
	{
		return $this->title;
	}
	
	/**
	 * Set Title
	 */
	public function set__title( $val )
	{
		$this->title = $val;
	}
	
	/**
	 * Get Data
	 */ 
	public $data = array();
	
	/**
	 * Associated Rule
	 */
	public $rule = NULL;
	
	/**
	 * [Node] Get whether or not this node is enabled
	 *
	 * @note	Return value NULL indicates the node cannot be enabled/disabled
	 * @return	bool|null
	 */
	protected function get__enabled()
	{
		return $this->enabled;
	}

	/**
	 * [Node] Set whether or not this node is enabled
	 *
	 * @param	bool|int	$enabled	Whether to set it enabled or disabled
	 * @return	void
	 */
	protected function set__enabled( $enabled )
	{
		$this->enabled = $enabled;
	}
	
	/**
	 * @brief	Action Definition
	 */
	public $definition = NULL;
		
	/**
	 * Init
	 *
	 * @return	void
	 */
	public function init()
	{
		$extClass = '\IPS\\' . $this->app . '\extensions\rules\Definitions\\' . $this->class;
		if ( class_exists( $extClass ) )
		{
			$ext 	= new $extClass;
			$actions = $ext->actions();
			
			if ( isset ( $actions[ $this->key ] ) )
			{
				$this->definition = $actions[ $this->key ];
			}
		}
		
		$this->data = json_decode( $this->_data[ 'data' ], TRUE ) ?: array();
		
		if ( $this->rule_id )
		{
			try 
			{
				$this->rule = \IPS\rules\Rule::load( $this->rule_id );
			}
			catch ( \OutOfRangeException $e ) {}
		}
	}
	
	/**
	 * Get the attached event
	 */
	public function event()
	{
		if ( $rule = $this->rule() )
		{
			return $rule->event();
		}
		
		/* Return Event Placeholder */
		return \IPS\rules\Event::load();
	}
	
	/**
	 * Get the attached event
	 */
	public function rule()
	{
		if ( isset ( $this->rule ) )
		{
			return $this->rule;
		}
		
		try
		{
			$rule = \IPS\rules\Rule::load( $this->rule_id );
		}
		catch ( \OutOfRangeException $e )
		{
			$rule = FALSE;
		}
		
		return $this->rule = $rule;
	}
	
	/**
	 * [Node] Get buttons to display in tree
	 * Example code explains return value
	 *
	 * @param	string	$url		Base URL
	 * @param	bool	$subnode	Is this a subnode?
	 * @return	array
	 */
	public function getButtons( $url, $subnode=FALSE )
	{
		$buttons = parent::getButtons( $url, $subnode );
		foreach ( $buttons as $id => &$button )
		{
			$button[ 'link' ] = $button[ 'link' ]->setQueryString( 'rule', $this->rule_id );
		}
		
		return $buttons;
	}
	

	
	/**
	 * [Node] Custom Badge
	 *
	 * @return	NULL|array	Null for no badge, or an array of badge data (0 => CSS class type, 1 => language string, 2 => optional raw HTML to show instead of language string)
	 */
	protected function get__badge()
	{
		if ( 0 )
		{
			return array(
				0	=> 'ipsBadge ipsBadge_intermediary',
				1	=> 'badge text',
			);
		}
		
		return NULL;
	}
	
	/**
	 * [Node] Add/Edit Form
	 *
	 * @param	\IPS\Helpers\Form	$form	The form
	 * @return	void
	 */
	public function form( &$form )
	{
		if ( ! isset ( $this->rule ) )
		{
			try
			{
				$this->rule = \IPS\rules\Rule::load( \IPS\Request::i()->rule );
			}
			catch( \OutOfRangeException $e ) 
			{
				\IPS\Output::i()->error( 'invalid_rule', '2RC02/A', 403, '' );
			}
		}

		$lang = \IPS\Member::loggedIn()->language();
		$form->hiddenValues[ 'action_rule_id' ] = $this->rule->id;
				
		\IPS\rules\Application::opform( $form, $this, 'actions' );
		
		$scheduling_options = array
		(
			0 => 'rules_action_execution_now',
			1 => 'rules_action_execution_future',
			2 => 'rules_action_execution_date',
			3 => 'rules_action_execution_custom',
		);
		
		$scheduling_toggles = array
		(
			1 => array( 'action_schedule_minutes', 'action_schedule_hours', 'action_schedule_days', 'action_schedule_months' ),
			2 => array( 'action_schedule_date' ),
			3 => array( 'action_schedule_customcode' ),
		);
		
		$form->add( new \IPS\Helpers\Form\Radio( 'action_schedule_mode', $this->schedule_mode ?: 0, TRUE, array( 'options' => $scheduling_options, 'toggles' => $scheduling_toggles ) ), 'operation_title' );
		$form->add( new \IPS\Helpers\Form\Number( 'action_schedule_minutes', $this->schedule_minutes ?: 0, TRUE, array(), NULL, NULL, NULL, 'action_schedule_minutes' ), 'action_schedule_mode' );
		$form->add( new \IPS\Helpers\Form\Number( 'action_schedule_hours', $this->schedule_hours ?: 0, TRUE, array(), NULL, NULL, NULL, 'action_schedule_hours' ), 'action_schedule_minutes' );
		$form->add( new \IPS\Helpers\Form\Number( 'action_schedule_days', $this->schedule_days ?: 0, TRUE, array(), NULL, NULL, NULL, 'action_schedule_days' ), 'action_schedule_hours' );
		$form->add( new \IPS\Helpers\Form\Number( 'action_schedule_months', $this->schedule_months ?: 0, TRUE, array(), NULL, NULL, NULL, 'action_schedule_months' ), 'action_schedule_days' );
		$form->add( new \IPS\Helpers\Form\Date( 'action_schedule_date', \IPS\DateTime::ts( $this->schedule_date ), FALSE, array( 'time' => TRUE ), NULL, NULL, NULL, 'action_schedule_date' ), 'action_schedule_months' );
		$form->add( new \IPS\Helpers\Form\Codemirror( 'action_schedule_customcode', $this->schedule_customcode ?: "//<?php\n\nreturn \IPS\DateTime::ts( time() );", FALSE, array( 'mode' => 'php' ), NULL, NULL, NULL, 'action_schedule_customcode' ), 'action_schedule_date' );
		
		$event = $this->event();
		
		if ( ! $this->id )
		{
			try
			{
				$event = \IPS\rules\Rule::load( \IPS\Request::i()->rule )->event();
			}
			catch ( \OutOfRangeException $e ) {}
		}
		
		try
		{
			$lang->words[ 'action_schedule_customcode_desc' ] = $lang->get( 'phpcode_desc' ) . "<br><br>" . $lang->get( 'phpcode_desc_details' ) . $lang->get( 'action_schedule_customcode__desc' ) . "<br>" . $lang->get( 'phpcode_desc_details_vars' ) . \IPS\rules\Application::eventArgInfo( $event );
		}
		catch ( \Exception $e ) {}
		
	}
	
	/**
	 * [Node] Save Add/Edit Form
	 *
	 * @param	array	$values	Values from the form
	 * @return	void
	 */
	public function saveForm( $values )
	{
		if ( $values[ 'action_schedule_date' ] instanceof \IPS\DateTime )
		{
			$values[ 'action_schedule_date' ] = $values[ 'action_schedule_date' ]->getTimestamp();
		}
		
		$values = \IPS\rules\Application::opformSave( $this, 'actions', $values, array( 'action_rule_id', 'action_schedule_mode', 'action_schedule_minutes', 'action_schedule_hours', 'action_schedule_days', 'action_schedule_months', 'action_schedule_date', 'action_schedule_customcode' ) );		
		parent::saveForm( $values );
	}
	
	/**
	 * Recursion Protection
	 */
	public $locked = FALSE;
	
	/**
	 * Invoke Action
	 */
	public function invoke()
	{
		if ( ! $this->locked )
		{
			/**
			 * Lock this action from being triggered recursively by itself
			 * and creating never ending loops
			 */
			$this->locked = TRUE;
			
			call_user_func_array( '\IPS\rules\Application::opInvoke', array( $this, 'actions', func_get_args() ) );
			
			$this->locked = FALSE;
		}
		else
		{
			if ( $rule = $this->rule() and $rule->debug )
			{
				\IPS\rules\Application::rulesLog( $rule->event(), $rule, $this, '--', 'Action recursion (not evaluated)' );
			}
		}
	}
	
	/**
	 * [ActiveRecord] Save 
	 */
	public function save()
	{
		$this->_data[ 'data' ] = json_encode( $this->data );
		$this->changed[ 'data' ] = $this->_data[ 'data' ];
		parent::save();
	}
		
	/**
	 * [ActiveRecord] Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{
		foreach ( $this->children() as $child )
		{
			$child->delete();
		}
		
		return parent::delete();
	}	
	
}