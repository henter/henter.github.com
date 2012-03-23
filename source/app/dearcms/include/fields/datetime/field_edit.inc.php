<?php 
$formats = array('date'=>"DATE NOT NULL DEFAULT '0000-00-00'", 'datetime'=>"DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'", 'int'=>"INT(10) UNSIGNED NOT NULL DEFAULT '0'");
$sql = "ALTER TABLE `$tablename` CHANGE `$field` `$field` ".$formats[$dateformat];
$db->query($sql);
?>