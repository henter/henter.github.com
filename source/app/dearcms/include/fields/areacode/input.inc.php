	function areacode($field, $value)
	{
		global $AREA;
		if($value && !isset($AREA[$value])) showmessage("所选地区不存在！");
		return $value;
	}
