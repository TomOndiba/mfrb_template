<?php
/**
 * Layout of the groups profile page
 *
 * @uses $vars['entity']
 */

/* @var ElggGroup $group */
$group = elgg_extract('entity', $vars);

//echo elgg_view('groups/profile/summary', $vars);

if (elgg_group_gatekeeper(false)) {
	if (!$group->isPublicMembership() && !$group->isMember()) {
		echo elgg_view('groups/profile/closed_membership');
	}

	if ($group->isMember()) echo elgg_view_form('thewire/add', array('class' => 'thewire-form')) . elgg_view('input/urlshortener');

	echo '<ul class="elgg-list elgg-list-river" data-page_type="group" data-group_guid="' . $group->getGUID() . '" load-river><div class="elgg-ajax-loader"></div></ul>';
} else {
	if ($group->isPublicMembership()) {
		echo elgg_view('groups/profile/membersonly_open');
	} else {
		echo elgg_view('groups/profile/membersonly_closed');
	}
}
