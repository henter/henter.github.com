<?php 
$db->query("ALTER TABLE `$tablename` DROP `paginationtype`");
$db->query("ALTER TABLE `$tablename` DROP `maxcharperpage`");
?>