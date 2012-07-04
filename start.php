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
	elgg_register_event_handler('create', 'object', 'community_spam_messages_filter');

	// profile spam
	elgg_register_plugin_hook_handler('action', 'profile/edit', 'community_spam_profile_blacklist');

	// limit access to the add links
	elgg_register_event_handler('pagesetup', 'system', 'community_spam_remove_add_links');
	elgg_register_plugin_hook_handler('action', 'bookmarks/save', 'community_spam_stop_add');
	elgg_register_plugin_hook_handler('action', 'pages/edit', 'community_spam_stop_add');
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

	if (community_spam_is_new_user()) {
		$msg_limit = elgg_get_plugin_setting('new_user_msg_limit', 'community_spam_tools');
	} else {
		$msg_limit = elgg_get_plugin_setting('msg_limit', 'community_spam_tools');
	}

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
		'count' => true,
	);
	$num_msgs = elgg_get_entities_from_metadata($params);
	if ($num_msgs > $msg_limit) {
		$spammer = elgg_get_logged_in_user_entity();
		$spammer->annotate('banned', 1); // this integrates with ban plugin
		$spammer->ban("Sent $num_msgs in 5 minutes");
		return false;
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
 * Remove some add links for new users
 */
function community_spam_remove_add_links() {
	if (elgg_is_logged_in() && community_spam_is_new_user()) {

		elgg_unregister_menu_item('extras', 'bookmark');
		
		if (elgg_in_context('bookmarks') || elgg_in_context('pages')) {
			// remove bookmarklet menu item
			elgg_unregister_plugin_hook_handler('register', 'menu:page', 'bookmarks_page_menu');

			// remove add buttons
			$callback = function() { return array(); };
			elgg_register_plugin_hook_handler('register','menu:title', $callback);
		}
	}
}

/**
 * Catch users trying to hit actions directly
 */
function community_spam_stop_add() {
	if (community_spam_is_new_user()) {
		// spammer tried to directly hit the action
		$spammer = elgg_get_logged_in_user_entity();
		$spammer->annotate('banned', 1); // this integrates with ban plugin
		$spammer->ban('tried to post content before allowed');
		return false;
	}
}

/**
 * Filter based on common spam terms
 */
function community_spam_messages_filter($event, $type, $object) {
	if ($object->getSubtype() !== 'messages') {
		return;
	}

	if (community_spam_is_new_user()) {
		$terms = array('yahoo', 'hotmail', 'miss', 'love', 'email address', 'dear', 'picture', 'profile', 'interest');
		$count = 0;
		foreach ($terms as $term) {
			if (stripos($object->description, $term) !== false) {
				$count++;
			}
		}
		if ($count > 3) {
			return false;
		}
	}
}
