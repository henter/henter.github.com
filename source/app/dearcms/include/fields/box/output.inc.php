	function box($field, $value)
	{
		$s1 = "\n";
		$s2 = '|';
		$options = explode($s1, $this->fields[$field]['options']);
		foreach($options as $option)
		{
			if(strpos($option, $s2))
			{
				list($name, $id) = explode($s2, trim($option));
			}
			else
			{
				$name = $id = trim($option);
			}
			$os[$id] = $name;
		}
		if(strpos($value, ','))
		{
			$ids = explode(',', $value);
			$value = '';
			foreach($ids as $id)
			{
				$value .= $os[$id].' ';
			}
		}
		else
		{
			$value = $os[$value];
		}
		return $value;
	}
