<?php 
if(!$maxlength) $maxlength = 255;
$maxlength = min($maxlength, 255);
$sql = "ALTER TABLE `$tablename` CHANGE `$field` `$field` VARCHAR( $maxlength ) NOT NULL DEFAULT '$defaultvalue'";
$db->query($sql);
?>