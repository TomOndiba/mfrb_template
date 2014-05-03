<?php
/**
 * Wire add form body
 *
 * @uses $vars['post']
 */

$post = elgg_extract('post', $vars);

$text = elgg_echo('post');
if ($post) {
	$text = elgg_echo('reply');
}

$post_input = elgg_view('input/plaintext', array(
	'name' => 'body',
	'class' => 'mtm',
	'id' => 'thewire-textarea',
	'rows' => 3
));

$submit_button = elgg_view('input/submit', array(
	'value' => $text,
	'id' => 'thewire-submit-button',
));

$loader = elgg_view('graphics/ajax_loader', array('hidden' => false));

echo <<<HTML
	$post_input

<div id="linkbox" class="hidden phm pvs">
	$loader
</div>

<div class="elgg-foot mts">
	$submit_button
</div>
HTML;
