//<?php

class rules_hook_modForumsSavedAction extends _HOOK_CLASS_
{

	/**
	 * Run
	 *
	 * @param	\IPS\forums\Topic	$topic	The topic to run on
	 * @param	\IPS\Member|NULL	$member	Member running (NULL for currently logged in member)
	 * @return	void
	 */
	public function runOn( \IPS\forums\Topic $topic, \IPS\Member $member=NULL )
	{
		call_user_func_array( 'parent::runOn', func_get_args() );
		\IPS\rules\Event::load( 'rules', 'Forums', 'saved_action_' . $this->_id )->trigger( $this, $topic, $member ?: \IPS\Member::loggedIn() );
	}

}