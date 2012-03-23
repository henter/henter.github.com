<?php 
$sql = "ALTER TABLE `$tablename` CHANGE `$field` `$field` TEXT NOT NULL";
$db->query($sql);
?>