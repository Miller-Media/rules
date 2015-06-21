//<?php

class rules_hook_ipsMember extends _HOOK_CLASS_
{

	/**
	 * Get logged in member
	 *
	 * @return	\IPS\Member
	 */
	public static function loggedIn()
	{
		if ( static::$loggedInMember === NULL )
		{
			if ( ! \IPS\Dispatcher::hasInstance() )
			{
				static::$loggedInMember = static::load( NULL );
			}
		}
		
		return parent::loggedIn();
	}
	
	/**
	 * Member Sync
	 *
	 * @param	string	$method	Method
	 * @param	array	$params	Additional parameters to pass
	 * @return	void
	 */
	public function memberSync( $method, $params=array () )
	{
		$event = \IPS\rules\Event::load( 'rules', 'Members', 'memberSync_' . $method );
		call_user_func_array( array( $event, 'trigger' ), array_merge( array( $this ), $params ) );
		
		return call_user_func_array( 'parent::memberSync', func_get_args() );
	}
	
	/**
	 * Recounts content for this member
	 *
	 * @return void
	 */
	public function recountContent()
	{
		parent::recountContent();
		
		\IPS\rules\Event::load( 'rules', 'Members', 'content_recounted' )->trigger( $this, $this->member_posts );
	}

}