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

/* Make sure application class is loaded */
class_exists( 'IPS\rules\Application' );

/**
 * Node
 */
class _Rule extends \IPS\rules\Secure\Rule
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
	 * @brief	Parent Node Class
	 */
	public static $parentNodeClass = '\IPS\rules\Rule\Ruleset';
	
	/**
	 * @brief	Parent Node ID
	 */
	public static $parentNodeColumnId = 'ruleset_id';
	
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
	 * Get Description
	 */
	public function get__description()
	{
		$description = "";
		
		if ( $this->parent_id == 0 )
		{
			$description .= "<i class='fa fa-flash'></i> Event: {$this->event()->title()}<br>";
		}
		
		$conditions_count = \IPS\Db::i()->select( 'COUNT(*)', 'rules_conditions', array( 'condition_rule_id=? AND condition_enabled=1', $this->id ) )->first();
		$actions_count = \IPS\Db::i()->select( 'COUNT(*)', 'rules_actions', array( 'action_rule_id=? AND action_enabled=1 AND action_else=0', $this->id ) )->first();
		$else_actions_count = \IPS\Db::i()->select( 'COUNT(*)', 'rules_actions', array( 'action_rule_id=? AND action_enabled=1 AND action_else=1', $this->id ) )->first();
		
		$description .= "<i class='fa fa-info'></i> Summary: {$conditions_count} Conditions / {$actions_count} Actions" . ( $else_actions_count ? " / {$else_actions_count} Else Actions" : "" );
		return $description;
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
		if ( $this->event()->placeholder )
		{
			return array(
				0	=> 'ipsBadge ipsBadge_negative',
				1	=> 'rule_event_missing_badge',
			);
		}
	
		if ( $this->debug )
		{
			return array(
				0	=> 'ipsBadge ipsBadge_intermediary',
				1	=> 'debug_on_badge',
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
		$events 	= array();
		$event_missing 	= FALSE;
				
		$form->addTab( 'rules_settings' );
		
		/**
		 * New Child Rules Inherit Event From Parent
		 */
		if 
		( 
			! $this->id and 
			(
				\IPS\Request::i()->parent and
				! \IPS\Request::i()->subnode
			)
		)
		{
			$parent = \IPS\rules\Rule::load( \IPS\Request::i()->parent );
			$this->event_app 	= $parent->event_app;
			$this->event_class 	= $parent->event_class;
			$this->event_key	= $parent->event_key;
			$form->actionButtons 	= array( \IPS\Theme::i()->getTemplate( 'forms', 'core', 'global' )->button( 'rules_next', 'submit', null, 'ipsButton ipsButton_primary', array( 'accesskey' => 's' ) ) );
		}
		
		/**
		 * Root rules can be moved between rule sets
		 */
		else if	( ! $this->parent() )
		{
			if ( \IPS\rules\Rule\Ruleset::roots( NULL ) )
			{
				$ruleset_id = $this->ruleset_id ?: 0;
				if 
				( 
					! $this->id and
					\IPS\Request::i()->subnode == 1 and
					\IPS\Request::i()->parent
				)
				{
					$ruleset_id = \IPS\Request::i()->parent;
				}
				
				$form->add( new \IPS\Helpers\Form\Node( 'rule_ruleset_id', $ruleset_id, TRUE, array( 'class' => '\IPS\rules\Rule\Ruleset', 'zeroVal' => 'rule_no_ruleset', 'subnodes' => FALSE ) ) );
			}
		}
		
		if ( $this->event_key and $this->event()->placeholder )
		{
			$form->addHtml( \IPS\Theme::i()->getTemplate( 'components' )->missingEvent( $this ) );
			$event_missing = TRUE;
		}

		/**
		 * If the event hasn't been configured for this rule, build an option list
		 * for all available events for the user to select.
		 */
		if ( ! $this->event_key )
		{
			$form->actionButtons 	= array( \IPS\Theme::i()->getTemplate( 'forms', 'core', 'global' )->button( 'rules_next', 'submit', null, 'ipsButton ipsButton_primary', array( 'accesskey' => 's' ) ) );
			foreach ( \IPS\rules\Application::rulesDefinitions() as $definition_key => $definition )
			{
				foreach ( $definition[ 'events' ] as $event_key => $event_data )
				{
					$group = ( isset( $event_data[ 'group' ] ) and $event_data[ 'group' ] ) ? $event_data[ 'group' ] : $definition[ 'group' ];
					$events[ $group ][ $definition_key . '_' . $event_key ] = $definition[ 'app' ] . '_' . $definition[ 'class' ] . '_event_' . $event_key;
				}
			}
			$form->add( new \IPS\Helpers\Form\Select( 'rule_event_selection', $this->id ? md5( $this->event_app . $this->event_class ) . '_' . $this->event_key : NULL, TRUE, array( 'options' => $events, 'noDefault' => TRUE ), NULL, "<div class='chosen-collapse' data-controller='rules.admin.ui.chosen'>", "</div>", 'rule_event_selection' ) );
		}
		
		/* Rule Title */
		$form->add( new \IPS\Helpers\Form\Text( 'rule_title', $this->title, TRUE, array( 'placeholder' => \IPS\Member::loggedIn()->language()->addToStack( 'rule_title_placeholder' ) ) ) );
		
		/**
		 * Conditions & Actions
		 *
		 * Only allow configuration if the rule has been saved (it needs an ID),
		 * and if the event it is assigned to has a valid definition.
		 */
		if ( $this->id and ! $event_missing )
		{			
			$form->add( new \IPS\Helpers\Form\YesNo( 'rule_debug', $this->debug, FALSE ) );
			
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
			
			/**
			 * Rule Conditions
			 */
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
			
			/* Replace form constructs with div's */
			$conditionsTreeHtml = (string) $conditions;
			$conditionsTreeHtml = str_replace( '<form ', '<div ', $conditionsTreeHtml );
			$conditionsTreeHtml = str_replace( '</form>', '</div>', $conditionsTreeHtml );
			$form->addHtml( $conditionsTreeHtml );
			
			/**
			 * Rule Actions
			 */
			$form->addTab( 'rules_actions' );
			$form->addHeader( 'rule_actions' );
			
			$actionClass		= '\IPS\rules\Action';
			$actionController 	= new \IPS\rules\modules\admin\rules\actions( NULL, $this, \IPS\rules\ACTION_STANDARD );
			$actions 		= new \IPS\Helpers\Tree\Tree( 
							\IPS\Http\Url::internal( "app=rules&module=rules&controller=actions&rule={$this->id}" ),
							$actionClass::$nodeTitle, 
							array( $actionController, '_getRoots' ), 
							array( $actionController, '_getRow' ), 
							array( $actionController, '_getRowParentId' ), 
							array( $actionController, '_getChildren' ), 
							array( $actionController, '_getRootButtons' )
						);
			
			/* Replace form constructs with div's */
			$actionsTreeHtml = (string) $actions;
			$actionsTreeHtml = str_replace( '<form ', '<div ', $actionsTreeHtml );
			$actionsTreeHtml = str_replace( '</form>', '</div>', $actionsTreeHtml );
			$form->addHtml( $actionsTreeHtml );
			
			/* Else Actions */
			$form->addHeader( 'rules_actions_else' );
			$form->addHtml( '<p class="ipsPad">' . \IPS\Member::loggedIn()->language()->addToStack( 'rules_actions_else_description' ) . '</p>' );
			
			$elseActionController 	= new \IPS\rules\modules\admin\rules\actions( NULL, $this, \IPS\rules\ACTION_ELSE );
			$elseActions 		= new \IPS\Helpers\Tree\Tree( 
							\IPS\Http\Url::internal( "app=rules&module=rules&controller=actions&rule={$this->id}" ),
							$actionClass::$nodeTitle, 
							array( $elseActionController, '_getRoots' ), 
							array( $elseActionController, '_getRow' ), 
							array( $elseActionController, '_getRowParentId' ), 
							array( $elseActionController, '_getChildren' ), 
							array( $elseActionController, '_getRootButtons' )
						);
			
			/* Replace form constructs with div's */
			$elseActionsTreeHtml = (string) $elseActions;
			$elseActionsTreeHtml = str_replace( '<form ', '<div ', $elseActionsTreeHtml );
			$elseActionsTreeHtml = str_replace( '</form>', '</div>', $elseActionsTreeHtml );
			$form->addHtml( $elseActionsTreeHtml );			
			
			/**
			 * Show debugging console for this rule if debugging is enabled
			 */
			if ( $this->debug )
			{
				$form->addTab( 'rules_debug_console' );
				
				$self 		= $this;
				$controllerUrl 	= \IPS\Http\Url::internal( "app=rules&module=rules&controller=rulesets&do=viewlog" );
				$table 		= new \IPS\Helpers\Table\Db( 'rules_logs', \IPS\Http\Url::internal( "app=rules&module=rules&controller=rules&do=form&id=". $this->id ), array( 'rule_id=? AND op_id=0', $this->id ) );
				$table->include = array( 'time', 'message', 'result' );
				$table->parsers = array(
					'time'	=> function( $val )
					{
						return (string) \IPS\DateTime::ts( $val );
					},
					'result' => function( $val )
					{
						return $val;
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
						'data'		=> array( 'ipsDialog' => '' ),
					);
					
					return $buttons;
				};
		
				$form->addHtml( (string) $table );
			}			
			
		}
		
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
		if ( isset( $values[ 'rule_event_selection' ] ) )
		{
			list( $definition_key, $event_key ) = explode( '_', $values[ 'rule_event_selection' ], 2 );
			
			if ( $definition = \IPS\rules\Application::rulesDefinitions( $definition_key ) )
			{
				$values[ 'rule_event_app' ]	= $definition[ 'app' ];
				$values[ 'rule_event_class' ]	= $definition[ 'class' ];
				$values[ 'rule_event_key' ] 	= $event_key;
			}
			
			unset( $values[ 'rule_event_selection' ] );
		}
		
		if ( isset ( $values[ 'rule_ruleset_id' ] ) and is_object( $values[ 'rule_ruleset_id' ] ) )
		{
			$values[ 'rule_ruleset_id' ] = $values[ 'rule_ruleset_id' ]->id;
		}
		
		parent::saveForm( $values );
		
		/**
		 * Save Footprint
		 */
		$this->init();
		if ( $this->event()->data !== NULL )
		{
			$this->event_footprint = md5( json_encode( $this->event()->data[ 'arguments' ] ) );
			$this->save();
		}
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
		
		if ( $subnode )
		{
			$url = $url->setQueryString( array( 'subnode' => 1 ) );
		}
						
		if ( isset ( $buttons[ 'add' ] ) )
		{
			$buttons[ 'add' ][ 'icon' ] = 'plus-square-o';
		}
		
		$_buttons = array
		(
			'conditions' => array
			(
				'icon'	=> 'pencil',
				'title'	=> 'edit_conditions',
				'link'	=> $url->setQueryString( array( 'do' => 'form', 'id' => $this->id, 'tab' => 'conditions' ) ),
				'data'	=> ( static::$modalForms ? array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'edit_conditions' ) ) : array() ),
			),			
			'actions' => array
			(
				'icon'	=> 'pencil',
				'title'	=> 'edit_actions',
				'link'	=> $url->setQueryString( array( 'do' => 'form', 'id' => $this->id, 'tab' => 'actions' ) ),
				'data'	=> ( static::$modalForms ? array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'edit_actions' ) ) : array() ),
			),
		);
		
		array_splice( $buttons, 2, 0, $_buttons );
		
		$buttons[ 'export' ] = array
		(
			'icon' => 'download',
			'title' => $this->hasChildren() ? 'rules_export_rule_group' : 'rules_export_rule',
			'link' => $url->setQueryString( array( 'controller' => 'rulesets', 'do' => 'export', 'rule' => $this->id ) ),		
		);
		
		$buttons[ 'overview' ] = array
		(
			'icon' => 'list',
			'title' => 'rules_view_overview',
			'link' => $url->setQueryString( array( 'controller' => 'rulesets', 'do' => 'viewOverview', 'rule' => $this->id ) ),
			'data' => array( 'ipsDialog' => '', 'ipsDialog-title' => 'Rule Overview' ),
		);
		
		if ( $this->debug )
		{
			$buttons[ 'debug_disable' ] = array
			(
				'icon'		=> 'bug',
				'title'		=> 'Disable Debugging',
				'id'		=> "{$this->id}-debug-disable",
				'link'		=> $url->setQueryString( array( 'controller' => 'rulesets', 'do' => 'debugDisable', 'id' => $this->id ) ),
			);
		
			$buttons[ 'debug' ] = array
			(
				'icon'		=> 'bug',
				'title'		=> 'View Debug Console',
				'id'		=> "{$this->id}-debug",
				'link'		=> $url->setQueryString( array( 'controller' => 'rules', 'do' => 'form', 'id' => $this->id, 'tab' => 'debug_console', 'subnode' => NULL ) ),
			);
		}
		else
		{
			$buttons[ 'debug_enable' ] = array
			(
				'icon'		=> 'bug',
				'title'		=> 'Enable Debugging',
				'id'		=> "{$this->id}-debug-enable",
				'link'		=> $url->setQueryString( array( 'controller' => 'rulesets', 'do' => 'debugEnable', 'id' => $this->id ) ),
			);		
		}
		
		return $buttons;
	}
	
	/**
	 * [Node] Get Parent
	 *
	 * @return	static|null
	 */
	public function parent()
	{
		if( static::$databaseColumnParent !== NULL )
		{
			$parentColumn = static::$databaseColumnParent;
			if( $this->$parentColumn !== static::$databaseColumnParentRootValue )
			{
				try
				{
					return static::load( $this->$parentColumn );
				}
				catch ( \OutOfRangeException $e ) {}
			}
		}
		
		return NULL;
	}

	/**
	 * Recursion Protection
	 */
	public $locked = FALSE;
	
	/**
	 * Invoke Rule
	 */
	public function invoke()
	{		
		if ( $this->enabled )
		{
			if ( ! $this->locked )
			{
				try
				{
					$this->locked = TRUE;
				
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
						foreach ( $this->actions( \IPS\rules\ACTION_STANDARD ) as $action )
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
								$result = call_user_func_array( array( $_rule, 'invoke' ), func_get_args() );
								
								if ( $this->debug )
								{
									\IPS\rules\Application::rulesLog( $this->event(), $_rule, NULL, $result, 'Rule evaluated' );
								}						
							}
							else
							{
								if ( $this->debug )
								{
									\IPS\rules\Application::rulesLog( $this->event(), $_rule, NULL, '--', 'Rule not evaluated (disabled)' );
								}
							}
						}
						
						$this->locked = FALSE;
						
						return 'conditions met';
					}
					else
					{
						/* Else Actions */
						foreach ( $this->actions( \IPS\rules\ACTION_ELSE ) as $action )
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
					
						$this->locked = FALSE;
					
						return 'conditions not met';
					}
				}
				catch( \Exception $e )
				{
					$this->locked = FALSE;
					throw $e;
				}
			}
			else
			{
				if ( $this->debug )
				{
					\IPS\rules\Application::rulesLog( $this->event(), $this, NULL, '--', 'Rule recursion (not evaluated)' );
				}
			}
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
	 * Get the event for this rule
	 */
	public function event()
	{
		return \IPS\rules\Event::load( $this->event_app, $this->event_class, $this->event_key, TRUE );
	}
	
	/**
	 * Ruleset Cache
	 */
	public $ruleset = NULL;
	
	/**
	 * Get the event for this rule
	 */
	public function ruleset()
	{
		if ( isset( $this->ruleset ) )
		{
			return $this->ruleset;
		}
		
		if ( $this->ruleset_id )
		{
			try
			{
				return $this->ruleset = \IPS\rules\Rule\Ruleset::load( $this->ruleset_id );
			}
			catch( \OutOfRangeException $e ) {}
		}
		
		return $this->ruleset = FALSE;
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
	protected $actionCache = array();
	
	/**
	 * Retrieve actions assigned to this rule
	 *
	 * @param	int|NULL	$mode		Mode of actions to return
	 */
	public function actions( $mode=NULL )
	{
		return parent::actions( $mode );
	}
	
	/**
	 * Get Compare Mode
	 */
	public function compareMode()
	{
		return $this->base_compare ?: 'and';
	}

	/**
	 * Copy Rule
	 */
	public function __clone()
	{
		if ( $this->skipCloneDuplication === TRUE )
		{
			return;
		}
	
		$oldId = $this->id;
		parent::__clone();
		
		$rule = \IPS\rules\Rule::load( $oldId );
		foreach ( $rule->conditions() as $condition )
		{
			$newCondition = clone $condition;
			$newCondition->rule_id = $this->id;
			$newCondition->save();
		}
		
		foreach ( $rule->actions() as $action )
		{
			$newAction = clone $action;
			$newAction->rule_id = $this->id;
			$newAction->save();
		}
	}
	
	/**
	 * [ActiveRecord] Save Changed Columns
	 *
	 * @return	void
	 */
	public function save()
	{
		/* Synchronize ruleset_id with parent rule */
		if ( $parent = $this->parent() )
		{
			if ( $this->ruleset_id != $parent->ruleset_id )
			{
				$this->ruleset_id = $parent->ruleset_id;
			}
		}
		
		parent::save();
		
		/**
		 * Synchronize Any Linked Rules
		 */
		foreach ( $this->children() as $child )
		{
			$child->save();
		}
	}
	
	/**
	 * Form to delete or move content
	 *
	 * @param	bool	$showMoveToChildren	If TRUE, will show "move to children" even if there are no children
	 * @return	\IPS\Helpers\Form
	 */
	public function deleteOrMoveForm( $showMoveToChildren=FALSE )
	{
		$form = new \IPS\Helpers\Form( 'delete_custom_action', 'rules_confirm_delete' );
		$form->hiddenValues[ 'node_move_children' ] = 0;
		return $form;
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