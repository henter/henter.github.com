	function number($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		return form::text($field.'[start]', $field, $value['start'], 'text', 10, $css, $formattribute).' - '.form::text($field.'[end]', $field, $value['end'], 'text', 10, $css, $formattribute);
	}
