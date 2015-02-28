//<?php

abstract class rules_hook_ipsContentComment extends _HOOK_CLASS_
{

	/**
	 * Create comment
	 *
	 * @param	\IPS\Content\Item		$item				The content item just created
	 * @param	string					$comment			The comment
	 * @param	bool					$first				Is the first comment?
	 * @param	string					$guestName			If author is a guest, the name to use
	 * @param	bool|NULL				$incrementPostCount	Increment post count? If NULL, will use static::incrementPostCount()
	 * @param	\IPS\Member|NULL		$member				The author of this comment. If NULL, uses currently logged in member.
	 * @param	\IPS\DateTime|NULL		$time				The time
	 * @return	static
	 */
	static public function create( $item, $comment, $first=false, $guestName=NULL, $incrementPostCount=NULL, $member=NULL, \IPS\DateTime $time=NULL )
	{
		$_comment = call_user_func_array( 'parent::create', func_get_args() );
		\IPS\rules\Event::load( 'rules', 'Comments', 'content_comment_created' )->trigger( $_comment, $item, $comment, $first, $guestName, isset( $incrementPostCount ) ? $incrementPostCount : static::incrementPostCount(), $member ?: \IPS\Member::loggedIn(), $time );
		return $_comment;
	}

	/**
	 * Move Comment to another item
	 *
	 * @param	\IPS\Content\Item	$item The item to move this comment too.
	 * @return	void
	 */
	public function move( \IPS\Content\Item $item )
	{
		call_user_func_array( 'parent::move', func_get_args() );
		\IPS\rules\Event::load( 'rules', 'Comments', 'content_comment_moved' )->trigger( $this, $item );
	}

}