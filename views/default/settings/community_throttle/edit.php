<?php
/**
 * Community customization plugin settings
 */

$msg_limit = get_plugin_setting('msg_limit', 'community_throttle');

echo '<label>' . elgg_echo('throttle:msg_limit') . ':</label>';
echo elgg_view('input/text', array(
	'internalname' => 'params[msg_limit]',
	'value' => $msg_limit,
));
