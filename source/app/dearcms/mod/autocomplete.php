<?php
define('IN_DC', TRUE);
define('NOCITYCODE', TRUE);
require dirname(__FILE__).'/inc/common.inc.php';
$keyword = trim($keyword);
if(!$keyword) exit;

$action = $action ? $action : 'shop';
switch($action)
{
	case 'shop':
        $_shops = $db->select("SELECT `id`,`shopname`,`subname`,`address` FROM `{$dbpre}shop` WHERE `shopname` LIKE '%$keyword%' OR `subname` LIKE '%$keyword%' OR `address` LIKE '%$keyword%' LIMIT 10");
        if($_shops){
            foreach ($_shops as $k=>$v) {
            		echo "<li><a href='##' onclick=\"setshop('".trim($v[shopname])."','".$v[id]."');\">".trim($v[shopname])."</a></li>\n";
            }
        }else{
            echo "<li>".utf8("暂无搜索结果")."</li>\n";
        }
        exit;
	break;
}



function utf8($str){
    $str = iconv('gbk', 'utf-8', $str);
    return $str;
}
function gbk($str){
    $str = iconv('utf-8', 'gbk', $str);
    return $str;
}

?>