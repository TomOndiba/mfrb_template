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

$dropzone = elgg_view('input/dropzone', array(
	'name' => 'upload_guids',
	'max' => 10,
	'multiple' => true,
	//'container_guid' => $container_guid, // optional
	'subtype' => 'file', // subtype of the files to be created
));

$toggle_buttons = elgg_view('output/url', array(
	'text' => elgg_echo('thwire:message:add_file'),
	'href' => '#',
	'class' => 'toggle-dropzone noajax fi-upload-cloud pbrs pas float'
));

$submit_button = elgg_view('input/submit', array(
	'value' => $text,
	'class' => 'elgg-button elgg-button-submit float-alt',
	'id' => 'thewire-submit-button',
));

echo <<<HTML
	$post_input

<div class="thewire-extra hidden">
	<div class="linkbox hidden phm pbs">
		$loader
	</div>

	<div class="notifybox mbn">
		<input name="notified_users" type="text" class="select2" placeholder="{$notify}" width="100%" />
	</div>

	<div class="filesbox hidden pvs">
		$dropzone
	</div>

	<div class="elgg-foot pbs">
		$toggle_buttons
		$submit_button
	</div>
</div>
HTML;
