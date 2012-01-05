<?php
/**
 * Ask a question that spammers won't get right
 */

echo "<label>" . elgg_echo('community_spam_tools:question') . "<br />";
echo elgg_view('input/text', array(
	'internalname' => 'question',
	'class' => "general-textarea",
));
echo "</label><br />";

