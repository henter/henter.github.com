<?php
require(dirname(__FILE__).'/inc/config.inc.php');
define('DC_ROOT', str_replace("\\", '/', dirname(__FILE__)).'/');
require 'inc/db.class.php';
require DC_ROOT. 'inc/sql.func.php';

$db = new db_mysql();
$db->connect(DB_HOST, DB_USER, DB_PW, DB_NAME, DB_PCONNECT, DB_CHARSET);



$sql = "INSERT INTO `dc_model_field` VALUES ('', '11', 'aid', '附件ID', '', '', '0', '0', '', '', 'number', '', '', '', '', '1', '1', '0', '1', '0', '0', '0', '0', '0', '0', '1', '1', '0', '0')";
//sql_execute($sql);

$sql = "ALTER table `dc_shop` add `aid` int(10) not null default 0";
//sql_execute($sql);


//unlink('update.php');


$shops = $db->select("SELECT `id`,`shopname` FROM `dc_shop` ORDER BY id DESC");
foreach($shops AS $r){
    $a = $db->get_one("SELECT * FROM `dc_attachment` WHERE `shopid`='$r[id]' AND `field`='thumb'");
    $aid = intval($a['aid']);
    $db->query("UPDATE `dc_shop` SET `aid` = $aid WHERE id = $r[id]");
}



echo '升级成功！<a href="admin.php">进入后台</a>';


?>