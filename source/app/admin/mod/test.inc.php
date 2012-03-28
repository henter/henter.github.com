<?php
defined('IN_ADMIN') or exit('Access Denied');



$test = import(APP.':model.test');
print_R($test);
exit;

$file = 'home';



include atpl($file);

