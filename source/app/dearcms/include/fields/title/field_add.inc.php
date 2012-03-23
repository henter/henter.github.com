<?php 
if(!$maxlength) $maxlength = 255;
$maxlength = min($maxlength, 255);
$sql = "ALTER TABLE `$tablename` ADD `$field` CHAR( $maxlength ) NOT NULL DEFAULT '$defaultvalue'";
if($isstyle) $sql .= ", ADD `style` VARCHAR( 30 ) NOT NULL";
$db->query($sql);
?>