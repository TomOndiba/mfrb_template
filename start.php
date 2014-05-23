<?php
/**
 *	mfrb_template plugin
 *	@package mfrb_template
 *	@author Emmanuel Salomon @ManUtopiK
 *	@license GNU Affero General Public License, version 3 or late
 *	@link https://github.com/revenudebase/mfrb_template
 **/



/**
 * Define global static variable
 */
define('MFRB_TEMPLATE', true); // usefull to say others plugins mfrb_template is active



/**
 * Load required files
 */
require_once(dirname(__FILE__) . "/lib/plugin_init/functions.php");
require_once(dirname(__FILE__) . "/lib/plugin_init/hooks.php");
require_once(dirname(__FILE__) . "/vendors/scss.inc.php");



/**
 * mfrb_template init
 */
elgg_register_event_handler('init','system','mfrb_template_init');

function mfrb_template_init() {

	$root = dirname(__FILE__);
	$http_base = '/mod/mfrb_template';

	// actions
	$action_path = "$root/actions/mfrb_template";

	elgg_register_action('thewire/add', "$root/actions/thewire/add.php");
	elgg_register_action('thewire/delete', "$root/actions/thewire/delete.php");

	elgg_extend_view('css/elgg', 'mfrb_template/css');
	elgg_extend_view('js/elgg', 'mfrb_template/js');

	/**
	 * Register or load external javascript and css files.
	 */
	elgg_register_js('respond', 'mod/mfrb_template/vendors/respond.min.js');
	elgg_load_js('respond');

	// js files only loaded by require.js
	elgg_register_js('history', array( // history.js for full ajax and play with HTML5 pushState
		'src' => "$http_base/vendors/jquery.history",
		'deps' => array('jquery')
	));
	elgg_register_js('scrollTo', array(
		'src' => "$http_base/vendors/jquery.scrollTo/jquery.scrollTo.min",
		'deps' => array('jquery')
	));

	/*
	 * Register librairies
	 */
	elgg_register_library('thewire', $root . '/lib/thewire.php');

	/*
	 * Register page handlers
	 */
	elgg_register_page_handler('activity', 'activity_page_handler');


	/**
	 * Plugins hook handlers
	 */

	// Add mfrb_execute_js in ajax forward
	elgg_register_plugin_hook_handler('output', 'ajax', 'mfrb_output_ajax_plugin_hook');

	// add metadatas in head
	elgg_register_plugin_hook_handler('head', 'page', 'mfrb_setup_head');

	// hook to modify menus
	elgg_register_event_handler('pagesetup', 'system', 'mfrb_page_setup');

	// Hook for thewire river menu
	elgg_unregister_plugin_hook_handler('register', 'menu:river', 'likes_river_menu_setup');
	elgg_register_plugin_hook_handler('register', 'menu:river', 'mfrb_likes_river_menu_setup', 400);

	// non-members do not get visible links to RSS feeds
	if (!elgg_is_logged_in()) {
		elgg_unregister_plugin_hook_handler('output:before', 'layout', 'elgg_views_add_rss_link');
	}

}


function activity_page_handler() {

	if (elgg_is_logged_in()) {

		// get user settings
		/*$user = elgg_get_logged_in_user_entity();
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
		}*/

		elgg_set_context('activity');
		include_once dirname(__FILE__) . '/pages/river.php';

	} else {
		forward('');
	}

	return true;
}



/**
 * Rearrange menu items
 */
function mfrb_page_setup() {

	elgg_unregister_menu_item('site', 'thewire');
	elgg_unregister_menu_item('topbar', 'dashboard');

	if (elgg_is_active_plugin('dashboard')) {
		elgg_register_menu_item('site', array(
			'name' => 'dashboard',
			'href' => 'dashboard',
			'text' => elgg_echo('dashboard'),
		));
	}

	if (!elgg_is_logged_in()) {
		elgg_unregister_menu_item('site', 'activity');
		elgg_unregister_menu_item('site', 'groups');
		elgg_unregister_menu_item('site', 'members');
	} else { // logged

		elgg_unregister_menu_item('topbar', 'friends');
		elgg_unregister_menu_item('topbar', 'profile');
		elgg_unregister_menu_item('topbar', 'messages');

		// menu messages
		$text = '';
		// get unread messages
		$num_messages = 6; //(int)messages_count_unread();
		if ($num_messages != 0) {
			$text .= "<span class=\"messages-new\">$num_messages</span>";
		}
		$text .=  '<span class="fi-mail"></span>';
		elgg_register_menu_item('topbar', array(
			'name' => 'messages',
			'href' => 'mesisages/inbox/' . elgg_get_logged_in_user_entity()->username,
			'text' => $text,
			'section' => 'alt',
			'priority' => 99
		));

		// menu account with submenu
		$user = elgg_get_logged_in_user_entity();
		$avatar_and_username = elgg_view('output/img', array(
			'src' => $user->getIconURL('tiny'),
			'alt' => $user->username,
			'title' => $user->username,
			'class' => 'float mrm',
		)) . '<div>' . $user->username . '</div>';
		elgg_register_menu_item('topbar', array(
			'name' => 'account',
			'text' => $avatar_and_username,
			'href' => "#",
			'priority' => 100,
			'section' => 'alt',
			'link_class' => 'elgg-topbar-dropdown mfrb-icon',
		));

		$item = elgg_get_menu_item('topbar', 'usersettings');
		if ($item) {
			$item->setParentName('account');
			$item->setText(elgg_echo('settings'));
			$item->setPriority(103);
			$item->addLinkClass('mfrb-icon');
		}

		$item = elgg_get_menu_item('topbar', 'logout');
		if ($item) {
			$item->setParentName('account');
			$item->setText(elgg_echo('logout'));
			$item->setPriority(104);
			$item->addLinkClass('mfrb-icon');
		}

		if (elgg_is_admin_logged_in()) {
			$item = elgg_get_menu_item('topbar', 'administration');
			if ($item) {
				$item->setParentName('account');
				$item->setText(elgg_echo('admin'));
				$item->setPriority(101);
				$item->addLinkClass('mfrb-icon noajax');
			}
		}

		// menu site notifications
		if (elgg_is_active_plugin('site_notifications')) {
			$item = elgg_get_menu_item('topbar', 'site_notifications');
			if ($item) {
				$item->setParentName('account');
				$item->setText(elgg_echo('site_notifications:topbar'));
				$item->setPriority(102);
			}
		}

		// menu reported content
		if (elgg_is_active_plugin('reportedcontent')) {
			$item = elgg_unregister_menu_item('footer', 'report_this');
			if ($item) {
				$item->setText(elgg_view_icon('report-this'));
				$item->setPriority(500);
				$item->setSection('default');
				elgg_register_menu_item('extras', $item);
			}
		}
	}

}
