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
function mfrb_setup_head($hook, $type, $return, $params) {
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
		'rel' => 'shortcut icon',
		'sizes' => '48x48',
		'type' => 'image/png',
		'href' => elgg_normalize_url('mod/mfrb_template/graphics/favicon_mfrb.png'),
	);
	$return['links'][] = array(
		'rel' => 'apple-touch-icon',
		'href' => elgg_normalize_url('mod/mfrb_template/graphics/favicon_mfrb.png'),
	);

	return $return;
}



/**
 * Hook called in ajax output, here : /engine/classes/Elgg/ActionsService.php line 311
 */
function mfrb_output_ajax_plugin_hook($hook, $type, $return, $params) {
	// Add code to be executed as javascript
	$code = '';
	foreach (mfrb_execute_js() as $code) {
		$return['js_code'] .= $code;
	}

	return $return;
}



/**
 * Add a like button to river actions
 */
function mfrb_likes_river_menu_setup($hook, $type, $return, $params) {
	if (elgg_is_logged_in()) {
		$item = $params['item'];

		// only like group creation #3958
		if ($item->type == "group" && $item->view != "river/group/create") {
			return $return;
		}

		// don't like users #4116
		if ($item->type == "user") {
			return $return;
		}

		$object = $item->getObjectEntity();
		if (!elgg_in_context('widgets') && $item->annotation_id == 0) {
			if ($object->canAnnotate(0, 'likes')) {
				$hasLiked = elgg_annotation_exists($object->guid, 'likes');

				// Always register both. That makes it super easy to toggle with javascript
				$return[] = ElggMenuItem::factory(array(
					'name' => 'like',
					'href' => elgg_add_action_tokens_to_url("/action/likes/add?guid={$object->guid}"),
					'text' => elgg_view_icon('thumbs-up') . elgg_echo('likes:likethis'),
					'title' => elgg_echo('likes:likethis'),
					'item_class' => $hasLiked ? 'hidden' : '',
					'priority' => 100,
				));
				$return[] = ElggMenuItem::factory(array(
					'name' => 'unlike',
					'href' => elgg_add_action_tokens_to_url("/action/likes/delete?guid={$object->guid}"),
					'text' => elgg_view_icon('thumbs-down') . elgg_echo('likes:remove'),
					'title' => elgg_echo('likes:remove'),
					'item_class' => $hasLiked ? '' : 'hidden',
					'priority' => 100,
				));

			}
		}
	}

	return $return;
}


/**
* This hooks into the getIcon API and returns a gravatar icon
*/
function gravatar_avatar_hook($hook, $type, $url, $params) {

	// check if user already has an icon
	if (!$params['entity']->icontime) {
		$icon_sizes = elgg_get_config('icon_sizes');
		$size = $params['size'];
		if (!in_array($size, array_keys($icon_sizes))) {
			$size = 'small';
		}

		// avatars must be square
		$size = $icon_sizes[$size]['w'];

		$hash = md5($params['entity']->email);
		return "https://secure.gravatar.com/avatar/$hash.jpg?r=pg&d=identicon&s=$size";
	}
}

