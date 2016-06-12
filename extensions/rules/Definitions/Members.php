<?php
/**
 * @brief		Rules extension: Members
 * @package		Rules for IPS Social Suite
 * @since		20 Feb 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\rules\extensions\rules\Definitions;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Content Router extension: Definitions
 */
class _Members
{

	/**
	 * Definition Group
	 */
	public $defaultGroup = 'members';

	/**
	 * @brief	Triggerable Events
	 */
	public function events()
	{
		$memberArg = array
		(
			'argtype' 	=> 'object',
			'class'		=> '\IPS\Member',
		);
		
		$events = array
		(
			'memberSync_onLogin' => array
			( 
				'arguments' => array
				( 
					'member' => $memberArg,
				),
			),		
			'memberSync_onLogout' => array
			( 
				'arguments' => array
				( 
					'member' => $memberArg,
				),
			),		
			'memberSync_onCreateAccount' => array
			( 
				'arguments' => array
				( 
					'member' => $memberArg,
				),
			),		
			'memberSync_onProfileUpdate' => array
			( 
				'arguments' => array
				( 
					'member' => $memberArg,
					'changed' => array( 'argtype' => 'array' ),
				),
			),
			'memberSync_onSetAsSpammer' => array
			( 
				'arguments' => array
				( 
					'member' => $memberArg,					
				),
			),
			'member_not_spammer' => array
			( 
				'arguments' => array
				( 
					'member' => $memberArg,
				),
			),
			'memberSync_onValidate' => array
			( 
				'arguments' => array
				( 
					'member' => $memberArg,
				),
			),
			'memberSync_onMerge' => array
			( 
				'arguments' => array
				( 
					'member' => $memberArg,
					'mergedMember' => $memberArg,
				),
			),
			'memberSync_onDelete' => array
			( 
				'arguments' => array
				( 
					'member' => $memberArg,
				),
			),
			'member_banned' => array
			( 
				'arguments' => array
				( 
					'member' => $memberArg,
				),
			),
			'member_unbanned' => array
			( 
				'arguments' => array
				( 
					'member' => $memberArg,
				),
			),
			'reputation_given' => array
			(
				'arguments' => array
				(
					'member' 	=> $memberArg,
					'giver' 	=> $memberArg,
					'content' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Content' ),
					'reptype' 	=> array( 'argtype' => 'int' ),
				),
			),
			'member_warned' => array
			(
				'arguments' => array
				(
					'warning'	=> array( 'argtype' => 'object', 'class' => '\IPS\core\Warnings\Warning' ),
					'member' 	=> $memberArg,
					'moderator' 	=> $memberArg,
				),
			),
			'content_recounted' => array
			(
				'arguments' => array
				(
					'member'	=> $memberArg,
					'count'		=> array( 'argtype' => 'int' ),
				),
			),
		);
		
		return $events;
	}
	
	/**
	 * Operational Conditions
	 */
	public function conditions()
	{
		return array
		(
			'check_member' => array
			(
				'callback' 	=> array( $this, 'checkMember' ),
				'configuration' => array
				(
					'form' => function( $form, $values )
					{
						$members = array();
						$rules_choose_members = isset( $values[ 'rules_choose_members' ] ) ? (array) $values[ 'rules_choose_members' ] : array();
						foreach( $rules_choose_members as $member_id )
						{
							if ( $member_id )
							{
								try
								{
									$members[] = \IPS\Member::load( $member_id );
								}
								catch ( \Exception $e ) {}
							}
						}
						
						$form->add( new \IPS\Helpers\Form\Member( 'rules_choose_members', $members, TRUE, array( 'multiple' => NULL ), NULL, NULL, NULL, 'rules_choose_members' ) );
					},
					'saveValues' => function( &$values )
					{	
						$members = array();
						$rules_choose_members = isset( $values[ 'rules_choose_members' ] ) ? (array) $values[ 'rules_choose_members' ] : array();
						foreach ( $rules_choose_members as $member )
						{
							$members[] = $member->member_id;
						}
						$values[ 'rules_choose_members' ] = $members;
					},
				),
				'arguments'	=> array
				(
					'member' => array
					(
						'argtypes' => \IPS\rules\Application::argPreset( 'member' ),
						'required' => TRUE,
					),
				),
			),
			'member_has_group' => array
			(
				'configuration' => array
				(
					'form' => function( &$form, $values, $operation )
					{
						$options = array();
						foreach( \IPS\Member\Group::groups() as $group )
						{
							$options[ $group->g_id ] = $group->name;
						}
						
						$form->add( new \IPS\Helpers\Form\CheckboxSet( 'rules_Members_member_groups', isset( $values[ 'rules_Members_member_groups' ] ) ? $values[ 'rules_Members_member_groups' ] : NULL, TRUE, array( 'options' => $options ), NULL, NULL, NULL, 'rules_Members_member_groups' ) );
					},
				),
				'callback' 	=> array( $this, 'checkMemberGroup' ),
				'arguments' => array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ), 
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required' 	=> TRUE,
					),
				),
			),
			'member_status' => array
			(
				'configuration' => array
				(
					'form' => function( &$form, $values, $operation )
					{
						$status_options = array
						(
							'online'	=> 'rules_member_online',
							'validating'	=> 'rules_member_validating',
							'spammer'	=> 'rules_member_spammer',
							'banned_perm' 	=> 'rules_member_banned_perm',
							'banned_temp'	=> 'rules_member_banned_temp',
							'warnlevel'	=> 'rules_member_warnlevel',
						);						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Members_member_status', isset( $values[ 'rules_Members_member_status' ] ) ? $values[ 'rules_Members_member_status' ] : NULL, TRUE, array( 'options' => $status_options ), NULL, NULL, NULL, 'rules_Members_member_status' ) );
					},
				),
				'callback' 	=> array( $this, 'checkMemberStatus' ),
				'arguments' => array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ), 
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required' 	=> TRUE,
					),
				),
			),
			'member_attributes' => array
			(
				'configuration' => array
				(
					'form' => function( &$form, $values, $operation )
					{
						$attribute_options = array
						(
							'photo' 		=> 'rules_member_attribute_photo',
							'signature' 		=> 'rules_member_attribute_signature',
							'followers'		=> 'rules_member_attribute_followers',
							'reputation'		=> 'rules_member_attribute_reputation',
							'posts'			=> 'rules_member_attribute_posts',
							'pviews'		=> 'rules_member_attribute_profile_views',
							'joined'		=> 'rules_member_attribute_joined',
							'birthdate'		=> 'rules_member_attribute_birthdate',
							'last_activity'		=> 'rules_member_attribute_last_activity',
							'last_post'		=> 'rules_member_attribute_last_post',
						);

						$attribute_toggles = array
						(
							'followers'		=> array( 'rules_Members_attribute_compare_type_value', 'rules_Members_attribute_compare_value' ),
							'reputation'		=> array( 'rules_Members_attribute_compare_type_value', 'rules_Members_attribute_compare_value' ),
							'posts'			=> array( 'rules_Members_attribute_compare_type_value', 'rules_Members_attribute_compare_value' ),
							'pviews'		=> array( 'rules_Members_attribute_compare_type_value', 'rules_Members_attribute_compare_value' ),
							'joined'		=> array( 'rules_Members_attribute_compare_type_date' ),
							'joined'		=> array( 'rules_Members_attribute_compare_type_date' ),
							'birthdate'		=> array( 'rules_Members_attribute_compare_type_date' ),
							'last_activity'		=> array( 'rules_Members_attribute_compare_type_date' ),
							'last_post'		=> array( 'rules_Members_attribute_compare_type_date' ),
						);
						
						$value_compare_options = array
						(
							'<' => 'Less than',
							'>' => 'More than',
							'=' => 'Equal to',
						);
						
						$date_compare_options = array
						(
							'<' => 'Before',
							'>' => 'After',
							'=' => 'On',
							'?' => 'Within the last',
						);
						
						$date_toggles = array
						(
							'<' => array( 'rules_Members_attribute_compare_date' ),
							'>' => array( 'rules_Members_attribute_compare_date' ),
							'=' => array( 'rules_Members_attribute_compare_date' ),
							'?' => array( 'rules_Members_attribute_compare_minutes', 'rules_Members_attribute_compare_hours', 'rules_Members_attribute_compare_days', 'rules_Members_attribute_compare_months', 'rules_Members_attribute_compare_years' ),
						);
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Members_member_attribute', isset( $values[ 'rules_Members_member_attribute' ] ) ? $values[ 'rules_Members_member_attribute' ] : NULL, TRUE, array( 'options' => $attribute_options, 'toggles' => $attribute_toggles ), NULL, NULL, NULL, 'rules_Members_member_attribute' ) );					
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Members_attribute_compare_type_value', isset( $values[ 'rules_Members_attribute_compare_type_value' ] ) ? $values[ 'rules_Members_attribute_compare_type_value' ] : NULL, FALSE, array( 'options' => $value_compare_options ), NULL, NULL, NULL, 'rules_Members_attribute_compare_type_value' ) );
						$form->add( new \IPS\Helpers\Form\Number( 'rules_Members_attribute_compare_value', isset( $values[ 'rules_Members_attribute_compare_value' ] ) ? $values[ 'rules_Members_attribute_compare_value' ] : 0, FALSE, array(), NULL, NULL, NULL, 'rules_Members_attribute_compare_value' ) );
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Members_attribute_compare_type_date', isset( $values[ 'rules_Members_attribute_compare_type_date' ] ) ? $values[ 'rules_Members_attribute_compare_type_date' ] : NULL, FALSE, array( 'options' => $date_compare_options, 'toggles' => $date_toggles ), NULL, NULL, NULL, 'rules_Members_attribute_compare_type_date' ) );
						$form->add( new \IPS\Helpers\Form\Date( 'rules_Members_attribute_compare_date', isset( $values[ 'rules_Members_attribute_compare_date' ] ) ? \IPS\DateTime::ts( $values[ 'rules_Members_attribute_compare_date' ] ) : NULL, FALSE, array(), NULL, NULL, NULL, 'rules_Members_attribute_compare_date' ) );
						$form->add( new \IPS\Helpers\Form\Number( 'rules_Members_attribute_compare_minutes', isset( $values[ 'rules_Members_attribute_compare_minutes' ] ) ? $values[ 'rules_Members_attribute_compare_minutes' ] : 0, TRUE, array(), NULL, NULL, NULL, 'rules_Members_attribute_compare_minutes' ) );
						$form->add( new \IPS\Helpers\Form\Number( 'rules_Members_attribute_compare_hours', isset( $values[ 'rules_Members_attribute_compare_hours' ] ) ? $values[ 'rules_Members_attribute_compare_hours' ] : 0, TRUE, array(), NULL, NULL, NULL, 'rules_Members_attribute_compare_hours' ) );
						$form->add( new \IPS\Helpers\Form\Number( 'rules_Members_attribute_compare_days', isset( $values[ 'rules_Members_attribute_compare_days' ] ) ? $values[ 'rules_Members_attribute_compare_days' ] : 0, TRUE, array(), NULL, NULL, NULL, 'rules_Members_attribute_compare_days' ) );
						$form->add( new \IPS\Helpers\Form\Number( 'rules_Members_attribute_compare_months', isset( $values[ 'rules_Members_attribute_compare_months' ] ) ? $values[ 'rules_Members_attribute_compare_months' ] : 0, TRUE, array(), NULL, NULL, NULL, 'rules_Members_attribute_compare_months' ) );
						$form->add( new \IPS\Helpers\Form\Number( 'rules_Members_attribute_compare_years', isset( $values[ 'rules_Members_attribute_compare_years' ] ) ? $values[ 'rules_Members_attribute_compare_years' ] : 0, TRUE, array(), NULL, NULL, NULL, 'rules_Members_attribute_compare_years' ) );
						
					},
					'saveValues' => function( &$values )
					{
						if ( isset( $values[ 'rules_Members_attribute_compare_date' ] ) and $values[ 'rules_Members_attribute_compare_date' ] instanceof \IPS\DateTime )
						{
							$values[ 'rules_Members_attribute_compare_date' ] = $values[ 'rules_Members_attribute_compare_date' ]->getTimestamp();
						}
					}
				),
				'callback' 	=> array( $this, 'checkMemberAttributes' ),
				'arguments' => array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ), 
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required' 	=> TRUE,
					),
				),
			),
			'member_following' => array
			(
				'callback' 	=> array( $this, 'memberFollowing' ),
				'arguments' => array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ), 
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required' 	=> TRUE,
					),
					'member2' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ), 
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member2' ),
						'required' 	=> TRUE,
					),
				),			
			),
			'member_ignoring' => array
			(
				'callback' 	=> array( $this, 'memberIgnoring' ),
				'configuration' => array
				(
					'form' => function( $form, $values )
					{
						$ignore_options = array
						(
							'topics' 	=> 'Content Posts',
							'messages'	=> 'Messages',
							'signatures'	=> 'Signatures',
						);
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Members_ignore_type', $values[ 'rules_Members_ignore_type' ] ?: 'topics', TRUE, array( 'options' => $ignore_options ) ) );
					},
				),
				'arguments' => array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ),
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required' 	=> TRUE,
					),
					'member2' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ), 
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member2' ),
						'required' 	=> TRUE,
					),
				),			
			),
		);
	}
	
	/**
	 * @brief	Triggerable Actions
	 */
	public function actions()
	{
		$actions = array
		(	
			'change_primary_group' => array
			(
				'callback'	=> array( $this, 'changePrimaryGroup' ),
				'configuration' => array
				(
					'form' => function( $form, $values, $action )
					{
						$form->add( new \IPS\Helpers\Form\Select( 'rules_Members_member_primary_group', isset( $values[ 'rules_Members_member_primary_group' ] ) ? $values[ 'rules_Members_member_primary_group' ] : NULL, TRUE, array( 'options' => \IPS\Member\Group::groups( \IPS\Member::loggedIn()->hasAcpRestriction( 'core', 'members', 'member_add_admin' ), FALSE ), 'parse' => 'normal' ) ) );
					},
				),
				'arguments'	=> array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ), 
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required'	=> TRUE,
					),
				),			
			),
			'add_secondary_groups' => array
			(
				'callback'	=> array( $this, 'addSecondaryGroups' ),
				'configuration' => array
				(
					'form' => function( $form, $values, $action )
					{
						$form->add( new \IPS\Helpers\Form\CheckboxSet( 'rules_Members_member_secondary_groups_add', isset( $values[ 'rules_Members_member_secondary_groups_add' ] ) ? $values[ 'rules_Members_member_secondary_groups_add' ] : NULL, TRUE, array( 'options' => \IPS\Member\Group::groups( \IPS\Member::loggedIn()->hasAcpRestriction( 'core', 'members', 'member_add_admin' ), FALSE ), 'multiple' => TRUE, 'parse' => 'normal' ) ) );
					},
				),
				'arguments'	=> array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ), 
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required'	=> TRUE,
					),
				),			
			),
			'remove_secondary_groups' => array
			(
				'callback'	=> array( $this, 'removeSecondaryGroups' ),
				'configuration' => array
				(
					'form' => function( $form, $values, $action )
					{
						$form->add( new \IPS\Helpers\Form\CheckboxSet( 'rules_Members_member_secondary_groups_remove', isset( $values[ 'rules_Members_member_secondary_groups_remove' ] ) ? $values[ 'rules_Members_member_secondary_groups_remove' ] : NULL, TRUE, array( 'options' => \IPS\Member\Group::groups( \IPS\Member::loggedIn()->hasAcpRestriction( 'core', 'members', 'member_add_admin' ), FALSE ), 'multiple' => TRUE, 'parse' => 'normal' ) ) );
					},
				),
				'arguments'	=> array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ), 
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required'	=> TRUE,
					),
				),			
			),
			'set_secondary_groups' => array
			(
				'callback'	=> array( $this, 'setSecondaryGroups' ),
				'configuration' => array
				(
					'form' => function( $form, $values, $action )
					{
						$form->add( new \IPS\Helpers\Form\CheckboxSet( 'rules_Members_member_secondary_groups_set', isset( $values[ 'rules_Members_member_secondary_groups_set' ] ) ? $values[ 'rules_Members_member_secondary_groups_set' ] : NULL, TRUE, array( 'options' => \IPS\Member\Group::groups( \IPS\Member::loggedIn()->hasAcpRestriction( 'core', 'members', 'member_add_admin' ), FALSE ), 'multiple' => TRUE, 'parse' => 'normal' ) ) );
					},
				),
				'arguments'	=> array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ), 
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required'	=> TRUE,
					),
				),			
			),
			'change_member_title' => array
			(
				'callback'	=> array( $this, 'changeMemberTitle' ),
				'arguments'	=> array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ), 
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required'	=> TRUE,
					),
					'title' => array
					(
						'configuration' => array
						(
							'form' => function( $form, $values, $action )
							{
								$form->add( new \IPS\Helpers\Form\Text( 'rules_Members_member_title', isset( $values[ 'rules_Members_member_title' ] ) ? $values[ 'rules_Members_member_title' ] : NULL, TRUE, array(), NULL, NULL, NULL, 'rules_Members_member_title' ) );
								return array( 'rules_Members_member_title' );
							},
							'getArg' => function( $values, $action )
							{
								return $values[ 'rules_Members_member_title' ];
							}
						),
						'argtypes'	=> array( 'string' ),
						'required'	=> TRUE,
					),
				),			
			),
			'flag_spammer' => array
			(
				'callback'	=> array( $this, 'flagAsSpammer' ),
				'arguments'	=> array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ), 
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required'	=> TRUE,
					),
				),					
			),
			'unflag_spammer' => array
			(
				'callback'	=> array( $this, 'unflagAsSpammer' ),
				'arguments'	=> array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ), 
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required'	=> TRUE,
					),
				),								
			),
			'ban_member' => array
			(
				'callback'	=> array( $this, 'banMember' ),
				'configuration'	=> array
				(
					'form' => function( $form, $values, $aciton )
					{
						$ban_options = array
						(
							'permanent' => 'rules_ban_permanent',
							'temporary' => 'rules_ban_temporary',
						);
						
						$ban_toggles = array
						(
							'temporary' => array( 'rules_Members_ban_setting_minutes', 'rules_Members_ban_setting_hours', 'rules_Members_ban_setting_days', 'rules_Members_ban_setting_months' ),
						);
						
						$form->add( new \IPS\Helpers\Form\Radio( 'rules_Members_ban_setting', isset( $values[ 'rules_Members_ban_setting' ] ) ? $values[ 'rules_Members_ban_setting' ] : NULL, TRUE, array( 'options' => $ban_options, 'toggles' => $ban_toggles ) ) );
						$form->add( new \IPS\Helpers\Form\Number( 'rules_Members_ban_setting_minutes', isset( $values[ 'rules_Members_ban_setting_minutes' ] ) ? $values[ 'rules_Members_ban_setting_minutes' ] : 0, TRUE, array(), NULL, NULL, NULL, 'rules_Members_ban_setting_minutes' ) );
						$form->add( new \IPS\Helpers\Form\Number( 'rules_Members_ban_setting_hours', isset( $values[ 'rules_Members_ban_setting_hours' ] ) ? $values[ 'rules_Members_ban_setting_hours' ] : 0, TRUE, array(), NULL, NULL, NULL, 'rules_Members_ban_setting_hours' ) );
						$form->add( new \IPS\Helpers\Form\Number( 'rules_Members_ban_setting_days', isset( $values[ 'rules_Members_ban_setting_days' ] ) ? $values[ 'rules_Members_ban_setting_days' ] : 0, TRUE, array(), NULL, NULL, NULL, 'rules_Members_ban_setting_days' ) );
						$form->add( new \IPS\Helpers\Form\Number( 'rules_Members_ban_setting_months', isset( $values[ 'rules_Members_ban_setting_months' ] ) ? $values[ 'rules_Members_ban_setting_months' ] : 0, TRUE, array(), NULL, NULL, NULL, 'rules_Members_ban_setting_months' ) );
					},
				),
				'arguments'	=> array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ), 
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required'	=> TRUE,
					),
				),					
			),
			'unban_member' => array
			(
				'callback'	=> array( $this, 'unbanMember' ),
				'arguments'	=> array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ), 
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required'	=> TRUE,
					),
				),					
			),
			'prune_member' => array
			(
				'callback'	=> array( $this, 'pruneMember' ),
				'arguments'	=> array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::argPreset( 'member' ), 
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required'	=> TRUE,
					),
				),								
			),
		);
		
		return $actions;
	}
	
	/***			***
	 ***     CONDITIONS	***
	 ***			***/
	
	/**
	 * Check a member
	 * 
	 * @return	bool
	 */
	public function checkMember( $member, $values )
	{
		$members = (array) $values[ 'rules_choose_members' ];
		
		if ( ! $member instanceof \IPS\Member )
		{
			return FALSE;
		}
		
		return in_array( $member->member_id, $members );
	}
	
	
	/**
	 * Check Member Groups
	 */
	public function checkMemberGroup( $member, $values )
	{
		if ( ! $member instanceof \IPS\Member )
		{
			return FALSE;
		}
		
		return $member->inGroup( $values[ 'rules_Members_member_groups' ] );
	}

	/**
	 * Check Member Attributes
	 */
	public function checkMemberAttributes( $member, $values )
	{
		if ( ! $member instanceof \IPS\Member )
		{
			return FALSE;
		}

		switch ( $values[ 'rules_Members_member_attribute' ] )
		{
			case 'photo':
				return (bool) $member->pp_main_photo;
				
			case 'signature':
				return (bool) $member->signature;
			
			case 'followers':
			
				$amount	= count( $member->followers() );
				break;
			
			case 'reputation':
			
				$amount = $member->pp_reputation_points;
				break;
			
			case 'posts':
			
				$amount = $member->real_member_posts;
				break;
				
			case 'pviews':
			
				$amount = $member->members_profile_views;
				break;
			
			case 'joined':
			
				$date = $member->joined;
				break;
			
			case 'birthdate':
			
				if ( ! $member->bday_year )
				{
					return FALSE;
				}
				
				$date = new \IPS\DateTime( $member->bday_year . '/' . $member->bday_month . '/' . $member->bday_day );
				break;
				
			case 'last_activity':
			
				$date = \IPS\DateTime::ts( $member->last_activity );
				break;
			
			case 'last_post':
			
				$date = \IPS\DateTime::ts( $member->member_last_post );
				break;
		}
		
		switch ( $values[ 'rules_Members_member_attribute' ] )
		{
			case 'followers':
			case 'reputation':
			case 'posts':
			case 'pviews':
			
				$value = $values[ 'rules_Members_attribute_compare_value' ];
				switch( $values[ 'rules_Members_attribute_compare_type_value' ] )
				{
					case '<':
						return $amount < $value;
						
					case '>':
						return $amount > $value;
						
					case '=':
						return $amount == $value;
						
					default:
						return FALSE;
				}
				break;
			
			case 'joined':
			case 'birthdate':
			case 'last_activity':
			case 'last_post':
							
				$value = $values[ 'rules_Members_attribute_compare_date' ];
				switch ( $values[ 'rules_Members_attribute_compare_type_date' ] )
				{
					case '?':
						$value = strtotime( 
							'-' . intval( $values[ 'rules_Members_attribute_compare_minutes' ] ) . ' minutes ' .
							'-' . intval( $values[ 'rules_Members_attribute_compare_hours' ] ) . ' hours ' .
							'-' . intval( $values[ 'rules_Members_attribute_compare_days' ] ) . ' days ' .
							'-' . intval( $values[ 'rules_Members_attribute_compare_months' ] ) . ' months ' .
							'-' . intval( $values[ 'rules_Members_attribute_compare_years' ] ) . ' years '
						);
						return $date->getTimestamp() > $value;
						
					case '>':
						return $date->getTimestamp() > $value;
						
					case '<':
						return $date->getTimestamp() < $value;
						
					case '=':
						$value = \IPS\DateTime::ts( $value );
						return (
							$value->format( 'Y' ) == $date->format( 'Y' ) and
							$value->format( 'm' ) == $date->format( 'm' ) and
							$value->format( 'd' ) == $date->format( 'd' )
						);
						
				}			
		}
		
		return FALSE;
	}

	/**
	 * Check Member Status
	 */
	public function checkMemberStatus( $member, $values )
	{
		if ( $member instanceof \IPS\Member )
		{
			switch ( $values[ 'rules_Members_member_status' ] )
			{
				case 'validating':
					return $member->members_bitoptions[ 'validating' ];
				
				case 'spammer':
					return $member->members_bitoptions[ 'bw_is_spammer' ];
				
				case 'banned_perm':
					return $member->temp_ban == -1;
					
				case 'banned_temp':
					return $member->temp_ban > 0;
				
				case 'warnlevel':
					return $member->warn_level > 0;
					
				case 'online':
					return $member->isOnline();
				
				default:
					return FALSE;
			}
		}

		return FALSE;
	}
	
	/**
	 * Member Following Another Member
	 */
	public function memberFollowing( $member, $member2 )
	{
		if ( ! ( $member instanceof \IPS\Member ) or ! ( $member2 instanceof \IPS\Member ) )
		{
			return FALSE;
		}
			
		try
		{
			$where = array( array( 'follow_app=? AND follow_area=? AND follow_member_id=? AND follow_rel_id=?', 'core', 'member', $member->member_id, $member2->member_id ) );
			\IPS\Db::i()->select( 'core_follow.*', 'core_follow', $where )->first();
			return TRUE;
		}
		catch ( \UnderflowException $e )
		{
			return FALSE;
		}
	}
	
	/**
	 * Member Ignoring Another Member
	 */
	public function memberIgnoring( $member, $member2, $values )
	{
		if ( ! ( $member instanceof \IPS\Member ) or ! ( $member2 instanceof \IPS\Member ) )
		{
			return FALSE;
		}
			
		if ( ! $member2 instanceof \IPS\Member )
		{
			return FALSE;
		}
		
		return $member->isIgnoring( $member2, $values[ 'rules_Members_ignore_type' ] );
	}
	
	/***		***
	 ***  ACTIONS	***
	 ***		***/
	 
	/**
	 * Change Member Primary Group
	 */
	public function changePrimaryGroup( $member, $values )
	{
		if ( $member instanceof \IPS\Member )
		{
			if ( $member->member_group_id != $values[ 'rules_Members_member_primary_group' ] )
			{
				try
				{
					$group = \IPS\Member\Group::load( $values[ 'rules_Members_member_primary_group' ] );
					$member->member_group_id = $group->g_id;
					$member->save();
					return "member group changed";
				}
				catch ( \OutOfRangeException $e )
				{
					throw new \UnexpectedValueException( "invalid member group, group not changed" );
				}
			}
			else
			{
				return "member already has primary group";
			}
		}
		else
		{
			throw new \UnexpectedValueException( "invalid member" );
		}
	}
	
	/**
	 * Add Member Secondary Groups
	 */
	public function addSecondaryGroups( $member, $values )
	{
		if ( $member instanceof \IPS\Member )
		{
			$member_groups = explode( ',', $member->mgroup_others );
			foreach ( (array) $values[ 'rules_Members_member_secondary_groups_add' ] as $g_id )
			{
				try
				{
					$group = \IPS\Member\Group::load( $g_id );
					$member_groups[] = $group->g_id;
				}
				catch ( \OutOfRangeException $e ) {}
			}
			
			$member_groups = array_unique( $member_groups );
			$member->mgroup_others = implode( ',', $member_groups );
			$member->save();
			return "member groups added";
		}
		else
		{
			throw new \UnexpectedValueException( "invalid member" );
		}
	}

	/**
	 * Add Member Secondary Groups
	 */
	public function removeSecondaryGroups( $member, $values )
	{
		if ( $member instanceof \IPS\Member )
		{
			$member_groups = explode( ',', $member->mgroup_others );
			foreach ( (array) $values[ 'rules_Members_member_secondary_groups_remove' ] as $g_id )
			{
				$i = array_search( $g_id, $member_groups );
				if ( $i !== FALSE )
				{
					unset( $member_groups[ $i ] );
				}
			}
			
			$member->mgroup_others = implode( ',', $member_groups );
			$member->save();
			return "member groups removed";
		}
		else
		{
			throw new \UnexpectedValueException( "invalid member" );
		}		
	}

	/**
	 * Add Member Secondary Groups
	 */
	public function setSecondaryGroups( $member, $values )
	{
		if ( $member instanceof \IPS\Member )
		{
			$member_groups = array();
			foreach ( (array) $values[ 'rules_Members_member_secondary_groups_set' ] as $g_id )
			{
				try
				{
					$group = \IPS\Member\Group::load( $g_id );
					$member_groups[] = $group->g_id;
				}
				catch ( \OutOfRangeException $e ) {}
			}
			
			$member->mgroup_others = implode( ',', $member_groups );
			$member->save();
			return "member groups set";
		}
		else
		{
			throw new \UnexpectedValueException( "invalid member" );
		}
	}

	/**
	 * Change Member Title
	 */
	public function changeMemberTitle( $member, $title, $values )
	{
		if ( $member instanceof \IPS\Member )
		{
			$member->member_title = $title;
			$member->save();
			return "member title changed";
		}
		else
		{
			throw new \UnexpectedValueException( "invalid member" );
		}
	}
	
	/**
	 * Flag As Spammer Callback
	 */
	public function flagAsSpammer( $member )
	{
		if ( $member instanceof \IPS\Member )
		{
			$member->flagAsSpammer();
			return "member flagged as spammer";
		}
		else
		{
			throw new \UnexpectedValueException( "invalid member" );
		}
	}
	
	/**
	 * Unflag As Spammer Callback
	 */
	public function unflagAsSpammer( $member )
	{
		if ( $member instanceof \IPS\Member )
		{
			$member->unflagAsSpammer();
			return "member unflagged as spammer";
		}
		else
		{
			throw new \UnexpectedValueException( "invalid member" );
		}
	}
	
	/**
	 * Flag As Spammer Callback
	 */
	public function banMember( $member, $values )
	{
		if ( $member instanceof \IPS\Member )
		{
			switch ( $values[ 'rules_Members_ban_setting' ] )
			{
				case 'temporary':
					$ban_time = \strtotime
					( 
						'+' . intval( $values[ 'rules_Members_ban_setting_months' ] ) . ' months ' . 
						'+' . intval( $values[ 'rules_Members_ban_setting_days' ] ) . ' days ' .
						'+' . intval( $values[ 'rules_Members_ban_setting_hours' ] ) . ' hours ' .
						'+' . intval( $values[ 'rules_Members_ban_setting_minutes' ] ) . ' minutes '
					);
					$member->temp_ban = $ban_time;
					$member->save();
					return "member temporarily banned";
					
				default:
					$member->temp_ban = -1;
					$member->save();
					return "member banned permanently";
			}
		}
		else
		{
			throw new \UnexpectedValueException( "invalid member" );
		}
	}
	
	/**
	 * Flag As Spammer Callback
	 */
	public function unbanMember( $member )
	{
		if ( $member instanceof \IPS\Member )
		{
			$member->temp_ban = 0;
			$member->save();
			return "member unbanned";
		}
		else
		{
			throw new \UnexpectedValueException( "invalid member" );
		}
	}
	
	/**
	 * Prune A Member
	 */
	public function pruneMember( $member, $values )
	{
		if ( $member instanceof \IPS\Member )
		{
			$member->delete();
			return "member deleted";
		}
		else
		{
			throw new \UnexpectedValueException( "invalid member" );
		}
	}
	
}