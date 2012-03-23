<?php 
$db->query("ALTER TABLE `$tablename` ADD `$field` MEDIUMTEXT NOT NULL");
if($storage == 'file' && !is_dir(CONTENT_ROOT.$field))
{
	content_init($field);
}
?>