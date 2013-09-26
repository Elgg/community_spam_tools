<?php

$guid = get_input('guid');

access_show_hidden_entities(true);

$entity = get_entity($guid);
if ($entity) {
	$entity->delete();
}

system_message(elgg_echo('community_spam_tools:entity:deleted'));
forward(REFERER);