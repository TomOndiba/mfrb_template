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

	if ($pos = strpos($user->name, " ")) {
		$text = substr_replace($user->name, '<br>', $pos, 0);
	} else {
		$text = $user->name;
	}

	$header = elgg_view('page/components/image_block', array(
		'image' => elgg_view_entity_icon($user, 'normal'),
		'class' => 'sidebar-avatar',
		'body' => '<h3 class="pam">' . elgg_view('output/url', array(
			'text' => $text,
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

	$groups = elgg_get_entities_from_relationship(array(
		'type' => 'group',
		'relationship' => 'member',
		'relationship_guid' => $user->getGUID(),
		'inverse_relationship' => false,
		'joins' => array("JOIN {$dbprefix}groups_entity ge ON e.guid = ge.guid"),
		'order_by' => 'ge.name ASC',
	));

	$body = '';
	foreach ($groups as $group) {
		$group_link = elgg_view('output/url', array(
			'text' => elgg_get_excerpt($group->name, 100),
			'href' => $group->getURL(),
			'class' => 'pas',
			'is_trusted' => true,
		));

		$selected = (elgg_get_page_owner_guid() == $group->getGUID()) ? 'elgg-state-selected' : '';

		$body .= "<li id=\"elgg-group-{$group->getGUID()}\" class=\"elgg-item elgg-item-group $selected\">$group_link</li>";
	}

	$title = elgg_echo('groups:yours');

	echo <<<HTML
<div class="elgg-module-aside mbl">
	<div class="elgg-head">
		<h3>$title</h3>
	</div>
	<ul class="elgg-list sidebar_alt_list">
		$body
	</ul>
</div>
HTML;

// select menu > if (elgg_http_url_is_identical(current_page_url(), $menu_item->getHref())) 

}



elgg_pop_context();

$sidebar = elgg_extract('sidebar_alt', $vars, '');

echo $sidebar;
