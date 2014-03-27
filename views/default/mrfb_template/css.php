<?php
/**
 *	mrfb_template - for elgg 1.9+
 *	@package mrfb_template
 *	@author Emmanuel Salomon @ManUtopiK
 *	@license GNU Affero General Public License, version 3 or late
 *	@link https://github.com/revenudebase/mrfb_template
 *
 *	mrfb_template css
 *
 *	Helper to include all css
 *
 **/


readfile(__DIR__ . '/scss/foundation-icons.css');
readfile(__DIR__ . '/scss/mrfb.css');
readfile(__DIR__ . '/scss/helpers.css');

ob_start();
include_once('scss/vars.php');
$ob = ob_get_clean();

$files = $ob . file_get_contents(__DIR__ . '/scss/test.scss');
$scss = new scssc();
$scss->setFormatter("scss_formatter_compressed");
echo $scss->compile($files);

?>

// End of all mrfb_template css files