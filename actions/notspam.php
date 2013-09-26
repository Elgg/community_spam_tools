<?php

$guid = get_input('guid');

access_show_hidden_entities(true);

$entity = get_entity($guid);
$owner = $entity->getOwnerEntity();

if ($entity) {
	$entity->enable();
	$entity->deleteAnnotations('community_spam_report');
	
	// remove last mark against the owner
	$annotations = $owner->getAnnotations('content_marked_spam', 1);
	
	if ($annotations) {
		$annotations[0]->delete();
	}
	
	$userlimit = elgg_get_plugin_setting('user_spam_count', 'community_spam_tools');
	$user_strtotime = elgg_get_plugin_setting('user_spam_strtotime', 'community_spam_tools');
	
	// if the user was banned for this lets unban them
	$time_lower = strtotime($user_strtotime);
	
	if ($owner->isBanned() && $time_lower && $userlimit) {
		$usercount = elgg_get_annotations(array(
			'guid' => $owner->guid,
			'annotation_names' => array('content_marked_spam'),
			'annotation_values' => array('1'),
			'annotation_created_time_lower' => $time_lower,
			'count' => true
		));
		
		if ($usercount < $userlimit) {
			$owner->unban();
		}
	}	
}

system_message(elgg_echo('community_spam_tools:entity:notspam'));
forward(REFERER);