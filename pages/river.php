<?php
/**
 * Main activity stream list page
 */

$user = elgg_get_logged_in_user_entity();

global $CONFIG, $jsonexport;
$dbprefix = $CONFIG->dbprefix;

$options = array('subtype' => 'thewire');

$page_type = preg_replace('[\W]', '', get_input('page_type', 'all'));
$posted = get_input('posted');

if ($posted) {
	$options['posted_time_upper'] = (int) $posted -1;
}

switch ($page_type) {
	case 'mine':
		$page_filter = 'mine';
		$options['subject_guid'] = $user->getGUID();
		break;
	case 'owner':
		$page_filter = 'subject';
		$options['subject_guid'] = get_input('subject_guid', false);
		if (!$options['subject_guid']) {
			$subject_username = get_input('subject_username', '', false);
			$subject = get_user_by_username($subject_username);
			if (!$subject) {
				register_error(elgg_echo('river:subject:invalid_subject'));
				forward('');
			}
			$options['subject_guid'] = $subject->getGUID();
		}
		//$options['joins'][] = "JOIN {$dbprefix}river r2 ON r2.object_guid = rv.target_guid";
		//$options['wheres'][] = "( r2.subject_guid IN (".$subject->getGUID().") AND rv.subtype = 'thewire' ) OR (1 = 1) ";
		break;
	case 'group':
		$page_filter = 'group';
		$options['target_guid'] = get_input('group_guid', false);
		break;
	case 'friends':
		$page_filter = 'friends';
		$options['relationship_guid'] = $user->getGUID();
		$options['relationship'] = 'friend';
		break;
	case 'view':
		$options['object_guid'] = get_input('object_guid');
	default:
		$page_filter = 'all';
		break;
}
/*
SELECT COUNT(DISTINCT rv.id) AS total 
FROM elgg_river rv 
JOIN elgg_entities oe ON rv.object_guid = oe.guid 
LEFT JOIN elgg_entities te ON rv.target_guid = te.guid 
WHERE (rv.target_guid = r2.target_guid) 
AND (rv.subject_guid IN (93)) 
AND (((rv.subtype = 'thewire'))) 
AND ((1 = 1) AND (oe.enabled = 'yes'))
AND (((1 = 1) AND (te.enabled = 'yes')) OR te.guid IS NULL)*/

if (elgg_get_viewtype() == 'default') {

	$header = elgg_view_form('thewire/add', array('class' => 'thewire-form')) . elgg_view('input/urlshortener');

	$sidebar = elgg_view('river/mfrb_sidebar');
	$sidebar_alt = elgg_view('river/mfrb_sidebar_alt');

	$short_site_url = preg_replace('/[^a-z0-9]/i', '', elgg_get_site_url()) . 'activity';

	$data = array(
		'all' => "data-page_type=\"all\" id=\"{$short_site_url}\"",
		'mine' => "data-page_type=\"mine\" id=\"{$short_site_url}owner{$user->username}\"",
		'friends' => "data-page_type=\"friends\" id=\"{$short_site_url}friends{$user->username}\""
	);

	$hidden = array(
		'all' => $page_type == 'all' ? '' : 'hidden',
		'mine' => $page_type == 'mine' ? '' : 'hidden',
		'friends' => $page_type == 'friends' ? '' : 'hidden'
	);

	if ($subject_username) $data .= 'data-subject_username="' . $subject_username . '"';

	//$activity = elgg_list_river($options);
	$activity = <<<HTML
<ul class="elgg-list elgg-list-river storable-tab {$hidden['all']}" {$data['all']} load-river><div class="elgg-ajax-loader"></div></ul>
<ul class="elgg-list elgg-list-river storable-tab {$hidden['mine']}" {$data['mine']} load-river><div class="elgg-ajax-loader"></div></ul>
<ul class="elgg-list elgg-list-river storable-tab {$hidden['friends']}" {$data['friends']} load-river><div class="elgg-ajax-loader"></div></ul>
HTML;

	$params = array(
		'header' => $header,
		'sidebar' => $sidebar,
		'sidebar_alt' => $sidebar_alt,
		'content' => $activity,
		'filter_context' => $page_filter,
		'class' => 'elgg-river-layout',
	);

	$body = elgg_view_layout('river', $params);

	echo elgg_view_page(elgg_echo('river:all'), $body);

} else {

	echo elgg_view_page(elgg_echo('river:all'), elgg_list_river($options));

}
