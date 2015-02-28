<?php

$lang = array(
	'__app_rules'				=> "Rules",
	'menu__rules_rules'			=> "Rules",
	'menu__rules_rules_rules'		=> "Manage Rules",
	'ext__Definitions'			=> "Events, Conditions, and Actions Definition Groups",
	
	// Rules Restrictions
	'r__rules'				=> "Rules Restrictions",
	'r__rules_manage'			=> "Manage Rules",
	
	// Form Generic
	'rule'					=> "Rule",
	'rules'					=> "Rules",
	'conditions'				=> "Conditions",
	'actions'				=> "Actions",
	'source'				=> "Source",
	'phpcode' 				=> "PHP Code",
	'phpcode_desc'				=> "<strong>Warning</strong>: PHP is for advanced users only. Your php code needs to return a value to use as the argument. Do not include a &lt;?php tag at the beginning of your code, or comment it out if you do.",
	'phpcode_desc_details'			=> "The operation expects you to return one of the following argument types:",
	'phpcode_desc_details_vars'		=> "The following variables are available to your php code:",
	'use_event_argument'			=> "Event Data To Use",
	'use_event_argument_default'		=> "Use A Default Configuration?",
	'use_event_argument_default_desc'	=> "The argument data may not be provided by the event in all cases. If it is not, would you like to provide a default configuration?",

	// Node Form
	'rule_debug'				=> "Debug This Rule",
	'debug_on'				=> "Debug On",
	'rule_debug_desc'			=> "If checked, information related to this rule will be logged to its debug console.",
	'rules_settings'			=> "Settings",
	'rules_conditions'			=> "Conditions",
	'rules_actions'				=> "Actions",
	'rule_actions'				=> "Rule Actions",
	'rule_conditions'			=> "Rule Conditions",
	'rules_add_condition'			=> "Add New Condition",
	'rules_add_action'			=> "Add New Action",
	'rules_add_child'			=> "Add New Linked Rule",
	'rule_event_selection'			=> "Rule Triggered When:",
	'edit_conditions'			=> "Edit Conditions",
	'edit_actions'				=> "Edit Actions",
	'rule_base_compare'			=> "Base Conditions Comparison",
	'rule_base_compare_desc'		=> "You can choose how you want the base conditions for this rule to be evaluated.<ul>
							<li>If you choose AND, all base conditions must be valid for actions to be executed.</li>
							<li>If you choose OR, actions will be executed if any base condition is valid.</li></ul>",
	'condition_group_compare'		=> "Compare Sub Conditions Using:",
	'condition_group_compare_desc'		=> "Since this condition has subconditions, you must choose how you want those subconditions to affect the state of this condition.<ul>
							<li>If you choose AND, this condition and all subconditions must be true for this condition to be valid.</li>
							<li>If you choose OR, this condition will pass if it is valid, or if any subcondition is valid.</li></ul>",
	
	'conditions_add_child'			=> "Add Sub Condition",
	'or_group_badge'			=> "OR ANY SUBCONDITION",
	'and_group_badge'			=> "AND ALL SUBCONDITIONS",
	
	'rule_title'				=> "Rule Title",
	'rule_title_placeholder'		=> "Describe what this rule is for",
	
	'rules_debug_console'			=> "Debug Console",
	'rules_conditions_table_op_id'		=> "Condition Title",
	'rules_conditions_table_message'	=> "Status",
	'rules_conditions_table_result'		=> "Result",
	'rules_actions_table_op_id'		=> "Action Title",
	'rules_actions_table_message'		=> "Status",
	'rules_actions_table_result'		=> "Result",
	'rules_subrules_table_rule_id'		=> "Rule Name",
	'rules_subrules_table_message'		=> "Status",
	'rules_subrules_table_result'		=> "Result",
	
	// Conditions
	'condition_title'			=> "Condition Name",
	
	// Operations ( Conditions / Actions )
	'actions_title'				=> "Action Title",
	'rule_actions_selection'		=> "Action to perform:",
	'conditions_title'			=> "Condition Title",
	'rule_conditions_selection'		=> "Condition to check",
	'operation_arg_source_event'		=> "Event Data",
	'operation_arg_source_manual'		=> "Manual Configuration",
	'operation_arg_source_phpcode'		=> "PHP Code",
	
	// Errors
	'invalid_rule'				=> "Invalid Rule",
	'rules_event_not_found'			=> "Rules Event Not Found",
	
	/**
	 * Members 
	 *
	 * Rules Definitions
	 */
	 
	/*** MEMBERS: Events ***/
	'rules_Members_event_memberSync_onCreateAccount'	=> "Member account is created",
	'rules_Members_event_memberSync_onCreateAccount_member'	=> "Member that was created",
	
	'rules_Members_event_memberSync_onProfileUpdate'	=> "Member profile is updated",
	'rules_Members_event_memberSync_onProfileUpdate_member'	=> "Member that was updated",
	'rules_Members_event_memberSync_onProfileUpdate_changed'=> "Array of profile fields that have changed",
	
	'rules_Members_event_memberSync_onSetAsSpammer'		=> "Member is flagged as spammer",
	'rules_Members_event_memberSync_onSetAsSpammer_member'	=> "Member that was flagged as spammer",
	
	'rules_Members_event_memberSync_onValidate'		=> "Member account is validated",
	'rules_Members_event_memberSync_onValidate_member'	=> "Member that was validated",
	
	'rules_Members_event_memberSync_onMerge'		=> "Member accounts are merged together",
	'rules_Members_event_memberSync_onMerge_member'		=> "Member that is being deleted",
	'rules_Members_event_memberSync_onMerge_mergedMember'	=> "Member that is being kept",
	
	'rules_Members_event_memberSync_onDelete'		=> "Member account is deleted",
	'rules_Members_event_memberSync_onDelete_member'	=> "Member flagged as spammer",
	
	'rules_Members_event_reputation_given'			=> "Member receives reputation points",
	'rules_Members_event_reputation_given_member'		=> "Member that received the reputation",
	'rules_Members_event_reputation_given_giver'		=> "Member that gave the reputation",
	'rules_Members_event_reputation_given_content'		=> "Content the reputation was given from",
	'rules_Members_event_reputation_given_reptype'		=> "Type of reputation given (1 or -1)",
	
	'rules_Members_event_member_warned'			=> "Member has been given a warning",
	'rules_Members_event_member_warned_warning'		=> "The warning content item",
	'rules_Members_event_member_warned_member'		=> "Member that was warned",
	'rules_Members_event_member_warned_moderator'		=> "Member that issued the warning",
	
	/*** MEMBERS: Conditions ***/
	'rules_Members_conditions_member_has_group'		=> "Member is in a certain member group",
	'rules_Members_conditions_member_has_group_member'	=> "Member to check for group",
	'rules_Members_member_groups'				=> "If member is in any of these groups",
	
	'rules_Members_conditions_member_attributes'		=> "Member has particular attributes",
	'rules_Members_conditions_member_attributes_member'	=> "Member to check attributes for",
	
	'rules_Members_conditions_member_status'		=> "Member has a particular status",
	'rules_Members_conditions_member_status_member'		=> "Member to check the status of",
	
	/*** MEMBERS: Actions ***/
	'rules_Members_actions_updateProfile'			=> "Update a members profile",
	'rules_Members_actions_updateProfile_member'		=> "Member to update",
	'rules_Members_actions_updateProfile_fields'		=> "Values to update",
	
	/**
	 * System 
	 * 
	 * Rules Definitions 
	 */

	/*** SYSTEM: Events ***/
	'rules_System_event_record_created'			=> "ActiveRecord: New Record Created",
	'rules_System_event_record_created_record'		=> "Record that was just created",
	'rules_System_event_record_being_updated'		=> "ActiveRecord: Record Being Updated",
	'rules_System_event_record_being_updated_record'	=> "Record that is going to be saved",
	'rules_System_event_record_being_updated_changed'	=> "An array of the values which have changed",
	'rules_System_event_record_being_deleted'		=> "ActiveRecord: Record Being Deleted",
	'rules_System_event_record_being_deleted_record'	=> "Record that is going to be deleted",
	
	'rules_System_event_browser_output'			=> "Output is being sent to browser",
	'rules_System_event_browser_output_output'		=> "The content which is going to be output",
	'rules_System_event_browser_output_status'		=> "The http status code",
	'rules_System_event_browser_output_type'		=> "The http content type",
	'rules_System_event_browser_output_headers'		=> "An array containing the http headers",
	'rules_System_event_browser_output_docache'		=> "Boolean value indicating of this page should be cached",
	'rules_System_event_browser_output_iscache'		=> "Boolean value indicating if this page is from a cache",
	
	/*** SYSTEM: Conditions ***/
	'rules_System_conditions_execute_php'			=> "Custom PHP Code",
	'rules_System_custom_phpcode'				=> "PHP Code",
	
	'rules_System_conditions_board_status'			=> "Board Status (Online/Offline)",
	'rules_System_board_online_status'			=> "Check that the board is:",
	
	/*** SYSTEM: Actions ***/
	'rules_System_actions_send_email'			=> "Send an email",
	'rules_System_actions_send_email_recipients'		=> "Recipients",
	'rules_System_actions_send_email_recipients_desc'	=> "Members to send the email to",
	'rules_System_member_recipients'			=> "Members",
	'rules_System_actions_send_email_message'		=> "Message Content",
	'rules_System_actions_send_email_message_desc'		=> "The contents of the message you want to send",
	'rules_System_email_message'				=> "Email Message",
	
	'rules_System_actions_execute_php'			=> "Execute PHP Code",
	
	'rules_System_actions_display_message'			=> "Display Inline Message",
	'rules_System_actions_display_message_message'		=> "Message",
	'rules_System_actions_display_message_message_desc'	=> "The message to display inline",
	'rules_System_inline_message'				=> "Message",

	/**
	 * Comparisons 
	 *
	 * Rules Definitions
	 */
	'rules_Comparisons_type'				=> "Comparison Type",
	'rules_Comparisons_value'				=> "Value",
	
	/*** COMPARISONS: Conditions ***/	
	'rules_Comparisons_conditions_compare_numbers'		=> "Number Values",
	'rules_Comparisons_conditions_compare_numbers_number1'	=> "The First Number",
	'rules_Comparisons_number1'				=> "Number 1",
	'rules_Comparisons_conditions_compare_numbers_number2'	=> "The Second Number",
	'rules_Comparisons_number2'				=> "Number 2",
	
	'rules_Comparisons_conditions_compare_strings'		=> "String Values",
	'rules_Comparisons_conditions_compare_strings_string1'	=> "The First String",
	'rules_Comparisons_string1'				=> "String 1",
	'rules_Comparisons_conditions_compare_strings_string2'	=> "The Second String",
	'rules_Comparisons_string2'				=> "String 2",
	
	'rules_Comparisons_conditions_compare_truth'		=> "Truth Values",
	'rules_Comparisons_conditions_compare_truth_value'	=> "Value to Compare",
	
	'rules_Comparisons_conditions_compare_type'		=> "Value Type",
	'rules_Comparisons_conditions_compare_type_value'	=> "Value to Compare",
	
	'rules_Comparisons_conditions_compare_objects'		=> "Object Comparison",
	'rules_Comparisons_conditions_compare_objects_object'	=> "Object to Compare",
	'rules_Comparisons_conditions_compare_objects_value'	=> "Value to Compare Object",
	
	'rules_Comparisons_conditions_compare_array'		=> "Array Comparison",
	'rules_Comparisons_conditions_compare_array_array'	=> "Array to Compare",
	'rules_Comparisons_conditions_compare_array_value'	=> "Value to Use",
	
	/**
	 * Content
	 *
	 * Rules Definitions
	 */
	 
	/*** CONTENT: Events ***/
	'rules_Content_event_content_pinned'		=> "Content has been pinned",
	'rules_Content_event_content_pinned_item'	=> "The content object",
	'rules_Content_event_content_pinned_member'	=> "The member who pinned the content",
	
	'rules_Content_event_content_unpinned'		=> "Content has been unpinned",
	'rules_Content_event_content_unpinned_item'	=> "The content object",
	'rules_Content_event_content_unpinned_member'	=> "The member who unpinned the content",
	
	'rules_Content_event_content_featured'		=> "Content has been featured",
	'rules_Content_event_content_featured_item'	=> "The content object",
	'rules_Content_event_content_featured_member'	=> "The member who featured the content",
	
	'rules_Content_event_content_unfeatured'	=> "Content has been unfeatured",
	'rules_Content_event_content_unfeatured_item'	=> "The content object",
	'rules_Content_event_content_unfeatured_member'	=> "The member who unfeatured the content",
	
	'rules_Content_event_content_locked'		=> "Content has been locked",
	'rules_Content_event_content_locked_item'	=> "The content object",
	'rules_Content_event_content_locked_member'	=> "The member who locked the content",
	
	'rules_Content_event_content_unlocked'		=> "Content has been unlocked",
	'rules_Content_event_content_unlocked_item'	=> "The content object",
	'rules_Content_event_content_unlocked_member'	=> "The member who unlocked the content",
	
	'rules_Content_event_content_hidden'		=> "Content has been hidden",
	'rules_Content_event_content_hidden_item'	=> "The content object",
	'rules_Content_event_content_hidden_member'	=> "The member who hid the content",
	'rules_Content_event_content_hidden_reason'	=> "Reason given for hiding the content",
	
	'rules_Content_event_content_unhidden'		=> "Content has been unhidden",
	'rules_Content_event_content_unhidden_item'	=> "The content object",
	'rules_Content_event_content_unhidden_member'	=> "The member who unhid the content",
	
	'rules_Content_event_content_reported'		=> "Content has been reported",
	'rules_Content_event_content_reported_item'	=> "The content object",
	'rules_Content_event_content_reported_message'	=> "The member who reported the content",

	'rules_Content_event_content_author_changed'		=> "Content author has changed",
	'rules_Content_event_content_author_changed_item'	=> "The content object",
	'rules_Content_event_content_author_changed_oldMember'	=> "The member who was the old author",
	'rules_Content_event_content_author_changed_newMember'	=> "The member who is the new author",

	'rules_Content_event_content_deleted'			=> "Content has been deleted",
	'rules_Content_event_content_deleted_item'		=> "The content object",
	
	/*** CONTENT: Conditions ***/
	'rules_Content_conditions_content_type'			=> "Content is of a certain type",
	'rules_Content_conditions_content_type_content' 	=> "The Content to Check",
	'rules_Content_content_types'				=> "Content is one of these types",

	'rules_Content_conditions_content_status'		=> "Content has a particular status",
	'rules_Content_conditions_content_status_content' 	=> "The Content to Check",
	'rules_Content_content_statuses'			=> "Content has the following statuses",
	
	'rules_Content_conditions_content_author'		=> "Content has a particular author",
	'rules_Content_conditions_content_author_content'	=> "The Content to Check",
	'rules_Content_author_members'				=> "Content has one of the following authors",
	'rules_Content_author_members_desc'			=> "Type the names of authors you want to check the content for",
	
	/*** CONTENT: Actions ***/
	'rules_Content_member'					=> "Member to use",
	'rules_Content_member_desc'				=> "Leave empty to use the acting member",
	'rules_Content_reason'					=> "Reason for hiding content",
	
	'rules_Content_actions_pin_content'			=> "Pin content",
	'rules_Content_actions_pin_content_content'		=> "Content To Pin",
	
	'rules_Content_actions_unpin_content'			=> "Unpin content",
	'rules_Content_actions_unpin_content_content'		=> "Content To Unpin",
	
	'rules_Content_actions_feature_content'			=> "Feature content",
	'rules_Content_actions_feature_content_content'		=> "Content To Feature",
	
	'rules_Content_actions_unfeature_content'		=> "Unfeature content",
	'rules_Content_actions_unfeature_content_content'	=> "Content To Unfeature",
	
	'rules_Content_actions_lock_content'			=> "Lock content",
	'rules_Content_actions_lock_content_content'		=> "Content To Lock",
	
	'rules_Content_actions_unlock_content'			=> "Unlock content",
	'rules_Content_actions_unlock_content_content'		=> "Content To Unlock",
	
	'rules_Content_actions_hide_content'			=> "Hide content",
	'rules_Content_actions_hide_content_content'		=> "Content To Hide",
	'rules_Content_actions_hide_content_member'		=> "Member Responsible for Hiding Content",
	'rules_Content_actions_hide_content_reason'		=> "Reason For Hiding Content",
	
	'rules_Content_actions_unhide_content'			=> "Unhide content",
	'rules_Content_actions_unhide_content_content'		=> "Content to unhide",
	'rules_Content_actions_unhide_content_member'		=> "Member Responsible for Unhiding Content",
		
	/**
	 * Content Item
	 *
	 * Rules Definitions
	 */
	 
	/*** CONTENT ITEM: Events ***/
	'rules_ContentItems_event_content_item_created'			=> "Content item has been created",
	'rules_ContentItems_event_content_item_created_item'		=> "The content item",
	'rules_ContentItems_event_content_item_created_values'		=> "An array of values used to create the content item",
	'rules_ContentItems_event_content_item_created_container'	=> "The content item container",
	'rules_ContentItems_event_content_item_created_notification'	=> "A boolean value indicating whether notifications were sent",
	
	'rules_ContentItems_event_content_item_approved'		=> "Content item has been approved",
	'rules_ContentItems_event_content_item_approved_item'		=> "The content item",
	'rules_ContentItems_event_content_item_approved_member'		=> "The member who approved the content",
	
	'rules_ContentItems_event_content_item_published'		=> "Content item has been published",
	'rules_ContentItems_event_content_item_published_item'		=> "The content item",
	'rules_ContentItems_event_content_item_published_member'	=> "The member who published the content",
	
	'rules_ContentItems_event_content_item_unpublished'		=> "Content item has been unpublished",
	'rules_ContentItems_event_content_item_unpublished_item'	=> "The content item",
	'rules_ContentItems_event_content_item_unpublished_member'	=> "The member who unpublished the content",
	
	'rules_ContentItems_event_content_item_moved'			=> "Content item has been moved",
	'rules_ContentItems_event_content_item_moved_item'		=> "The content item",
	'rules_ContentItems_event_content_item_moved_oldContainer'	=> "The content item's old container",
	'rules_ContentItems_event_content_item_moved_container'		=> "The content item's new container",
	'rules_ContentItems_event_content_item_moved_link'		=> "Boolean value indicating if a link was left in the old container",
	 
	'rules_ContentItems_event_content_item_merging'			=> "Content item is being merged with others",
	'rules_ContentItems_event_content_item_merging_item'		=> "The content item",
	'rules_ContentItems_event_content_item_merging_items'		=> "Array of content items being merged in",
	 
	/**
	 * Content Comment
	 *
	 * Rules Definitions
	 */
	'rules_Comments_event_content_comment_created'			=> "Content comment/review has been created",
	'rules_Comments_event_content_comment_created_comment'		=> "The content comment/review item",
	'rules_Comments_event_content_comment_created_item'		=> "The content item the comment belongs to",
	'rules_Comments_event_content_comment_created_content'		=> "The comment content (text)",
	'rules_Comments_event_content_comment_created_first'		=> "Boolean value indicating if the comment is the first one",
	'rules_Comments_event_content_comment_created_guestName'	=> "Name to use for guest authors",
	'rules_Comments_event_content_comment_created_increment'	=> "Boolean value indicating if post count has increased",
	'rules_Comments_event_content_comment_created_member'		=> "The member who created the comment",
	'rules_Comments_event_content_comment_created_time'		=> "A Date/Time object for when the comment was created",
	 	
	'rules_Comments_event_content_comment_moved'			=> "Content comment has been moved",
	'rules_Comments_event_content_comment_moved_comment'		=> "The comment item",
	'rules_Comments_event_content_comment_moved_item'		=> "The content item the comment belongs to",
	 
	/**
	 * Forums
	 */
	
	/*** FORUMS: Events ***/
	'rules_Forums_event_saved_action'				=> "Saved Action: %s",
	'rules_Forums_event_saved_action_action'			=> "The saved action",
	'rules_Forums_event_saved_action_topic'				=> "The topic the saved action was used on",
	'rules_Forums_event_saved_action_member'			=> "The member using the saved action",
	 
	'time' => "Time",
	'result' => "Result",
);
