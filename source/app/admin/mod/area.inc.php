<?php
defined('IN_ADMIN') or exit('Access Denied');

require_once 'area.class.php';
require_once 'form.class.php';
$area = new area();

if(!$action) $action = 'manage';
if(!$forward) $forward = "?file=$file&action=manage";

$submenu = array(
	array('添加地区', '?file='.$file.'&action=add'),
	array('管理地区', '?file='.$file.'&action=manage'),
	array('更新地区缓存', '?file='.$file.'&action=cache'),
);
$menu = admin_menu($modelname.'地区管理', $submenu);


switch ($action){
	case 'manage':
              if($parentid){
                    $len = strlen($parentid)+2;
                    $lensql = " AND LENGTH(areacode)=$len";
                    $where = "areacode LIKE '$parentid%' AND areacode!=$parentid $lensql";
              }else{
                    $lensql = " AND LENGTH(areacode)=2";
                    $where = "1 $lensql";
              }

		$areas = $area->listinfo($where,'listorder,aid');
		$tpl='area_manage';
	break;


	case 'add' :
		if ($dosubmit){
            	      if(!is_array($info) || empty($info['areaname'])) showmessage('地区名称不能为空！');
                    if($area->getbyname($info[areaname])) showmessage('该地区已经存在！');
                    $info['setting'] = array2string($setting);
		      $area->add($info,$parentid);
                    cache_area();
                    showmessage('添加成功！',"?file=$file");
		}
		$tpl='area_add';
	break;

	case 'edit' :
		if ($dosubmit){
            	      if(!is_array($info) || empty($info['areaname'])) showmessage('地区名称不能为空！');
                    $info['setting'] = array2string($setting);
		      $area->edit($info,$areacode);
                    cache_area();
                    showmessage('修改成功！',"?file=$file");

		}else{
			$result = $area->get($areacode);
			@extract($result);
                     @extract(string2array($setting));
			$tpl='area_add';
		}
	break;

	case 'delete':
		$area->delete($areacode);
              cache_area();
		showmessage('删除成功',"?file=$file");
	break;

    case 'listorder':
		$result = $area->listorder($listorder);
		if($result)
		{
			showmessage('操作成功！', $forward);
		}
		else
		{
			showmessage('操作失败！');
		}
	break;


	case 'status':
		if(!$acode) showmessage('请选择要操作的地区');
		$area->status($acode,$status);
              cache_area();
		showmessage('操作成功！', $forward);
	break;


    case 'cache':
            cache_area();
            cache_areaconfig();
            showmessage('操作成功！', $forward);
    break;



	case 'getchild':
            $array = array();
            $len = $parentid ? (strlen($parentid) == 2 ? 4 : 6) : 2;
            $where = $parentid ? "areacode LIKE '$parentid%' AND areacode!=$parentid AND LENGTH(areacode)=$len": "LENGTH(areacode)=$len";
            $infos = $area->listinfo($where, 'listorder, aid', $page, 20);
            foreach($infos as $k=>$v)
            {
                $array[$v['areacode']] = $v['areaname'];
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