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
			/**
			 * Rules may trigger core system functions that use \IPS\Member::loggedIn(), (i.e \IPS\Content\Item::setTags )
			 * This prevents the script from crashing in API mode because the parent method attempts to start a session.
			 */
			if ( ( ! isset( $_SERVER[ 'SERVER_SOFTWARE' ] ) && ( in_array( php_sapi_name(), array( 'cli', 'cgi', 'cgi-fcgi' ) ) || ( is_numeric( $_SERVER[ 'argc' ] ) && $_SERVER[ 'argc' ] > 0 ) ) ) )
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
	
	/**
	 * Unflag as spammer
	 *
	 * @return	void
	 */
	public function unflagAsSpammer()
	{
		parent::unflagAsSpammer();
		
		\IPS\rules\Event::load( 'rules', 'Members', 'member_not_spammer' )->trigger( $this );
	}
	
	/**
	 * Set banned
	 *
	 * @param	string	$value	Value
	 * @return	void
	 */
	public function set_temp_ban( $value )
	{
		if ( $this->temp_ban == 0 and $value != 0 )
		{
			/* Banned */
			parent::set_temp_ban( $value );
			\IPS\rules\Event::load( 'rules', 'Members', 'member_banned' )->trigger( $this );
			return;
		}
		else if ( $this->temp_ban != 0 and $value == 0 )
		{
			/* Unbanned */
			parent::set_temp_ban( $value );
			\IPS\rules\Event::load( 'rules', 'Members', 'member_unbanned' )->trigger( $this );
			return;
		}
		
		parent::set_temp_ban( $value );
	}	

}