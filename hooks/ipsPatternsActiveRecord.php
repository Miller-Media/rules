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
			
			/**
			 * Content Created
			 */
			if ( $this instanceof \IPS\Content )
			{
				\IPS\rules\Event::load( 'rules', 'Content', 'content_created' )->trigger( $this );
			}
		}
		else
		{
			$changed = $this->changed;
			if ( $changed )
			{
				\IPS\rules\Event::load( 'rules', 'System', 'record_being_updated' )->trigger( $this, $changed );
			}
			
			call_user_func_array( 'parent::save', func_get_args() );
			
			/**
			 * Content Updated
			 */
			if ( $changed and $this instanceof \IPS\Content )
			{
				\IPS\rules\Event::load( 'rules', 'Content', 'content_created' )->trigger( $this, $changed );
			}
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
		return call_user_func_array( 'parent::delete', func_get_args() );
	}

}