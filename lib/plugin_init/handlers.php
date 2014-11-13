<?php



function activity_page_handler($page) {

	if (elgg_is_logged_in()) {

		elgg_set_page_owner_guid(elgg_get_logged_in_user_guid()); // set logged_in_user as owner of the page
		elgg_set_context('activity');

		// make a URL segment available in page handler script
		$input = get_input('page_type', false);
		$page_type = $input ? $input : elgg_extract(0, $page, 'all');
		$page_type = preg_replace('[\W]', '', $page_type);

		if ($page_type == 'owner') {
			elgg_gatekeeper();
			$input = get_input('subject_username', false);
			$page_username = $input ? $input : elgg_extract(1, $page, '');
			if ($page_username == elgg_get_logged_in_user_entity()->username) {
				$page_type = 'mine';
			} else {
				elgg_admin_gatekeeper();
				set_input('subject_username', $page_username);
			}
		}
		set_input('page_type', $page_type);

		//require_once("{$CONFIG->path}pages/river.php");

		include_once dirname(dirname(dirname(__FILE__))) . '/pages/river.php';

	} else {
		forward('');
	}

	return true;
}



/**
 * Message page handler
 *
 * Supports:
 * message/owner/<username>     View this user's wire posts
 * message/view/<guid>          View a post
 * thewire/tag/<tag>            View wire posts tagged with <tag>
 *
 * @param array $page From the page_handler function
 * @return bool
 */
function message_page_handler($page) {

	$base_dir = elgg_get_plugins_path() . PLUGIN_ID . '/pages/thewire';

	if (!isset($page[0]) || $page[0] == 'all') {
		forward('activity');
	}

	switch ($page[0]) {
		case "owner":
			include "$base_dir/owner.php";
			break;
		case "view":
			if (isset($page[1])) {
				set_input('guid', $page[1]);
			}
			include "$base_dir/view.php";
			break;
		case "tag":
			if (isset($page[1])) {
				set_input('tag', $page[1]);
			}
			include "$base_dir/tag.php";
			break;
		default:
			return false;
	}
	return true;
}



/**
 * Provide handler to edit and view return avatar for user and group
 * URLs take the form of
 *
 *  Defaults Elgg avatar handler:
 * /avatar/edit/<username>
 * /avatar/view/<username>/<size>/<icontime>
 *
 *  Added:
 *
 *  user avatar:      avatar/user/username
 *  user avatar:      avatar/user/username?size=$size
 *  user avatar:      avatar/user/username/$size
 *
 *  group avatar:      avatar/group/groupname
 *  group avatar:      avatar/group/groupname?size=$size
 *  group avatar:      avatar/group/groupname/$size
 *
 * @return forward to avatar file or 404
 */
function avatar_handler($page) {

	if (!isset($page[0]) || !isset($page[1])) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	if ($page[0] == 'edit') {
		global $CONFIG;
		$user = get_user_by_username($page[1]);
		elgg_set_page_owner_guid($user->getGUID());
		require_once("{$CONFIG->path}pages/avatar/edit.php");
		return true;
	} else if ($page[0] == 'view') {
		global $CONFIG;
		$user = get_user_by_username($page[1]);
		elgg_set_page_owner_guid($user->getGUID());
		set_input('size', $page[2]);
		require_once("{$CONFIG->path}pages/avatar/view.php");
		return true;
	} else if ($page[0] == 'user') {
		$object = get_user_by_username($page[1]);
	} else if ($page[0] == 'group'){
		$object = search_group_by_title($page[1]);
	} else {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$icon_sizes = elgg_get_config('icon_sizes');

	$size = get_input('size', 'small');
	if (!in_array($size, array_keys($icon_sizes))) {
		$size = 'small';
	}
	// override size
	if ($page[2] && in_array($size, array_keys($icon_sizes))) $size = $page[2];

	if ($object instanceof ElggUser) {
		// check if user already has an icon
		if (!$object->icontime) {
			// avatars must be square
			$size = $icon_sizes[$size]['w'];

			$hash = md5($object->email);
			$forward = "https://secure.gravatar.com/avatar/$hash.jpg?r=pg&d=identicon&s=$size";
		} else {
			$forward = profile_set_icon_url('entity:icon:url', 'user', false, array(
				'entity' => $object,
				'size' => $size
			));
		}
	} else if ($object instanceof ElggGroup) {
		set_input('group_guid', $object->getGUID());
		set_input('size', $size);
		$forward = 'groups/icon.php';
	} else {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	header("Content-type: image/jpeg");
	forward($forward);
}
