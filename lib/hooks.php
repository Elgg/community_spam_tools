<?php

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
 * Add spam links to entity menus
 */
function community_spam_entity_menu($hook, $type, $return, $params) {
	$user = elgg_get_logged_in_user_entity();
	if (!community_spam_is_trusted_user($user)) {
		return $return;
	}
	
	// only objects should be markable with the entity menu
	if (!elgg_instanceof($params['entity'], 'object')) {
		return $return;
	}
	
	// only allow them to mark it once
	if (community_spam_is_marked($params['entity'], $user)) {
		return $return;
	}
	
	// we're a trusted user, give us a spam link
	$text = elgg_view_icon('attention');
	$href = elgg_add_action_tokens_to_url('action/report_spam?guid=' . $params['entity']->guid);
	$item = new ElggMenuItem('report_spam', $text, $href);
	$item->setTooltip(elgg_echo('community_spam:mark'));
	
	$return[] = $item;
	
	return $return;
}


function community_spam_river_menu($hook, $type, $return, $params) {
	if ($params['item']->type == 'object' && elgg_is_logged_in() && !$params['item']->annotation_id) {
		$user = elgg_get_logged_in_user_entity();
		$object = $params['item']->getObjectEntity();
		
		if (community_spam_is_trusted_user($user) && !community_spam_is_marked($object, $user)) {
			// we're a trusted user, give us a spam link
			$text = elgg_view_icon('attention');
			$href = elgg_add_action_tokens_to_url('action/report_spam?guid=' . $object->guid);
			$item = new ElggMenuItem('report_spam', $text, $href);
			$item->setTooltip(elgg_echo('community_spam:mark'));
	
			$return[] = $item;
		}
	}
	
	return $return;
}