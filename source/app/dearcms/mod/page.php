<?php
require dirname(__FILE__).'/inc/common.inc.php';

$pageid = QUERY_STRING ;
$BLOCK = cache_read('block.php');
$title = $BLOCK[$pageid][name];



$head['title'] = $title.'_'.$DC['sitename'];
$head['keywords'] = str_replace(' ', ',', $title);
$head['description'] = $title;


include template('page');
?>