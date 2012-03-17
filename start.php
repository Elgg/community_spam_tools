<?php
/**
 * Community spammer tools
 */

elgg_register_event_handler('init', 'system', 'community_spam_init');

/**
 * Initialize the spam tools plugin
 */
function community_spam_init() {
	// messages spam
	elgg_register_event_handler('create', 'object', 'community_spam_messages_throttle');

	// profile spam
	elgg_register_plugin_hook_handler('action', 'profile/edit', 'community_spam_profile_blacklist');
}

/**
 * ban user if sending too many messages
 *
 * @param string     $event
 * @param string     $type
 * @param ElggObject $object
 * @return bool
 */
function community_spam_messages_throttle($event, $type, $object) {
	if ($object->getSubtype() !== 'messages') {
		return;
	}

	$msg_limit = elgg_get_plugin_setting('msg_limit', 'community_spam_tools');
	if (!$msg_limit) {
		return;
	}
	// two message objects created per message but after they are saved,
	// both are set to private so we only have access to one later on
	$msg_limit = $msg_limit + 1;

	$params = array(
		'type' => 'object',
		'subtype' => 'messages',
		'created_time_lower' => time() - (5*60), // 5 minutes
		'metadata_names' => 'fromId',
		'metadata_values' => elgg_get_logged_in_user_guid(),
		'count' => TRUE,
	);
	$num_msgs = elgg_get_entities_from_metadata($params);
	if ($num_msgs > $msg_limit) {

		$report = new ElggObject;
		$report->subtype = "reported_content";
		$report->owner_guid = elgg_get_logged_in_user_guid();
		$report->title = "Private message throttle";
		$report->address = get_loggedin_user()->getURL();
		$report->description = "this user exceeded the limit by sending $msg_limit messages in 5 minutes";
		$report->access_id = ACCESS_PRIVATE;
		$report->save();

		ban_user(elgg_get_logged_in_user_guid(), 'messages throttle');
	}
}

/**
 * Filter profile fields by blacklist
 */
function community_spam_profile_blacklist() {
	$blacklist = elgg_get_plugin_setting('profile_blacklist', 'community_spam_tools');
	$blacklist = explode(",", $blacklist);
	$blacklist = array_map('trim', $blacklist);

	foreach ($_REQUEST as $key => $value) {
		if (is_string($value)) {
			foreach ($blacklist as $word) {
				if (stripos($value, $word) !== false) {
					ban_user(elgg_get_logged_in_user_guid(), "used '$word' on profile");
					return false;
				}
			}
		}
	}
}
