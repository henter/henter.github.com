<?php 
$sql = "ALTER TABLE `$tablename` CHANGE `$field` `$field` CHAR( 5 ) NOT NULL DEFAULT '$defaultvalue'";
$db->query($sql);
?>