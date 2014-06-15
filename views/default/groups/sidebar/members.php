<?php
/**
 * Group members sidebar
 *
 * @package ElggGroups
 *
 * @uses $vars['entity'] Group entity
 * @uses $vars['limit']  The number of members to display
 */

$limit = elgg_extract('limit', $vars, 14);

$all_link = elgg_view('output/url', array(
	'href' => 'groups/members/' . $vars['entity']->guid,
	'text' => '',
	'class' => 'more fi-plus',
	'is_trusted' => true,
));

$entities = elgg_get_entities_from_relationship(array(
	'relationship' => 'member',
	'relationship_guid' => $vars['entity']->guid,
	'inverse_relationship' => true,
	'type' => 'user',
	'limit' => $limit
));

$body = '<ul class="elgg-gallery elgg-gallery-users">';

elgg_push_context('gallery');

foreach ($entities as $item) {
	$body .= "<li id=\"elgg-user-{$item->getGUID()}\" class=\"elgg-item\">";
	$body .= elgg_view_list_item($item);
	$body .= '</li>';
}

elgg_pop_context();

$body .= "<li>$all_link</li></ul>";

echo elgg_view_module('aside', elgg_echo('groups:members'), $body);
