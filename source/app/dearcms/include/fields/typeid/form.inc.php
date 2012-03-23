	function typeid($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		return form::select_type('dearcms', 'info['.$field.']', $field, '请选择', $value, '', $this->modelid);
	}
