<?php
defined('IN_ADMIN') or exit('Access Denied');

if(!$action) $action = 'manage';
if(!$forward) $forward = "?file=$file&action=manage";
$subtitles = array('main'=>'主站','shop'=>'商铺','member'=>'个人空间');

switch ($action){

	case 'manage':
            if($dosubmit){
                if(is_array($info)){
                    $_tmptpls[$type] = $info;
                    if(is_array($TPLS)){
                        $tmptpls = array_merge($TPLS, $_tmptpls);
                    }else{
                        $tmptpls = $_tmptpls;
                    }
                    cache_write('template.php',$tmptpls);
                }
                showmessage("$subtitles[$type]模板缓存更新成功！");
                
            }else{
                $list = glob(TPL_ROOT.$type.'/'.'*');
                $files = glob(TPL_ROOT.$type.'/'.'*.*');
                $dirs = $files ? array_diff($list, $files) : $list;


                $tpldirs = array();
                foreach($dirs as $d)
                {
                    $tpldirs['dir'] = basename($d);
                    $tpldirs['name'] = isset($TPLS[$type][$tpldirs['dir']]) ? $TPLS[$type][$tpldirs['dir']][name] : '';
                    $tpldirs['isdefault'] = $DC["tpl_$type"] == $tpldirs['dir'] ? 1 : 0;
                    $tpldirs['mtime'] = date('Y-m-d H:i:s',filemtime($d));
                    $tpldirarray[$type][]= $tpldirs;
                }
                ksort($tpldirarray);


            }
            $tpl='template';
	break;


}

include atpl($tpl);
?>