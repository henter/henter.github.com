	function posid($field, $value)
	{
		global $action;
		$this->db->query("DELETE FROM `".DB_PRE."content_position` WHERE `contentid`='$this->contentid'");
		if($value == '-99')
		{
			if($action == 'edit') $this->db->query("UPDATE ".DB_PRE."content SET posids=0 WHERE contentid='$this->contentid'");
			return false;
		}
		if(is_array($value))
		{
			foreach($value as $posid)
			{
				$posid = intval($posid);
				$this->db->query("INSERT INTO `".DB_PRE."content_position` (`contentid`,`posid`) VALUES('$this->contentid','$posid')");
			}
		}
		return true;
	}
