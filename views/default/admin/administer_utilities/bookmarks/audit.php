<style>
	table.bookmarks tr:nth-child(even) {
		background-color: #DEDEDE;
	}
	
	table.bookmarks tr {
		border-bottom: 1px solid black;
	}
	
	table.bookmarks tr:first-child td {
		font-weight: bold;
	}
	
	table.bookmarks td {
		padding: 5px;
		font-size: 10px;
	}
	
	div.table-wrap {
		width: 100%;
		overflow: auto;
	}
</style>
<?php

$dbprefix = elgg_get_config('dbprefix');
$discussion_id = get_subtype_id('object', 'groupforumtopic');
$reply_id = get_subtype_id('object', 'discussion_reply');
$comment_id = get_subtype_id('object', 'comment');
$bookmark_id = get_subtype_id('object', 'bookmarks');

$limit = max(array(50, abs((int) get_input('limit', 50))));
$offset = abs((int) get_input('offset', 0));

$count = elgg_get_entities(array('type' => 'user', 'count' => true));

$pagination = elgg_view('navigation/pagination', array(
	'count' => $count,
	'offset' => $offset,
	'limit' => $limit
));

$options = array(
        'type' => 'user',
        'selects' => array(
			"(SELECT COUNT(pc.guid) FROM {$dbprefix}entities pc WHERE pc.type = 'object' AND pc.subtype IN({$discussion_id}, {$reply_id}, {$comment_id}) AND pc.owner_guid = e.guid) AS participation",
			"(SELECT COUNT(pb.guid) FROM {$dbprefix}entities pb WHERE pb.type = 'object' AND pb.subtype = {$bookmark_id} AND pb.owner_guid = e.guid) AS bookmarks"
        ),
		'wheres' => array(
			"(SELECT COUNT(pbw.guid) FROM {$dbprefix}entities pbw WHERE pbw.type = 'object' AND pbw.subtype = {$bookmark_id} AND pbw.owner_guid = e.guid) > 0"
		),
        'order_by' => 'participation ASC, bookmarks DESC',
        'limit' => 5,
        'callback' => false, // keep initial query light
		'offset' => $offset,
		'limit' => $limit
);

$results = elgg_get_entities($options);


$body = '<div class="table-wrap">';
$body .= '<table class="bookmarks">';
$body .= '<tr>';
$body .= '<td>Delete</td>';
$body .= '<td>User</td>';
$body .= '<td>Participation</td>';
$body .= '<td>Bookmarks</td>';
$body .= '<td>Bookmark URLs</td>';
$body .= '<td>Bookmark Tags</td>';
$body .= '</tr>';
foreach ($results as $result) {
	$user = get_user($result->guid);
	if (!$user) {
		continue; // ??
	}
	
	// get most recent bookmarks
	$options = array(
		'type' => 'object',
		'subtype' => 'bookmarks',
		'owner_guid' => $user->guid,
		'limit' => 5
	);
	
	$bookmarks = elgg_get_entities($options);
	$bookmark_urls = '';
	$bookmark_tags = '';
	foreach ($bookmarks as $b) {
		$bookmark_urls .= '<div>' . elgg_get_excerpt($b->address, 70) . '</div>';
		
		if ($bookmark_tags && $b->tags) {
			$bookmark_tags .= ', ';
		}
		$bookmark_tags .= implode(', ', (array) $b->tags);
	}
	
	$body .= '<tr>';
	$body .= '<td><input type="checkbox" name="guids[]" value="' . $user->guid . '">';
	$body .= '<td>' . elgg_view_entity_icon($user, 'small') . $user->username  . '</td>';
	$body .= '<td>' . $result->participation . '</td>';
	$body .= '<td>' . $result->bookmarks . '</td>';
	$body .= '<td>' . $bookmark_urls . '</td>';
	$body .= '<td>' . $bookmark_tags . '</td>';
	$body .= '</tr>';
}
$body .= '</table>';
$body .= '</div>';
$body .= elgg_view('input/submit', array(
	'value' => elgg_echo('delete'),
	'class' => 'mvm'
));


echo elgg_view('output/longtext', array(
	'value' => "Participation is defined as the sum of all discussion topics, replies, and comments owned by that user.",
	'class' => 'elgg-subtext'
));

echo $pagination;
echo elgg_view('input/form', array(
	'action' => elgg_normalize_url('action/spam_tools/bulk_user_delete'),
	'onSubmit' => "return confirm('Are you ABSOLUTELY sure?');",
	'body' => $body
));
echo $pagination;