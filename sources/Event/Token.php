<?php
/**
 * @brief		IPS4 Rules
 * @author		Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright		(c) 2014 - Kevin Carwile
 * @package		Rules
 * @since		6 Feb 2015
 */


namespace IPS\rules\Event;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Node
 */
class _Token
{
	
	/**
	 * @brief	Argument to create token from
	 */
	protected $argument = NULL;
	
	/**
	 * @brief	The token converter definition
	 */
	protected $converter = NULL;
	
	/**
	 * @brief	The token value
	 */
	protected $token = NULL;
		
	/**
	 * Constructor
	 *
	 * @param 	object	$argument	Argument object to create token from
	 * @param	array	$converter	The token converter definition
	 * @return 	void
	 */
	public function __construct( $argument, $converter=NULL )
	{
		$this->argument = $argument;
		$this->converter = $converter;
		
		if ( $argument === NULL or $converter === NULL )
		{
			$this->token = (string) $argument;
		}
	}
	
	/**
	 * String Value
	 */
	public function __toString()
	{
		if ( $this->token === NULL )
		{
			$this->token = $this->tokenValue();
		}
		
		return (string) $this->token;
	}
	
	/**
	 * Get Token Value
	 *
	 * @return	string		The token value
	 */
	protected function tokenValue()
	{
		if ( $this->argument !== NULL )
		{
			$tokenValues = array();
			$input_arg = $this->argument;
			$converter = $this->converter;
			
			/* Create array so single args and array args can be processed in the same way */
			if ( ! is_array( $input_arg ) )
			{
				$input_arg = array( $input_arg );
			}
			
			foreach( $input_arg as $_input_arg )
			{
				if ( is_object( $_input_arg ) )
				{
					try
					{
						/* Standard conversion */
						$_tokenValue = call_user_func( $converter[ 'converter' ], $_input_arg );
						
						/* Token formatter? */
						if ( isset( $converter[ 'tokenValue' ] ) and is_callable( $converter[ 'tokenValue' ] ) )
						{
							$_tokenValue = call_user_func( $converter[ 'tokenValue' ], $_tokenValue );
						}
					
						$tokenValues[] = (string) $_tokenValue;
					}
					catch( \Exception $e ) { }
				}
			}
			
			$this->token = implode( ', ', $tokenValues );
		}
		
		return $this->token;
	}

}