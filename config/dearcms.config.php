<?php
/*
    基础配置
*/

define('DC_PATH', '/'); //网站所在目录，例如：如果在dc目录 则为'/dc/' ，根目录则为'/'
$dc_config = array();

$dc_config['debug'] = 1;
$dc_config['authkey'] = '$#!%^$#@%$';
$dc_config['charset'] = 'utf-8';

$dc_config['template'] = 'default'; //模板目录名
$dc_config['tplrefresh'] = 1; //模板自动刷新
$dc_config['tplrefresh_time'] = 600;//模板自动刷新时间 秒
    
$dc_config['staticurl'] = '';


/*db*/
$dc_config['dbid'] = 1;
$dc_config['db']['1']['host'] = 'localhost';
$dc_config['db']['1']['user'] = 'root';
$dc_config['db']['1']['pw'] = '';
$dc_config['db']['1']['charset'] = 'utf8';
$dc_config['db']['1']['pconnect'] = '0';
$dc_config['db']['1']['name'] = 'gaofen_dz';
$dc_config['db']['1']['pre'] = 'pre_';
/*sql*/
$dc_config['querysafe']['status'] = 1;
$dc_config['querysafe']['dfunction']['0'] = 'load_file';
$dc_config['querysafe']['dfunction']['1'] = 'hex';
$dc_config['querysafe']['dfunction']['2'] = 'substring';
$dc_config['querysafe']['dfunction']['3'] = 'if';
$dc_config['querysafe']['dfunction']['4'] = 'ord';
$dc_config['querysafe']['dfunction']['5'] = 'char';
$dc_config['querysafe']['daction']['0'] = 'intooutfile';
$dc_config['querysafe']['daction']['1'] = 'intodumpfile';
$dc_config['querysafe']['daction']['2'] = 'unionselect';
$dc_config['querysafe']['daction']['3'] = '(select';
$dc_config['querysafe']['dnote']['0'] = '/*';
$dc_config['querysafe']['dnote']['1'] = '*/';
$dc_config['querysafe']['dnote']['2'] = '#';
$dc_config['querysafe']['dnote']['3'] = '--';
$dc_config['querysafe']['dnote']['4'] = '"';
$dc_config['querysafe']['dlikehex'] = 1;
$dc_config['querysafe']['afullnote'] = 1;

/*cookie*/
$dc_config['cookie']['pre'] = 'uchome_';
$dc_config['cookie']['domain'] = (strrpos(strstr($_SERVER['HTTP_HOST'], '.'),'.')==0) ? '.'.$_SERVER['HTTP_HOST'] : strstr($_SERVER['HTTP_HOST'], '.');
$dc_config['cookie']['path'] = '/';

return $dc_config;
