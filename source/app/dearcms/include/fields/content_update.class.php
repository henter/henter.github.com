<?php
class content_update
{
	var $modelid;
	var $fields;
	var $contentid;
	var $data;

    function __construct($modelid, $contentid)
    {
		global $db;
		$this->db = &$db;
		$this->modelid = $modelid;
		$this->contentid = $contentid;
		$this->fields = cache_read($this->modelid.'_fields.inc.php', CACHE_MODEL_PATH);
    }

	function content_update($modelid, $contentid)
	{
		$this->__construct($modelid, $contentid);
	}

	function update($data)
	{
		$info = array();
		$this->data = $data;
		foreach($data as $field=>$value)
		{
			if(!isset($this->fields[$field])) continue;
			$func = $this->fields[$field]['formtype'];
			$info[$field] = method_exists($this, $func) ? $this->$func($field, $value) : $value;
		}
		return $info;
	}

}?>