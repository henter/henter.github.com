<?php
defined('IN_ADMIN') or exit('Access Denied');

require_once 'cat.class.php';
require_once 'form.class.php';
$cat = new cat();

if(!$action) $action = 'manage';
if(!$forward) $forward = "?file=$file&action=manage";

$submenu = array(
	array('全部栏目', "?file=$file"),
);

    foreach($MODULE as $mid => $m)
    {
        if($mid == 'dearcms' || $mid == 'shop'){
            $_tmp[0] = $m[name];
            $_tmp[1]="?file=$file&module=$mid";
            $submenu[]=$_tmp;
        }
    }


//$menu = admin_menu($modelname.'按模块区分', $submenu);


switch ($action){
	case 'manage':
              $module = 'dearcms';
              $module_where = $module ? " AND `module`='$module' " : "";
              if($parentid){
                    $len = strlen($parentid)+2;
                    $lensql = " $module_where AND LENGTH(catcode)=$len";
                    $where = "catcode LIKE '$parentid%' AND catcode!=$parentid $lensql";
              }else{
                    $lensql = " $module_where AND LENGTH(catcode)=2";
                    $where = "1 $lensql";
              }

		$cats = $cat->listinfo($where,'listorder,cid');
		$tpl='cat_manage';
	break;


	case 'add' :
		if ($dosubmit){
            	      if(!is_array($info) || empty($info['catname'])) showmessage('分类名称不能为空！');
                    if($cat->getbyname($info[catname])) showmessage('该分类已经存在！');
                    $info['setting'] = array2string($setting);
		      $cat->add($info,$parentid);
                    cache_cat();
                    showmessage('添加成功！',"?file=$file");
		}else{


			$tpl='cat_add';
            }
	break;

	case 'edit' :
		if ($dosubmit){
            	      if(!is_array($info) || empty($info['catname'])) showmessage('分类名称不能为空！');
                    $info['setting'] = array2string($setting);
		      $cat->edit($info,$catcode);
                    cache_cat();
                    showmessage('修改成功！',"?file=$file");

		}else{
			$result = $cat->get($catcode);
			@extract($result);
                     @extract(string2array($setting));

			$tpl='cat_add';
		}
	break;

	case 'delete':
		$cat->delete($catcode);
                    cache_cat();
		showmessage('删除成功',"?file=$file");
	break;

    case 'listorder':
		$result = $cat->listorder($listorder);
		if($result)
		{
			showmessage('操作成功！', $forward);
		}
		else
		{
			showmessage('操作失败！');
		}
		break;


	case 'getchild':
            $array = array();
            $len = $parentid ? (strlen($parentid) == 2 ? 4 : 6) : 2;
            $where = $parentid ? "catcode LIKE '$parentid%' AND catcode!=$parentid AND LENGTH(catcode)=$len": "LENGTH(catcode)=$len";
            $infos = $cat->listinfo($where, 'listorder, cid', $page, 20);
            foreach($infos as $k=>$v)
            {
                $array[$v['catcode']] = $v['catname'];
            }
            if(!$parentid || $array)
            {
                $array[0] = $parentid ? '请选择' : '无';
                ksort($array);
                echo form::select($array, 'setparentid', 'setparentid', $parentid, 1, '', 'onchange="if(this.value>0){getchild(this.value);myform.parentid.value=this.value;this.disabled=true;}"');
            }
            exit;
            break;

}

include atpl($tpl);
?>