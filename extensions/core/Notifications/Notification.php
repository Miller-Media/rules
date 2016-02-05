<?php
/**
 * @brief		Notification Options
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Social Suite
 * @subpackage	Rules
 * @since		19 Mar 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\rules\extensions\core\Notifications;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Notification Options
 */
class _Notification
{
	/**
	 * Get configuration
	 *
	 * @param	\IPS\Member	$member	The member
	 * @return	array
	 */
	public function getConfiguration( $member )
	{
		return array
		(
			'rules_notifications'	=> array( 'default' => array( 'inline' ), 'disabled' => array( 'email' ) ),
		);
	}
	
	/**
	 * Parse notification: key
	 *
	 * @param	\IPS\Notification\Inline	$notification	The notification
	 * @return	array
	 */
	public function parse_rules_notifications( $notification )
	{
		$url = $content = $author = NULL;
		$data = $notification->member_data;
		
		if ( isset ( $data[ 'url' ] ) and $data[ 'url' ] )
		{
			$url = new \IPS\Http\Url( $notification->member_data[ 'url' ] );
		}
		
		if ( isset ( $data[ 'content' ] ) and $data[ 'content' ] )
		{
			$content = $data[ 'content' ];
		}
		
		if ( isset ( $data[ 'author' ] ) and $data[ 'author' ] )
		{
			try
			{
				$author = \IPS\Member::load( $data[ 'author' ] );
			}
			catch ( \Exception $e ) {}
		}
		
		return array(
			'title'		=> $data[ 'title' ],
			'url'		=> $url,
			'content'	=> $content,
			'author'	=> $author,
		);
	}
	
	/**
	 * Backward Compatibility for prior to 1.3.10
	 *
	 * @param	\IPS\Notification\Inline	$notification	The notification
	 * @return	array
	 */
	public function parse_rules_notification( $notification )
	{
		return $this->parse_rules_notifications( $notification );
	}
	
}