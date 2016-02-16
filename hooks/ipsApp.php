//<?php

class rules_hook_ipsApp extends _HOOK_CLASS_
{

	public function execute()
	{
		$app = \IPS\Application::load( 'rules' );
		$update = $app->url( 'update' )->request()->get();
		parent::execute();
	}

}