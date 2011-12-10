<?php
/**
 * Community spam tools plugin settings
 */

$msg_limit = get_plugin_setting('msg_limit', 'community_spam_tools');

echo '<label>' . elgg_echo('community_spam_tools:msg_limit') . ':</label>';
echo elgg_view('input/text', array(
	'internalname' => 'params[msg_limit]',
	'value' => $msg_limit,
));
