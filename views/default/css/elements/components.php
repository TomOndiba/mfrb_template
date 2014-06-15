<?php
/**
 * Layout Object CSS
 *
 * Image blocks, lists, tables, gallery, messages
 *
 * @package Elgg.Core
 * @subpackage UI
 */
?>
/* <style> /**/

/* ***************************************
	Image Block
*************************************** */
.elgg-image-block {
	padding: 10px 0;
}
.elgg-image-block .elgg-image {
	float: left;
	margin-right: 8px;
}
.elgg-image-block .elgg-image-alt {
	float: right;
	margin-left: 8px;
}

/* ***************************************
	List
*************************************** */
.elgg-list {
	margin: 5px 0;
	clear: both;
}
.elgg-list > li {
	border-bottom: 1px solid #DCDCDC;
}
.elgg-item h3 a {
	padding-bottom: 4px;
}
.elgg-item > .elgg-subtext {
	margin-bottom: 4px;
}
.elgg-item .elgg-content {
	margin: 10px 5px 10px 0;
}
.elgg-content {
	clear: both;
}

/* ***************************************
	Gallery
*************************************** */
.elgg-gallery {
	border: none;
	margin-right: auto;
	margin-left: auto;
}
.elgg-gallery td {
	padding: 5px;
}
.elgg-gallery-fluid > li {
	float: left;
}
.elgg-gallery-users > li {
	margin: 0 2px;
}

/* ***************************************
	Tables
*************************************** */
.elgg-table {
	width: 100%;
	border-top: 1px solid #EBEBEB;
}
.elgg-table td, .elgg-table th {
	padding: 4px 8px;
	border: 1px solid #EBEBEB;
}
.elgg-table th {
	background-color: #DDD;
}
.elgg-table tr:nth-child(odd), .elgg-table tr.odd {
	background-color: #FFF;
}
.elgg-table tr:nth-child(even), .elgg-table tr.even {
	background-color: #F0F0F0;
}
.elgg-table-alt {
	width: 100%;
	border-top: 1px solid #EBEBEB;
}
.elgg-table-alt th {
	background-color: #EEE;
	font-weight: bold;
}
.elgg-table-alt td, .elgg-table-alt th {
	padding: 5px;
	border-bottom: 1px solid #EBEBEB;
	vertical-align: middle;
}
.elgg-table-alt td:first-child {
	width: 200px;
}
.elgg-table-alt tr:hover {
	background: #FCFCFC;
}

/* ***************************************
	Owner Block
*************************************** */
.elgg-owner-block {
	margin-bottom: 20px;
}

/* ***************************************
	Messages
*************************************** */
.elgg-message {
	color: #FFF;
	display: block;
	padding: 10px 20px;
	cursor: pointer;
	opacity: 0.9;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
	border-radius: 3px;
}
.elgg-state-success {
	background-color: #090;
}
.elgg-state-error {
	background-color: #F00;
}
.elgg-state-notice {
	background-color: #5097CF;
}
.elgg-box-error {
	margin-top: 10px;
	padding: 20px;
	color: #B94A48;
	background-color: #F8E8E8;
	border: 1px solid #E5B7B5;
	border-radius: 5px;
}

/* ***************************************
	River
*************************************** */
.elgg-river-layout .elgg-list-river {
	border-top: 1px solid #DCDCDC;
}
.elgg-list-river > li {
	border-bottom: 1px solid #DCDCDC;
}
.elgg-river-item .elgg-pict {
	margin-right: 20px;
}
.elgg-river-timestamp,
.elgg-friendlytime time {
	color: #666;
	font-size: 85%;
	font-style: italic;
}

.elgg-river-attachments,
.elgg-river-message,
.elgg-river-content {
	border-left: 1px solid #DCDCDC;
	margin: 10px 0 10px 0;
	padding-left: 8px;
}
.elgg-river-comments .elgg-river-message {
	margin-bottom: 5px;
}
.elgg-river-attachments .elgg-avatar,
.elgg-river-attachments .elgg-icon {
	float: left;
}
.elgg-river-attachments .elgg-icon-arrow-right {
	margin: 3px 8px 0;
}
.elgg-river-layout .elgg-input-dropdown {
	float: right;
	margin: 10px 0;
}
.elgg-river-comments-tab {
	display: block;
	background-color: #EEE;
	margin-top: 5px;
	width: auto;
	float: right;
	font-size: 85%;
	padding: 1px 8px;
	border-radius: 3px 3px 0 0;
}

<?php //@todo components.php ?>
.elgg-river-comments {
	border-top: 1px solid #DCDCDC;
}
.elgg-river-comments > li {
	border-top: 1px solid #DCDCDC;
}
.elgg-river-comments > li:first-child {
	border-top: none;
}
.elgg-river-comments .elgg-media {
	padding: 0;
}
.elgg-river-comments time {
	line-height: 1em;
}
.elgg-river-comments .elgg-avatar {
	margin-top: 4px;
}
.elgg-river-more {
	background-color: #EEE;
	border-radius: 3px;
	padding: 2px 4px;
	font-size: 85%;
	margin-bottom: 2px;
}


.elgg-form-comment-river {
	position: relative;
	border-top: 1px solid #DCDCDC;
}
.elgg-form-comment-river:before,
.elgg-form-comment-river:after {
	content: " ";
	position: absolute;
	width: 0;
	height: 0;
	border-style: solid;
	border-width: 15px 15px 0 0;
	left: 20px;
	transform: rotate(45deg);
	-moz-transform: rotate(45deg);
	-webkit-transform: rotate(45deg);
}
.elgg-form-comment-river:before{
	border-color: #DCDCDC transparent transparent transparent;
	top: 13px;
	z-index: -1;
}
.elgg-form-comment-river:after {
	border-color: white transparent transparent transparent;
	top: 14px;
}
.elgg-form-comment-river.focus:before {
	box-shadow: 0 0 2px #F1C40F;
	border-color: #F1C40F transparent transparent transparent;
}
.elgg-form-comment-river.focus:after {
	border-color: white transparent transparent transparent;
}


/* **************************************
	Comments (from elgg_view_comments)
************************************** */
.elgg-comments {
	margin-top: 25px;
}
.elgg-comments > form {
	margin-top: 0;
}
.elgg-comments > ul + form {
	margin-top: 15px;
}

/* ***************************************
	Image-related
*************************************** */
.elgg-photo {
	border: 1px solid #DCDCDC;
	padding: 3px;
	background-color: #FFF;

	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
	
	max-width: 100%;
	height: auto;
}

/* ***************************************
	Tags
*************************************** */
.elgg-tags {
	font-size: 85%;
}
.elgg-tags > li {
	float:left;
	margin-right: 5px;
}
.elgg-tags li.elgg-tag:after {
	content: ",";
}
.elgg-tags li.elgg-tag:last-child:after {
	content: "";
}
.elgg-tagcloud {
	text-align: justify;
	margin-bottom: 5px;
}

