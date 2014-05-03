<?php
/**
 *	mfrb_template plugin
 *	@package mfrb_template
 *	@author Emmanuel Salomon @ManUtopiK
 *	@license GNU Affero General Public License, version 3 or late
 *	@link https://github.com/revenudebase/mfrb_template
 *
 * Settings file
 **/


$non_logged_in_home_text_string = elgg_echo('mfrb:settings:non_logged_in_home_text');
$non_logged_in_home_text_view = elgg_view('input/longtext', array(
	'name' => 'params[non_logged_in_home_text]',
	'value' => $vars['entity']->non_logged_in_home_text,
	'class' => 'elgg-input-thin',
));

// display html

echo <<<__HTML
<br />
<div><label>$non_logged_in_home_text_string</label><br>$non_logged_in_home_text_view</div>
__HTML;
