	function editor($field, $value)
	{
	    global $aids,$attachment;
		if(!$value) return false;
		if($this->fields[$field]['storage'] == 'file')
		{
			content_set($this->contentid, $field, stripslashes($value));
		}
		$attachment->update($this->contentid, $field, $value);
		if($GLOBALS['add_introduce'] && $value)
		{
			$attachment->update_intr($this->contentid, $value, $GLOBALS['introcude_length']);
		}
		if($GLOBALS['auto_thumb'])
		{
			$attachment->update_thumb($this->contentid, $GLOBALS['auto_thumb_no']);
		}
		return 1;
	}
