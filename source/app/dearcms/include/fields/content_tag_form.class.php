<?php
class content_tag_form
{
	var $modelid;
	var $fields;
	var $contentid;

    function __construct($modelid)
    {
		global $db;
		$this->db = &$db;
		$this->modelid = $modelid;
		$this->fields = $this->modelid ? cache_read($this->modelid.'_fields.inc.php', CACHE_MODEL_PATH) : cache_read('common_fields.inc.php', 'fields/');
    }

	function content_tag_form($modelid)
	{
		$this->__construct($modelid);
	}

	function get($data = array())
	{
		$info = array();
		foreach($this->fields as $field=>$v)
		{
			if(!$v['iswhere']) continue;
			$func = $v['formtype'];
			if(!method_exists($this,$func)) continue;
			$value = isset($data[$field]) ? $data[$field] : '';
			$form = $this->$func($field, $value, $v);
			$info[$field] = array('name'=>$v['name'], 'tips'=>$v['tips'], 'form'=>$form, 'star'=>$v['minlength']);
		}
		return $info;
	}

}?>