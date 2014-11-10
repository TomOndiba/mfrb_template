<?php
/**
 *	mfrb_template - for elgg 1.9+
 *	@package mfrb_template
 *	@author Emmanuel Salomon @ManUtopiK
 *	@license GNU Affero General Public License, version 3 or late
 *	@link https://github.com/revenudebase/mfrb_template
 *
 *	mfrb_template css
 *
 *	Helper to include all css
 *
 **/


readfile(__DIR__ . '/scss/foundation-icons.css');
readfile(__DIR__ . '/scss/mfrb.css');
readfile(__DIR__ . '/scss/mfrb_activity.css');

readfile(__DIR__ . '/scss/select2.css');
//readfile(__DIR__ . '/scss/helpers.css');

ob_start();
include_once('scss/vars.php');
include_once('scss/helpers.php');
$ob = ob_get_clean();

$files = $ob . file_get_contents(__DIR__ . '/scss/test.scss');
$scss = new scssc();
$scss->setFormatter("scss_formatter_compressed");
echo $scss->compile($files);

?>

/* End of all mfrb_template css files */

