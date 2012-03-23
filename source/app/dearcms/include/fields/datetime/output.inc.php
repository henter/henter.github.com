	
	function datetime($field, $value)
	{
		return $this->fields[$field]['dateformat'] == 'int' ? date($this->fields[$field]['format'], $value) : (substr($value, 0, 4) =='0000' ? '' : $value);
	}