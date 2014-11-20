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
$post->container_guid = get_input('container_guid', elgg_get_logged_in_user_guid());
$post->method = 'site'; //method: site, email, api, ...

$post->link_description = get_input('link_description', false);
$post->link_name = get_input('link_name', false);
$post->link_picture = get_input('link_picture', false);
$post->link_url = get_input('link_url', false);

$post->access_id = ACCESS_PUBLIC;

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
	$item_id = elgg_create_river_item(array(
		'view' => 'river/object/thewire/create',
		'action_type' => 'create',
		'subject_guid' => $post->owner_guid,
		'object_guid' => $post->guid,
		'target_guid' => $post->container_guid
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

	$item = get_wire_object(elgg_get_river(array('id' => $item_id))[0]);

	elgg_nodejs_broadcast(array(
		'type'=> 'new_wire',
		'message' => get_wire_object($item)
	));

	$notified_users = get_input('notified_users', false);
	if ($notified_users) {
		$user = elgg_get_logged_in_user_entity();
		$notified_users = explode(',', $notified_users);
		foreach ($notified_users as $user) {
		}
		notify_user($notified_users,
			$post->owner_guid,
			elgg_echo('thewire:notify:subject', array($user->name)),
			elgg_echo('thewire:notify:body', array(
				$user->getURL(),
				$user->name,
				$post->getURL(),
				$post->description,
				$post->getURL()
			))
		);
	}

	echo json_encode($item);

} else {
	register_error(elgg_echo('thewire:notsaved'));
	echo json_encode(false);
}
