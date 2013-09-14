<?php

access_show_hidden_entities(true);

echo elgg_list_entities_from_metadata(array(
	'metadata_names' => array('disable_reason'),
	'metadata_values' => array('reported spam'),
	'full_view' => false
));