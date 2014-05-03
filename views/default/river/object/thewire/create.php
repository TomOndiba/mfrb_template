<?php
/**
 * File river view.
 */

$object = $vars['item']->getObjectEntity();

$subject = $vars['item']->getSubjectEntity();
$subject_link = elgg_view('output/url', array(
	'href' => $subject->getURL(),
	'text' => $subject->name,
	'class' => 'elgg-river-subject',
	'is_trusted' => true,
));

$excerpt = strip_tags($object->description);
$excerpt = $excerpt; //thewire_filter($excerpt);

$attachments = elgg_view('likes/count', array('entity' => $object));

echo elgg_view('river/elements/layout', array(
	'item' => $vars['item'],
	'summary' => $subject_link,
	'message' => $excerpt,
	'attachments' => $attachments,
	'responses' => false
));