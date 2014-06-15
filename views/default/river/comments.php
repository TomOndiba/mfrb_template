<?php

$date = get_input('date', false);
$object_guid = get_input('object_guid', false);

if (!$date || !$object_guid) {
	register_error(elgg_echo('get_comments:error'));
}

$comments = elgg_get_entities(array(
	'type' => 'object',
	'subtype' => 'comment',
	'container_guid' => $object_guid,
	'created_time_upper' => (int) $date -1,
	'limit' => 20
));

$output = array();
foreach ($comments as $comment) {
	$output[] = get_comment_river($comment);
}

echo json_encode($output);
