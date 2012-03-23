<?php
defined('IN_DC') or exit('Access Denied');

if(!$action) $action = 'all';


switch($action)
{
        case 'cache':
                //更新模型缓存
                require_once 'admin/model_field.class.php';
                require_once 'admin/model.class.php';
                $model = new model();
                $model->cache();
                foreach($MODEL as $modelid=>$v)
                {
                	if($v['modeltype'] == 0)
                	{
                		$field = new model_field($modelid);
                		$field->cache();
                	}
                }

            //更新系统缓存
            cache_all();
            
            
            showmessage("缓存更新成功！");
        break;
        
        
        case 'rebuild':
            switch($rebuild){
                case 'catshop';
                    foreach($CAT AS $c=>$v){
                        $_shops = $db->get_one("SELECT COUNT(*) AS count FROM `{$dbpre}shop` WHERE `catcode` = $c");
                        $_num = $_shops['count'];
                        $db->query("UPDATE `{$dbpre}cat` SET `items`='$_num' WHERE `catcode` = '$c' ");
                    }
                    showmessage("更新成功！");

                break;

                case 'shoppic';
                    $_allshop = $db->select("SELECT `id`,`pics`,`shopname` FROM `{$dbpre}shop`",'id');
                    foreach($_allshop AS $k=>$v){
                        $_pics = $db->get_one("SELECT COUNT(*) AS count FROM `{$dbpre}attachment` WHERE `module`='shop' AND `shopid` = $k");
                        $_num = $_pics['count'];
                        $db->query("UPDATE `{$dbpre}shop` SET `pics`='$_num' WHERE `id` = '$k' ");
                    }
                    showmessage("更新成功！");

                break;

                case 'review';
                    $_allshop = $db->select("SELECT `id`,`reviews`,`shopname` FROM `{$dbpre}shop`",'id');
                    foreach($_allshop AS $k=>$v){
                        $_reviews = $db->get_one("SELECT COUNT(*) AS count FROM `{$dbpre}review` WHERE `shopid` = $k");
                        $_num = $_reviews['count'];
                        $db->query("UPDATE `{$dbpre}shop` SET `reviews`='$_num' WHERE `id` = '$k' ");
                    }
                    showmessage("更新成功！");

                break;
            }
        
            $tpl = 'tools_rebuild';
        break;
        
        
        case 'phpinfo':

            $tpl = 'tools_phpinfo';
        break;
        
        
        case 'notice':

            $tpl = 'tools_notice';
        break;
        
}
include atpl($tpl);
?>