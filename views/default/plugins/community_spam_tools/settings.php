<?php
/**
 * Community spam tools plugin settings
 */

$msg_limit = elgg_get_plugin_setting('msg_limit', 'community_spam_tools');

echo '<div>';
echo '<label>' . elgg_echo('community_spam_tools:msg_limit') . ':</label>';
echo elgg_view('input/text', array(
	'name' => 'params[msg_limit]',
	'value' => $msg_limit,
));
echo '</div>';

$new_user_msg_limit = elgg_get_plugin_setting('new_user_msg_limit', 'community_spam_tools');

echo '<div>';
echo '<label>' . elgg_echo('community_spam_tools:msg_limit:new_user') . ':</label>';
echo elgg_view('input/text', array(
	'name' => 'params[new_user_msg_limit]',
	'value' => $new_user_msg_limit,
));
echo '</div>';


$blacklist = elgg_get_plugin_setting('profile_blacklist', 'community_spam_tools');

echo '<div>';
echo '<label>' . elgg_echo('community_spam_tools:blacklist') . ':</label>';
echo elgg_view('input/plaintext', array(
	'name' => 'params[profile_blacklist]',
	'value' => $blacklist,
));
echo '<span class="elgg-text-help">' . elgg_echo('community_spam_tools:blacklist:desc') . '</span>';
echo '</div>';
