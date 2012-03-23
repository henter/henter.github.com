	function keyword($field, $value)
	{
		$r = $this->db->get_one("SELECT `$field` FROM `".DB_PRE."content` WHERE `contentid`=$this->contentid");
		$value = $r[$field];
		$this->db->query("DELETE FROM `".DB_PRE."tags` WHERE `contentid`=$this->contentid");
		$keywords = explode(' ', $value);
		foreach($keywords as $tag)
		{
			$tag = addslashes(trim($tag));
			//if($tag) $this->db->query("INSERT INTO `".DB_PRE."tags` (`tag`,`contentid`) VALUES('$tag','$this->contentid')");
			if($tag) $this->db->query("REPLACE INTO `".DB_PRE."tags` (`tag`,`contentid`) VALUES('$tag','$this->contentid')");
		}
        if(function_exists('cache_keyword')) cache_keyword();
		return true;
	}
