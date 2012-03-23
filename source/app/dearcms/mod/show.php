<?php
require dirname(__FILE__).'/inc/common.inc.php';
require_once CACHE_MODEL_PATH.'content_output.class.php';



$contentid = isset($id) ? intval($id) : 0;
if($contentid <= 0) showmessage('参数错误！');

$r = $c->get($contentid);
if(!$r['status']) showmessage('该信息已被删除！',SITE_URL);

//$c->hits($contentid);//增加点击 已改为动态加载

$C = $CAT[$r[catcode]];
$out = new content_output();
$data = $out->get($r);
extract($data);

$template = $template ? "show_$C[template]" : "show";





$title = strip_tags($title);
$head['title'] = $title.'_'.$C['catname'].'_'.$DC['sitename'];
$head['keywords'] = str_replace(' ', ',', $r['keywords']);
$head['description'] = $r['description'];

include template($template);
?>