	function images($field, $value)
	{
	    global $aids,$attachment;
		return $attachment->update($this->contentid, $field);
	}
