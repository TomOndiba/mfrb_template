<?php
/**
 *	mrfb_template - for elgg 1.9+
 *	@package mrfb_template
 *	@author Emmanuel Salomon @ManUtopiK
 *	@license GNU Affero General Public License, version 3 or late
 *	@link https://github.com/revenudebase/mrfb_template
 *
 *	mrfb_template fr.php
 *
 *	Helper to include all fr languages files
 *
 **/

// Declare files
$files = array(
	'elgg', // Elgg core language
	'mrfb', // language for mrfb
	'reportedcontent',
	'groups',
	'tagcloud',
	'invitefriend',
	'logbrowser',
	'logrotate',
	'uservalidationbyemail',
	'notifications',
	'search'
);

// merge all files
$fr = array();
foreach ($files as $file) {
	include_once("fr/$file.php");
	$fr = array_merge($fr, $french);
}

add_translation('fr', $fr);