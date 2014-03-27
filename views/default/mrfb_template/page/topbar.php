<?php
/**
 * Elgg topbar
 * The standard elgg top toolbar
 */

// insert site-wide navigation
echo elgg_view_menu('site');

echo elgg_view_menu('topbar', array('sort_by' => 'priority', array('elgg-menu-hz')));
