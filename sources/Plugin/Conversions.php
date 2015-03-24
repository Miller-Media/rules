<?php
/**
 * @brief		Rules conversions: Plugins
 * @package		Rules for IPS Social Suite
 * @since		27 Feb 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\rules\Plugin;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Rules conversions extension: Plugins
 *
 * Documentation and examples:
 * 
 * ./applications/rules/data/defaults/extensions/Conversions.txt
 *
 */
class _Conversions
{

	/**
	 * Global Arguments
	 *
	 * Let rules know about any global arguments that your app may have. Global arguments
	 * are made available to all rule configurations (conditions/actions), and can also
	 * be used as token replacements on rules forms.
	 *
	 * @return 	array		Array of global arguments
	 */
	public function globalArguments()
	{
		$globals = array
		(

		);
		
		return $globals;
	}

	/**
	 * Conversion Map
	 *
	 * Let rules know how to convert objects into different types of arguments.
	 * For example, if an event provides an \IPS\Content object, a conversion map
	 * will tell rules how to derive another possible argument from it (such as the 
	 * content title).
	 *
	 * @return 	array		Conversion map
	 */
	public function conversionMap()
	{
		$map = array
		(

		);
		
		return $map;		
	}
	
}