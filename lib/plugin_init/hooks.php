<?php
/**
 *	Ggouv_template plugin - for elgg 1.9+
 *	@package ggouv_template
 *	@author Emmanuel Salomon @ManUtopiK
 *	@license GNU Affero General Public License, version 3 or late
 *	@link https://github.com/ggouv/ggouv_template
 *
 *	File for plugin hooks
 *
 **/


/**
 * Override page head to add metadatas
 */
function mrfb_setup_head($hook, $type, $return, $params) {
	// rewrite title
	if (empty($params['title'])) {
		$return['title'] = elgg_get_config('sitename');
	} else {
		$return['title'] = $params['title'] . ' | ' . elgg_get_config('sitename');
	}

	// add viewport for mobile
	$return['metas'][] = array(
		'name' => 'viewport',
		'content' => 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'
	);

	// Remove favicons
	foreach ($return['links'] as $key => $link) {
		if ($link['rel'] == 'icon') unset($return['links'][$key]);
	}
	// Add ggouv favicon
	$return['links'][] = array(
		'id' => 'favicon',
		'rel' => 'icon',
		'sizes' => '48x48',
		'type' => 'image/png',
		'href' => elgg_normalize_url('mod/mrfb/graphics/favicon/favicon.png'),
	);
	$return['links'][] = array(
		'rel' => 'apple-touch-icon',
		'href' => elgg_normalize_url('mod/aalborg_theme/graphics/homescreen.png'),
	);

	return $return;
}



/**
 * Hook called in ajax output, here : /engine/classes/Elgg/ActionsService.php line 311
 */
function mfrb_output_ajax_plugin_hook($hook, $type, $return, $params) {
	// Add code to be executed as javascript
	$code = '';
	foreach (mrfb_execute_js() as $code) {
		$return['js_code'] .= $code;
	}

	return $return;
}
