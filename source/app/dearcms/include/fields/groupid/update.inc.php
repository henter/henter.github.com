	function groupid($field, $value)
	{
		if(!$value) return false;
		global $priv_group;
		$priv_groupid = array();
		$priv = $this->fields[$field]['priv'];
		foreach($value as $groupid)
		{
		    $priv_groupid[] = $priv.','.$groupid;
		}
		$priv_group->update('contentid', $this->contentid, $priv_groupid);
		return true;
	}
