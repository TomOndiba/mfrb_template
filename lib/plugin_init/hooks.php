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

	// https://developer.chrome.com/multidevice/android/installtohomescreen
	$data['metas'][] = array(
		'name' => 'mobile-web-app-capable',
		'content' => 'yes',
	);
	$data['metas'][] = array(
		'name' => 'apple-mobile-web-app-capable',
		'content' => 'yes',
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



/**
 * Override the url for a wire post to return the thread
 *
 * @param string $hook
 * @param string $type
 * @param string $url
 * @param array  $params
 * @return string
 */
function thewire_set_url($hook, $type, $url, $params) {
	if (elgg_instanceof($params['entity'], 'object', 'thewire')) {
		return "message/view/" . $params['entity']->guid;
	}
}



/**
 * Override the url for a wire post to return the thread
 *
 * @param string $hook
 * @param string $type
 * @param string $url
 * @param array  $params
 * @return string
 */
function mfrb_json_file_object($hook, $type, $return, $params) {
	if (elgg_instanceof($params['entity'], 'object', 'file')) {
		$return->thumbnail = elgg_format_url($params['entity']->getIconURL('small'));
		$return->read_access = $params['entity']->read_access;
	}
	return $return;
}



/**
 * Override the default entity icon for files
 *
 * Plugins can override or extend the icons using the plugin hook: 'file:icon:url', 'override'
 *
 * @param string $hook
 * @param string $type
 * @param string $url
 * @param array  $params
 * @return string Relative URL
 */
function mfrb_override_file_icon($hook, $type, $url, $params) {
	$file = $params['entity'];
	$size = $params['size'];
	if (elgg_instanceof($file, 'object', 'file')) {

		// thumbnails get first priority
		if ($file->thumbnail) {
			$ts = (int)$file->icontime;
			return "mod/mfrb_template/thumbnail.php?file_guid=$file->guid&size=$size&icontime=$ts";
		}

		$mapping = array(
			'application/excel' => 'excel',
			'application/msword' => 'word',
			'application/ogg' => 'music',
			'application/pdf' => 'pdf',
			'application/powerpoint' => 'ppt',
			'application/vnd.ms-excel' => 'excel',
			'application/vnd.ms-powerpoint' => 'ppt',
			'application/vnd.oasis.opendocument.text' => 'openoffice',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'word',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'excel',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'ppt',
			'application/x-gzip' => 'archive',
			'application/x-rar-compressed' => 'archive',
			'application/x-stuffit' => 'archive',
			'application/zip' => 'archive',

			'text/directory' => 'vcard',
			'text/v-card' => 'vcard',

			'application' => 'application',
			'audio' => 'music',
			'text' => 'text',
			'video' => 'video',
		);

		$mime = $file->mimetype;
		if ($mime) {
			$base_type = substr($mime, 0, strpos($mime, '/'));
		} else {
			$mime = 'none';
			$base_type = 'none';
		}

		if (isset($mapping[$mime])) {
			$type = $mapping[$mime];
		} elseif (isset($mapping[$base_type])) {
			$type = $mapping[$base_type];
		} else {
			$type = 'general';
		}

		if ($size == 'large') {
			$ext = '_lrg';
		} else {
			$ext = '';
		}

		$url = "mod/file/graphics/icons/{$type}{$ext}.gif";
		$url = elgg_trigger_plugin_hook('file:icon:url', 'override', $params, $url);
		return $url;
	}
	return $url;
}



