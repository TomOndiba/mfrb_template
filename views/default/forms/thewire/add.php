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
	'class' => 'mtm thewire-textarea',
	'autoresize' => '',
	'placeholder' => elgg_echo('mfrb:thewire:placeholder')
));

$submit_button = elgg_view('input/submit', array(
	'value' => $text,
	'class' => 'elgg-button elgg-button-submit float-alt',
	'id' => 'thewire-submit-button',
));

$loader = elgg_view('graphics/ajax_loader', array('hidden' => false));

echo <<<HTML
	$post_input

<div class="linkbox hidden phm pvs">
	$loader
</div>

<div class="elgg-foot mts">
	$submit_button
</div>
HTML;
