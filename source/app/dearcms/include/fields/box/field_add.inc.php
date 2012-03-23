<?php 
if(!$maxlength) $maxlength = 10;
if($fieldtype == 'INT')
{
	$defaultvalue = intval($defaultvalue);
	if($defaultvalue > 2147483647) $defaultvalue = 0;
	if($maxlength > 10) $maxlength = 11;
}
else if($fieldtype == 'MEDIUMINT')
{
	$defaultvalue = intval($defaultvalue);
	if($defaultvalue > 8388607) $defaultvalue = 0;
	if($maxlength > 6) $maxlength = 7;
}
else if($fieldtype == 'SMALLINT')
{
	$defaultvalue = intval($defaultvalue);
	if($defaultvalue > 32767) $defaultvalue = 0;
	if($maxlength > 4) $maxlength = 5;
}
else if($fieldtype == 'TINYINT')
{
	$defaultvalue = intval($defaultvalue);
	if($defaultvalue > 127) $defaultvalue = 0;
	if($maxlength > 2) $maxlength = 3;
}
$sql = "ALTER TABLE `$tablename` ADD `$field` $fieldtype( $maxlength ) NOT NULL DEFAULT '$defaultvalue'";
$db->query($sql);
?>