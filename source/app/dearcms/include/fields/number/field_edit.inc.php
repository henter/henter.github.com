<?php 
$minnumber = intval($minnumber);
$defaultvalue = $decimaldigits == 0 ? intval($defaultvalue) : floatval($defaultvalue);
$sql = "ALTER TABLE `$tablename` CHANGE `$field` `$field` ".($decimaldigits == 0 ? 'INT' : 'FLOAT')." ".($minnumber >= 0 ? 'UNSIGNED' : '')." NOT NULL DEFAULT '$defaultvalue'";
$db->query($sql);
?>