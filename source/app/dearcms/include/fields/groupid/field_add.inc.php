<?php 
$db->query("ALTER TABLE `$tablename` ADD `$field` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'");
?>