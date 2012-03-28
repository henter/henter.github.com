<?php
require dirname(__FILE__).'/source/dearcms.php';

$dearcms = & dearcms::instance();
$dearcms->init();

//print_R($dearcms);
//print_R($_G);

$data = DB::get('SELECT * FROM '.DB::table('common_member').' LIMIT 10');
//print_r($data);


