<?php
/**
 * Elgg sidebar contents
 *
 * @uses $vars['sidebar'] Optional content that is displayed at the bottom of sidebar
 */

$body = 'à faire...
.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>.<br>test pour voir si ça suit le scroll';

echo elgg_view_module('aside', elgg_echo('mrfb:river:block_infos'), $body, array(
	'class' => 'elgg-river-info ptm',
));

//echo elgg_view_menu('page', array('sort_by' => 'name'));

//echo elgg_view('page/elements/owner_block', $vars);

// optional 'sidebar' parameter
if (isset($vars['sidebar'])) {
	//echo $vars['sidebar'];
}