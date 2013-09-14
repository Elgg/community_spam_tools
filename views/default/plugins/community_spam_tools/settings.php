<?php
/**
 * Community spam tools plugin settings
 */


/* Messages settings */
$title = elgg_echo('community_spam_tools:messages');

$body = '<div>';
$body .= '<label>' . elgg_echo('community_spam_tools:msg_limit') . ':</label>';
$body .= elgg_view('input/text', array(
	'name' => 'params[msg_limit]',
	'value' => $vars['entity']->msg_limit,
));
$body .= '</div>';


$body .= '<div>';
$body .= '<label>' . elgg_echo('community_spam_tools:msg_limit:new_user') . ':</label>';
$body .= elgg_view('input/text', array(
	'name' => 'params[new_user_msg_limit]',
	'value' => $vars['entity']->new_user_msg_limit,
));
$body .= '</div>';

echo elgg_view_module('main', $title, $body);



/* Profile settings */
$title = elgg_echo('community_spam_tools:profile');

$body = '<div>';
$body .= '<label>' . elgg_echo('community_spam_tools:blacklist') . ':</label>';
$body .= elgg_view('input/plaintext', array(
	'name' => 'params[profile_blacklist]',
	'value' => $vars['entity']->profile_blacklist,
));
$body .= '</div>';

echo elgg_view_module('main', $title, $body);


/* Trusted user settings */
$title = elgg_echo('community_spam_tools:trusted_users');

$body = '<div>';
$body .= '<label>' . elgg_echo('community_spam_tools:trusted_users:months') . ':</label>';
$body .= elgg_view('input/text', array(
	'name' => 'params[trusted_users_months]',
	'value' => $vars['entity']->trusted_users_months,
));
$body .= '</div>';


$body .= '<div>';
$body .= '<label>' . elgg_echo('community_spam_tools:trusted_users:flag_count') . ':</label>';
$body .= elgg_view('input/text', array(
	'name' => 'params[trusted_users_flag_count]',
	'value' => $vars['entity']->trusted_users_flag_count,
));
$body .= '</div>';

echo elgg_view_module('main', $title, $body);