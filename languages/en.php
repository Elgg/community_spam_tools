<?php
/**
 * English language file for community spam tools plugin
 */

$english = array(
	'community_spam_tools:msg_limit' => 'Maximum number of sent messages over 5 minutes',
	'community_spam_tools:msg_limit:new_user' => 'Maximum number of sent messages for new users',
	'community_spam_tools:blacklist' => 'Comma-separated list of spam words or phrases',
	'community_spam_tools:messages' => 'Messages settings',
	'community_spam_tools:profile' => 'Profile settings',
	'community_spam_tools:trusted_users' => 'Trusted user settings',
	'community_spam_tools:trusted_users:months' => 'Trust users after how many months?',
	'community_spam_tools:trusted_users:flag_count' => 'How many spam reports by trusted users should trigger an action?',
	'community_spam:mark' => 'Mark as Spam',
	'community_spam:error:permissions' => "You don't have permission to do that.",
	'community_spam:error:marked' => "This content has already been marked as spam.",
	'community_spam:success:marked' => "Content has been reported as spam",
);

add_translation("en", $english);
