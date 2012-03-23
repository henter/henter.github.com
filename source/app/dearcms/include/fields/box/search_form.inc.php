	function box($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if($boxtype == 'radio')
		{
			return form::radio($options, $field, $field, $value, $cols, $css, $formattribute, $width);
		}
		else
		{
			return form::select($options, $field, $field, $value, $size, $css, $formattribute);
		}
	}
