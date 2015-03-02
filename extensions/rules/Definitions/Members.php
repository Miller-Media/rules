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
	public $group = 'members';

	/**
	 * @brief	Triggerable Events
	 */
	public function events()
	{
		$memberArg = array(
			'argtype' 	=> 'object',
			'class'		=> '\IPS\Member',
		);
		
		$events = array
		(
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
			'reputation_given' => array
			(
				'arguments' => array
				(
					'member' 	=> $memberArg,
					'giver' 	=> $memberArg,
					'content' 	=> array( 'argtype' => '\IPS\Content' ),
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
						
						$form->add( new \IPS\Helpers\Form\CheckboxSet( 'rules_Members_member_groups', $values[ 'rules_Members_member_groups' ], TRUE, array( 'options' => $options ), NULL, NULL, NULL, 'rules_Members_member_groups' ) );
					},
				),
				'callback' 	=> array( $this, 'checkMemberGroup' ),
				'arguments' => array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::getPreset( 'member' ), 
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
					
					},
				),
				'callback' 	=> array( $this, 'checkMemberAttributes' ),
				'arguments' => array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::getPreset( 'member' ), 
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

					},
				),
				'callback' 	=> array( $this, 'checkMemberStatus' ),
				'arguments' => array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::getPreset( 'member' ), 
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
			'modify_posts' => array
			(
				'callback'	=> array( $this, 'modifyPosts' ),
				'configuration' => array
				(
					'form' => function( $form, $values, $action )
					{
						// need to setup options... add to posts / decrement posts / set posts to value etc.
					},
				),
				'arguments'	=> array
				(
					'member' => array
					(
						'argtypes' 	=> \IPS\rules\Application::getPreset( 'member' ), 
						'required'	=> TRUE,
					),
					'value' => array
					(
						'configuration' => array
						(
							'form' => function( $form, $values, $action )
							{
								$form->add( new \IPS\Helpers\Form\Number( 'rules_Members_posts_value', $values[ 'rules_Members_posts_value' ], TRUE, array( 'min' => NULL ), NULL, NULL, NULL, 'rules_Members_posts_value' ) );
								return array( 'rules_Members_posts_value' );
							},
							'getArg' => function( $values, $action )
							{
								return $values[ 'rules_Members_posts_value' ];
							}
						),
						'argtypes'	=> array( 'int' ),
						'required'	=> TRUE,
					),
				),
			),
			'modify_reputation' => array
			(
			
			),
			'change_primary_group' => array
			(
			
			),
			'modify_secondary_groups' => array
			(
			
			),
			'change_title' => array
			(
			
			),
			'ban_member' => array
			(
			
			),
			'unban_member' => array
			(
			
			),
			'prune_member' => array
			(
			
			),
		);
		
		return $actions;
	}
	
	/**
	 * Update Member Callback
	 */
	public function modifyPosts( $member, $value, $values )
	{

	}
	
	/**
	 * Flag As Spammer Callback
	 */
	public function flagAsSpammer( $member )
	{
		if ( is_object( $member ) and $member instanceof \IPS\Member )
		{
			$member->flagAsSpammer();
		}
	}
	
	/**
	 * Unflag As Spammer Callback
	 */
	public function unflagAsSpammer( $member )
	{
		if ( is_object( $member ) and $member instanceof \IPS\Member )
		{
			$member->unflagAsSpammer();
		}
	}
	
	/**
	 * Check Member Groups
	 */
	public function checkMemberGroup( $member, $values )
	{
		if ( ! is_object( $member ) or ! ( $member instanceof \IPS\Member ) )
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

	}

	/**
	 * Check Member Status
	 */
	public function checkMemberStatus( $member, $values )
	{

	}
	
}