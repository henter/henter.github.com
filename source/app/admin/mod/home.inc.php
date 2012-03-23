<?php
defined('IN_ADMIN') or exit('Access Denied');
require 'version.inc.php';

$installfiles = array('install.php', 'install');
foreach($installfiles as $val) {
    if(file_exists($val)) {
        $install_exists = TRUE;
    }
}


$server['time'] = date('Y-m-d H:i:s', TIME);
$server['upfile'] = (ini_get('file_uploads')) ? '允许 ' . ini_get('upload_max_filesize') : '关闭';
$server['register_globals'] = (ini_get('register_globals')) ? '允许' : '关闭';
$server['safe_mode'] = (ini_get('safe_mode')) ? '允许' : '关闭';
$server['software'] = $_SERVER['SERVER_SOFTWARE'];
$server['phpver'] = phpversion();


$server['mysqlver'] = $db->version();
$s = function_exists('gd_info') ? gd_info() : '<span class="font_1"><strong>Not Support</strong></span>';
$server['gd'] = is_array($s) ? ($s['GD Version']) : $s;
if (function_exists('memory_get_usage')) {
    $server['memory'] = round(memory_get_usage()/1024,2); //KB
}


if(!function_exists('imagepng') && !function_exists('imagejpeg') && !function_exists('imagegif'))
{
    $gd = "<font color='red'>不支持GD库</font>";
    $enablegd = 0;
}
else
{
    $gd = "支持";
    $enablegd = 1;
}
if(function_exists('imagepng')) $gd .= "PNG ";
if(function_exists('imagejpeg')) $gd .= " JPG ";
if(function_exists('imagegif')) $gd .= " GIF ";



$r = $db->get_one("SELECT COUNT(*) AS c FROM {$dbpre}member");
$system['members'] = $r['c'];
$r = $db->get_one("SELECT COUNT(*) AS c FROM {$dbpre}shop");
$system['shops'] = $r['c'];
$r = $db->get_one("SELECT COUNT(*) AS c FROM {$dbpre}shop WHERE userid > 0");
$system['shops_mbpost'] = $r['c'];
$r = $db->get_one("SELECT COUNT(*) AS c FROM {$dbpre}content WHERE `catcode` LIKE '13%' ");
$system['discounts'] = $r['c'];
$r = $db->get_one("SELECT COUNT(*) AS c FROM {$dbpre}review");
$system['reviews'] = $r['c'];
$r = $db->get_one("SELECT COUNT(*) AS c FROM {$dbpre}respond");
$system['respond'] = $r['c'];



include atpl($file);
?>
