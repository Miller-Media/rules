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
		return call_user_func_array( 'parent::sendOutput', func_get_args() );
	}

}