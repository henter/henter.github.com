	function editor($field, $value)
	{
		$data = $this->fields[$field]['storage'] == 'database' ? $value : content_get($this->contentid, $field);
		return $data;
	}
