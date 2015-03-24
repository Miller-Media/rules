//<?php

abstract class rules_hook_ipsContentItem extends _HOOK_CLASS_
{

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
		$result = call_user_func_array( 'parent::move', func_get_args() );
		
		\IPS\rules\Event::load( 'rules', 'Content', 'content_item_moved' )->trigger( $this, $oldContainer, $container, $keepLink );
		
		$classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_item_moved_' . md5( get_class( $this ) ) );
		if ( ! $classEvent->placeholder )
		{
			$classEvent->trigger( $this, $oldContainer, $container, $keepLink );
		}

		return $result;
	}

	/**
	 * Merge other items in (they will be deleted, this will be kept)
	 *
	 * @param	array	$items	Items to merge in
	 * @return	void
	 */
	public function mergeIn( array $items )
	{
		\IPS\rules\Event::load( 'rules', 'Content', 'content_item_merging' )->trigger( $this, $items );
		
		$classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_item_merging_' . md5( get_class( $this ) ) );
		if ( ! $classEvent->placeholder )
		{
			$classEvent->trigger( $this, $items );
		}

		return call_user_func_array( 'parent::mergeIn', func_get_args() );
	}

	/**
	 * Publishes a 'future' entry now
	 *
	 * @param	\IPS\Member|NULL	$member	The member doing the action (NULL for currently logged in member)
	 * @return	void
	 */
	public function publish( $member=NULL )
	{
		$result = call_user_func_array( 'parent::publish', func_get_args() );
		
		\IPS\rules\Event::load( 'rules', 'Content', 'content_item_published' )->trigger( $this, $member ?: \IPS\Member::loggedIn() );
		
		$classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_item_published_' . md5( get_class( $this ) ) );
		if ( ! $classEvent->placeholder )
		{
			$classEvent->trigger( $this, $member ?: \IPS\Member::loggedIn() );
		}

		return $result;
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
		$result = call_user_func_array( 'parent::unpublish', func_get_args() );
		
		\IPS\rules\Event::load( 'rules', 'Content', 'content_item_unpublished' )->trigger( $this, $member ?: \IPS\Member::loggedIn() );
		
		$classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_item_unpublished_' . md5( get_class( $this ) ) );
		if ( ! $classEvent->placeholder )
		{
			$classEvent->trigger( $this, $member ?: \IPS\Member::loggedIn() );
		}

		return $result;
	}
	
	/**
	 * Set tags
	 *
	 * @param	array	$set	The tags (if one has the key "prefix", it will be set as the prefix)
	 * @return	void
	 */
	public function setTags( $set )
	{		
		$result = call_user_func_array( 'parent::setTags', func_get_args() );
		
		\IPS\rules\Event::load( 'rules', 'Content', 'content_item_tags_set' )->trigger( $this, $set );
		
		$classEvent = \IPS\rules\Event::load( 'rules', 'Content', 'content_item_tags_set_' . md5( get_class( $this ) ) );
		if ( ! $classEvent->placeholder )
		{
			$classEvent->trigger( $this, $set );
		}
	}
	
	/**
	 * Get elements for add/edit form
	 *
	 * @param	\IPS\Content\Item|NULL	$item		The current item if editing or NULL if creating
	 * @param	\IPS\Node\Model|NULL	$container	Container (e.g. forum), if appropriate
	 * @return	array
	 */
	public static function formElements( $item=NULL, \IPS\Node\Model $container=NULL )
	{
		$formElements = parent::formElements( $item, $container );
		
		foreach ( \IPS\Db::i()->select( '*', 'rules_data', array( 'data_class=? AND data_use_mode IN ( \'public\', \'admin\' )', static::rulesDataClass() ) ) as $row )
		{
			if ( $row[ 'data_use_mode' ] == 'public' or static::modPermission( 'edit', NULL, $container ) )
			{
				$data_field = \IPS\rules\Data::constructFromData( $row );
				$formElements = array_merge( $formElements, $data_field->formElements( $item ) );
			}
		}
		
		return $formElements;		
	}
	
	/**
	 * Process create/edit form
	 *
	 * @param	array				$values	Values from form
	 * @return	void
	 */
	public function processForm( $values )
	{
		parent::processForm( $values );
		
		foreach ( \IPS\Db::i()->select( '*', 'rules_data', array( 'data_class=? AND data_use_mode IN ( \'public\', \'admin\' )', $this::rulesDataClass() ) ) as $row )
		{
			$data_field = \IPS\rules\Data::constructFromData( $row );
			
			if ( isset ( $values[ 'rules_data_' . $data_field->column_name ] ) )
			{
				$this->setRulesData( $data_field->column_name, $data_field->valueFromForm( $values ) );
			}
		}
	}
	
}