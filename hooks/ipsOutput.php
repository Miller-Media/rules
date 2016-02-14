//<?php

class rules_hook_ipsOutput extends _HOOK_CLASS_
{

	/**
	 * Send output
	 *
	 * @param	string	$output				Content to output
	 * @param	int		$httpStatusCode		HTTP Status Code
	 * @param	string	$contentType		HTTP Content-type
	 * @param	array	$httpHeaders		Additional HTTP Headers
	 * @param	bool	$cacheThisPage		Can/should this page be cached?
	 * @param	bool	$pageIsCached		Is the page from a cache? If TRUE, no language parsing will be done
	 * @return	void
	 */
	public function sendOutput( $output='', $httpStatusCode=200, $contentType='text/html', $httpHeaders=array (), $cacheThisPage=true, $pageIsCached=false )
	{
		\IPS\rules\Event::load( 'rules', 'System', 'browser_output' )->trigger( $output, $httpStatusCode, $contentType, $httpHeaders, $cacheThisPage, $pageIsCached );
		
		/* Shut down rules early for actual page requests (so redirects can be performed, etc) */
		\IPS\rules\Application::shutDown();
		
		return call_user_func_array( 'parent::sendOutput', func_get_args() );
	}
	
	/**
	 * Redirect
	 *
	 * @param	\IPS\Http\Url	$url			URL to redirect to
	 * @param	string			$message		Optional message to display
	 * @param	int				$httpStatusCode	HTTP Status Code
	 * @param	bool			$forceScreen	If TRUE, an intermediate screen will be shown
	 * @return	void
	 */
	public function redirect( $url, $message='', $httpStatusCode=301, $forceScreen=FALSE )
	{
		if ( static::$instance->inlineMessage )
		{
			$_SESSION[ 'inlineMessage' ] = static::$instance->inlineMessage;
		}

		return parent::redirect( $url, $message, $httpStatusCode, $forceScreen );
	}

}