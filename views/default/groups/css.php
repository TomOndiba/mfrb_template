<?php
/**
 * Elgg Groups css
 *
 * @package groups
 */

?>

.elgg-group-layout > .elgg-head {
	float: left;
	width: 860px;
	margin-left: 280px;
	padding: 20px 0;
	border-bottom: 1px solid #DEDEDE;
}
@media (max-width: 1179px) {
	.elgg-group-layout > .elgg-head {
		width: 600px;
	}
}
@media (max-width: 939px) {
	.elgg-group-layout > .elgg-head {
		width: 100%;
		margin-left: 0;
	}
}

.groups-profile > .elgg-image {
	margin-right: 20px;
}
.groups-profile > .elgg-image-alt {
	width: 240px;
	margin-left: 40px;
}
.groups-stats {
	margin-top: 10px;
}
.groups-stats p {
	margin-bottom: 2px;
}
.groups-profile-fields div:first-child {
	padding-top: 0;
}

.groups-profile-fields .odd,
.groups-profile-fields .even {
	border-bottom: 1px solid #DCDCDC;
	padding: 5px 0;
	margin-bottom: 0;
}

.groups-profile-fields .elgg-output {
	margin: 0;
}

#groups-tools > li {
	width: 48%;
	min-height: 200px;
	margin-bottom: 40px;
}

#groups-tools > li:nth-child(odd) {
	margin-right: 4%;
}

.groups-widget-viewall {
	float: right;
	font-size: 85%;
}

.groups-latest-reply {
	float: right;
}

.elgg-menu-groups-my-status li a {
	color: #444;
	display: block;
	margin: 3px 0 5px 0;
	padding: 2px 4px 2px 0;
}
.elgg-menu-groups-my-status li a:hover {
	color: #999;
}
.elgg-menu-groups-my-status li.elgg-state-selected > a {
	color: #999;
}

/* sidebar search */
.elgg-form-groups-search {
	position: relative;
}
.elgg-form-groups-search:before {
	position: absolute;
	color: #DEDEDE;
	right: 10px;
	font-size: 1.4em;
	top: 15px;
}
.elgg-form-groups-search input {
	margin: 5px 0 20px;
	width: 100%;
}

/* sidebar members */
.elgg-sidebar .elgg-gallery-users .fi-plus:before {
	font-size: 1.3em;
	color: #DEDEDE;
	height: 25px;
	float: left;
	width: 25px;
	text-align: center;
	line-height: 23px;
}
.elgg-sidebar .elgg-gallery-users .fi-plus:hover:before {
	background: #5097CF;
	color: white;
	text-decoration: none;
}
