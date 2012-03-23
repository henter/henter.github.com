<?php 
$sql = "ALTER TABLE `$tablename` DROP `$field` ";
$db->query($sql);
?>