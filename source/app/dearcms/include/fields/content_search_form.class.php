<?php
class content_search_form
{
	var $db;
	var $modelid;
	var $fields;
	var $common_fields;
	var $contentid;

    function __construct()
    {
		global $db;
		$this->db = &$db;
        $this->fields = $this->common_fields = cache_read('common_fields.inc.php', 'fields/');
		$catcode = isset($_GET['catcode']) ? intval($_GET['catcode']) : 0;
		if($catcode > 0) $this->set_catcode($catcode);
        $this->set();
    }

	function content_search_form()
	{
		$this->__construct();
	}

	function set()
	{
		$this->where = array();
		if(!is_array($this->fields) || empty($this->fields)) return true;
		foreach($this->fields as $field=>$v)
		{
			$func = $v['formtype'];
			if($v['issearch'] && method_exists($this, $func))
			{
				$value = isset($_GET[$field]) ? $_GET[$field] : '';
				$form = $this->$func($field, $value, $v);
				if($form !== false) 
				{
					$this->where[$field] = array('name'=>$v['name'], 'tips'=>$v['tips'], 'form'=>$form, 'star'=>$v['minlength']);
				}
			}
			if($v['isorder'])
			{
				$pre = isset($this->common_fields[$field]) ? 'a.' : 'b.';
				$this->order[$pre.$field.' ASC'] = $v['name'].' 升序';
				$this->order[$pre.$field.' DESC'] = $v['name'].' 降序';
			}
		}
		return true;
	}

	function set_catcode($catcode)
	{
		global $MODEL,$CAT;
		if(!isset($CAT[$catcode])) return false;
		$modelid = $CAT[$catcode]['modelid'];
		$this->fields = cache_read($modelid.'_fields.inc.php', CACHE_MODEL_PATH);
		return true;
	}

	function get_where()
	{
		return $this->where;
	}

	function get_order()
	{
		return $this->order;
	}

}?>