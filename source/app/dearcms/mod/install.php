<?php
require(dirname(__FILE__).'/inc/config.inc.php');
define('DC_ROOT', str_replace("\\", '/', dirname(__FILE__)).'/');
require 'inc/db.class.php';
require DC_ROOT. 'inc/sql.func.php';

$db = new db_mysql();
$db->c(DB_HOST, DB_USER, DB_PW, DB_NAME, DB_PRE, DB_PCONNECT, DB_CHARSET);



if(file_exists(DC_ROOT.'/inc/install.lock')) exit('您已经安装了,如果需要重新安装，请删除 ./inc/install.lock 文件！');

$sql = file_get_contents(DC_ROOT. '/install.sql');

sql_execute($sql);
file_put_contents(DC_ROOT.'/inc/install.lock','');


//unlink('install.php');
echo '安装成功！<a href="admin.php">进入后台</a>';
//echo $sql;

?>