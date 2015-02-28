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
class _Rule extends \IPS\Node\Model
{
	/**
	 * @brief	[ActiveRecord] Multiton Store
	 */
	protected static $multitons;
	
	/**
	 * @brief	[ActiveRecord] Database Table
	 */
	public static $databaseTable = 'rules_rules';
	
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'rule_';
		
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
	public static $nodeTitle = 'rules';
	
	/**
	 * @brief	Sub Node Class
	 */
	public static $subnodeClass = NULL;
	
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
	 * Init
	 *
	 * @return	void
	 */
	public function init()
	{

	}
	
	/**
	 * [Node] Custom Badge
	 *
	 * @return	NULL|array	Null for no badge, or an array of badge data (0 => CSS class type, 1 => language string, 2 => optional raw HTML to show instead of language string)
	 */
	protected function get__badge()
	{
		if ( $this->debug )
		{
			return array(
				0	=> 'ipsBadge ipsBadge_warning',
				1	=> 'debug_on',
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
		$events = array();
		$form->addTab( 'rules_settings' );
		
		/**
		 * Children Rules Inherit Event From Parent
		 */
		if ( ! $this->id and \IPS\Request::i()->parent )
		{
			$parent = \IPS\rules\Rule::load( \IPS\Request::i()->parent );
			$this->event_app 	= $parent->event_app;
			$this->event_class 	= $parent->event_class;
			$this->event_key	= $parent->event_key;
		}
					
		$form->add( new \IPS\Helpers\Form\Text( 'rule_title', $this->title, TRUE, array( 'placeholder' => \IPS\Member::loggedIn()->language()->addToStack( 'rule_title_placeholder' ) ) ) );
		
		if ( ! $this->event_key )
		{
			foreach ( \IPS\rules\Application::rulesDefinitions() as $definition_key => $definition )
			{
				foreach ( $definition[ 'events' ] as $event_key => $event_data )
				{				
					$events[ $definition[ 'group' ] ][ $definition_key . '_' . $event_key ] = $definition[ 'app' ] . '_' . $definition[ 'class' ] . '_event_' . $event_key;
				}
			}
			$form->add( new \IPS\Helpers\Form\Select( 'rule_event_selection', $this->id ? md5( $this->event_app . $this->event_class ) . '_' . $this->event_key : NULL, TRUE, array( 'options' => $events, 'noDefault' => TRUE ), NULL, NULL, NULL, 'rule_event_selection' ) );
		}
		else
		{
			$events[ md5( $this->event_app . $this->event_class ) . '_' . $this->event_key ] = $this->event_app . '_' . $this->event_class . '_event_' . $this->event_key;
			if ( \IPS\Request::i()->parent or $this->parent() )
			{
				$form->hiddenValues[ 'rule_event_selection' ] = md5( $this->event_app . $this->event_class ) . '_' . $this->event_key;
			}
			else
			{
				$form->add( new \IPS\Helpers\Form\Select( 'rule_event_selection', $this->id ? md5( $this->event_app . $this->event_class ) . '_' . $this->event_key : NULL, TRUE, array( 'options' => $events, 'noDefault' => TRUE ), NULL, NULL, NULL, 'rule_event_selection' ) );
			}
		}
		
		
		/**
		 * Conditions & Actions 
		 */
		if ( $this->id )
		{
			$form->add( new \IPS\Helpers\Form\Checkbox( 'rule_debug', $this->debug, FALSE ) );
			
			if ( isset( \IPS\Request::i()->tab ) )
			{
				$form->activeTab = 'rules_' . \IPS\Request::i()->tab;
			}
		
			$form->addTab( 'rules_conditions' );
			$form->addHeader( 'rule_conditions' );
			
			$compare_options = array(
				'and' 	=> 'AND',
				'or'	=> 'OR',
			);
			
			$form->add( new \IPS\Helpers\Form\Radio( 'rule_base_compare', $this->base_compare ?: 'and', FALSE, array( 'options' => $compare_options ), NULL, NULL, NULL, 'rule_base_compare' ) );
			
			/* Just a little nudging */
			$form->addHtml( "
				<style>
					#rule_base_compare br { display:none; }
					#elRadio_rule_base_compare_rule_base_compare { width: 100px; display:inline-block; }
				</style>
			" );
			
			$conditionClass		= '\IPS\rules\Condition';
			$conditionController 	= new \IPS\rules\modules\admin\rules\conditions( NULL, $this );
			$conditions 		= new \IPS\Helpers\Tree\Tree( 
							\IPS\Http\Url::internal( "app=rules&module=rules&controller=conditions&rule={$this->id}" ),
							$conditionClass::$nodeTitle, 
							array( $conditionController, '_getRoots' ), 
							array( $conditionController, '_getRow' ), 
							array( $conditionController, '_getRowParentId' ), 
							array( $conditionController, '_getChildren' ), 
							array( $conditionController, '_getRootButtons' )
						);
			
			/**
			 * Replace form constructs with div's
			 */
			$conditionsTreeHtml = (string) $conditions;
			$conditionsTreeHtml = str_replace( '<form ', '<div ', $conditionsTreeHtml );
			$conditionsTreeHtml = str_replace( '</form>', '</div>', $conditionsTreeHtml );
			$form->addHtml( $conditionsTreeHtml );
			
			$form->addTab( 'rules_actions' );
			$form->addHeader( 'rule_actions' );
			
			$actionClass		= '\IPS\rules\Action';
			$actionController 	= new \IPS\rules\modules\admin\rules\actions( NULL, $this );
			$actions 		= new \IPS\Helpers\Tree\Tree( 
							\IPS\Http\Url::internal( "app=rules&module=rules&controller=actions&rule={$this->id}" ),
							$actionClass::$nodeTitle, 
							array( $actionController, '_getRoots' ), 
							array( $actionController, '_getRow' ), 
							array( $actionController, '_getRowParentId' ), 
							array( $actionController, '_getChildren' ), 
							array( $actionController, '_getRootButtons' )
						);
			
			/**
			 * Replace form constructs with div's
			 */
			$actionsTreeHtml = (string) $actions;
			$actionsTreeHtml = str_replace( '<form ', '<div ', $actionsTreeHtml );
			$actionsTreeHtml = str_replace( '</form>', '</div>', $actionsTreeHtml );
			$form->addHtml( $actionsTreeHtml );
			
			/**
			 * Show debugging information about this rule if debugging is enabled
			 */
			if ( $this->debug )
			{
				$form->addTab( 'rules_debug_console' );
				
				$self 		= $this;
				$controllerUrl 	= \IPS\Http\Url::internal( "app=rules&module=rules&controller=rules&do=viewlog" );
				$table 		= new \IPS\Helpers\Table\Db( 'rules_logs', $controllerUrl, array( 'rule_id=? AND op_id=0', $this->id ) );
				$table->include = array( 'time', 'message', 'result' );
				$table->parsers = array(
					'time'	=> function( $val )
					{
						return (string) \IPS\DateTime::ts( $val );
					},
					'result' => function( $val )
					{
						$result = json_decode( $val );
						return $result;
					},
				);			
				$table->sortBy = 'time';
				$table->rowButtons = function( $row ) use ( $self, $controllerUrl )
				{	
					$buttons = array();
					
					$buttons[ 'view' ] = array(
						'icon'		=> 'search',
						'title'		=> 'View Details',
						'id'		=> "{$row['id']}-view",
						'link'		=> $controllerUrl->setQueryString( array( 'logid' => $row[ 'id' ] ) ),
						'data'		=> array( 'ipsDialog' => '', 'ipsDialogTitle' => 'Log Details' ),
					);
					
					return $buttons;
				};
		
				$form->addHtml( (string) $table );
			}
			
			
		}
	}
	
	/**
	 * [Node] Save Add/Edit Form
	 *
	 * @param	array	$values	Values from the form
	 * @return	void
	 */
	public function saveForm( $values )
	{
		list( $definition_key, $event_key ) = explode( '_', $values[ 'rule_event_selection' ], 2 );
		
		if ( $definition = \IPS\rules\Application::rulesDefinitions( $definition_key ) )
		{
			$values[ 'rule_event_app' ]	= $definition[ 'app' ];
			$values[ 'rule_event_class' ]	= $definition[ 'class' ];
			$values[ 'rule_event_key' ] 	= $event_key;
		}
		
		unset( $values[ 'rule_event_selection' ] );
	
		parent::saveForm( $values );
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
		$_buttons = array
		(
			'conditions' => array
			(
				'icon'	=> 'pencil',
				'title'	=> 'edit_conditions',
				'link'	=> $url->setQueryString( array( 'do' => 'form', 'id' => $this->_id, 'tab' => 'conditions' ) ),
				'data'	=> ( static::$modalForms ? array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'edit_conditions' ) ) : array() ),
			),			
			'actions' => array
			(
				'icon'	=> 'pencil',
				'title'	=> 'edit_actions',
				'link'	=> $url->setQueryString( array( 'do' => 'form', 'id' => $this->_id, 'tab' => 'actions' ) ),
				'data'	=> ( static::$modalForms ? array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'edit_actions' ) ) : array() ),
			),			
		);
		
		array_splice( $buttons, 2, 0, $_buttons );
		return $buttons;
	}
	
	/**
	 * Invoke Rule
	 */
	public function invoke()
	{
		if ( $this->enabled )
		{
			$compareMode 		= $this->compareMode();
			$conditions		= $this->conditions();
			$conditionsCount	= 0;
			
			/**
			 * For 'or' operations, starting condition is FALSE
			 * For 'and' operations, starting condition is TRUE
			 */
			$conditionsValid = $compareMode != 'or';
			
			foreach ( $conditions as $condition )
			{
				if ( $condition->enabled )
				{
					$conditionsCount++;
					$result = call_user_func_array( array( $condition, 'invoke' ), func_get_args() );
					
					if ( $result and $compareMode == 'or' ) 
					{
						$conditionsValid = TRUE;
						break;
					}

					if ( ! $result and $compareMode == 'and' )
					{
						$conditionsValid = FALSE;
						break;
					}
				}
				else
				{
					if ( $this->debug )
					{
						\IPS\rules\Application::rulesLog( $this->event(), $this, $condition, '--', 'Condition not evaluated (disabled)' );
					}
				}
			}
			
			if ( $conditionsValid or $conditionsCount === 0 )
			{
				foreach ( $this->actions() as $action )
				{
					if ( $action->enabled )
					{
						call_user_func_array( array( $action, 'invoke' ), func_get_args() );
					}
					else
					{
						if ( $this->debug )
						{
							\IPS\rules\Application::rulesLog( $this->event(), $this, $action, '--', 'Action not taken (disabled)' );
						}
					}
				}
				
				foreach ( $this->children() as $_rule )
				{
					if ( $_rule->enabled )
					{
						call_user_func_array( array( $_rule, 'invoke' ), func_get_args() );
					}
					else
					{
						if ( $this->debug )
						{
							\IPS\rules\Application::rulesLog( $this->event(), $_rule, NULL, '--', 'Rule not evaluated (disabled)' );
						}
					}
				}
				
				return 'conditions met';
			}
			
			return 'conditions not met';
		}
		else
		{
			if ( $this->debug )
			{
				\IPS\rules\Application::rulesLog( $this->event(), $this, NULL, '--', 'Rule not evaluated (disabled)' );
			}
		}
	}
	 
	/**
	 * [Node] Fetch Child Nodes
	 *
	 * @param	string|NULL			$permissionCheck	The permission key to check for or NULL to not check permissions
	 * @param	\IPS\Member|NULL	$member				The member to check permissions for or NULL for the currently logged in member
	 * @param	bool				$subnodes			Include subnodes? NULL to *only* check subnodes
	 * @param	array|NULL			$skip				Children IDs to skip
	 * @param	mixed				$_where				Additional WHERE clause
	 * @return	array
	 */
	public function children( $permissionCheck='view', $member=NULL, $subnodes=TRUE, $skip=null, $_where=array() )
	{
		$children = parent::children( $permissionCheck, $member, $subnodes, $skip, $_where );
		
		/**
		 * Add aspect containers as children here since they aren't actually
		 * database records that can be loaded
		 */
		//$children[] = new \IPS\rules\Rule\Aspect( 'conditions',	$this );
		//$children[] = new \IPS\rules\Rule\Aspect( 'actions', 	$this );
		
		return $children;		
	}
	
	/**
	 * Get the event for this rule
	 */
	public function event()
	{
		return \IPS\rules\Event::load( $this->event_app, $this->event_class, $this->event_key );
	}
	
	/**
	 * @brief	Cache for conditions
	 */
	protected $conditionCache = NULL;
	
	/**
	 * Retrieve enabled conditions assigned to this rule
	 */
	public function conditions()
	{
		if ( isset( $this->conditionCache ) )
		{
			return $this->conditionCache;
		}
		
		return $this->conditionCache = \IPS\rules\Condition::roots( NULL, NULL, array( array( 'condition_rule_id=?', $this->id ) ) );
	}
	
	/**
	 * @brief	Cache for actions
	 */
	protected $actionCache = NULL;
	
	/**
	 * Retrieve actions assigned to this rule
	 */
	public function actions()
	{
		if ( isset( $this->actionCache ) )
		{
			return $this->actionCache;
		}
		
		return $this->actionCache = \IPS\rules\Action::roots( NULL, NULL, array( array( 'action_rule_id=?', $this->id ) ) );
	}
	
	/**
	 * Get Compare Mode
	 */
	public function compareMode()
	{
		return $this->base_compare ?: 'and';
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
		
		foreach ( $this->actions() as $action )
		{
			$action->delete();
		}
		
		foreach ( $this->conditions() as $condition )
		{
			$condition->delete();
		}
		
		return parent::delete();
	}

}