<?php

$lang = array(

	'__app_rules'				=> "Rules",
	'menu__rules_rules'			=> "Rules",
	'menu__rules_rules_rulesets'		=> "Manage Rules",
	'menu__rules_logs'			=> "Rules Logs",
	'menu__rules_rules_logs'		=> "Rules Logs",
	'menu__rules_system'			=> "System",
	'menu__rules_rules_custom'		=> "Custom Actions",
	'menu__rules_rules_data'		=> "Custom Data Fields",
	'menu__rules_rules_customlogs'		=> "Custom Logs",
	'menu__rules_rules_schedule'		=> "Scheduled Actions",
	'ext__Definitions'			=> "Events, Conditions, and Actions Definition Groups",
	'ext__Conversions'			=> "Global Arguments and Object Conversion Mapping",
	'module__rules_system'			=> "Rules System",
	'module__rules_logs'			=> "Custom Logs",
	
	'notifications__rules_Notification'	=> "Rules Notifications",
	'notifications__rules_notifications'	=> "Automated Notifications",
	'member__rules_RulesMember'		=> "Rules Data",
	'rules_perm__label'			=> "Data Permissions",
	'rules_perm__view'			=> "View Data",
	'rules_perm__edit'			=> "Edit Data",
	'rules_log_perm__label'			=> "Log Permissions",
	'rules_log_perm__view'			=> "View Logs",
	'rules_log_perm__delete'		=> "Delete Logs",
	'rules_log_data_perm__label'		=> "Data Permissions",
	'rules_log_data_perm__view'		=> "View Logged Data",
	
	'editor__rules_Generic'			=> "Rules Generic",
	
	// Rules Restrictions
	'r__rules'				=> "Rules Restrictions",
	'r__rules_manage'			=> "Manage Rules",
	'r__logs_manage'			=> "View Logs",
	'r__logs'				=> "Logs Restrictions",
	'rules_logs'				=> "Rules Logs",
	'rules_flush_logs'			=> "Flush System Log",
	'rules_logs_flushed'			=> "System log has been flushed!",
	'rules_prune_logs'			=> "Prune Custom Logs",
	'rules_logs_pruned'			=> "Custom logs have been pruned!",
	'rules_flush_custom_log'		=> "Flush Log Entries",
	'rules_flush_confirm'			=> "This will flush all entries for this log. Are you sure you want to continue?",
	'rules_view_custom_log'			=> "View Log Entries",
	'custom_log_flushed'			=> "Log has been flushed!",
	'rules_export_all'			=> "Export All",
	'rules_export_enabled'			=> "Export Enabled",
	'rules_manage_all_rules'		=> "Manage Rules",
	'rules_manage_rule'			=> "Back To Rule",
	
	'rules_overview'			=> "Rules Overview",
	'rules_welcome'				=> "Welcome To Rules",
	'rules_documentation'			=> "Documentation",
	'rules_system_log'			=> "System Log",
	
	'rules_logs_table_id'			=> "Log ID",
	'rules_logs_table_app'			=> "Event",
	'rules_logs_table_key'			=> "Rule",
	'rules_logs_table_type'			=> "Type",
	'rules_logs_table_message'		=> "Status",
	'rules_logs_table_result'		=> "Result",
	'rules_logs_table_time'			=> "Time",
	
	'rules_log_lang_time'			=> "Log Time",
	'rules_log_lang_message'		=> "Log Message",
	'rules_log_lang_entity_id'		=> "Associated With",
	
	'custom_data'				=> "Custom Data Fields",
	'data_name'				=> "Custom Data Title",
	'data_description'			=> "Description",
	'data_column_name'			=> "Data Key",
	'data_column_name_desc'			=> "<div class='ipsMessage ipsMessage_warning' >
							The data key can only be lowercase alphanumerics (including underscores).<br>
							It is used to access this data from the host object. <em>Example: \$value = \$object->getRulesData( 'key' );</em><br>
							<strong>Note:</strong> If you change this key, rules that use this data will still work. <br>
							However, any token replacements or custom code which use the changed key will break.
						   </div>",
						   
	'data_class'				=> "Data Associated With",
	'data_type'				=> "Stored Data Type",
	'data_type_class'			=> "Stored Data",
	'data_custom_class'			=> "Custom Classname",
	'data_display_mode'			=> "Field Display Mode",
	'data_display_mode_desc'		=> "Automatic display will output the field data on the front end where it is appropriate ( inline with content, on member profile, etc. ). Manual will require you to use template tags in your theme to display field data. Note: template tags can still be used in automatic display mode.",
	'data_use_mode'				=> "Field Editing Mode",
	'data_use_mode_desc'			=> "Public mode will allow the field to be edited by any member with permission. Administrative mode will limit editing of the field to moderators with permission to moderate the item the data is associated with.",
	'data_tab'				=> "Form Tab Name",
	'data_tab_desc'				=> "Enter the name of the tab that this field should appear under on editing forms",
	'data_required'				=> "Is this data required?",
	'data_text_mode' 			=> "Data Input Control",
	'data_value_options'			=> "Option Values",
	'data_value_default'			=> "Default Value",
	'rules_profiledata_title'		=> "Profile Data",
	
	'rules_numeric_math_operation'		=> "Value update method",
	'rules_array_operation'			=> "Array update method",
	
	'rules_node_data_tab'			=> "Rules Data",
	'rules_profile_data_header'		=> "Custom Profile Data",
	
	'data_column_not_unique'		=> "This key is already being used for another field",
	'rules_data_column_invalid'		=> "Your data key contains invalid characters. Only lowercase alphanumerics (including underscores) are allowed.",
	
	'rules_fixed_arguments'			=> "Fixed Arguments",
	'rules_bulk_options'			=> "Bulk Processing Option",
	'rules_repeats'				=> "Repeats Every",
	'rules_scheduled_actions'		=> "Scheduled Actions",
	'rules_schedule_custom_action'		=> "Custom Action Schedule",
	'rules_schedule_custom_now'		=> "Schedule Manually",
	'rules_schedule_custom_frequency'	=> "Action Frequency",
	'rules_schedule_custom_bulk'		=> "Bulk Process",
	'rules_schedule_custom_bulk_desc'	=> "If you select a bulk process argument, this action will be run repeatedly until every associated record in the system has been used as the argument.",
	'rules_schedule_bulk_limit'		=> "Batch Limit",
	'rules_schedule_bulk_limit_desc'	=> "Bulk processing is processed in batches so that large runs can be handled reliably.",
	'custom_actions'			=> "Custom Actions",
	'custom_action_arguments'		=> "Action Arguments",
	'custom_action_title'			=> "Custom Action Name",
	'custom_action_description'		=> "Description",
	'custom_actions_add_child'		=> "Add Action Argument",
	'rules_tokens_description'		=> "You can type the names of replacement tokens (including the brackets) into text entry fields on this form and they will be replaced by their associated data when the rule is executed.<br><i class='fa fa-arrow-right'></i> Alternative token format: Replace the brackets with tildes ( ~ ) ( Example: ~token:name~ ) for use in places where brackets are problematic (such as urls).",
	'custom_logs'				=> "Custom Logs",
	'custom_log_arguments'			=> "Log Arguments",
	'custom_log_title'			=> "Custom Log Name",
	'custom_log_description'		=> "Log Description",
	'custom_log_class'			=> "Log Associated With",
	'custom_logs_add_child'			=> "Add Loggable Argument",
	'rules_custom_log_message'		=> "Log Message",
	'rules_custom_log_entity'		=> "Associated Entity",
	'custom_log_options'			=> "Log Options",
	'custom_log_max_logs'			=> "Maximum Log Size",
	'custom_log_max_logs_desc'		=> "Once the log reaches the maximum size, old log entries will be discarded as new entries are added.",
	'custom_log_entity_max'			=> "Maximum Logs Per Entity",
	'custom_log_entity_max_desc'		=> "If the maximum number of entries is reached for any specific entity, older logs for that entity will be discarded as new ones are added.",
	'custom_log_max_age'			=> "Maximum Log Age",
	'custom_log_max_age_desc'		=> "Log entries older than the specified number of days will be automatically discarded.",
	'custom_log_limit'			=> "Logs Per Page",
	'custom_log_limit_desc'			=> "Choose the number of logs that should show per page when this log is displayed.",
	'custom_log_display_empty'		=> "Display When Empty",
	'custom_log_display_empty_desc'		=> "If selected, this log will display even if it has no entries.",
	'custom_log_sortby'			=> "Default Sort Column",
	'custom_log_sortdir'			=> "Default Sort Direction",
	'custom_log_logtime'			=> "Log Time",
	'custom_log_display_time'		=> "Show Log Time",
	'custom_log_display_time_desc'		=> "This setting controls whether the log time should be displayed in the log table.",
	'custom_log_lang_time'			=> "Log Time Title",
	'custom_log_lang_time_desc'		=> "This language will be used for the log time column title.",
	'custom_log_lang_message'		=> "Log Message Title",
	'custom_log_lang_message_desc'		=> "This language will be used for the log message column title.",
	'profile_rules_ProfileLogs'		=> "Logged Activity",
	
	'argument_name'				=> "Argument Name",
	'argument_name_desc'			=> "Human Friendly Name (i.e. Email Recipient)",
	'argument_varname'			=> "Machine Name",
	'argument_varname_desc'			=> "The machine name can only be lowercase alphanumerics (including underscores). It will be used as a variable name in rules configurations.",
	'argument_type'				=> "Argument Type",
	'argument_class'			=> "Value Type",
	'argument_custom_class'			=> "Custom Object Classname",
	'argument_custom_class_desc'		=> "Enter the classname of the object",
	'argument_required'			=> "Required",
	'argument_required_desc'		=> "Select if this argument must be provided.",
	'argument_description'			=> "Description",
	'argument_description_desc'		=> "Describe what this argument is for. (ex: Member to send the email to )",
	
	'argument_not_unique'			=> "This machine name is already being used",
	'argument_not_unique_to_class'		=> "This machine name is already being used by another parent",
	'argument_name_invalid'			=> "This machine name contains invalid characters.  Only lowercase alphanumerics (including underscores) are allowed.",
	
	// Form Generic
	'rulesets'				=> "Rules",
	'ruleset_title'				=> "Ruleset Title",
	'rulesets_add_child'			=> "Add Rule to Ruleset",
	'rules_add_rule'			=> "Add New Rule",
	'rulesets_add'				=> "Create New Rule Set",
	'rule_ruleset_id'			=> "Associated Rule Set",
	'rule_no_ruleset'			=> "Independent Rule (not part of a rule set)",
	'ruleset_description'			=> "Rule Set Description",
	'ruleset_creator'			=> "Created By",
	'action_schedule_mode'			=> "Action should be executed",
	'action_schedule_mode_desc'		=> "Immediate actions are taken before other rules on the same event are evaluated.<br>Actions executed at the end of the event allow actions to queue while other rules on the same event are tested.<br>Actions selected to execute at the end of the page load will queue until all events on the page have finished.<br>Actions selected to happen at a future time will be queued and executed via cron.",
	'rules_action_execution_now'		=> "Immediately",
	'rules_action_execution_defer'		=> "At the end of the event (default)",
	'rules_action_execution_end'		=> "At the end of the page load",
	'rules_action_execution_future'		=> "Fixed amount of time in the future",
	'rules_action_execution_date'		=> "A specific date in the future",
	'rules_action_execution_custom'		=> "A calculated date and time",
	'action_schedule_minutes'		=> "Minutes",
	'action_schedule_hours'			=> "Hours",
	'action_schedule_days'			=> "Days",
	'action_schedule_months'		=> "Months",
	'action_schedule_date'			=> "Date / Time",
	'action_schedule_customcode'		=> "PHP Code",
	'action_schedule_customcode__desc'	=> "<ul><li><strong>object</strong>: An \IPS\DateTime Object</li><li><strong>int</strong>: A unix timestamp</li><li><strong>string</strong>: A date/time string</li></ul>",
	'action_schedule_key'			=> "Unique Scheduling Keyphrase",
	'action_schedule_key_desc'		=> "Optional. Only one action will remain scheduled for any given keyphrase at a time. If an action is rescheduled, any previously scheduled actions with the same keyphrase will be removed.<br><strong>Note:</strong> Token replacements can be used in your keyphrase.",
	'action_else'				=> "Action Mode",
	'rules_action_mode_standard'		=> "Standard - Perform when conditions on the rule <strong>ARE MET</strong>.",
	'rules_action_mode_else'		=> "Else - Perform when conditions on the rule <strong>ARE NOT MET</strong>.",

	'rules_editing_scheduled_action'	=> "Editing Scheduled Action",
	'rules_scheduled_date'			=> "Date / Time Scheduled",
	'rules_scheduled_action_updated'	=> "Scheduled Action Updated",
	'rules_scheduled_action_executed'	=> "Scheduled Action Executed",
	'rules_scheduled_action_deleted'	=> "Scheduled Action Deleted",
	
	'rules_confirm_delete'			=> "Confirm Delete",
	'rules_execute'				=> "Execute Now",
	'rules_unlock_action'			=> "Unlock Action",
	'rules_reset_bulk'			=> "Reset Bulk Action",
	'rules_view_debug'			=> "View Debug Log",
	
	'rule'					=> "Rule",
	'rules'					=> "Rules",
	'conditions'				=> "Conditions",
	'actions'				=> "Actions",
	'source'				=> "Source",
	'phpcode' 				=> "PHP Code",
	'phpcode_desc'				=> "<strong>Warning</strong>: PHP is for advanced users only. Your php code needs to return a value to use as the argument. Do not include a &lt;?php tag at the beginning of your code, or comment it out if you do.",
	'phpcode_desc_details'			=> "The operation expects you to return one of the following argument types:",
	'phpcode_desc_details_vars'		=> "The following variables are available to your php code:",
	'use_event_argument'			=> "Data To Use",
	'use_event_argument_default'		=> "Use An Alternative Value?",
	'use_event_argument_default_desc'	=> "If the event data for this argument is empty, an alternative value can be used.",

	// Node Form
	'rule_debug'				=> "Debug This Rule",
	'debug_on_badge'			=> "<i class='fa fa-bug'></i> Debug Mode",
	'rule_event_missing_badge'		=> "<i class='fa fa-warning'></i> Event Missing",
	'rule_debug_desc'			=> "If enabled, information related to this rule will be logged in the debug console.",
	'rules_settings'			=> "Settings",
	'rules_conditions'			=> "Conditions",
	'rules_actions'				=> "Actions",
	'rules_actions_else'			=> "Else Actions",
	'rules_actions_else_description'	=> "These actions will be taken when the conditions on the rule ARE NOT MET.",
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
	'condition_not'				=> "NOT",
	'condition_not_desc'			=> "Using NOT will reverse the condition result so that the result is TRUE if the condition is NOT MET.",
	'rules_event_argument_optional_notice'	=> "Rules has determined that this data may sometimes be empty. This argument is optional for the operation. However, you may still provide an alternative value to use in case it is not provided by the event.",
	'rules_event_argument_required_notice'	=> "Rules has determined that this data may sometimes be empty. This argument is REQUIRED for the operation to work. If you do not provide an alternative value, this operation will be skipped if the event data is missing.",
	'conditions_add_child'			=> "Add Sub Condition",
	'or_group_badge'			=> "OR ANY SUBCONDITION",
	'and_group_badge'			=> "AND ALL SUBCONDITIONS",
	'rules_export_rule'			=> "Export Rule",
	'rules_export_rule_group'		=> "Export Rule Group",
	'rules_export_rule_set'			=> "Export Rule Set",
	'rules_import'				=> "Import Rules",
	'rules_imported'			=> "Rules Successfully Imported",
	'rule_missing_notice'			=> "This rule was designed for an event that cannot currently be triggered on your system. Configuration is disabled.",
	'rules_next'				=> "Next <i class='fa fa-caret-right'></i>",
	
	'rule_title'				=> "Rule Title",
	'rule_title_placeholder'		=> "Describe what this rule is for",
	
	'rules_debug_console'			=> "Debug Console",
	'rules_conditions_table_op_id'		=> "Condition Title",
	'rules_conditions_table_message'	=> "Status",
	'rules_conditions_table_result'		=> "Result",
	'rules_actions_table_op_id'		=> "Action Title",
	'rules_actions_table_message'		=> "Status",
	'rules_actions_table_result'		=> "Result",
	'rules_actions_table_time'		=> "Time",
	'rules_subrules_table_rule_id'		=> "Rule Name",
	'rules_subrules_table_message'		=> "Status",
	'rules_subrules_table_result'		=> "Result",
	'rules_view_overview'			=> "Display Overview",
	
	'rules_scheduled_schedule_time'		=> "Scheduled Date",
	'rules_scheduled_schedule_action_id'	=> "Scheduled Action",
	'rules_scheduled_schedule_unique_key'	=> "Keyphrase",
	'rules_scheduled_schedule_thread'	=> "Scheduled By",
	'rules_scheduled_schedule_parent'	=> "Status",
	'rules_scheduled_schedule_created'	=> "Created On",
	'schedule_pending'			=> "Pending",
	'schedule_locked'			=> "Unresponsive",
	'schedule_running'			=> "Running",
	'schedule_filter_automated'		=> "Automated",
	'schedule_filter_manual'		=> "Manual",
	'schedule_filter_keyphrases'		=> "Keyphrased",
	'rules_onetime'				=> "One Time",
	'rules_recurring'			=> "Recurring",
	'rules_bulk'				=> "Bulk",
	
	'rules_choose_member'			=> "Type A Member Name",
	'rules_choose_member2'			=> "Type A Member Name",
	'rules_choose_members'			=> "Type Member Names",
	'rules_choose_members2'			=> "Type Member Names",
	
	'rules_choose_item'			=> "Type An Item Name",
	'rules_choose_item2'			=> "Type An Item Name",
	'rules_choose_items'			=> "Type Item Names",
	'rules_choose_items2' 			=> "Type Item Names",
	
	// Conditions
	'condition_title'			=> "Condition Name",
	
	// Operations ( Conditions / Actions )
	'actions_title'				=> "Action Title",
	'rule_actions_selection'		=> "Action to perform:",
	'conditions_title'			=> "Condition Title",
	'rule_conditions_selection'		=> "Condition to check",
	'operation_arg_source_event'		=> "Event / Global Data",
	'operation_arg_source_manual'		=> "Manual Configuration",
	'operation_arg_source_phpcode'		=> "PHP Code",
	
	// Errors
	'invalid_rule'				=> "Invalid Rule",
	'rules_event_not_found'			=> "Rules Event Not Found",
	
	// Global Arguments
	'__global_site_settings'		=> "Global: Site Settings",
	'__global_logged_in_member'		=> "Global: Logged in member",
	'__global_current_time'			=> "Global: Current time",
	'__global_request_url'			=> "Global: Current page url",
	
	
	/**
	 * Members 
	 *
	 * Rules Definitions
	 */
	 
	/*** MEMBERS: Events ***/
	'rules_Members_event_memberSync_onLogin'		=> "Member logged in to system",
	'rules_Members_event_memberSync_onLogin_member'		=> "Member that logged in",
	
	'rules_Members_event_memberSync_onLogout'		=> "Member logged out of system",
	'rules_Members_event_memberSync_onLogout_member'	=> "Member that logged out",
	
	'rules_Members_event_memberSync_onCreateAccount'	=> "Member account is created",
	'rules_Members_event_memberSync_onCreateAccount_member'	=> "Member that was created",
	
	'rules_Members_event_memberSync_onProfileUpdate'	=> "Member profile is updated",
	'rules_Members_event_memberSync_onProfileUpdate_member'	=> "Member that was updated",
	'rules_Members_event_memberSync_onProfileUpdate_changed'=> "Array of profile fields that have changed",
	
	'rules_Members_event_memberSync_onSetAsSpammer'		=> "Member is flagged as spammer",
	'rules_Members_event_memberSync_onSetAsSpammer_member'	=> "Member that was flagged as spammer",
	
	'rules_Members_event_member_not_spammer'		=> "Member is unflagged as spammer",
	'rules_Members_event_member_not_spammer_member'		=> "Member that was unflagged as spammer",
	
	'rules_Members_event_member_banned'			=> "Member is banned",
	'rules_Members_event_member_banned_member'		=> "Member that was banned",
	
	'rules_Members_event_member_unbanned'			=> "Member is unbanned",
	'rules_Members_event_member_unbanned_member'		=> "Member that was unbanned",
	
	'rules_Members_event_memberSync_onValidate'		=> "Member account is validated",
	'rules_Members_event_memberSync_onValidate_member'	=> "Member that was validated",
	
	'rules_Members_event_memberSync_onMerge'		=> "Member accounts are merged together",
	'rules_Members_event_memberSync_onMerge_member'		=> "Member that is being deleted",
	'rules_Members_event_memberSync_onMerge_mergedMember'	=> "Member that is being kept",
	
	'rules_Members_event_memberSync_onDelete'		=> "Member account is deleted",
	'rules_Members_event_memberSync_onDelete_member'	=> "Member whose account was deleted",
	
	'rules_Members_event_reputation_given'			=> "Member receives reputation points",
	'rules_Members_event_reputation_given_member'		=> "Member that received the reputation",
	'rules_Members_event_reputation_given_giver'		=> "Member that gave the reputation",
	'rules_Members_event_reputation_given_content'		=> "Content the reputation was given from",
	'rules_Members_event_reputation_given_reptype'		=> "Type of reputation given (1 or -1)",
	
	'rules_Members_event_member_warned'			=> "Member has been given a warning",
	'rules_Members_event_member_warned_warning'		=> "The warning content item",
	'rules_Members_event_member_warned_member'		=> "Member that was warned",
	'rules_Members_event_member_warned_moderator'		=> "Member that issued the warning",
	
	'rules_Members_event_content_recounted'			=> "Member content has been re-counted",
	'rules_Members_event_content_recounted_member'		=> "Member whose content has been re-counted",
	'rules_Members_event_content_recounted_count'		=> "The updated content count",
	
	'rules_Members_event_profile_visit'			=> "A member visit is logged to a members profile",
	'rules_Members_event_profile_visit_member'		=> "Member whose profile was visited",
	'rules_Members_event_profile_visit_visitor'		=> "Member who visited the profile",
	
	/*** MEMBERS: Conditions ***/
	'rules_Members_conditions_check_member'			=> "Check for a specific member or members",
	'rules_Members_conditions_check_member_member'		=> "Member To Check",
	
	'rules_Members_conditions_member_has_group'		=> "Member is in a certain member group",
	'rules_Members_conditions_member_has_group_member'	=> "Member To Check",
	'rules_Members_member_groups'				=> "Check if member is in any of these groups",
	
	'rules_Members_conditions_member_attributes'		=> "Member attribute values",
	'rules_Members_conditions_member_attributes_member'	=> "Member To Check",
	'rules_Members_member_attribute'			=> "Attribute to check",
	'rules_Members_attribute_compare_type_value'		=> "Is",
	'rules_Members_attribute_compare_type_date'		=> "Is",
	'rules_Members_attribute_compare_value'			=> "Value",
	'rules_Members_attribute_compare_date'			=> "Date",
	'rules_member_attribute_photo'				=> "Has Photo",
	'rules_member_attribute_signature'			=> "Has Signature",
	'rules_member_attribute_followers'			=> "Followers",
	'rules_member_attribute_reputation'			=> "Reputation Level",
	'rules_member_attribute_profile_views'			=> "Profile Views",
	'rules_member_attribute_posts'				=> "Content Count",
	'rules_member_attribute_joined'				=> "Joined Date",
	'rules_member_attribute_birthday'			=> "Birthday",
	'rules_member_attribute_birthdate'			=> "Birthdate",
	'rules_member_attribute_last_activity'			=> "Last Activity",
	'rules_member_attribute_last_post'			=> "Last Post",
	'rules_Member_attribute_compare_type'			=> "Comparison type",
	'rules_Members_attribute_compare_date'			=> "Date",
	'rules_Members_attribute_compare_minutes'		=> "Minutes",
	'rules_Members_attribute_compare_hours'			=> "Hours",
	'rules_Members_attribute_compare_days'			=> "Days",
	'rules_Members_attribute_compare_months'		=> "Months",
	'rules_Members_attribute_compare_years'			=> "Years",
	
	'rules_Members_conditions_member_status'		=> "Member has a particular status",
	'rules_Members_conditions_member_status_member'		=> "Member To Check",
	'rules_Members_member_status'				=> "Member has the following status",
	'rules_member_validating'				=> "Validating",
	'rules_member_spammer'					=> "Marked as Spammer",
	'rules_member_banned_temp'				=> "Banned: Temporarily",
	'rules_member_banned_perm'				=> "Banned: Permanently",
	'rules_member_warnlevel'				=> "Has Warnings",
	'rules_member_online'					=> "Member is Online",
	
	'rules_Members_conditions_member_following'		=> "Member is following another member",
	'rules_Members_conditions_member_following_member'	=> "Member Who Is Following",
	'rules_Members_conditions_member_following_member2'	=> "Member Being Followed",
	
	'rules_Members_conditions_member_ignoring'		=> "Member is ignoring another member",
	'rules_Members_conditions_member_ignoring_member'	=> "Member Who Is Ignoring",
	'rules_Members_conditions_member_ignoring_member2'	=> "Member Being Ignored",
	'rules_Members_ignore_type'				=> "Ignoring What?",
		
	/*** MEMBERS: Actions ***/
	'rules_Members_actions_modify_posts'			=> "Modify the post count of a member",
	'rules_Members_actions_modify_posts_member'		=> "Modify Posts For This Member",
	'rules_Members_actions_modify_posts_value'		=> "Value to modify posts count by",
	
	'rules_Members_actions_change_primary_group'		=> "Change the primary group of a member",
	'rules_Members_actions_change_primary_group_member'	=> "Member To Change Primary Group For",
	'rules_Members_member_primary_group'			=> "Primary Group",
	
	'rules_Members_actions_add_secondary_groups'		=> "Add secondary group(s) to member",
	'rules_Members_actions_add_secondary_groups_member'	=> "Member To Add Secondary Groups To",
	'rules_Members_member_secondary_groups_add'		=> "Add Secondary Groups",
	'rules_Members_member_secondary_groups_add_desc'	=> "Any groups selected here will be added to the members secondary groups, preserving any existing secondary groups they may be assigned to.",
	
	'rules_Members_actions_remove_secondary_groups'		=> "Remove secondary group(s) from member",
	'rules_Members_actions_remove_secondary_groups_member'	=> "Member To Remove Secondary Groups From",
	'rules_Members_member_secondary_groups_remove'		=> "Remove Secondary Groups",
	'rules_Members_member_secondary_groups_remove_desc'	=> "Any groups selected here will be removed from the members secondary groups, preserving any other secondary groups they may be assigned to.",
	
	'rules_Members_actions_set_secondary_groups'		=> "Set secondary group(s) on member",
	'rules_Members_actions_set_secondary_groups_member'	=> "Member To Set Secondary Groups For",
	'rules_Members_member_secondary_groups_set'		=> "Set Secondary Groups",
	'rules_Members_member_secondary_groups_set_desc'	=> "Any groups selected here will become the only secondary groups assigned to the member.",
	
	'rules_Members_actions_change_member_title'		=> "Change the title of a member",
	'rules_Members_actions_change_member_title_member'	=> "Member To Change Title",
	'rules_Members_actions_change_member_title_title'	=> "The New Member Title",

	'rules_Members_actions_flag_spammer'			=> "Flag a member as spammer",
	'rules_Members_actions_flag_spammer_member'		=> "Member To Flag As Spammer",
	
	'rules_Members_actions_unflag_spammer'			=> "Unflag a member as spammer",
	'rules_Members_actions_unflag_spammer_member'		=> "Member To Unflag As Spammer",

	'rules_Members_actions_ban_member'			=> "Ban a member from the site",
	'rules_Members_actions_ban_member_member'		=> "Member To Ban",
	'rules_Members_ban_setting'				=> "Type of ban",
	'rules_ban_permanent'					=> "Permanent ban",
	'rules_ban_temporary'					=> "Temporary ban",
	'rules_Members_ban_setting_minutes'			=> "Minutes",
	'rules_Members_ban_setting_hours'			=> "Hours",
	'rules_Members_ban_setting_days'			=> "Days",
	'rules_Members_ban_setting_months'			=> "Months",
	
	'rules_Members_actions_unban_member'			=> "Unban a member from the site",
	'rules_Members_actions_unban_member_member'		=> "Member To Unban",

	'rules_Members_actions_prune_member'			=> "Prune a member",
	'rules_Members_actions_prune_member_member'		=> "Member To Prune",
	
	/**
	 * System 
	 * 
	 * Rules Definitions 
	 */

	/*** SYSTEM: Events ***/	
	'rules_System_event_record_updated'			=> "ActiveRecord: Database record created/updated",
	'rules_System_event_record_updated_record'		=> "Record that was created/updated",
	'rules_System_event_record_updated_changed'		=> "An array of the values which have changed",
	'rules_System_event_record_updated_new'			=> "A boolean value indicating if the record is new",
	
	'rules_System_event_record_deleted'			=> "ActiveRecord: Database record deleted",
	'rules_System_event_record_deleted_record'		=> "Record that was deleted",
	
	'rules_System_event_browser_output'			=> "Output is being sent to browser",
	'rules_System_event_browser_output_output'		=> "The content which is going to be output",
	'rules_System_event_browser_output_status'		=> "The http status code",
	'rules_System_event_browser_output_type'		=> "The http content type",
	'rules_System_event_browser_output_headers'		=> "An array containing the http headers",
	'rules_System_event_browser_output_docache'		=> "Boolean value indicating of this page should be cached",
	'rules_System_event_browser_output_iscache'		=> "Boolean value indicating if this page is from a cache",
	
	/*** SYSTEM: Conditions ***/	
	'rules_System_conditions_compare_numbers'		=> "Number Value",
	'rules_System_conditions_compare_numbers_number1'	=> "The First Number",
	'rules_Comparisons_number1'				=> "Number Value",
	'rules_System_conditions_compare_numbers_number2'	=> "The Second Number",
	'rules_Comparisons_number2'				=> "Number Value",
	
	'rules_System_conditions_compare_strings'		=> "String Value",
	'rules_System_conditions_compare_strings_string1'	=> "The First String",
	'rules_Comparisons_string1'				=> "String Value",
	'rules_System_conditions_compare_strings_string2'	=> "The Second String",
	'rules_Comparisons_string2'				=> "String Value",
	
	'rules_System_conditions_compare_truth'			=> "Truth Value",
	'rules_System_conditions_compare_truth_value'		=> "Value to Compare",
	
	'rules_System_conditions_compare_type'			=> "Value Type",
	'rules_System_conditions_compare_type_value'		=> "Value to Compare",
	
	'rules_System_conditions_compare_dates'			=> "Compare Dates",
	'rules_System_conditions_compare_dates_date1'		=> "The First Date",
	'rules_System_conditions_compare_dates_date2'		=> "The Second Date",
	'rules_Comparisons_date1'				=> "Date",
	'rules_Comparisons_date2'				=> "Date",
	
	'rules_System_conditions_compare_objects'		=> "Compare Objects",
	'rules_System_conditions_compare_objects_object'	=> "Object to Compare",
	'rules_System_conditions_compare_objects_value'		=> "Value to Compare Object",
	
	'rules_System_conditions_compare_array'			=> "Array Value",
	'rules_System_conditions_compare_array_array'		=> "Array to Compare",
	'rules_System_conditions_compare_array_value'		=> "Value to Use",
	
	'rules_System_conditions_execute_php'			=> "Custom PHP Code",
	'rules_System_custom_phpcode'				=> "PHP Code",
	
	'rules_System_conditions_board_status'			=> "Board Status (Online/Offline)",
	'rules_System_board_online_status'			=> "Check that the board is:",
	
	'rules_System_conditions_scheduled_action'		=> "Check if action is scheduled",
	'rules_System_conditions_scheduled_action_key'		=> "Action Keyphrase To Check",
	'rules_System_schedule_mode'				=> "Keyphrase Matching",
	'rules_System_schedule_key'				=> "Keyphrase",
	'rules_System_schedule_key_desc'			=> "Enter a keyphrase that you want to check has an existing scheduled action.",
	
	/*** SYSTEM: Actions ***/
	'rules_System_actions_send_email'			=> "Send an email",
	'rules_System_actions_send_email_recipients'		=> "Recipients",
	'rules_System_actions_send_email_recipients_desc'	=> "Members to send the email to",
	'rules_System_actions_send_email_bcc_recipients'	=> "Blind Carbon Copy Recipients",
	'rules_System_actions_send_email_bcc_recipients_desc'	=> "Choose additional members to recieve a bcc.",
	'rules_System_actions_send_email_subject'		=> "Email Subject Line",
	'rules_System_actions_send_email_message'		=> "Message Content",
	'rules_System_actions_send_email_message_desc'		=> "The contents of the message you want to send",
	'rules_System_email_message'				=> "Email Message",
	'rules_System_email_message_desc'			=> "Enter the content for the email message. HTML is allowed.",
	
	'rules_System_message_subject'				=> "Subject",
	'rules_System_message_body'				=> "Message Content",
	
	'rules_System_actions_execute_php'			=> "Execute PHP Code",
	'rules_System_actions_unschedule_action'		=> "Delete a scheduled action",
	'rules_System_actions_unschedule_action_key'		=> "Action Keyphrase To Unschedule",
	'rules_System_unschedule_mode'				=> "Keyphrase Matching",
	'rules_System_unschedule_key'				=> "Keyphrase",
	'rules_System_unschedule_key_desc'			=> "Enter the unique keyphrase of the action you want to unschedule. If it is scheduled, it will be deleted. If it isn't already scheduled, then no action will be taken.",
	
	'rules_System_actions_display_message'			=> "Display Inline Message",
	'rules_System_actions_display_message_message'		=> "Message",
	'rules_System_actions_display_message_message_desc'	=> "The message to display inline",
	'rules_System_inline_message'				=> "Message",

	'rules_System_actions_url_redirect'			=> "Redirect to another page",
	'rules_System_actions_url_redirect_url'			=> "URL of page to redirect to",
	'rules_System_actions_url_redirect_message'		=> "The message to display upon redirect",
	'rules_System_redirect_message'				=> "Message",

	'rules_System_actions_create_conversation'		=> "Create a new private conversation",
	'rules_System_actions_create_conversation_creator'	=> "Conversation Creator",
	'rules_System_actions_create_conversation_participants' => "Conversation Participants",
	'rules_System_actions_create_conversation_subject'	=> "Conversation Subject",
	'rules_System_actions_create_conversation_message'	=> "Conversation Message Body",
	'rules_participation_mode'				=> "Conversation Participation Mode",
	'rules_participation_all'				=> "All recipients will be participants in the same conversation",
	'rules_participation_individual'			=> "A separate conversation will be created with each participant",
	'rules_conversation_join_creator'			=> "Join creator in the conversation",
	'rules_conversation_join_creator_desc'			=> "If you do not join the creator, the creator will not see the conversation or replies to it.",
	
	'rules_System_message_subject'				=> "Subject",
	'rules_System_message_body'				=> "Content",
	
	'rules_System_actions_create_notification'		=> "Create a new member notification",
	'rules_System_actions_create_notification_recipients'	=> "Notification Recipients",
	'rules_System_actions_create_notification_title'	=> "Notification Title",
	'rules_System_actions_create_notification_url'		=> "Notification Url",
	'rules_System_actions_create_notification_content'	=> "Notification Content",
	'rules_System_actions_create_notification_author'	=> "Notification Sponsor",
	
	'rules_System_url'					=> "Url",

	'rules_Comparisons_type'				=> "Comparison Type",
	'rules_Comparisons_value'				=> "Value",
	
	/**
	 * Content
	 *
	 * Rules Definitions
	 */
	 
	/*** CONTENT: Events ***/
	'rules_Content_event_content_created'			=> "Any content has been created",
	'rules_Content_event_content_created_content'		=> "The created/updated content",
	
	'rules_Content_event_content_updated'			=> "Any content has been updated",
	'rules_Content_event_content_updated_content'		=> "The created/updated content",
	'rules_Content_event_content_updated_changed'		=> "An array of the properties being saved",
	'rules_Content_event_content_updated_new'		=> "Boolean value indicating if content is new",

	'rules_Content_event_content_approved'			=> "Any content has been approved",
	'rules_Content_event_content_approved_content'		=> "The content",
	'rules_Content_event_content_approved_member'		=> "The member who approved the content",
	
	'rules_Content_event_content_pinned'			=> "Any content has been pinned",
	'rules_Content_event_content_pinned_content'		=> "The pinned content",
	'rules_Content_event_content_pinned_member'		=> "The member who pinned the content",
	
	'rules_Content_event_content_unpinned'			=> "Any content has been unpinned",
	'rules_Content_event_content_unpinned_content'		=> "The unpinned content",
	'rules_Content_event_content_unpinned_member'		=> "The member who unpinned the content",
	
	'rules_Content_event_content_featured'			=> "Any content has been featured",
	'rules_Content_event_content_featured_content'		=> "The featured content",
	'rules_Content_event_content_featured_member'		=> "The member who featured the content",
	
	'rules_Content_event_content_unfeatured'		=> "Any content has been unfeatured",
	'rules_Content_event_content_unfeatured_content'	=> "The unfeatured content",
	'rules_Content_event_content_unfeatured_member'		=> "The member who unfeatured the content",
	
	'rules_Content_event_content_locked'			=> "Any content has been locked",
	'rules_Content_event_content_locked_content'		=> "The locked content",
	'rules_Content_event_content_locked_member'		=> "The member who locked the content",
	
	'rules_Content_event_content_unlocked'			=> "Any content has been unlocked",
	'rules_Content_event_content_unlocked_content'		=> "The unlocked content",
	'rules_Content_event_content_unlocked_member'		=> "The member who unlocked the content",
	
	'rules_Content_event_content_hidden'			=> "Any content has been hidden",
	'rules_Content_event_content_hidden_content'		=> "The hidden content",
	'rules_Content_event_content_hidden_member'		=> "The member who hid the content",
	'rules_Content_event_content_hidden_reason'		=> "Reason given for hiding the content",
	
	'rules_Content_event_content_unhidden'			=> "Any content has been unhidden",
	'rules_Content_event_content_unhidden_content'		=> "The unhidden content",
	'rules_Content_event_content_unhidden_member'		=> "The member who unhid the content",
	
	'rules_Content_event_content_reported'			=> "Any content has been reported",
	'rules_Content_event_content_reported_content'		=> "The reported content",
	'rules_Content_event_content_reported_message'		=> "The message provided by the reporting member",

	'rules_Content_event_content_author_changed'		=> "Any content author has changed",
	'rules_Content_event_content_author_changed_content'	=> "The content whose author changed",
	'rules_Content_event_content_author_changed_oldMember'	=> "The member who was the old author",
	'rules_Content_event_content_author_changed_newMember'	=> "The member who is the new author",

	'rules_Content_event_content_deleted'			=> "Any content has been deleted",
	'rules_Content_event_content_deleted_content'		=> "The deleted content",
	
	'rules_Content_event_content_item_published'		=> "Any content item has been published",
	'rules_Content_event_content_item_published_item'	=> "The content item",
	'rules_Content_event_content_item_published_member'	=> "The member who published the content",
	
	'rules_Content_event_content_item_unpublished'		=> "Any content item has been unpublished",
	'rules_Content_event_content_item_unpublished_item'	=> "The content item",
	'rules_Content_event_content_item_unpublished_member'	=> "The member who unpublished the content",
	
	'rules_Content_event_content_item_moved'		=> "Any content item has been moved",
	'rules_Content_event_content_item_moved_item'		=> "The content item",
	'rules_Content_event_content_item_moved_oldContainer'	=> "The content item's old container",
	'rules_Content_event_content_item_moved_container'	=> "The content item's new container",
	'rules_Content_event_content_item_moved_link'		=> "Boolean value indicating if a link was left in the old container",
	 
	'rules_Content_event_content_item_merging'		=> "Any content item is being merged with others",
	'rules_Content_event_content_item_merging_item'		=> "The content item",
	'rules_Content_event_content_item_merging_items'	=> "Array of content items being merged in",
	 
	'rules_Content_event_content_item_viewed'		=> "Any content item is being viewed",
	'rules_Content_event_content_item_viewed_item'		=> "The content item",

	'rules_Content_event_content_item_tags_set'		=> "Any content item's tags have been updated",
	'rules_Content_event_content_item_tags_set_item'	=> "The content item",
	'rules_Content_event_content_item_tags_set_tags'	=> "The content tags",
	
	/*** CONTENT: App Events ***/
	'rules_Content_event_content_created_'			=> "%s has been created",
	'rules_Content_event_content_created_content_'		=> "The created %s",
	
	'rules_Content_event_content_updated_'			=> "%s has been updated",
	'rules_Content_event_content_updated_content_'		=> "The updated %s",
	'rules_Content_event_content_updated_changed_'		=> "An array of any changed properties",
	'rules_Content_event_content_updated_new_'		=> "Boolean value indicating if content is new",

	'rules_Content_event_content_approved_'			=> "%s has been approved",
	'rules_Content_event_content_approved_content_'		=> "The %s",
	'rules_Content_event_content_approved_member_'		=> "The member who approved the %s",
	
	'rules_Content_event_content_pinned_'			=> "%s has been pinned",
	'rules_Content_event_content_pinned_content_'		=> "The pinned %s",
	'rules_Content_event_content_pinned_member_'		=> "The member who pinned the %s",
	
	'rules_Content_event_content_unpinned_'			=> "%s has been unpinned",
	'rules_Content_event_content_unpinned_content_'		=> "The unpinned %s",
	'rules_Content_event_content_unpinned_member_'		=> "The member who unpinned the %s",
	
	'rules_Content_event_content_featured_'			=> "%s has been featured",
	'rules_Content_event_content_featured_content_'		=> "The featured %s",
	'rules_Content_event_content_featured_member_'		=> "The member who featured the %s",
	
	'rules_Content_event_content_unfeatured_'		=> "%s has been unfeatured",
	'rules_Content_event_content_unfeatured_content_'	=> "The unfeatured %s",
	'rules_Content_event_content_unfeatured_member_'		=> "The member who unfeatured the %s",
	
	'rules_Content_event_content_locked_'			=> "%s has been locked",
	'rules_Content_event_content_locked_content_'		=> "The locked %s",
	'rules_Content_event_content_locked_member_'		=> "The member who locked the %s",
	
	'rules_Content_event_content_unlocked_'			=> "%s has been unlocked",
	'rules_Content_event_content_unlocked_content_'		=> "The unlocked %s",
	'rules_Content_event_content_unlocked_member_'		=> "The member who unlocked the %s",
	
	'rules_Content_event_content_hidden_'			=> "%s has been hidden",
	'rules_Content_event_content_hidden_content_'		=> "The hidden %s",
	'rules_Content_event_content_hidden_member_'		=> "The member who hid the %s",
	'rules_Content_event_content_hidden_reason_'		=> "Reason given for hiding the %s",
	
	'rules_Content_event_content_unhidden_'			=> "%s has been unhidden",
	'rules_Content_event_content_unhidden_content_'		=> "The unhidden %s",
	'rules_Content_event_content_unhidden_member_'		=> "The member who unhid the %s",
	
	'rules_Content_event_content_reported_'			=> "%s has been reported",
	'rules_Content_event_content_reported_content_'		=> "The reported %s",
	'rules_Content_event_content_reported_message_'		=> "The message provided by the reporting member",

	'rules_Content_event_content_author_changed_'		=> "%s author has changed",
	'rules_Content_event_content_author_changed_content_'	=> "The %s whose author changed",
	'rules_Content_event_content_author_changed_oldMember_'	=> "The member who was the old author",
	'rules_Content_event_content_author_changed_newMember_'	=> "The member who is the new author",

	'rules_Content_event_content_deleted_'			=> "%s has been deleted",
	'rules_Content_event_content_deleted_content_'		=> "The deleted %s",
	
	'rules_Content_event_content_item_published_'		=> "%s has been published",
	'rules_Content_event_content_item_published_item_'	=> "The %s",
	'rules_Content_event_content_item_published_member_'	=> "The member who published the %s",
	
	'rules_Content_event_content_item_unpublished_'		=> "%s has been unpublished",
	'rules_Content_event_content_item_unpublished_item_'	=> "The %s",
	'rules_Content_event_content_item_unpublished_member_'	=> "The member who unpublished the %s",
	
	'rules_Content_event_content_item_moved_'		=> "%s has been moved",
	'rules_Content_event_content_item_moved_item_'		=> "The %s",
	'rules_Content_event_content_item_moved_oldContainer_'	=> "The %s's old container",
	'rules_Content_event_content_item_moved_container_'	=> "The %s's new container",
	'rules_Content_event_content_item_moved_link_'		=> "Boolean value indicating if a link was left in the old container",
	 
	'rules_Content_event_content_item_merging_'		=> "%s is being merged with others",
	'rules_Content_event_content_item_merging_item_'	=> "The %s",
	'rules_Content_event_content_item_merging_items_'	=> "Array of %s's being merged in",
	 
	'rules_Content_event_content_item_viewed_'		=> "%s is being viewed",
	'rules_Content_event_content_item_viewed_item_'		=> "The %s",

	'rules_Content_event_content_item_tags_set_'		=> "%s tags have been updated",
	'rules_Content_event_content_item_tags_set_item_'	=> "The %s",
	'rules_Content_event_content_item_tags_set_tags_'	=> "The %s tags",

	/*** CONTENT: Conditions ***/
	'rules_Content_conditions_content_type'			=> "Content is of a certain type",
	'rules_Content_conditions_content_type_content' 	=> "The Content to Check",
	'rules_Content_content_types'				=> "Content is one of these types",

	'rules_Content_conditions_content_status'		=> "Content has a particular status",
	'rules_Content_conditions_content_status_content' 	=> "The Content to Check",
	'rules_Content_content_statuses'			=> "Content has the following statuses",
	
	'rules_Content_conditions_content_author'		=> "Content has a particular author",
	'rules_Content_conditions_content_author_content'	=> "The Content to Check",
	'rules_Content_conditions_content_author_member'	=> "The Member to Check",
	'rules_Content_author_members'				=> "Content has one of the following authors",
	'rules_Content_author_members_desc'			=> "Type the names of authors you want to check the content for",
	
	'rules_Content_conditions_content_tags'			=> "Content has specific tags",
	'rules_Content_conditions_content_tags_item'		=> "The Content Item to Check",
	'rules_Content_conditions_content_tags_tags'		=> "Tags",
	'rules_Content_check_tags_type'				=> "Check if content is tagged with",
	'rules_Content_check_tags_prefix'			=> "Include prefix tag?",
	
	'rules_Content_conditions_content_attributes'		=> "Content attribute values",
	'rules_Content_conditions_content_attributes_content'	=> "The Content to Check",
	'rules_Content_content_attribute'			=> "Attribute to check",
	'rules_Content_attribute_compare_type_value'		=> "Is",
	'rules_Content_attribute_compare_type_date'		=> "Is",
	'rules_Content_attribute_compare_value'			=> "Value",
	'rules_Content_attribute_compare_date'			=> "Date",
	'rules_content_attribute_comments'			=> "Number of Comments",
	'rules_content_attribute_reviews'			=> "Number of Reviews",
	'rules_content_attribute_unapproved_comments'		=> "Number of Unapproved Comments",
	'rules_content_attribute_unapproved_reviews'		=> "Number of Unapproved Reviews",
	'rules_content_attribute_views'				=> "Number of Views",
	'rules_content_attribute_poll_votes'			=> "Number of Poll Votes",
	'rules_content_attribute_created'			=> "Created Date",
	'rules_content_attribute_updated'			=> "Last Updated Date",
	'rules_content_attribute_last_post'			=> "Last Post Date",
	'rules_Member_attribute_compare_type'			=> "Comparison type",
	'rules_Content_attribute_compare_date'			=> "Date",
	'rules_Content_attribute_compare_minutes'		=> "Minutes",
	'rules_Content_attribute_compare_hours'			=> "Hours",
	'rules_Content_attribute_compare_days'			=> "Days",
	'rules_Content_attribute_compare_months'		=> "Months",
	'rules_Content_attribute_compare_years'			=> "Years",
	
	'rules_Content_conditions_content_container'		=> "%s is in certain %s",
	'rules_Content_conditions_content_container_content'	=> "The %s to Check",
	'rules_Content_conditions_content_container_container'	=> "%s",
	'rules_Content_content_containers'			=> "Select %s",
	
	'rules_Content_conditions_container'			=> "Check for specific %s",
	'rules_Content_conditions_container_container'		=> "Select Container To Check",
	'rules_Content_conditions_container_containers'		=> "Check For Any Of These %s",
	'rules_Content_containers'				=> "Select %s",
	'rules_Content_container'				=> "Select Container",
	
	/*** CONTENT: Actions ***/
	'rules_Content_actions_set_content_tags'		=> "Modify content tags",
	'rules_Content_actions_set_content_tags_content'	=> "Content To Modify",
	'rules_Content_actions_set_content_tags_tags'		=> "Tags",
	'rules_Content_modify_tags_type'			=> "How to modify tags",
	'rules_Content_tags_stack'				=> "Tags",
	
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
		
	'rules_Content_actions_delete_content'			=> "Delete content",
	'rules_Content_actions_delete_content_content'		=> "Content To Delete",
	
	'rules_Content_actions_change_author'			=> "Change content author",
	'rules_Content_actions_change_author_content'		=> "Content To Change Author Of",
	'rules_Content_actions_change_author_author'		=> "The New Author",
	
	'rules_Content_actions_move_content'			=> "Move %s to new %s",
	'rules_Content_actions_move_content_item'		=> "The %s to Move",
	'rules_Content_actions_move_content_container'		=> "New %s Container",
	'rules_Content_content_container'			=> "Select New Container",
	'rules_Content_move_content_link'			=> "Leave a link in the old container?",
	
	'rules_Content_actions_create_content'			=> "Create new %s",
	'rules_Content_actions_create_content_container'	=> "%s Container",
	'rules_Content_actions_create_content_author'		=> "%s Author",
	'rules_Content_actions_create_content_title'		=> "%s Title",
	'rules_Content_actions_create_content_content'		=> "%s Content",
	'rules_Content_actions_create_content_tags'		=> "%s Tags",
	'rules_Content_increase_posts'				=> "Increase post count of author?",
	'rules_Content_guest_name'				=> "Name to use if author is a guest",
	'rules_Content_content_body'				=> "Content Body",
	'rules_Content_content_title'				=> "Title",
	'rules_Content_use_created_handler'			=> "Process Content After Creation?",
	'rules_Content_use_created_handler_desc'		=> "(Optional) If you need to perform additional actions on the content after it's created, you can enter custom php code which will be executed after the content has been saved.",
	'rules_Content_created_handler'				=> "PHP Code",
	'rules_Content_created_handler_description'		=> "Custom php code entered here will be executed after the content is saved. The following variables are available to your code:<br><br><ul><li><strong>\$createdContent</strong> - The created content</li></ul>",
	
	'rules_Content_actions_create_content_comment'		=> "Post new %s comment",
	'rules_Content_actions_create_content_comment_item'	=> "%s",
	'rules_Content_actions_create_content_comment_author'	=> "%s Comment Author",
	'rules_Content_actions_create_content_comment_content'	=> "%s Comment Content",
	
	
	/**
	 * Content Comment
	 *
	 * Rules Definitions
	 */
	'rules_Comments_event_content_comment_created'			=> "Content comment/review has been posted",
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
	
	'time' => "Time",
	'result' => "Result",
	'import' => "Import",
);
