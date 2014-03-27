<?php 
/**
 * Elgg secondary sidebar contents
 *
 * You can override, extend, or pass content to it
 *
 * @uses $vars['sidebar_alt] HTML content for the alternate sidebar
 */

elgg_push_context('owner_block');

// groups and other users get owner block
$user = elgg_get_logged_in_user_entity();

if ($user instanceof ElggGroup || $user instanceof ElggUser) {

	$header = elgg_view('page/components/image_block', array(
		'image' => elgg_view_entity_icon($user, 'normal'),
		'class' => 'sidebar-avatar',
		'body' => '<h3>' . elgg_view('output/url', array(
			'text' => substr_replace($user->name, '<br>', strpos($user->name, " "), 1),
			'href' => $user->getURL()
		)) . '</h3>'
	));

	$body = '';

	echo elgg_view('page/components/module', array(
		'header' => $header,
		'body' => $body,
		'class' => 'elgg-owner-block',
	));
}

elgg_pop_context();

$sidebar = elgg_extract('sidebar_alt', $vars, '');

echo $sidebar;
