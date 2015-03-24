<?php
/**
 * @brief		Rules conversions: CustomData
 * @package		Rules for IPS Social Suite
 * @since		22 Mar 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\rules\extensions\rules\Conversions;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Rules conversions extension: CustomData
 */
class _CustomData
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
		$map = array();
		$lang = \IPS\Member::loggedIn()->language();
		
		foreach ( \IPS\rules\Data::roots( NULL ) as $data )
		{
			$objClass 	= str_replace( '-', '\\', $data->class );
			$returnObjClass = str_replace( '-', '\\', $data->type_class );
			$key 		= $data->column_name;
			
			$lang->words[ 'custom_data_conversion_' . $data->key ] = $data->name;
			
			$map[ $objClass ][ 'custom_data_conversion_' . $data->key ] = array
			(
				'token' 	=> $key,
				'description' 	=> ucfirst( mb_strtolower( $data->name ) ),
				'argtype' 	=> $data->type,
				'class' 	=> $returnObjClass,
				'nullable' 	=> in_array( $data->type, array( 'object', 'mixed' ) ),
				'converter' 	=> function( $object ) use ( $data )
				{
					$data = $object->getRulesData( $data->column_name );
					
					switch ( $data->type )
					{
						case 'int':
							return (int) $data;
						case 'bool':
							return (bool) $data;
						case 'float':
							return (float) $data;
						case 'array':
							return (array) $data;
						case 'string':
							return (string) $data;
						default:
							return $data;
					}					
				}
			);
		}

		return $map;		
	}
	
}