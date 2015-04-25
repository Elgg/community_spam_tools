<?php

set_time_limit(0); // it could be a lot of stuff to delete

$guids = (array)get_input('guids');

foreach ($guids as $guid) {
	$user = get_user($guid);
	if ($user) {
		$user->delete();
	}
}

system_message('Selected users deleted');
forward(REFERER);