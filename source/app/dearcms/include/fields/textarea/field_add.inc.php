<?php 
$sql = "ALTER TABLE `$tablename` ADD `$field` ";
$maxlength = max(intval($maxlength), 0);
$sql .= ($maxlength && $maxlength <= 255) ? "VARCHAR( $maxlength ) NOT NULL DEFAULT '$defaultvalue'" : "MEDIUMTEXT NOT NULL";
$db->query($sql);
?>