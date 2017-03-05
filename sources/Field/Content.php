<?php
/**
 * @brief		Member input class for Form Builder
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Social Suite
 * @since		11 Mar 2013
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\rules\Field;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Member input class for Form Builder
 */
class _Content extends \IPS\Helpers\Form\Text
{
	/**
	 * @brief	Default Options
	 * @code
	 	$childDefaultOptions = array(
	 		'multiple'	=> 1,	// Maximum number of members. NULL for any. Default is 1.
	 	);
	 * @encode
	 */
	public $childDefaultOptions = array
	(
		'multiple'	=> 1,
	);


	/**
	 * Constructor
	 * Adds 'confirm' to the available options
	 *
	 * @see		\IPS\Helpers\Form\Abstract::__construct
	 * @return	void
	 */
	public function __construct()
	{
		$args = func_get_args();
		
		$this->defaultOptions[ 'autocomplete' ] = array
		(
			'source' 			=> 'app=rules&module=system&controller=ajax&class=' . str_replace( '\\', '-', $args[ 3 ][ 'class' ] ) . '&do=findContent',
			'resultItemTemplate' 		=> 'core.autocomplete.memberItem',
			'unique'			=> true,
			'minAjaxLength'			=> 3,
			'forceLower'			=> false,
			'prefix'			=> false,
		);
		
		if( isset( $args[ 3 ] ) and array_key_exists( 'multiple', $args[ 3 ] ) and $args[ 3 ][ 'multiple' ] > 0 )
		{
			$this->defaultOptions[ 'autocomplete' ][ 'maxItems' ] = $args[ 3 ][ 'multiple' ];
		}
		elseif ( !isset( $args[ 3 ] ) or !array_key_exists( 'multiple', $args[ 3 ] ) )
		{
			$this->defaultOptions[ 'autocomplete' ][ 'maxItems' ] = $this->childDefaultOptions[ 'multiple' ];
		}
		
		call_user_func_array( 'parent::__construct', $args );
	}
	
	/**
	 * Get HTML
	 *
	 * @return	string
	 */
	public function html()
	{
		$value 		= $this->value;
		$contentClass 	= $this->options[ 'class' ];
		$idField 	= $contentClass::$databaseColumnId;
		
		if ( is_array( $this->value ) )
		{
			$value = array();
			foreach ( $this->value as $v )
			{
				if ( $v instanceof \IPS\Content\Item )
				{
					$value[] = 'ID:' . $v->$idField . ' - ' . $v->mapped( 'title' );
				}
				else
				{
					$value[] = $v;
				}
			}
		}
		elseif ( $value instanceof \IPS\Content\Item )
		{
			$value = 'ID:' . $value->$idField . ' - ' . $value->mapped( 'title' );
		}
		
		return \IPS\Theme::i()->getTemplate( 'forms', 'core', 'global' )->text( $this->name, 'text', $value, $this->required, $this->options['maxLength'], $this->options['size'], $this->options['disabled'], $this->options['autocomplete'], $this->options['placeholder'], NULL, NULL );
	}
	
	/**
	 * Format Value
	 *
	 * @return	\IPS\Content\Item|array|NULL
	 */
	public function formatValue()
	{
		if ( $this->value and ! ( $this->value instanceof \IPS\Content\Item ) )
		{
			// IPS 4.1.17.1 and below use comma delimiter on autocomplete fields. Higher versions use a new line break.
			$delimiter = \IPS\Application::getAvailableVersion( 'core' ) < 101079 ? "," : "\n";

			$return       = array();
			$contentClass = $this->options[ 'class' ];
			$idField      = $contentClass::$databaseColumnId;
			$matches      = NULL;
			
			if ( is_array( $this->value ) )
			{
				$this->value = array_map( 
					function( $val ) {
						preg_match( "/ID:(\d+) - /", $val, $matches );
						return empty( $matches[ 1 ] ) ? $val : $matches[ 1 ];
					}, 
					$this->value 
				);
			}
			else
			{
				preg_match_all( "/ID:(\d+) - /", $this->value, $matches );
				
				if ( empty( $matches[ 1 ] ) )
				{
					$this->value = explode( $delimiter, $this->value );
				}
				else
				{
					$this->value = $matches[ 1 ];
				}
			}
			
			// assemble array of items from array of item id's
			foreach ( $this->value as $v )
			{
				if ( $v instanceof \IPS\Content\Item )
				{
					$return[ $v->$idField ] = $v;
				}
				else if( $v )
				{
					try
					{
						$content = $contentClass::load( $v );
						if ( $this->options[ 'multiple' ] === 1 )
						{
							return $content;
						}
						$return[ $content->$idField ] = $content;
					}
					catch( \OutOfRangeException $e )
					{
					
					}
				}
			}

			if ( ! empty( $return ) )
			{
				return ( $this->options[ 'multiple' ] === NULL or $this->options[ 'multiple' ] == 0 ) ? $return : array_slice( $return, 0, $this->options[ 'multiple' ] );
			}
		}
		
		return $this->value;
	}
	
	/**
	 * Validate
	 *
	 * @throws	\InvalidArgumentException
	 * @return	TRUE
	 */
	public function validate()
	{
		parent::validate();
		
		if ( $this->value !== '' and ! ( $this->value instanceof \IPS\Content\Item ) and ! is_array( $this->value ) )
		{
			throw new \InvalidArgumentException( 'Bad Content Selected' );
		}
	}	
	
	/**
	 * String Value
	 *
	 * @param	mixed	$value	The value
	 * @return	string
	 */
	public static function stringValue( $value )
	{
		if ( is_array( $value ) )
		{
			$values = array();
			foreach( $value as $v )
			{
				if ( $v instanceof \IPS\Content )
				{
					$values[] = $v->activeid;
				}
			}
			
			return implode( ',', $values );
		}
		
		return $value instanceof \IPS\Content ? $value->activeid : NULL;
	}
}