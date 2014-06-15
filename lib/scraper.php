<?php

require_once('../vendors/url-scraper-php/website_parser.php');

$url = urldecode($_GET['url']);

//Instance of WebsiteParser
$parser = new WebsiteParser($url);

//Get all hyper links
//$links = $parser->getHrefLinks();

//Get title
$title = $parser->getTitle(true);

//Get all metadatas
$metatags = $parser->getMetaTags();

//Get all image sources
$images = $parser->getImageSources();

// Send as array
$arr = array(
	'url' => $url,
	'title' => $title,
	//'links' => $links,
	'metatags' => $metatags,
	'images' => $images,
	'message' => $parser->message
);

header('Content-Type: application/json; charset=UTF-8');

echo json_encode($arr);