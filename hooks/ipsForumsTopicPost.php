//<?php

class rules_hook_ipsForumsTopicPost extends _HOOK_CLASS_
{

	/**
	 * Best Answer Tracking
	 */
	protected $isBestAnswer = NULL;
	
	/**
	 * Construct ActiveRecord from database row
	 *
	 * @param	array	$data							Row from database table
	 * @param	bool	$updateMultitonStoreIfExists	Replace current object in multiton store if it already exists there?
	 * @return	static
	 */
	public static function constructFromData( $data, $updateMultitonStoreIfExists = TRUE )
	{
		$post = call_user_func_array( 'parent::constructFromData', func_get_args() );
		$this->isBestAnswer = $this->post_bwoptions[ 'best_answer' ];
	}
	
	/**
	 * Save Changed Columns
	 *
	 * @return	void
	 */
	public function save()
	{
		parent::save();
		
		/**
		 * Best Answer Set
		 */
		if ( ! $this->isBestAnswer and $this->post_bwoptions[ 'best_answer' ] )
		{
			\IPS\rules\Event::load( 'rules', 'Forums', 'best_answer_set' )->trigger( $this->item(), $this );
		}
		
		/**
		 * Best Answer Unset
		 */
		if ( $this->isBestAnswer and ! $this->post_bwoptions[ 'best_answer' ] )
		{
			\IPS\rules\Event::load( 'rules', 'Forums', 'best_answer_unset' )->trigger( $this->item(), $this );
		}
	}

}