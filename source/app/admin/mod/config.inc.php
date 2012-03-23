<?php
defined('IN_ADMIN') or exit('Access Denied');

if($submit){
    $ks_name_ok = $data['ks_name_ok'];
    $ks_name_ok = $ks_name_ok ? $ks_name_ok : 0;
	$config = array(
             'DB_HOST'=>$data['db_host'],
		'DB_USER'=>$data['db_user'],
		'DB_PW'=>$data['db_pw'],
		'DB_NAME'=>$data['db_name'],
		'KS_NAME'=>$data['ks_name'],
		'KS_NAME_OK'=>$ks_name_ok,
		'KS_TIME'=>$data['ks_time'],
		'KS_PAGENUM'=>$data['ks_pagenum'],
		'KS_TESTNUM'=>$data['ks_testnum'],
		);
	set_config($config);
      showmessage('配置修改成功！',"?file=$file");
}


include atpl('config');
?>