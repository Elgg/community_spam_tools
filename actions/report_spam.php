<?php

$user = elgg_get_logged_in_user_entity();
$object = get_entity(get_input('guid'));
$forward = REFERER;

if (!elgg_instanceof($object) || !community_spam_is_trusted_user($user)) {
	register_error(elgg_echo('community_spam:error:permissions'));
	forward(REFERER);
}

$owner = $object->getOwnerEntity();

if (community_spam_is_marked($object, $user)) {
	register_error(elgg_echo('community_spam:error:marked'));
	forward(REFERER);
}

$ia = elgg_set_ignore_access(true);

// all good, lets mark it as spam
$object->annotate('community_spam_report', 1);

// count how many reports there have been
$markcount = elgg_get_annotations(array(
	'guid' => $object->guid,
	'annotation_names' => array('community_spam_report'),
	'annotation_values' => array('1'),
	'count' => true
));

$marklimit = elgg_get_plugin_setting('trusted_users_flag_count', 'community_spam_tools');
$userlimit = elgg_get_plugin_setting('user_spam_count', 'community_spam_tools');
$user_strtotime = elgg_get_plugin_setting('user_spam_strtotime', 'community_spam_tools');

if ($markcount >= $marklimit) {
	// this is enough to call it spam
	$object->disable('reported spam');
	$forward = 'activity';
	
	// record that the user had content disabled
	// if the user has too much content disabled we'll auto-ban them
	$owner->annotate('content_marked_spam', 1);
	
	$time_lower = strtotime($user_strtotime);
	
	if ($time_lower && $userlimit) {
		$usercount = elgg_get_annotations(array(
			'guid' => $owner->guid,
			'annotation_names' => array('content_marked_spam'),
			'annotation_values' => array('1'),
			'annotation_created_time_lower' => $time_lower,
			'count' => true
		));
		
		if ($usercount >= $userlimit) {
			$owner->ban('content_marked_spam');
		}
	}	
}

elgg_set_ignore_access($ia);

system_message(elgg_echo('community_spam_tools:success:marked'));

forward($forward);