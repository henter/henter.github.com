	function catcode($field, $value)
	{
		global $CAT;
		if(!isset($CAT[$value])) showmessage("所选栏目不存在！");
		return $value;
	}
