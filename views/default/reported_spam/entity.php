<?php

$entity = $vars['entity'];
$owner = $entity->getOwnerEntity();

$title = $entity->title ? $entity->title : $entity->name;
if (!strlen($title)) {
	$title = $entity->type . ':' . $entity->getSubtype() . ':' . $entity->guid;
}

$description = $entity->description ? $entity->description : $entity->briefdescription;

$owner_link = elgg_view('output/url', array(
	'href' => $owner->getURL(),
	'text' => $owner->name,
	'is_trusted' => true,
));
$author_text = elgg_echo('byline', array($owner_link));
$date = elgg_view_friendly_time($entity->time_created);

$params = array(
		'entity' => $entity,
		'title' => $title,
		'metadata' => elgg_view_menu('entity', array(
				'entity' => $entity,
				'class' => 'elgg-menu-hz',
				'sort_by' => 'priority',
				'moderate_spam' => true,
			)),
		'tags' => false,
		'subtitle' => $author_text . ' ' . $date,
		'content' => elgg_get_excerpt($description)
	);

$summary = elgg_view('object/elements/summary', $params);

echo elgg_view_image_block(elgg_view_entity_icon($owner, 'small'), $summary);