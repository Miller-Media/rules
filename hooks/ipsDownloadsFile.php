//<?php

class rules_hook_ipsDownloadsFile extends _HOOK_CLASS_
{

	/**
	 * Get available comment/review tabs
	 *
	 * @return	array
	 */
	public function commentReviewTabs()
	{
		$tabs = parent::commentReviewTabs();
		
		foreach( \IPS\rules\Log\Custom::roots( 'view', NULL, array( array( 'custom_log_class=? AND custom_log_enabled=1', $this::rulesDataClass() ) ) ) as $log )
		{
			if ( $log->display_empty or $log->logCount( $this ) )
			{
				$tab_id = 'custom_log_' . $log->id;			
				$tabs[ $tab_id ] = $log->title;
			}
		}

		return $tabs;
	}
	
	/**
	 * Get comment/review output
	 *
	 * @param	string	$tab	Active tab
	 * @return	string
	 */
	public function commentReviews( $tab )
	{	
		foreach( \IPS\rules\Log\Custom::roots( 'view', NULL, array( array( 'custom_log_class=? AND custom_log_enabled=1', $this::rulesDataClass() ) ) ) as $log )
		{
			if ( $tab == 'custom_log_' . $log->id )
			{
				return (string) $log->logsTable( $this, $limit );
			}
		}	
	
		return parent::commentReviews( $tab );
	}
	
}