	
	function keyword($field, $value)
	{
		if($value)
		{
			$query = $this->db->query("SELECT `contentid` FROM `".DB_PRE."tags` WHERE `tag`='$value'");
			while($r = $this->db->fetch_array($query))
			{
				$result[] = $r['contentid'];  
			}
			if(!$result) 
			{
				return " `contentid` IN ('') ";
			}	
			$str_content = implode(",", $result);
		}
		return ($value === '' || $str_content == '') ? '' : " `contentid` IN($str_content) "; 
	}