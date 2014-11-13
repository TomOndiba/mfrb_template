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
const PLUGIN_ID = 'mfrb_template';


/**
 * Load required files
 */
require_once(dirname(__FILE__) . "/lib/plugin_init/handlers.php");
require_once(dirname(__FILE__) . "/lib/plugin_init/functions.php");
require_once(dirname(__FILE__) . "/lib/plugin_init/hooks.php");
require_once(dirname(__FILE__) . "/vendors/scss.inc.php");


// unregister group discussion
elgg_unregister_event_handler('init', 'system', 'discussion_init');

/**
 * mfrb_template init
 */
elgg_register_event_handler('init','system','mfrb_template_init');

function mfrb_template_init() {

	$root = dirname(__FILE__);
	$http_base = '/mod/' . PLUGIN_ID;

	// actions
	$action_path = "$root/actions";

	elgg_register_action('comment/save', "$action_path/comment/save.php");
	elgg_register_action('thewire/add', "$action_path/thewire/add.php");
	elgg_register_action('thewire/delete', "$action_path/thewire/delete.php");
	elgg_register_action('thewire/edit', "$action_path/thewire/edit.php");
	elgg_register_action('like', "$action_path/likes/add.php");
	elgg_register_action('unlike', "$action_path/likes/delete.php");

	elgg_extend_view('css/elgg', PLUGIN_ID . '/css');
	elgg_extend_view('js/elgg', PLUGIN_ID . '/js');
	elgg_extend_view('page/elements/foot', PLUGIN_ID . '/handlebars_templates');

	elgg_register_ajax_view('river/comments');

	/**
	 * Register or load external javascript and css files.
	 */
	elgg_register_js('respond', 'mod/' . PLUGIN_ID . '/vendors/respond.min.js');
	elgg_load_js('respond');

	// js files only loaded by require.js
	elgg_define_js('history', array( // history.js for full ajax and play with HTML5 pushState
		'src' => "$http_base/vendors/jquery.history",
		'deps' => array('jquery')
	));
	elgg_define_js('scrollTo', array(
		'src' => "$http_base/vendors/jquery.scrollTo/jquery.scrollTo.min",
		'deps' => array('jquery')
	));
	elgg_define_js('select2', array(
		'src' => "$http_base/vendors/select2/select2",
		'deps' => array('jquery')
	));

	/*
	 * Register librairies
	 */
	elgg_register_library('thewire', $root . '/lib/thewire.php');
	elgg_register_library('elgg:groups', "$root/lib/groups.php");

	/*
	 * Register page handlers
	 */
	// activity
	elgg_unregister_page_handler('activity');
	elgg_register_page_handler('activity', 'activity_page_handler');
	elgg_register_page_handler('message', 'message_page_handler');
	// register avatar handler
	elgg_register_page_handler('avatar', 'avatar_handler');

	/**
	 * Plugins hook handlers
	 */

	// Add mfrb_execute_js in ajax forward
	elgg_register_plugin_hook_handler('output', 'ajax', 'mfrb_output_ajax_plugin_hook');

	// add metadatas in head
	elgg_register_plugin_hook_handler('head', 'page', 'mfrb_setup_head');

	// hook to modify menus
	elgg_register_event_handler('pagesetup', 'system', 'mfrb_page_setup');

	// hook to get avatar from gravatar
	elgg_register_plugin_hook_handler('entity:icon:url', 'user', 'gravatar_avatar_hook', 900);

	// Hook for thewire river menu
	elgg_unregister_plugin_hook_handler('register', 'menu:river', 'likes_river_menu_setup');
	elgg_register_plugin_hook_handler('register', 'menu:river', 'mfrb_likes_river_menu_setup', 400);

	// Hook for page_owner
	// ????? elgg_unregister_plugin_hook_handler('page_owner', 'system', 'default_page_owner_handler');

	// non-members do not get visible links to RSS feeds
	if (!elgg_is_logged_in()) {
		elgg_unregister_plugin_hook_handler('output:before', 'layout', 'elgg_views_add_rss_link');
	}

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
