<?php 
if($storage == 'database') $db->query("ALTER TABLE `$tablename` DROP `$field`");
?>