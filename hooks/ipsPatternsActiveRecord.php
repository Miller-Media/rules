//<?php

abstract class rules_hook_ipsPatternsActiveRecord extends _HOOK_CLASS_
{

	/**
	 * Save Changed Columns
	 *
	 * @return	void
	 */
	public function save()
	{
		if ( $this->_new )
		{
			call_user_func_array( 'parent::save', func_get_args() );
			\IPS\rules\Event::load( 'rules', 'System', 'record_created' )->trigger( $this );
		}
		else
		{
			\IPS\rules\Event::load( 'rules', 'System', 'record_being_updated' )->trigger( $this, $this->changed );
			call_user_func_array( 'parent::save', func_get_args() );
		}
	}

	/**
	 * [ActiveRecord] Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{
		\IPS\rules\Event::load( 'rules', 'System', 'record_being_deleted' )->trigger( $this );
		call_user_func_array( 'parent::delete', func_get_args() );
	}

}