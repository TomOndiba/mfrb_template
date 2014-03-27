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
$params['topbar'] = elgg_view('mrfb_template/page/topbar', $vars);


/**
 * Ajax call
 */
if (elgg_is_xhr()) {
	if (empty($vars['title'])) {
		$params['title'] = elgg_get_config('sitename');
	} else {
		$params['title'] = $vars['title'] . ' | ' . elgg_get_config('sitename');
	}

	if ($vars['sysmessages']) {
		$params['system_messages'] = $vars['sysmessages'];
	} else {
		$params['system_messages'] = array(
			'error' => array(),
			'success' => array()
		);
	}

	mrfb_execute_js(elgg_view('mrfb_template/page/reinitialize_elgg'));
	$code = ''; // reset $code !

	$params['js_code'] .= 'console.log("'. _elgg_services()->db->getQueryCount() .'", "queryCount");'; // uncomment to see number of SQL calls

	foreach (mrfb_execute_js() as $code) {
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
$logo = elgg_view('output/url', array(
	'id' => 'logo',
	'href' => 'activity',
	'text' => '<img src="' . elgg_get_site_url() . 'mod/mrfb_template/graphics/Logo_mrfb.png" class="float t500">'
));


$echo_btt = elgg_echo('back:to:top');
$body = <<<__BODY
<div class="elgg-page elgg-page-default">

	<div class="elgg-page-messages">
		$messages
	</div>

	<div id="progress"></div>

	<div class="elgg-page-topbar">
		<div class="elgg-inner">
			<div id="toggle-sidebar" class="hidden fi-arrow-right"></div>
			$logo
			{$params['topbar']}
		</div>
	</div>

	<div class="elgg-page-body">
		<div class="elgg-inner">
			{$params['body']}
		</div>
	</div>
</div>

<div id="goTop" class="t250"><div class="mrfb-icon tooltip e" title="{$echo_btt}"></div></div>
__BODY;

$body .= elgg_view('page/elements/foot');

echo '<script type="text/javascript">console.log("'. _elgg_services()->db->getQueryCount() .'", "queryCount");</script>'; // uncomment to see number of SQL calls

if (isset($vars['body_attrs'])) {
	$params['body_attrs'] = $vars['body_attrs'];
}

$head = elgg_view('page/elements/head', $vars['head']);

echo elgg_view('page/elements/html', array(
	'head' => $head,
	'body_attrs' => $vars['body_attrs'],
	'body' => $body
));

