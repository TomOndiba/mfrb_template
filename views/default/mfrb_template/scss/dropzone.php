@-webkit-keyframes move {
	0% {
	background-position: 0 0;
	}

	100% {
	background-position: 50px 50px;
	}
}


.elgg-dropzone [data-template] {
	display: none;
	visibility: hidden;
}
.elgg-dropzone .elgg-input-dropzone {
	position: relative;
	border: 1px solid #DCDCDC;
	border-radius: 3px;
	min-height: 53px;
}
.elgg-dropzone .elgg-input-dropzone .elgg-dropzone-instructions {
	font-size: 16px;
	text-align: center;
	display: block;
	overflow: hidden;
}
.elgg-dropzone .elgg-input-dropzone .elgg-dropzone-instructions * {
	text-align: center;
}
.elgg-dropzone .elgg-input-dropzone .elgg-dropzone-instructions strong, .elgg-dropzone .elgg-input-dropzone .elgg-dropzone-instructions span {
	line-height: 20px;
	color: #666;
}
.elgg-dropzone .elgg-input-dropzone .elgg-dropzone-instructions .fi-upload-cloud {
	width: 100%;
	font-size: 50px !important;
	line-height: 1;
	display: block;
	color: #dedede;
}
.elgg-dropzone .elgg-input-dropzone.dz-drag-hover {
	border-color: #C2C2C2;
}
.elgg-dropzone .elgg-input-dropzone.dz-drag-hover .elgg-dropzone-instructions {
	background-color: #EEE;
	background-image: repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(255,255,255,.7) 35px, rgba(255,255,255,.7) 70px);
}
.elgg-dropzone .elgg-input-dropzone.dz-drag-hover .elgg-dropzone-instructions:before {
	color: #60B6F7;
}
.elgg-dropzone .elgg-dropzone-preview {
	*zoom: 1;
	border-top: 1px solid #DCDCDC;
	display: block;
	vertical-align: middle;
	width: 100%;
	position: relative;
}
.elgg-dropzone .elgg-dropzone-preview:before, .elgg-dropzone .elgg-dropzone-preview:after {
	content: " ";
	display: table;
}
.elgg-dropzone .elgg-dropzone-preview:after {
	clear: both;
}
.elgg-dropzone-instructions + .elgg-dropzone .elgg-dropzone-preview {
	border-top: 1px solid #cccccc;
	margin-top: 10px;
}
.elgg-dropzone .elgg-dropzone-preview:nth-child(odd) {
	/*background: white;*/
}
.elgg-dropzone .elgg-dropzone-preview:nth-child(even) {
	/*background: rgba(249, 249, 249, 0.8);*/
}
.elgg-dropzone .elgg-dropzone-preview .elgg-dropzone-filename {
	font-size: 12px;
	text-align: left;
	line-height: 30px;
	white-space: nowrap;
	overflow: hidden;
}
.elgg-dropzone .elgg-dropzone-preview .elgg-dropzone-size {
	font-size: 10px;
	line-height: 30px;
	white-space: nowrap;
	overflow: hidden;
	margin: 1px 0 -1px;
}
.elgg-dropzone .elgg-dropzone-preview .elgg-dropzone-thumbnail {
	text-align: center;
	max-height: 30px;
	overflow: hidden;
	vertical-align: middle;
}
.elgg-dropzone .elgg-dropzone-preview .elgg-dropzone-thumbnail img {
	width: 100%;
	height: auto;
	max-width: 30px;
	line-height: 30px;
	display: inline-block;
}
.elgg-dropzone .elgg-dropzone-preview .elgg-dropzone-progress {
	position: absolute;
	bottom: 0;
	left: 0;
	width: 100%;
	top: 0;
	z-index: -1;
}
.elgg-dropzone .elgg-dropzone-preview .elgg-dropzone-upload {
	position: absolute;
	overflow: hidden;
	left: 0;
	top: 0;
	right: 0;
	bottom: 0;
	width: 0;
}
.elgg-dropzone .elgg-dropzone-preview .elgg-dropzone-upload:after {
	content: "";
	position: absolute;
	top: 0;
	left: 0;
	bottom: 0;
	right: 0;
	height: 100%;
	background-color: rgba(71, 135, 184, 0.2);
	background-image: -webkit-gradient(linear, 0 0, 100% 100%, color-stop(0.25, rgba(255, 255, 255, 0.2)), color-stop(0.25, transparent), color-stop(0.5, transparent), color-stop(0.5, rgba(255, 255, 255, 0.2)), color-stop(0.75, rgba(255, 255, 255, 0.2)), color-stop(0.75, transparent), to(transparent));
	background-image: -moz-linear-gradient(-45deg, rgba(255, 255, 255, 0.2) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.2) 50%, rgba(255, 255, 255, 0.2) 75%, transparent 75%, transparent);
	z-index: 1;
	-webkit-background-size: 25px 25px;
	-moz-background-size: 25px 25px;
	-webkit-animation: move 2s linear infinite;
	overflow: hidden;
}
.elgg-dropzone .elgg-dropzone-preview .elgg-dropzone-success-icon {
	display: inline-block;
	display: none;
	margin: 0 5px;
	font-size: 16px;
	line-height: 30px;
	color: #00BB00;
}
.elgg-dropzone .elgg-dropzone-preview .elgg-dropzone-error-icon {
	display: inline-block;
	display: none;
	width: 100%;
	height: 100%;
	font-size: 16px;
	line-height: 30px;
	margin: 0 5px;
	color: #C0392B;
}
.elgg-dropzone .elgg-dropzone-preview .elgg-dropzone-remove-icon {
	display: inline-block;
	width: 100%;
	margin: 0 5px;
	font-size: 16px;
	line-height: 30px;
	cursor: pointer;
	color: inherit;
}
.elgg-dropzone .elgg-dropzone-preview .elgg-dropzone-remove-icon:hover {
	text-decoration: none;
	color: #C0392B;
}
.elgg-dropzone .elgg-dropzone-preview .elgg-dropzone-messages {
	font-size: 10px;
	color: #0054a7;
	clear: both;
	margin-bottom: -10px;
}
.elgg-dropzone .elgg-dropzone-preview.elgg-dropzone-success .elgg-dropzone-success-icon {
	display: inline-block;
}
.elgg-dropzone .elgg-dropzone-preview.elgg-dropzone-success .elgg-dropzone-progress {
	width: 100%;
	opacity: 0.5;
}
.elgg-dropzone .elgg-dropzone-preview.elgg-dropzone-success .elgg-dropzone-progress .elgg-dropzone-upload:after {
	display: none;
}
.elgg-dropzone .elgg-dropzone-preview.dz-error .elgg-dropzone-error-icon {
	display: inline-block;
}
.elgg-dropzone .elgg-dropzone-preview.dz-error .elgg-dropzone-messages {
	color: #C0392B;
}
.elgg-dropzone .elgg-dropzone-preview.dz-error .elgg-dropzone-progress {
	width: 100%;
	opacity: 0.2;
}
.elgg-dropzone .elgg-dropzone-preview.dz-error .elgg-dropzone-progress .elgg-dropzone-upload {
	background: #C0392B;
}
.elgg-dropzone .elgg-dropzone-preview.dz-error .elgg-dropzone-progress .elgg-dropzone-upload:after {
	display: none;
}

