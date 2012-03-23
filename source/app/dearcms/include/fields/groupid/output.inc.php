	function groupid($field, $value)
	{
	    global $priv_group, $GROUP;
		if(!isset($GROUP)) $GROUP = cache_read('member_group.php');
        $value = '';
		$priv = $this->fields[$field]['priv'];
		$groupids = $priv_group->get_groupid('contentid', $this->contentid, $priv);
		foreach($groupids as $groupid)
		{
			$value .= $GROUP[$groupid].' ';
		}
		return $value;
	}
