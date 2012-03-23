	function datetime($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		$isdatetime = $dateformat == 'datetime' ? 1 : 0;
		return form::date("{$field}[start]", $value['start'], $isdatetime).' - '.form::date("{$field}[end]", $value['end'], $isdatetime);
	}
