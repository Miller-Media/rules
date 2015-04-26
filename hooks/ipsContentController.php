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
		if ( $item = parent::manage() )
		{
			\IPS\rules\Event::load( 'rules', 'Content', 'content_item_viewed' )->trigger( $item );
			
			$classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_item_viewed_' . md5( get_class( $item ) ) );
			if ( ! $classEvent->placeholder )
			{
				$classEvent->trigger( $item );
			}
		}

		return $item;
	}
	
}