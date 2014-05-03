<?php
/**
 * Walled garden login
 */

$welcome = elgg_get_plugin_setting('non_logged_in_home_text', 'mfrb_template');

$menu = elgg_view_menu('walled_garden', array(
	'sort_by' => 'priority',
	'class' => 'elgg-menu-general elgg-menu-hz',
));

$login_box = elgg_view('core/account/login_box', array('module' => 'walledgarden-login'));

echo <<<HTML
<div class="elgg-col elgg-col-1of2">
	<div class="elgg-inner">
		$welcome
		$menu
	</div>
</div>
<div class="elgg-col elgg-col-1of2">
	<div class="elgg-inner">
		$login_box
	</div>
</div>
HTML;
