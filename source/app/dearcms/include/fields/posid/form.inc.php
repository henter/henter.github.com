	function posid($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		$result = $this->db->select("SELECT `posid` FROM `".DB_PRE."content_position` WHERE `contentid`='$this->contentid'", 'posid');
		$posids = implode(',', array_keys($result));
		return form::select_pos('info['.$field.']', $field, $posids, $cols, $width);
	}
