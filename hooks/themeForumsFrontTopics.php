//<?php

class rules_hook_themeForumsFrontTopics extends _HOOK_CLASS_
{

/* !Hook Data - DO NOT REMOVE */
public static function hookData() {
 return array_merge_recursive( array (
  'topic' => 
  array (
    0 => 
    array (
      'selector' => 'div[data-role=replyArea]',
      'type' => 'add_inside_start',
      'content' => '{expression="\IPS\rules\Log\Custom::allLogs($topic)" raw="1"}',
    ),
  ),
), parent::hookData() );
}
/* End Hook Data */
















}