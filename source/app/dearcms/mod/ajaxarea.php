<?php
define('IN_DC', TRUE);
require dirname(__FILE__).'/inc/common.inc.php';

$thiscity = "<span style='cursor:pointer;' onclick=\"setCurCity('".$AREA[$return_citycode][areaname]."','$return_citycode');\">".$AREA[$return_citycode][areaname]."</span> ".$cityname."站";

$area_24 = "<select name='select' id='select' onChange=\"setCurCity('henter',this.value);\" style='width:80px;'>";
$area_24 .= "<option value=''>其它省份站</option>";
foreach($area2 AS $r){
    $selected = ($r[areacode]==substr($citycode,0,2)) ? ' selected' : '';
    $area_24 .= "<option value=\"$r[areacode]\" $selected>$r[areaname]</option>";
}
$area_24 .= "</select>";
$area_24 .= "<select name='select2' id='select2' onChange=\"setCurCity('henter',this.value);\" style='width:80px;'>";
$area_24 .= "<option value='$return_citycode'>地市子站</option>";
foreach($area4 AS $r){
    $selected = ($r[areacode]==substr($citycode,0,4)) ? ' selected' : '';
    $area_24 .= "<option value='$r[areacode]'  $selected>$r[areaname]</option>";
}
$area_24 .= "</select>";







$area_6 = "<div id='ThisArea'><strong>选择区域：</strong>"; 


if($mod=='shop' && $file='list' && $cat){
    if($areacode && strlen($areacode)<6) $allareastyle = " class='Area'";
    $area_6 .= "<a href=\"".$MODULE[shop][url]."list.php?cat=$cat&areacode=$all_citycode\"  title=\"$r[areaname]\" $allareastyle>全部</a> ";
}else{
    if(strlen($citycode)<6) $allareastyle = " class='Area'";
    $area_6 .= "<a href=\"javascript:setCurCity('".$AREA[$all_citycode][areaname]."','$all_citycode');\" $allareastyle>全部</a> ";
}

foreach($area6 AS $r){
    if($mod=='shop' && $file='list' && $cat){
        $areaclass = ($areacode == $r[areacode] || $citycode == $r[areacode]) ? ' class="Area"' : '';
        $area_6 .= "<a href=\"".$MODULE[shop][url]."list.php?cat=$cat&areacode=$r[areacode]\"  title=\"$r[areaname]\"  $areaclass>$r[areaname]</a> ";
    }else{
        $areaclass = ($citycode == $r[areacode]) ? ' class="Area"' : '';
        $area_6 .= "<a href=\"javascript:setCurCity('".$AREA[$r[areacode]][areaname]."','$r[areacode]');\"  title=\"$r[areaname]\"  $areaclass>$r[areaname]</a> ";
    }
}
$area_6 .= "</div>";


    
$thiscity = str_replace("'","\'",$thiscity);
$area_24 = str_replace("'","\'",$area_24);
$area_6 = str_replace("'","\'",$area_6);


echo "$('#thiscity').html('$thiscity');";
echo "$('#area_24').html('$area_24');";
echo "$('#area_6').html('$area_6');";



