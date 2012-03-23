<?php
$db->query("ALTER TABLE `$tablename` ADD `paginationtype` TINYINT( 1 ) NOT NULL DEFAULT '0'");
$db->query("ALTER TABLE `$tablename` ADD `maxcharperpage` MEDIUMINT( 6 ) NOT NULL DEFAULT '10000'");
?>