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

$loader = elgg_view('graphics/ajax_loader', array('hidden' => false));

$notify = elgg_echo('mfrb:thewire:notify:placeholder');
$submit_button = elgg_view('input/submit', array(
	'value' => $text,
	'class' => 'elgg-button elgg-button-submit float-alt',
	'id' => 'thewire-submit-button',
));

echo <<<HTML
	$post_input

<div class="linkbox hidden phm pvs">
	$loader
</div>

<div class="notifybox mbn">
	<input name="notified_users" type="text" class="select2" placeholder="{$notify}" width="100%" />
</div>
<div class="elgg-foot mts">
	$submit_button
</div>
HTML;
