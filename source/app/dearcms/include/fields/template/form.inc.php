	function template($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		return form::select_template('dearcms','info['.$field.']', $field, $value, '', 'show');
	}
