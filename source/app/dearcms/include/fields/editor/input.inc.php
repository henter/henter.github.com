	function editor($field, $value)
	{
	    global $attachment;
		if($this->fields[$field]['enablesaveimage'] && !$this->isimport) $value = $attachment->download($field, $value);
		return $value;
	}
