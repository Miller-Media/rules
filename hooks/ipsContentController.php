//<?php

class rules_hook_ipsContentController extends _HOOK_CLASS_
{

	/**
	 * View Item
	 *
	 * @return	\IPS\Content\Item|NULL
	 */
	protected function manage()
	{
		$item = parent::manage() and \IPS\rules\Event::load( 'rules', 'Content', 'content_item_viewed' )->trigger( $item );
		return $item;
	}
	
}