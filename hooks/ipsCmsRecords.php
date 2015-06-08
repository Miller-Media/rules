//<?php

class rules_hook_ipsCmsRecords extends _HOOK_CLASS_
{

	/**
	 * Get elements for add/edit form
	 *
	 * @param	\IPS\Content\Item|NULL	$item		The current item if editing or NULL if creating
	 * @param	\IPS\Node\Model|NULL	$container	Container (e.g. forum), if appropriate
	 * @return	array
	 */
	public static function formElements( $item=NULL, \IPS\Node\Model $container=NULL )
	{
		/**
		 * This code is also in our hook for \IPS\Content\Item, but the pages app neglects our added elements
		 * so we're forced to hook here again.
		 */
		return array_merge( parent::formElements( $item, $container ), static::rulesFormElements( $item, $container ) );
	}

}