	
	function datetime($field, $value)
	{
		if($this->fields[$field]['dateformat'] == 'int')
		{
			return strtotime($value);
		}
		else
		{
			return $value;
		}
	}
