//<?php

abstract class rules_hook_ipsContentItem extends _HOOK_CLASS_
{

	/**
	 * Create from form
	 *
	 * @param	array					$values				Values from form
	 * @param	\IPS\Node\Model|NULL	$container			Container (e.g. forum), if appropriate
	 * @param	bool					$sendNotification	TRUE to automatically send new content notifications (useful for items that may be uploaded in bulk)
	 * @return	static
	 */
	static public function createFromForm( $values, \IPS\Node\Model $container=NULL, $sendNotification=true )
	{
		$obj = call_user_func_array( 'parent::createFromForm', func_get_args() );
		\IPS\rules\Event::load( 'rules', 'ContentItems', 'content_item_created' )->trigger( $obj, $values, $container, $sendNotification );
	}

	/**
	 * Move
	 *
	 * @param	\IPS\Node\Model	$container	Container to move to
	 * @param	bool			$keepLink	If TRUE, will keep a link in the source
	 * @return	void
	 */
	public function move( \IPS\Node\Model $container, $keepLink=false )
	{
		$oldContainer = $this->container();
		call_user_func_array( 'parent::move', func_get_args() );
		\IPS\rules\Event::load( 'rules', 'ContentItems', 'content_item_moved' )->trigger( $this, $oldContainer, $container, $keepLink );
	}

	/**
	 * Merge other items in (they will be deleted, this will be kept)
	 *
	 * @param	array	$items	Items to merge in
	 * @return	void
	 */
	public function mergeIn( array $items )
	{
		\IPS\rules\Event::load( 'rules', 'ContentItems', 'content_item_merging' )->trigger( $this, $items );
		call_user_func_array( 'parent::mergeIn', func_get_args() );
	}

	/**
	 * Publishes a 'future' entry now
	 *
	 * @param	\IPS\Member|NULL	$member	The member doing the action (NULL for currently logged in member)
	 * @return	void
	 */
	public function publish( $member=NULL )
	{
		call_user_func_array( 'parent::publish', func_get_args() );
		\IPS\rules\Event::load( 'rules', 'ContentItems', 'content_item_published' )->trigger( $this, $member ?: \IPS\Member::loggedIn() );
	}
	
	/**
	 * "Unpublishes" an item.
	 * @note    This will not change the item's date. This should be done via the form methods if required
	 *
	 * @param	\IPS\Member|NULL	$member	The member doing the action (NULL for currently logged in member)
	 * @return	void
	 */
	public function unpublish( $member=NULL )
	{
		call_user_func_array( 'parent::unpublish', func_get_args() );
		\IPS\rules\Event::load( 'rules', 'ContentItems', 'content_item_unpublished' )->trigger( $this, $member ?: \IPS\Member::loggedIn() );
	}
	
}