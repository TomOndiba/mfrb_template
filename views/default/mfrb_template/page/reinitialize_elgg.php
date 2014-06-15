<?php 
/**
 * Initialize Elgg's js lib with the uncacheable data
 */

$config = array(
	'lastcache' => (int)elgg_get_config('lastcache'),
	'viewtype' => elgg_get_viewtype(),
	'simplecache_enabled' => (int)elgg_is_simplecache_enabled()
);

$security_token = array(
	'__elgg_ts' => $ts = time(),
	'__elgg_token' => generate_action_token($ts)
);

echo <<<JS
	elgg.config.lastcache = {$config['lastcache']};
	elgg.config.viewtype = "{$config['viewtype']}";
	elgg.config.simplecache_enabled = {$config['simplecache_enabled']};

	elgg.security.token.__elgg_ts = {$security_token['__elgg_ts']};
	elgg.security.token.__elgg_token = "{$security_token['__elgg_token']}";
JS;

$page_owner = elgg_get_page_owner_entity();
if ($page_owner instanceof ElggEntity) {
	$page_owner = json_encode($page_owner->toObject());
	echo "elgg.page_owner = {$page_owner};";
}

$user = elgg_get_logged_in_user_entity();
if ($user instanceof ElggUser) {
	$user_object = $user->toObject();
	$user_object->admin = $user->isAdmin();
	$session_user = json_encode($user_object);
	echo "elgg.session.user = new elgg.ElggUser({$session_user});";
}

// test API
//get_user_tokens($user->getGUID());
//remove_expired_user_tokens();

