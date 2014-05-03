<?php
/**
 * Main activity stream list page
 */

$options = array('subtype' => 'thewire');

$page_type = preg_replace('[\W]', '', get_input('page_type', 'all'));

switch ($page_type) {
	case 'mine':
		$page_filter = 'mine';
		$options['subject_guid'] = elgg_get_logged_in_user_guid();
		break;
	case 'owner':
		$subject_username = get_input('subject_username', '', false);
		$subject = get_user_by_username($subject_username);
		if (!$subject) {
			register_error(elgg_echo('river:subject:invalid_subject'));
			forward('');
		}
		$page_filter = 'subject';
		$options['subject_guid'] = $subject->guid;
		break;
	case 'friends':
		$page_filter = 'friends';
		$options['relationship_guid'] = elgg_get_logged_in_user_guid();
		$options['relationship'] = 'friend';
		break;
	default:
		$page_filter = 'all';
		break;
}

$header = elgg_view_form('thewire/add', array('class' => 'thewire-form')) . elgg_view('input/urlshortener');

$sidebar = elgg_view('river/mfrb_sidebar');
$sidebar_alt = elgg_view('river/mfrb_sidebar_alt');

$activity = elgg_list_river($options);

if (!$activity) {
	$activity = elgg_echo('river:none');
}

$params = array(
	'header' => $header,
	'content' => $activity,
	'sidebar' => $sidebar,
	'sidebar_alt' => $sidebar_alt,
	'filter_context' => $page_filter,
	'class' => 'elgg-river-layout',
);

$body = elgg_view_layout('river', $params);

echo elgg_view_page(elgg_echo('river:all'), $body);
