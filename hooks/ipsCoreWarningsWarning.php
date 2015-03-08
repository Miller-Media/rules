//<?php

class rules_hook_ipsCoreWarningsWarning extends _HOOK_CLASS_
{

	/**
	 * Process created object AFTER the object has been created
	 *
	 * @param	\IPS\Content\Comment|NULL	$comment	The first comment
	 * @param	array						$values		Values from form
	 * @return	void
	 */
	protected function processAfterCreate( $comment, $values )
	{
		$result = call_user_func_array( 'parent::processAfterCreate', func_get_args() );
		\IPS\rules\Event::load( 'rules', 'Members', 'member_warned' )->trigger( $this, \IPS\Member::load( $this->member ), $this->author() );
		return $result;
	}

}