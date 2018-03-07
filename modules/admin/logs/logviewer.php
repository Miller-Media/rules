<?php

namespace IPS\rules\modules\admin\logs;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * logs
 */
class _logviewer extends \IPS\Dispatcher\Controller
{	
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		parent::execute();
	}
	
	/**
	 * Manage
	 *
	 * @return	void
	 */
	protected function manage()
	{		
		$log 		= \IPS\rules\Log\Custom::load( \IPS\Request::i()->log );
		$logClass 	= str_replace( '-', '\\', $log->class );
		$entity 	= $logClass::load( \IPS\Request::i()->entity );
		
		\IPS\Output::i()->title = $log->title;
		\IPS\Output::i()->output = $log->logsTable( $entity );
		
		if ( ! \IPS\Request::i()->isAjax() )
		{
			$url = $entity->url();
			if ( $entity instanceof \IPS\Member ) {
				$url = $url->setQueryString( array( 'tab' => 'node_rules_ProfileLogs' ) );
			}
			\IPS\Output::i()->redirect( $url->setQueryString( array( 'log' => $log->id, 'logtab' => $log->id, 'logsortby' => \IPS\Request::i()->sortby, 'logsortdir' => \IPS\Request::i()->sortdirection, 'logpage' => \IPS\Request::i()->page ) ) );
		}
	}

}