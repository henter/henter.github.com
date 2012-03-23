<?php 
$maxlength = max(intval($maxlength), 0);
$fieldtype = $issystem ? 'CHAR' : 'VARCHAR';
$sql = "ALTER TABLE `$tablename` CHANGE `$field` `$field` ";
$sql .= ($maxlength && $maxlength <= 255) ? "$fieldtype( $maxlength ) NOT NULL DEFAULT '$defaultvalue'" : "MEDIUMTEXT NOT NULL";
$db->query($sql);
?>