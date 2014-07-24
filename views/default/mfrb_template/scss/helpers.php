
* { /* border and padding included in width */
	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
}

.t100 { /* transition 100ms */
	-webkit-transition: all .1s ease;
	-moz-transition: all .1s ease;
	-ms-transition: all .1s ease;
	-o-transition: all .1s ease;
	transition: all .1s ease;
}
.t250 { /* transition 250ms */
	-webkit-transition: all .25s ease;
	-moz-transition: all .25s ease;
	-ms-transition: all .25s ease;
	-o-transition: all .25s ease;
	transition: all .25s ease;
}
.t500 { /* transition 500ms */
	-webkit-transition: all .5s ease;
	-moz-transition: all .5s ease;
	-ms-transition: all .5s ease;
	-o-transition: all .5s ease;
	transition: all .5s ease;
}

.rcw90 { /* clockwise 90° (sens des aiguilles d'une montre de 90°) */
	-webkit-transform: rotateZ(90deg);
	-moz-transform: rotateZ(90deg);
	-o-transform: rotateZ(90deg);
	-ms-transform: rotateZ(90deg);
	transform: rotateZ(90deg);
}
.rccw90 { /* counterclockwise 90° (sens inverse des aiguilles d'une montre de 90°) */
	-webkit-transform: rotateZ(-90deg);
	-moz-transform: rotateZ(-90deg);
	-o-transform: rotateZ(-90deg);
	-ms-transform: rotateZ(-90deg);
	transform: rotateZ(-90deg);
}
.rcw180 { /* clockwise 180° (sens des aiguilles d'une montre de 180°) */
	-webkit-transform: rotateZ(180deg);
	-moz-transform: rotateZ(180deg);
	-o-transform: rotateZ(180deg);
	-ms-transform: rotateZ(180deg);
	transform: rotateZ(180deg);
}

<?php
/**
 * Spacing classes
 * Should be used to modify the default spacing between objects (not between nodes of the same object)
 * Please use judiciously. You want to be using defaults most of the time, these are exceptions!
 * <type><pseudo-class><location><size>
 * <type>: m = margin, p = padding
 * <pseudo-class>: :before, :after
 * <location>: a = all, t = top, r = right, b = bottom, l = left, h = horizontal, v = vertical
 * <size>: n = none, s = small, m = medium, l = large
 */

$none = '0';
$small = '5px';
$medium = '10px';
$large = '20px';

echo <<<CSS
/* Padding */

.pbas:before, .paas:after{padding:$small}
.pbrs:before, .pbhs:before, .pars:after, .pahs:after{padding-right:$small}
.pbls:before, .pbhs:before, .pals:after, .pahs:after{padding-left:$small}
.pbts:before, .pbvs:before, .pats:after, .pavs:after{padding-top:$small}
.pbbs:before, .pbvs:before, .pabs:after, .pavs:after{padding-bottom:$small}

.pbam:before, .paam:after{padding:$medium}
.pbrm:before, .pbhm:before, .parm:after, .pahm:after{padding-right:$medium}
.pblm:before, .pbhm:before, .palm:after, .pahm:after{padding-left:$medium}
.pbtm:before, .pbvm:before, .patm:after, .pavm:after{padding-top:$medium}
.pbbm:before, .pbvm:before, .pabm:after, .pavm:after{padding-bottom:$medium}

.pbal:before, .paal:after{padding:$large}
.pbrl:before, .pbhl:before, .parl:after, .pahl:after{padding-right:$large}
.pbll:before, .pbhl:before, .pall:after, .pahl:after{padding-left:$large}
.pbtl:before, .pbvl:before, .patl:after, .pavl:after{padding-top:$large}
.pbbl:before, .pbvl:before, .pabl:after, .pavl:after{padding-bottom:$large}

/* Margin */

.mbas:before, .maas:after{margin:$small}
.mbrs:before, .mbhs:before, .mars:after, .mahs:after{margin-right:$small}
.mbls:before, .mbhs:before, .mals:after, .mahs:after{margin-left:$small}
.mbts:before, .mbvs:before, .mats:after, .mavs:after{margin-top:$small}
.mbbs:before, .mbvs:before, .mabs:after, .mavs:after{margin-bottom:$small}

.mbam:before, .maam:after{margin:$medium}
.mbrm:before, .mbhm:before, .marm:after, .mahm:after{margin-right:$medium}
.mblm:before, .mbhm:before, .malm:after, .mahm:after{margin-left:$medium}
.mbtm:before, .mbvm:before, .matm:after, .mavm:after{margin-top:$medium}
.mbbm:before, .mbvm:before, .mabm:after, .mavm:after{margin-bottom:$medium}

.mbal:before, .maal:after{margin:$large}
.mbrl:before, .mbhl:before, .marl:after, .mahl:after{margin-right:$large}
.mbll:before, .mbhl:before, .mall:after, .mahl:after{margin-left:$large}
.mbtl:before, .mbvl:before, .matl:after, .mavl:after{margin-top:$large}
.mbbl:before, .mbvl:before, .mabl:after, .mavl:after{margin-bottom:$large}
CSS;
