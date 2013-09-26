<?php

access_show_hidden_entities(true);

$offset = get_input('offset', 0);
$limit = get_input('limit', 10);

$options = array(
	'type' => 'object',
	'metadata_names' => array('disable_reason'),
	'metadata_values' => array('reported spam'),
	'limit' => $limit,
	'offset' => $offset,
	'count' => true
);

$count = elgg_get_entities_from_metadata($options);

if ($count) {
	unset($options['count']);
	
	$entities = elgg_get_entities_from_metadata($options);

	foreach ($entities as $entity) {
		echo elgg_view('reported_spam/entity', array('entity' => $entity));
	}
	
	echo elgg_view('navigation/pagination', array(
		'offset' => $offset,
		'limit' => $limit,
		'count' => $count
	));
}
else {
	echo elgg_echo('community_spam_tools:noresults');
}
