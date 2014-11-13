<?php
/**
 * Action for editing a wire post
 *
 */

// Get input data
$guid = (int) get_input('guid');
$body = get_input('body', false);

// Make sure we actually have permission to edit
$thewire = get_entity($guid);
if ($thewire->getSubtype() == "thewire" && $thewire->canEdit() && $body) {

	$thewire->description = $body;
	$thewire->save();
	system_message(elgg_echo("thewire:edited"));

	$item = get_wire_object(elgg_get_river(array('object_guid' => $guid))[0]);

	elgg_nodejs_broadcast(array(
		'type'=> 'edit_wire',
		'message' => get_wire_object($item)
	));

}
