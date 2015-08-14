//<?php

abstract class rules_hook_ipsHelpersFormAbstract extends _HOOK_CLASS_
{

	/**
	 * Force Validation Pass
	 */
	public function noError()
	{
		$this->error = FALSE;
		$this->valueSet = TRUE;
	}
	
}