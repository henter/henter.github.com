	function editor($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		if($this->contentid && $this->fields[$field]['storage'] == 'file') $value = content_get($this->contentid, $field);
		$data = "<textarea name=\"info[$field]\" id=\"$field\" style=\"display:none\">$value</textarea>\n";
		return $data.form::editor($field, $toolbar, $width, $height);
	}
