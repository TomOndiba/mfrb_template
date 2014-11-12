<?php
/**
 * User's wire posts
 *
 */

$owner = elgg_get_page_owner_entity();
if (!$owner) {
	forward(REFERER);
}

$title = elgg_echo('thewire:message:user', array($owner->name));

elgg_push_breadcrumb(elgg_echo('activity'), 'activity');
elgg_push_breadcrumb($title);

$loader = elgg_view('graphics/ajax_loader', array('hidden' => false));

$content = <<<HTML
<div id="{$owner->guid}-river-activity">
	<ul class="elgg-list elgg-list-river" load-river data-page_type="owner" data-subject_guid="{$owner->getGUID()}">$loader</ul>
</div>
HTML;

$body = elgg_view_layout('content', array(
	'filter_override' => '',
	'content' => $content,
	'title' => $title,
	'sidebar' => elgg_view('thewire/sidebar'),
));

echo elgg_view_page($title, $body);
