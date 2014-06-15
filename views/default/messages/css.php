<?php
/**
 * Elgg Messages CSS
 *
 * @package ElggMessages
 */
?>

.messages-container {
	min-height: 200px;
}
.message.unread a {
	color: #D40005;
}
.messages-buttonbank {
	text-align: right;
}
.messages-buttonbank input {
	margin-left: 10px;
}

/*** message metadata ***/
.messages-owner {
	float: left;
	width: 20%;
	margin-right: 2%;
}
.messages-subject {
	float: left;
	width: 55%;
	margin-right: 2%;
}
.messages-timestamp {
	float: left;
	width: 14%;
	margin-right: 2%;
}
.messages-delete {
	float: left;
	width: 5%;
}
/*** topbar icon ***/
.messages-new {
	color: #FFF;
	background-color: #d2322d;
	border-radius: 20px;
	position: absolute;
	text-align: center;
	line-height: 13px;
	top: 5px;
	left: auto;
	right: 5px;
	min-width: 17px;
	height: 17px;
	font-size: 12px;
	font-weight: bold;
	margin: 0;
}