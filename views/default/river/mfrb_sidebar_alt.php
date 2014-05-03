<?php
/**
 * Elgg secondary sidebar contents
 *
 * You can override, extend, or pass content to it
 *
 * @uses $vars['sidebar_alt] HTML content for the alternate sidebar
 */

// groups and other users get owner block
$user = elgg_get_logged_in_user_entity();

elgg_push_context('owner_block');

if ($user instanceof ElggUser) {

	$header = elgg_view('page/components/image_block', array(
		'image' => elgg_view_entity_icon($user, 'normal'),
		'class' => 'sidebar-avatar',
		'body' => '<h3>' . elgg_view('output/url', array(
			'text' => substr_replace($user->name, '<br>', strpos($user->name, " "), 0),
			'href' => $user->getURL()
		)) . '</h3>'
	));

	$body = '';

	echo elgg_view('page/components/module', array(
		'header' => $header,
		'body' => $body,
		'class' => 'elgg-owner-block',
	));


	// groups
	$dbprefix = elgg_get_config('dbprefix');

	$body = elgg_list_entities_from_relationship(array(
		'type' => 'group',
		'relationship' => 'member',
		'relationship_guid' => $user->getGUID(),
		'inverse_relationship' => false,
		'full_view' => 'small_list',
		'joins' => array("JOIN {$dbprefix}groups_entity ge ON e.guid = ge.guid"),
		'order_by' => 'ge.name ASC',
		'no_results' => elgg_echo('groups:add'),
		'list_class' => 'sidebar_alt_list',
		'pagination' => false
	));

	echo elgg_view_module('aside', elgg_echo('groups:yours'), $body);

}



elgg_pop_context();

$sidebar = elgg_extract('sidebar_alt', $vars, '');

echo $sidebar;
