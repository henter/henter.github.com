<?php
defined('IN_ADMIN') or exit('Access Denied');

require_once 'marketarea.class.php';
require_once 'form.class.php';
$marea = new marketarea();

if(!$action) $action = 'manage';
if(!$forward) $forward = "?file=$file&action=manage&areacode=$areacode";


switch ($action){
	case 'manage':
              if($areacode){
                    $where = "  `areacode` = '$areacode' ";
              }else{
                    $where = "";
              }

		$mareas = $marea->listinfo($where,'listorder,id');
		$tpl='marea_manage';
	break;


	case 'add' :
		if ($dosubmit){
            	      if(!is_array($info) || empty($info['areaname'])) showmessage('商圈名称不能为空！');
                    if($marea->ck($areacode,$info[areaname])) showmessage('该地区已经存在！');
		      $marea->add($info);
                    cache_marea();
                    showmessage('添加成功！',"?file=$file&areacode=$areacode");
		}
		$tpl='marea_add';
	break;

	case 'edit' :
		if ($dosubmit){
            	      if(!is_array($info) || empty($info['areaname'])) showmessage('商圈名称不能为空！');
		      $marea->edit($info,$id);
                    cache_marea();
                    showmessage('修改成功！',"?file=$file&areacode=$areacode");
		}else{
                    $data = $marea->get($id);
                    extract($data);
			$tpl='marea_add';
		}
	break;

	case 'delete':
		$marea->delete($id);
              cache_marea();
		showmessage('删除成功',"?file=$file&areacode=$areacode");
	break;

    case 'listorder':
		$result = $marea->listorder($listorder);
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
		if(!$id) showmessage('请选择要操作的地区');
		$marea->status($id,$status);
              cache_marea();
		showmessage('操作成功！', $forward);
	break;


    case 'cache':
            cache_marea();
            showmessage('操作成功！', $forward);
    break;



}




include atpl($tpl);
?>