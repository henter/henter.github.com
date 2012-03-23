	function keyword($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		if(defined('IN_ADMIN')) $data .= " <a href=\"###\" onclick=\"SelectKeyword();\">更多&gt;&gt;</a>";
		return form::text('info['.$field.']', $field, $value, $type, $size, $css, $formattribute).$data;
	}
