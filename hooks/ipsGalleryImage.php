//<?php

class rules_hook_ipsGalleryImage extends _HOOK_CLASS_
{

	/**
	 * Magic Get
	 */
	public function __get( $key )
	{
		$value = parent::__get( $key );
	
		/* Add logs to image description when viewing on the image page */
		if 
		( 
			$key == 'description' and
			\IPS\Request::i()->app == 'gallery' and 
			\IPS\Request::i()->module == 'gallery' and 
			\IPS\Request::i()->controller == 'view' and 
			\IPS\Request::i()->id == $this->activeid and
			\IPS\Request::i()->do != 'edit'
		)
		{
			$value .= \IPS\rules\Log\Custom::allLogs( $this );
		}
		
		return $value;
	}
}