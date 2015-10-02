<?php
/**
 * @brief		Profile extension: ProfileLogs
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Social Suite
 * @subpackage	Rules
 * @since		27 Sep 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\rules\extensions\core\Profile;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Profile extension: ProfileLogs
 */
class _ProfileLogs
{
	/**
	 * Member
	 */
	protected $member;
	
	/**
	 * Constructor
	 *
	 * @param	\IPS\Member	$member	Member whose profile we are viewing
	 * @return	void
	 */
	public function __construct( \IPS\Member $member )
	{
		$this->member = $member;
	}
	
	/**
	 * Is there content to display?
	 *
	 * @return	bool
	 */
	public function showTab()
	{
		foreach( \IPS\rules\Log\Custom::roots( 'view', NULL, array( array( 'custom_log_class=? AND custom_log_enabled=1', \IPS\Member::rulesDataClass() ) ) ) as $log )
		{
			if ( $log->logCount( $this->member ) )
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	/**
	 * Display
	 *
	 * @return	string
	 */
	public function render()
	{
		return \IPS\rules\Log\Custom::allLogs( $this->member );
	}
}