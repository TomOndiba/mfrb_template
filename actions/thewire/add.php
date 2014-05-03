<?php
/**
 * Action for adding a wire post
 *
 */

// don't filter since we strip and filter escapes some characters
$body = get_input('body', '', false);

$method = 'site';

// make sure the post isn't blank
if (empty($body)) {
	register_error(elgg_echo('thewire:blank'));
	forward(REFERER);
}


elgg_load_library('thewire');

$post = new ElggObject();
$post->subtype = 'thewire';
$post->owner_guid = elgg_get_logged_in_user_guid();
$post->access_id = ACCESS_PUBLIC;
$post->method = 'site'; //method: site, email, api, ...

$post->link_description = get_input('link_description', false);
$post->link_name = get_input('link_name', false);
$post->link_picture = get_input('link_picture', false);
$post->link_url = get_input('link_url', false);

// no html tags allowed so we escape
$post->description = htmlspecialchars($body, ENT_NOQUOTES, 'UTF-8');

$tags = thewire_get_hashtags($body);
if ($tags) {
	$post->tags = $tags;
}

// must do this before saving so notifications pick up that this is a reply
if ($parent_guid) {
	$post->reply = true;
}

$guid = $post->save();

if ($guid) {
	elgg_create_river_item(array(
		'view' => 'river/object/thewire/create',
		'action_type' => 'create',
		'subject_guid' => $post->owner_guid,
		'object_guid' => $post->guid,
	));

	// let other plugins know we are setting a user status
	$params = array(
		'entity' => $post,
		'user' => $post->getOwnerEntity(),
		'message' => $post->description,
		'url' => $post->getURL(),
		'origin' => 'thewire',
	);
	elgg_trigger_plugin_hook('status', 'user', $params);
} else {
	register_error(elgg_echo('thewire:notsaved'));
	forward(REFERER);
}

system_message(elgg_echo('thewire:posted'));
forward(REFERER);
