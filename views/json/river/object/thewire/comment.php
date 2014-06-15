<?php

$comment = $vars['comment'];
global $fb; $fb->info($comment);
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

echo array(
	'subject' => $comment->getOwnerEntity()->toObject(),
	'object_guid' => $comment->getGUID(),
	'posted' => $comment->time_created,
	'message' => $comment->description,
	'likes' => $comment_likes,
	'liked' => $comment_liked,
	'likers' => $comment_likers
);