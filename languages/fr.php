<?php
/**
 *	mfrb_template - for elgg 1.9+
 *	@package mfrb_template
 *	@author Emmanuel Salomon @ManUtopiK
 *	@license GNU Affero General Public License, version 3 or late
 *	@link https://github.com/revenudebase/mfrb_template
 *
 *	mfrb_template fr.php
 *
 *	Helper to include all fr languages files
 *
 **/

// Declare files
$files = array(
	'elgg', // Elgg core language
	'mfrb', // language for mfrb
	'reportedcontent',
	'groups',
	'tagcloud',
	'invitefriend',
	'likes',
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

return $fr;