<?php
/**
 * Community spammer tools
 */

register_elgg_event_handler('init', 'system', 'community_spam_init');

/**
 * Initialize the spam tools plugin
 */
function community_spam_init() {
	// messages spam
	register_elgg_event_handler('create', 'object', 'community_spam_messages_throttle');

	// profile spam
	register_plugin_hook('action', 'profile/edit', 'community_spam_profile_blacklist');

	// registration question for spammers
	if (strpos($_SERVER['REMOTE_ADDR'], '122.162.') === 0) {
		elgg_extend_view('register/extend', 'community_spam_tools/question');
		register_plugin_hook('action', 'register', 'community_spam_registration_catch');
	}
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

	$msg_limit = get_plugin_setting('msg_limit', 'community_spam_tools');
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
		'metadata_values' => get_loggedin_userid(),
		'count' => TRUE,
	);
	$num_msgs = elgg_get_entities_from_metadata($params);
	if ($num_msgs > $msg_limit) {

		$report = new ElggObject;
		$report->subtype = "reported_content";
		$report->owner_guid = get_loggedin_userid();
		$report->title = "Private message throttle";
		$report->address = get_loggedin_user()->getURL();
		$report->description = "this user exceeded the limit by sending $msg_limit messages in 5 minutes";
		$report->access_id = ACCESS_PRIVATE;
		$report->save();

		ban_user(get_loggedin_userid(), 'messages throttle');
	}
}

/**
 * Filter profile fields by blacklist
 */
function community_spam_profile_blacklist() {
	$blacklist = get_plugin_setting('profile_blacklist', 'community_spam_tools');
	$blacklist = explode(",", $blacklist);
	$blacklist = array_map('trim', $blacklist);

	foreach ($_REQUEST as $key => $value) {
		if (is_string($value)) {
			foreach ($blacklist as $word) {
				if (stripos($value, $word) !== false) {
					ban_user(get_loggedin_userid(), "used '$word' on profile");
					return false;
				}
			}
		}
	}
}

/**
 * Check the answer to the registration question
 *
 * @return void
 */
function community_spam_registration_catch() {
	$question = get_input('question');
	$names = array('brett', 'evan', 'cash');
	foreach ($names as $name) {
		if (stripos($question, $name) !== false) {
			return;
		}
	}

	register_error(elgg_echo('community_spam_tools:error:reject'));
	forward(REFERER);
}
