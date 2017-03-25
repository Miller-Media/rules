<?php
/**
 * @brief		Rules Actions API
 * @author		Kevin Carwile
 * @package		Automation Rules
 * @since		Mar 21, 2017
 */

namespace IPS\rules\api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Rules Actions API
 */
class _actions extends \IPS\Api\Controller
{

	/**
	 * Execute
	 *
	 * @param	array	$pathBits	The parts to the path called
	 * @param	bool	$shouldLog	Gets set to TRUE if this call should log
	 * @return	\IPS\Api\Response
	 * @throws	\IPS\Api\Exception
	 */
	public function execute( $pathBits, &$shouldLog )
	{
		$method = ( isset( $_SERVER['REQUEST_METHOD'] ) and in_array( mb_strtoupper( $_SERVER['REQUEST_METHOD'] ), array( 'GET', 'POST', 'PUT', 'DELETE' ) ) ) ? mb_strtoupper( $_SERVER['REQUEST_METHOD'] ) : 'GET';
		$params = array();
		
		$action_id = $pathBits[0];
		
		try
		{
			$action = \IPS\rules\Action\Custom::load( $action_id );
			if ( ! $action->enable_api )
			{
				throw new \OutOfRangeException;
			}
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'NO_ENDPOINT', '2S291/1', 404 );
		}
		
		if ( ! $this->apiKey->canAccess( 'rules', 'actions', "{$method}{$action_id}" ) )
		{
			throw new \IPS\Api\Exception( 'NO_PERMISSION', '2S291/3', 403 );
		}
		
		$shouldLog = $this->apiKey->shouldLog( 'rules', 'actions', "{$method}{$action_id}" );
		$postdata = json_decode( file_get_contents( 'php://input' ), true );
		
		$arguments = array();
		
		foreach( $action->children() as $argument )
		{
			$varname = $argument->varname;
			
			if ( isset( \IPS\Request::i()->$varname ) )
			{
				$input_data = \IPS\Request::i()->$varname;
			}
			elseif ( isset( $postdata[ $varname ] ) )
			{
				$input_data = $postdata[ $varname ];
			}
			else
			{
				$arguments[] = NULL;
				continue;
			}
			
			switch( $argument->type )
			{
				case 'object':
				
					$class = str_replace( '-', '\\', $argument->class );
					if ( $class )
					{
						if ( is_subclass_of( $class, 'IPS\Patterns\ActiveRecord' ) )
						{
							try {
								$input_data = $class::load( $input_data );
							}
							catch( \OutOfRangeException $e )
							{
								$input_data = NULL;
							}
						}
						elseif ( is_a( $class, 'IPS\DateTime', TRUE ) )
						{
							$input_data = new \IPS\DateTime( $input_data );
						}
						elseif ( is_a( $class, 'IPS\Http\Url', TRUE ) )
						{
							$input_data = new \IPS\Http\Url( $input_data );
						}
					}
					$arguments[] = $input_data;
					break;
					
				case 'array':
				
					if ( ! is_array( $input_data ) )
					{
						$input_data = explode( ',', $input_data );
					}
					
					$class = str_replace( '-', '\\', $argument->class );
					if ( $class )
					{
						if ( $class and is_subclass_of( $class, 'IPS\Patterns\ActiveRecord' ) )
						{
							$input_data = array_filter( array_map( function( $val ) use ( $class ) {
								try {
									return $class::load( $val );
								}
								catch( \OutOfRangeException $e ) {}
							}, $input_data ) );
						}
						elseif ( is_a( $class, 'IPS\DateTime', TRUE ) )
						{
							$input_data = array_map( function( $val ) { return new \IPS\DateTime( $val ); }, $input_data );
						}
						elseif ( is_a( $class, 'IPS\Http\Url', TRUE ) )
						{
							$input_data = array_map( function( $val ) { return new \IPS\Http\Url( $val ); }, $input_data );
						}
					}
					
					$arguments[] = $input_data;
					break;
					
				case 'int':
				
					$arguments[] = (int) $input_data;
					break;
					
				case 'float':
				
					$arguments[] = (float) $input_data;
					break;
					
				case 'string':
				
					$arguments[] = (string) $input_data;
					break;
					
				case 'bool':
				
					$arguments[] = (bool) $input_data;
					break;
					
				default:
				
					$arguments[] = $input_data;
			}
		}
		
		$event = \IPS\rules\Event::load( 'rules', 'CustomActions', 'custom_action_' . $action->key );
		call_user_func_array( array( $event, 'trigger' ), $arguments );
		
		return new \IPS\Api\Response( 200, array_merge( array( 'processed' => true ), $event->apiResponse ) );
	}

	/**
	 * Get endpoint data
	 *
	 * @param	array	$pathBits	The parts to the path called
	 * @return	array
	 * @throws	\RuntimeException
	 */
	protected function _getEndpoint( $pathBits )
	{
		return array( 'endpoint' => 'index', 'params' => array( $database ) );		
	}

}
