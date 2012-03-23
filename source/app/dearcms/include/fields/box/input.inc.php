	function box($field, $value)
	{
		if($this->fields[$field]['boxtype'] == 'checkbox') 
		{
			if(!is_array($value) || empty($value)) return false;
			$value = implode(',', $value);
		}
		return $value;
	}
