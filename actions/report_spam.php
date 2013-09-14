<?php

$user = elgg_get_logged_in_user_entity();
$object = get_entity(get_input('guid'));
$forward = REFERER;

if (!elgg_instanceof($object) || !community_spam_is_trusted_user($user)) {
	register_error(elgg_echo('community_spam:error:permissions'));
	forward(REFERER);
}

if (community_spam_is_marked($object, $user)) {
	register_error(elgg_echo('community_spam:error:marked'));
	forward(REFERER);
}

$ia = elgg_set_ignore_access(true);

// all good, lets mark it as spam
$object->annotate('community_spam_report', 1);

// count how many reports there have been
$count = elgg_get_annotations(array(
	'guid' => $object->guid,
	'annotation_names' => array('community_spam_report'),
	'annotation_values' => array('1'),
	'count' => true
));

$limit = elgg_get_plugin_setting('trusted_users_flag_count', 'community_spam_tools');

if ($count >= $limit) {
	// this is enough to call it spam
	$object->disable('reported spam');
	$forward = 'activity';
}

elgg_set_ignore_access($ia);

system_message(elgg_echo('community_spam_tools:success:marked'));

forward($forward);