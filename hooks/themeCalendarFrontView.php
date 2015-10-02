//<?php

class rules_hook_themeCalendarFrontView extends _HOOK_CLASS_
{

/* !Hook Data - DO NOT REMOVE */
public static function hookData() {
 return array_merge_recursive( array (
  'comments' => 
  array (
    0 => 
    array (
      'selector' => 'div[data-role=replyArea]',
      'type' => 'add_before',
      'content' => '{expression="\IPS\rules\Log\Custom::allLogs($event)" raw="1"}',
    ),
  ),
), parent::hookData() );
}
/* End Hook Data */




}