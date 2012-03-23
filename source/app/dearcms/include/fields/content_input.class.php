<?php
class content_input
{
	var $modelid;
	var $fields;
	var $data;
	var $isimport;

    function __construct($modelid)
    {
		global $db;
		$this->db = &$db;
		$this->modelid = $modelid;
		$this->fields = cache_read($this->modelid.'_fields.inc.php', CACHE_MODEL_PATH);
    }

	function content_input($modelid)
	{
		$this->__construct($modelid);
	}

	function get($data, $isimport = 0)
	{
		global $MODEL, $_groupid,$action,$G;
		$this->isimport = $isimport;

		$this->data = $data;
		$info = array();
		$debar_filed = array('catcode','title','style','thumb','status','islink','description');
		foreach($data as $field=>$value)
		{
			if($data['islink']==1 && !in_array($field,$debar_filed)) continue;

			$name = $this->fields[$field]['name'];
			$minlength = $this->fields[$field]['minlength'];
			$maxlength = $this->fields[$field]['maxlength'];
			$pattern = $this->fields[$field]['pattern'];
			$errortips = $this->fields[$field]['errortips'];
			if(empty($errortips)) $errortips = "$name 不符合要求！";
			$length = strlen($value);
			if($minlength && $length < $minlength && !$isimport) showmessage("$name 不得少于 $minlength 个字符！");
			if($maxlength && $length > $maxlength && !$isimport)
			{
				showmessage("$name 不得超过 $maxlength 个字符！");
			}
			else
			{
				str_cut($value, $maxlength);
			}
			if($pattern && $length && !preg_match($pattern, $value) && !$isimport) showmessage($errortips);
            $checkunique_table = $this->fields[$field]['issystem'] ? DB_PRE.'content' : DB_PRE.'c_'.$MODEL[$this->modelid]['tablename'];
            if($this->fields[$field]['isunique'] && $this->db->get_one("SELECT $field FROM $checkunique_table WHERE `$field`='$value' LIMIT 1") && $action != 'edit') showmessage("$name 的值不得重复！");
			$func = $this->fields[$field]['formtype'];
			if(method_exists($this, $func)) $value = $this->$func($field, $value);
			if($this->fields[$field]['issystem']) $info['system'][$field] = $value;
			else $info['model'][$field] = $value;
		}
		if($isimport) $info['system']['username'] = $data['username'];
		return $info;
	}

}?>