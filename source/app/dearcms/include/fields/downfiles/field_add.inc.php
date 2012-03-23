<?php 
$sql = "ALTER TABLE `$tablename` ADD `$field` TEXT NOT NULL";
$db->query($sql);
?>