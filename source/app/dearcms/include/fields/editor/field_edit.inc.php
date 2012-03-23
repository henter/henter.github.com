<?php 
if($storage == 'file' && !is_dir(CONTENT_ROOT.$field))
{
	content_init($field);
}
?>