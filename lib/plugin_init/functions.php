<?php
/**
 *	mfrb_template plugin
 *	@package mfrb_template
 *	@author Emmanuel Salomon @ManUtopiK
 *	@license GNU Affero General Public License, version 3 or late
 *	@link https://github.com/revenudebase/mfrb_template
 *
 * PHP functions library file
 *
 **/



/**
 * Store some javascript code to be executed when page change in full ajax.
 * @param  Array/String      $code      Some javascript code to be executed
 * @param  Boolean           $count     If true, return number of items
 * @return Boolean/Array                Return true when code is stored / A call with no var (mfrb_execute_js()) return an array of stored items, and clear this array.
 */
function mfrb_execute_js($code = null, $count = false) {
	if (!isset($_SESSION['js_code'])) {
		$_SESSION['js_code'] = array();
	}

	if (!$count) {
		if (!empty($code) && is_array($code)) {
			$_SESSION['js_code'] = array_merge($_SESSION['js_code'], $code);
			return true;
		} else if (!empty($code) && is_string($code)) {
			$_SESSION['js_code'][] = $code;
			return true;
		} else if (is_null($code)) {
			$returnarray = $_SESSION['js_code'];
			$_SESSION['js_code'] = array(); // clear variable fo this session
			return $returnarray;
		}
	} else {
		return count($_SESSION['js_code']);
	}
	return false;
}



function get_wire_object($item) {
	$object = $item->toObject();

	//$mention = elgg_extract('mention', $vars, false);

	$subject = $item->getSubjectEntity();
	$item->subject = $subject->toObject();

	$object = $item->getObjectEntity();

	if ($object->container_guid) {
		$container = get_entity($object->container_guid);
		if (elgg_instanceof($container, 'group')) {
			$container_link = "<a href=\"{$container->getURL()}\" class=\"elgg-river-target\">{$container->name}</a>";
			$item->summary = elgg_echo('river:create:object:thewire', array($container_link));
		}
	}



	$excerpt = strip_tags($object->description);

	//if ($mention) $excerpt = deck_river_highlight_mention($excerpt, $mention);

	if ($object->link_url) {
		$item->attachment = array(
			'link_description' => $object->link_description,
			'link_name' => $object->link_name,
			'link_picture' => $object->link_picture ? $object->link_picture : null,
			'link_url' => $object->link_url
		);
	}



	// Comments
	$item->comments_count = $object->countComments();

	$comments = array_reverse(elgg_get_entities(array(
		'type' => 'object',
		'subtype' => 'comment',
		'container_guid' => $object->getGUID(),
		'limit' => 5,
	)));
	$item->comments = array();
	foreach ($comments as $comment) {
		$item->comments[] = get_comment_river($comment);
	}



	// likes
	$item->likes = elgg_get_entities_from_relationship(array(
		'relationship' => 'like',
		'relationship_guid' => $object->getGUID(),
		'inverse_relationship' => true,
		'count' => true
	));
	$item->liked = check_entity_relationship(elgg_get_logged_in_user_guid(), 'like', $object->getGUID()) ? true : false;

	$likers = elgg_get_entities_from_relationship(array(
		'relationship' => 'like',
		'relationship_guid' => $object->getGUID(),
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

	//if ($object->reply) $item->responses = $object->wire_thread;

	if ($object->method) $item->method = $object->method;

	$item->message = $excerpt;

	return $item;
}



function get_comment_river($comment) {
	// likes
	$comment_likes = elgg_get_entities_from_relationship(array(
		'relationship' => 'like',
		'relationship_guid' => $comment->getGUID(),
		'inverse_relationship' => true,
		'count' => true
	));
	$comment_liked = check_entity_relationship(elgg_get_logged_in_user_guid(), 'like', $comment->getGUID()) ? true : false;

	$likers = elgg_get_entities_from_relationship(array(
		'relationship' => 'like',
		'relationship_guid' => $comment->getGUID(),
		'inverse_relationship' => true,
		'limit' => 3
	));
	$comment_likers = array();
	foreach($likers as $liker) {
		$comment_likers[] = array(
			'guid' => $liker->getGUID(),
			'name' => $liker->name,
			'username' => $liker->username
		);
	}

	return array(
		'subject' => $comment->getOwnerEntity()->toObject(),
		'object_guid' => $comment->getGUID(),
		'container_guid' => $comment->container_guid,
		'posted' => $comment->time_created,
		'message' => $comment->description,
		'likes' => $comment_likes,
		'liked' => $comment_liked,
		'likers' => $comment_likers
	);
}



/**
 * Web service for read latest wire post of user
 *
 * @param string $username username of author
 *
 * @return bool
 */
function river_get_items($limit = 10, $offset = 0) {
	$user = elgg_get_logged_in_user_guid();

	$params = array(
		'types' => 'object',
		'subtypes' => 'thewire',
		'owner_guid' => $user,
		'limit' => $limit,
		'offset' => $offset,
	);
	$latest_wire = elgg_get_entities($params);

	foreach($latest_wire as $single ) {
		$wire[$single->guid]['time_created'] = $single->time_created;
		$wire[$single->guid]['description'] = $single->description;
	}
	return $wire;
}

elgg_ws_expose_function('river.get_items',
	'river_get_items',
	array(
		'limit' => array(
			'type' => 'int',
			'required' => false
		),
		'offset' => array(
			'type' => 'int',
			'required' => false
		)
	),
	"Read lates wire post",
	'GET',
	false,
	true
);






