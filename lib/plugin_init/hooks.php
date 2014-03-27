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
 * Hook called in ajax output, here : /engine/classes/Elgg/ActionsService.php line 308
 */
function mfrb_output_ajax_plugin_hook($hook, $type, $return, $params) {
	// Add code to be executed as javascript
	$code = '';
	foreach (mrfb_execute_js() as $code) {
		$return['js_code'] .= $code;
	}

	return $return;
}



/**
 * Register user slidr-group with default widgets
 *
 * @param unknown_type $hook
 * @param unknown_type $type
 * @param unknown_type $return
 * @param unknown_type $params
 * @return array
 */
function slidr_group_default_widgets($hook, $type, $return, $params) {
	$return[] = array(
		'name' => elgg_echo('slidr-group'),
		'widget_context' => 'slidr-group',
		'widget_columns' => 1,

		'event' => 'create',
		'entity_type' => 'user',
		'entity_subtype' => ELGG_ENTITIES_ANY_VALUE,
	);

	return $return;
}



/**
 * Add links/info to entity menu particular to group entities
 */
function ggouv_groups_entity_menu_setup($hook, $type, $return, $params) {
	if (elgg_in_context('widgets')) {
		return $return;
	}

	$handler = elgg_extract('handler', $params, false);
	if ($handler != 'groups') {
		return $return;
	}

	foreach ($return as $index => $item) {
		if (in_array($item->getName(), array('access', 'likes', 'edit', 'delete'))) {
			unset($return[$index]);
		}
	}

	$entity = $params['entity'];

	// membership type
	$membership = $entity->membership;
	if ($membership == ACCESS_PUBLIC) {
		if (in_array( $entity->getSubtype(), array('metagroup', 'typogroup', 'localgroup'))) {
			$mem = elgg_echo("groups:" . $entity->getSubtype());
		} else {
			$mem = elgg_echo("groups:open");
		}
	} else {
		$mem = elgg_echo("groups:closed");
	}
	$options = array(
		'name' => 'membership',
		'text' => $mem,
		'item_class' => 'prs',
		'href' => false,
		'priority' => 100,
	);
	$return[] = ElggMenuItem::factory($options);

	// number of members
	if (!in_array( $entity->getSubtype(), array('metagroup', 'typogroup'))) {
		$num_members = get_group_members($entity->guid, 10, 0, 0, true);
		$members_string = elgg_echo('groups:member');
		$options = array(
			'name' => 'members',
			'text' => $num_members . ' ' . $members_string,
			'item_class' => 'phs',
			'href' => false,
			'priority' => 200,
		);
		$return[] = ElggMenuItem::factory($options);
	}

	// feature link
	if (elgg_is_admin_logged_in()) {
		if ($entity->featured_group == "yes") {
			$url = "action/groups/featured?group_guid={$entity->guid}&action_type=unfeature";
			$wording = elgg_echo("groups:makeunfeatured");
		} else {
			$url = "action/groups/featured?group_guid={$entity->guid}&action_type=feature";
			$wording = elgg_echo("groups:makefeatured");
		}
		$options = array(
			'name' => 'feature',
			'text' => $wording,
			'item_class' => 'pls',
			'href' => $url,
			'priority' => 300,
			'is_action' => true
		);
		$return[] = ElggMenuItem::factory($options);
	}

	return $return;
}