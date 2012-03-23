	function groupid($field, $value, $fieldinfo)
	{
	    global $priv_group;
		extract($fieldinfo);
		$groupids = '';
		if($value && $this->contentid) 
		{
			$groupids = $priv_group->get_groupid('contentid', $this->contentid, $priv);
			$groupids = implode(',', $groupids);
		}
		return form::select_group('info['.$field.']', $field, $groupids, $cols, $width);
	}
