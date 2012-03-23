<?php
class content_form
{
	var $modelid;
	var $fields;
	var $contentid;

    function __construct($modelid)
    {
		global $db;
		$this->db = &$db;
		$this->modelid = $modelid;
		$this->fields = cache_read($this->modelid.'_fields.inc.php', CACHE_MODEL_PATH);
    }

	function content_form($modelid)
	{
		$this->__construct($modelid);
	}

	function get($data = array())
	{
		global $_roleid,$_groupid;
		if(isset($data['contentid'])) $this->contentid = $data['contentid'];
		$info = array();
		$this->content_url = $data['url'];
		foreach($this->fields as $field=>$v)
		{
			if(defined('IN_ADMIN'))
			{
				if($v['iscore'] || check_in($_roleid, $v['unsetroleids']) || check_in($_groupid, $v['unsetgroupids'])) continue;
			}
			else
			{
				if($v['iscore'] || !$v['isadd'] || check_in($_roleid, $v['unsetroleids']) || check_in($_groupid, $v['unsetgroupids'])) continue;
			}
			$func = $v['formtype'];
			$value = isset($data[$field]) ? htmlspecialchars($data[$field], ENT_QUOTES) : '';
			if($func=='pages' && isset($data['maxcharperpage']))
			{
				$value = $data['paginationtype'].'|'.$data['maxcharperpage'];
			}
			$form = $this->$func($field, $value, $v);
			if($form !== false)
			{
				if(defined('IN_ADMIN'))
				{
					if($v['isbase'])
					{
						$star = $v['minlength'] || $v['pattern'] ? 1 : 0;
						$info['base'][$field] = array('name'=>$v['name'], 'tips'=>$v['tips'], 'form'=>$form, 'star'=>$star);
					}
					else
					{
						$star = $v['minlength'] || $v['pattern'] ? 1 : 0;
						$info['senior'][$field] = array('name'=>$v['name'], 'tips'=>$v['tips'], 'form'=>$form, 'star'=>$star);
					}
				}
				else
				{
					$star = $v['minlength'] || $v['pattern'] ? 1 : 0;
					$info[$field] = array('name'=>$v['name'], 'tips'=>$v['tips'], 'form'=>$form, 'star'=>$star);
				}
			}
		}
		return $info;
	}
}?>