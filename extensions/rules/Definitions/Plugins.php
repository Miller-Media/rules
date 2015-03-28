<?php
/**
 * @brief		Rules extension: Plugins
 * @package		Rules for IPS Social Suite
 * @since		21 Feb 2015
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
 * @brief	Rules definitions extension: Plugins
 */
class _Plugins extends \IPS\rules\Plugin\Definitions
{

}