<?php
/**
 * @brief		IPS4 Rules
 * @author		Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright		(c) 2014 - Kevin Carwile
 * @package		Rules
 * @since		6 Feb 2015
 */


namespace IPS\rules\Log;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Node
 */
class _Table extends \IPS\Helpers\Table\Db
{

	/**
	 * Convert to string
	 */
	public function __toString()
	{
		$result = parent::__toString();
		
		/* Remove Page Jump Form */
		$result = preg_replace( '/<form (.+?)<\/form>/is', '', $result );
		
		/* Remove Page Jump Wrapping */
		$result = preg_replace( '/<li class=\'ipsPagination_pageJump\'>(.+?)<\/li>/is', '', $result );
		
		return $result;
	}

}