<?php
/**
 *	mrfb_template plugin
 *	@package mrfb_template
 *	@author Emmanuel Salomon @ManUtopiK
 *	@license GNU Affero General Public License, version 3 or late
 *	@link https://github.com/revenudebase/mrfb_template
 **/

elgg_register_event_handler('init','system','mrfb_template_init');

/**
 * mrfb_template init
 */
function mrfb_template_init() {

	$root = dirname(__FILE__);
	$http_base = '/mod/mrfb_template';

	// actions
	$action_path = "$root/actions/mrfb_template";

	elgg_extend_view('css/elgg', 'mrfb_template/css');
	elgg_extend_view('js/elgg', 'mrfb_template/js');

	/**
	 * Register or load external javascript and css files.
	 */

	// js files only loaded by require.js
	elgg_register_js('history', array( // history.js for full ajax and play with HTML5 pushState
		'src' => "$http_base/vendors/jquery.history.js",
		'deps' => array('jquery')
	));

	// register page handlers
	//elgg_register_page_handler('activity', 'activity_page_handler');
}


function activity_page_handler($page) {

	if (elgg_is_logged_in()) {

		// get user settings
		$user = elgg_get_logged_in_user_entity();
		$user_river_settings = json_decode($user->getPrivateSetting('deck_river_settings'), true);

		// if first time, create settings for this user
		if ( !$user_river_settings || !is_array($user_river_settings) ) {
			$set = str_replace("&gt;", ">", elgg_get_plugin_setting('default_columns', 'elgg-deck_river'));
			if (!$set) $set = elgg_echo('deck_river:settings:default_column:default');
			eval("\$defaults = $set;");
			$user->setPrivateSetting('deck_river_settings', json_encode($defaults));
			$user_river_settings = $defaults;
		}

		if (!isset($page[0])) {
			reset($user_river_settings);
			$page[0] = key($user_river_settings);
		}

		elgg_set_context($page[0]);
		include_once dirname(__FILE__) . '/pages/river.php';

	} else {
		forward('');
	}

	return true;
}