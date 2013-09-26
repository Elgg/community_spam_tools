<?php
/**
 * Community spammer tools
 */

require_once 'lib/hooks.php';
require_once 'lib/events.php';

elgg_register_event_handler('init', 'system', 'community_spam_init');

/**
 * Initialize the spam tools plugin
 */
function community_spam_init() {
	// messages spam
	elgg_register_event_handler('create', 'object', 'community_spam_messages_throttle');
	elgg_register_event_handler('create', 'object', 'community_spam_messages_filter');

	// profile spam
	elgg_register_plugin_hook_handler('action', 'profile/edit', 'community_spam_profile_blacklist');

	// limit access to the add links
	elgg_register_event_handler('pagesetup', 'system', 'community_spam_remove_add_links');
	elgg_register_plugin_hook_handler('action', 'bookmarks/save', 'community_spam_stop_add');
	elgg_register_plugin_hook_handler('action', 'pages/edit', 'community_spam_stop_add');
	elgg_register_event_handler('create', 'object', 'community_spam_prevent_create');
	
	// add spam report buttons to entity menus
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'community_spam_entity_menu');
	// run last to create a specific menu for admin
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'community_spam_reported_spam_menu', 1000);
	elgg_register_plugin_hook_handler('register', 'menu:river', 'community_spam_river_menu');
	
	if (community_spam_is_trusted_user(elgg_get_logged_in_user_entity())) {
		elgg_register_action('report_spam', dirname(__FILE__) . '/actions/report_spam.php');
		elgg_register_action('reported_spam/delete', dirname(__FILE__) . '/actions/delete.php', 'admin');
		elgg_register_action('reported_spam/notspam', dirname(__FILE__) . '/actions/notspam.php', 'admin');
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

/**
 * Is this a new user
 * @return bool
 */
function community_spam_is_new_user() {
	$user = elgg_get_logged_in_user_entity();
	if (!$user) {
		// logged out users are new users I guess
		return true;
	}

	// 2 days
	$cutoff = time() - 2 * 24 * 60 * 60;
	if ($user->getTimeCreated() > $cutoff) {
		return true;
	} else {
		return false;
	}
}


/**
 * is this a trusted user?
 */
function community_spam_is_trusted_user($user) {
	if (!elgg_instanceof($user, 'user')) {
		return false;
	}
	
	$months = elgg_get_plugin_setting('trusted_users_months', 'community_spam_tools');
	
	if (!$months || !is_numeric($months)) {
		return false;
	}
	
	return $user->getTimeCreated() < strtotime("-{$months} months");
}


/**
 * has the user already marked the entity as spam?
 */
function community_spam_is_marked($object, $user) {
	if (!elgg_instanceof($object) || !elgg_instanceof($user, 'user')) {
		return false;
	}
	
	$annotations = elgg_get_annotations(array(
		'guid' => $object->guid,
		'annotation_owner_guid' => $user->guid,
		'annotation_names' => 'community_spam_user_marked'
	));
	
	return $annotations ? true : false;
}