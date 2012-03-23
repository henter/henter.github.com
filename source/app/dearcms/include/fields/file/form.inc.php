	function file($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		$data = '<input type="hidden" name="info['.$field.']" value="'.$value.'"/>';
		$data .= form::file($field, $field, $size, $css, $formattribute);
		if($value) $data .= " <a href='$value' title='$value'>查看文件</a>";
		return $data;
	}
