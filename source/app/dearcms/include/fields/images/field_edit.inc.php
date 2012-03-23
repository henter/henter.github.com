<?php 
$db->query("ALTER TABLE `$tablename` CHANGE `$field` `$field` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'");
?>