<?php
/**
 * Elgg pageshell
 * The standard HTML page shell that everything else fits into
 *
 * @package Elgg
 * @subpackage Core
 *
 * @uses $vars['title']       The page title
 * @uses $vars['body']        The main content of the page
 * @uses $vars['sysmessages'] A 2d array of various message registers, passed from system_messages()
 */



/**
 * We load body only. It's used for standard call or ajax call.
 */
$params['body'] = elgg_view('page/elements/body', $vars);
if (!elgg_is_logged_in()) $params['body'] .= elgg_view('core/account/login_dropdown');



/**
 * Ajax call
 */
if (elgg_is_xhr()) {
	if (empty($vars['title'])) {
		$params['title'] = elgg_get_config('sitename');
	} else {
		$params['title'] = $vars['title'] . ' &nabla; ' . elgg_get_config('sitename');
	}

	if ($vars['sysmessages']) {
		$params['system_messages'] = $vars['sysmessages'];
	} else {
		$params['system_messages'] = array(
			'error' => array(),
			'success' => array()
		);
	}

	ggouv_execute_js(elgg_view('ggouv_template/page/reinitialize_elgg'));
	$code = ''; // reset $code !

	$params['js_code'] .= 'console.log("'. _elgg_services()->db->getQueryCount() .'", "queryCount");'; // uncomment to see number of SQL calls

	foreach (ggouv_execute_js() as $code) {
		$params['js_code'] .= $code;
	}

	// Set the content type
	header("Content-type: application/json; charset=UTF-8");

	echo json_encode($params);
	exit;
}



/**
 * Here start standard call.
 */

// render content before head so that JavaScript and CSS can be loaded. See #4032
$messages = elgg_view('page/elements/messages', array('object' => $vars['sysmessages']));
$header_wrapper = elgg_view('ggouv_template/page/header_wrapper', $vars);


$body = <<<__BODY
<div id="overlay"></div>
<div class="elgg-page elgg-page-default">
	<div class="elgg-page-messages">
		$messages
	</div>
__BODY;


if (elgg_is_logged_in()) {

	$topbar = elgg_view('ggouv_template/page/vertical_menubar', $vars);
	$slidr_left = elgg_view('ggouv_template/page/slidr_left');

	$body .= <<<__BODY
	<div class="elgg-page-topbar">
		<div class="elgg-inner">
			$topbar
		</div>
	</div>
	$slidr_left
	$header_wrapper
__BODY;

} else {

	$nologin_mainpage_header = elgg_view('ggouv_template/page/nologin_mainpage_header');

	$body .= <<<__BODY
	<div class="elgg-page-header">
		<div class="elgg-inner-nolog">
			$nologin_mainpage_header
		</div>
	</div>
__BODY;

}

$body .= <<<__BODY
	<div class="toggle-sidebar-button gwf hidden link">&#xac06;</div>

	<div class="elgg-page-body">
		<div class="elgg-inner">
			{$params['body']}
		</div>
	</div>

</div>

<div id="goTop" class="t"><div class="gwf tooltip e" title="<?php echo elgg_echo('back:to:top'); ?>">&uarr;</div></div>
__BODY;

$body .= elgg_view('page/elements/foot');

echo '<script type="text/javascript">console.log("'. _elgg_services()->db->getQueryCount() .'", "queryCount");</script>'; // uncomment to see number of SQL calls


// make attribute for body
$class = elgg_get_context() == 'main' ? 'homepage t25' : 't25';
if (!elgg_is_logged_in()) $class .= ' nolog';
// warnings, this is override intial var @todo
$vars['body_attrs'] = array(
	'class' => $class
);

$head = elgg_view('ggouv_template/page/head', $vars['head']);

echo elgg_view('page/elements/html', array(
	'head' => $head,
	'body_attrs' => $vars['body_attrs'],
	'body' => $body
));

