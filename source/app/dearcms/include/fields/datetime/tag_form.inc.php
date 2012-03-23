	function datetime($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		$isdatetime = $dateformat == 'datetime' ? 1 : 0;
		$str = form::date("info[$field]", $value, $isdatetime);
		return $str;
    }
