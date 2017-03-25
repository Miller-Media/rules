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
class _Condition extends \IPS\Node\Model
{
	/**
	 * @brief	[ActiveRecord] Multiton Store
	 */
	protected static $multitons;
	
	/**
	 * @brief	[ActiveRecord] Database Table
	 */
	public static $databaseTable = 'rules_conditions';
	
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'condition_';
		
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
	public static $databaseColumnParent = 'parent_id';
	
	/**
	 * @brief	[Node] Node Title
	 */
	public static $nodeTitle = 'conditions';
		
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
	 * @brief 	Conditions Cache
	 */
	protected static $conditionsCache = array();
		
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
			if ( isset( static::$conditionsCache[ $this->app ][ $this->class ] ) )
			{
				$conditions = static::$conditionsCache[ $this->app ][ $this->class ];
			}
			else
			{
				$ext = new $extClass;
				$conditions = static::$conditionsCache[ $this->app ][ $this->class ] = ( method_exists( $ext, 'conditions' ) ? $ext->conditions() : array() );
			}
			
			if ( isset ( $conditions[ $this->key ] ) )
			{
				$this->definition = $conditions[ $this->key ];
			}
		}
		
		$this->data = json_decode( $this->_data[ 'data' ], TRUE ) ?: array();
		
		if ( $this->rule_id )
		{
			try 
			{
				$this->rule = \IPS\rules\Rule::load( $this->rule_id );
			}
			catch ( \OutOfRangeException $e ) { }
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
		if ( $this->hasChildren() )
		{
			return array(
				0	=> 'ipsBadge ipsBadge_warning',
				1	=> $this->compareMode() == 'or' ? 'or_group_badge' : 'and_group_badge',
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
		
		$form->hiddenValues[ 'condition_rule_id' ] = $this->rule->id;
		
		\IPS\rules\Application::opform( $form, $this, 'conditions' );
		
		$form->add( new \IPS\Helpers\Form\YesNo( 'condition_not', $this->not, FALSE ), 'operation_title' );
		
		if ( $this->id and $this->hasChildren() )
		{
			$compare_options = array
			(
				'and' 	=> 'AND',
				'or'	=> 'OR',
			);
			
			$form->add( new \IPS\Helpers\Form\Radio( 'condition_group_compare', $this->group_compare ?: 'and', FALSE, array( 'options' => $compare_options ), NULL, NULL, NULL, 'condition_group_compare' ), 'operation_title' );
		}
		
		$form->add( new \IPS\Helpers\Form\YesNo( 'condition_enable_recursion', $this->enable_recursion, FALSE ), 'operation_title' );
		
		parent::form( $form );
	}
	
	/**
	 * [Node] Save Add/Edit Form
	 *
	 * @param	array	$values	Values from the form
	 * @return	void
	 */
	public function saveForm( $values )
	{
		$values = \IPS\rules\Application::opformSave( $this, 'conditions', $values, array( 'condition_rule_id', 'condition_group_compare', 'condition_not', 'condition_enable_recursion' ) );		
		parent::saveForm( $values );
		
		/**
		 * Save Footprint
		 */
		$this->init();
		if ( isset( $this->definition ) )
		{
			$this->footprint = md5( json_encode( $this->definition[ 'arguments' ] ) );
			$this->save();
		}		
	}
	
	/**
	 * Get Compare Mode
	 */
	public function compareMode()
	{
		return $this->group_compare ?: 'and';
	}
	
	/**
	 * Recursion Protection
	 */
	public $locked = FALSE;
	
	/**
	 * Invoke Condition
	 */
	public function invoke()
	{
		if ( ! $this->locked or $this->enable_recursion )
		{
			/**
			 * Lock this from being triggered recursively
			 * and creating never ending loops
			 */
			$this->locked = TRUE;
			
			try
			{
				$result = call_user_func_array( '\IPS\rules\Application::opInvoke', array( $this, 'conditions', func_get_args() ) );
			}
			catch( \Exception $e )
			{
				$this->locked = FALSE;
				throw $e;
			}
			
			if ( $this->hasChildren() )
			{
				$compareMode = $this->compareMode();
				
				/**
				 * We already have a winner
				 */
				if ( $result and $compareMode == 'or' )
				{
					return TRUE;
				}
				
				/**
				 * We have already failed
				 */
				if ( ! $result and $compareMode == 'and' )
				{
					return FALSE;
				}
				
				/* Only possibilities at this point */
				// result FALSE mode OR
				// result TRUE mode AND
							
				foreach ( $this->children() as $condition )
				{
					if ( $condition->enabled )
					{
						$conditionsCount++;
						$_result = call_user_func_array( array( $condition, 'invoke' ), func_get_args() );
						
						if ( $_result and $compareMode == 'or' ) 
						{
							$result = TRUE;
							break;
						}

						if ( ! $_result and $compareMode == 'and' )
						{
							$result = FALSE;
							break;
						}
					}
					else
					{
						if ( $rule = $this->rule() and $rule->debug )
						{
							\IPS\rules\Application::rulesLog( $rule->event(), $rule, $condition, '--', 'Condition not evaluated (disabled)' );
						}
					}
				}
			}
			
			$this->locked = FALSE;
			
			return $result;
		}
		else
		{
			if ( $rule = $this->rule() and $rule->debug )
			{
				\IPS\rules\Application::rulesLog( $rule->event(), $rule, $this, '--', 'Condition recursion (not evaluated)' );
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