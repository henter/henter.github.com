<?php
require dirname(__FILE__).'/inc/common.inc.php';
require_once 'admin/content.class.php';
if($contentid)
{
    $c->hits($contentid);
    $r = $c->get($contentid);
    if(!$r) exit;
    $hits = $r['hits'];
}
elseif($shopid)
{
    $shop->hits($shopid);
    $r = $shop->get($shopid);
    if(!$r) exit;
    $hits = $r['hits'];
}
?>
$('#hits').html('<?=$hits?>');
