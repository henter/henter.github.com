<?php 
$sql = "ALTER TABLE `$tablename` ADD `$field` CHAR( 5 ) NOT NULL";
$db->query($sql);
?>