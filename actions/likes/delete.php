<?php
/**
 * Elgg delete like action
 *
 */

gatekeeper();

$entity_guid = (int) get_input('guid');

// Let's see if we can get an entity with the specified GUID
$entity = get_entity($entity_guid);
if (!$entity) {
	register_error(elgg_echo('likes:notfound'));
	return true;
}
// Only like thewire object
if (!in_array($entity->getSubtype(), array('thewire', 'comment'))) {
	register_error(elgg_echo('likes:failure'));
	return true;
}

$user = elgg_get_logged_in_user_entity();

// check to see if the user hasn't already liked the item
if (!check_entity_relationship($user->getGUID(), 'like', $entity_guid)) {
	register_error(elgg_echo('likes:alreadyliked'));
	return true;
}


if (!remove_entity_relationship($user->getGUID(), 'like', $entity_guid)) {
	register_error(elgg_echo('likes:failure'));
	return true;
}

// notify if poster wasn't owner
if ($entity->getOwnerGUID() != $user->getGUID()) {
	//likes_notify_user($entity->getOwnerEntity(), $user, $entity);
}


$item = new stdClass();
$item->guid = $entity_guid;
// likes
$item->likes = elgg_get_entities_from_relationship(array(
	'relationship' => 'like',
	'relationship_guid' => $entity_guid,
	'inverse_relationship' => true,
	'count' => true
));
$item->liked = false;

$likers = elgg_get_entities_from_relationship(array(
	'relationship' => 'like',
	'relationship_guid' => $entity_guid,
	'inverse_relationship' => true,
	'limit' => 3
));
$item->likers = array();
foreach($likers as $liker) {
	$item->likers[] = array(
		'guid' => $liker->getGUID(),
		'name' => $liker->name,
		'username' => $liker->username
	);
}


echo json_encode($item);
