<?php
/**
 * View conversation thread
 */

$wire_guid = get_input('guid');
$wire = get_entity($wire_guid);

if (!$wire) {
	register_error(elgg_echo('noaccess'));
	forward(REFERER);
}

$owner = $wire->getOwnerEntity();
if (!$owner) {
	forward(REFERER);
}

$title = elgg_echo('thewire:by', array($owner->name));

if ($wire->getContainerEntity() instanceof ElggGroup)
	$title .= ' ' . elgg_echo('thewire:message:group', array($wire->getContainerEntity()->name));

elgg_push_breadcrumb(elgg_echo('activity'), 'activity');
elgg_push_breadcrumb(elgg_echo('thewire:message:user', array($owner->name)), 'message/owner/' . $owner->username);

$loader = elgg_view('graphics/ajax_loader', array('hidden' => false));

$content = <<<HTML
<div id="message-river-activity">
	<ul class="elgg-list elgg-list-river single-view" load-river data-page_type="view" data-object_guid="{$wire_guid}">$loader</ul>
</div>
HTML;

$body = elgg_view_layout('content', array(
	'filter' => false,
	'content' => $content,
	'title' => $title,
));

echo elgg_view_page($title, $body);
