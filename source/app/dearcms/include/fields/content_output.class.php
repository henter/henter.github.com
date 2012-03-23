<?php
class content_output
{
	var $fields;
	var $data;

    function __construct()
    {
		global $db, $CAT;
		$this->db = &$db;
		$this->CAT = $CAT;
    }

	function content_output()
	{
		$this->__construct();
	}

	function set_catcode($catcode)
	{
		$modelid = $this->CAT[$catcode]['modelid'];
		$this->modelid = $modelid;
		$this->fields = cache_read($modelid.'_fields.inc.php', CACHE_MODEL_PATH);
	}

	function get($data)
	{
		$this->data = $data;
		$this->contentid = $data['contentid'];
		$this->set_catcode($data['catcode']);
		$info = array();
		foreach($this->fields as $field=>$v)
		{
			if(!isset($data[$field])) continue;
			$func = $v['formtype'];
			$value = $data[$field];
			$result = method_exists($this, $func) ? $this->$func($field, $data[$field]) : $data[$field];
			if($result !== false) $info[$field] = $result;
		}
		return $info;
	}
}?>