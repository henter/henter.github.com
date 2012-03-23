	function keyword($field, $value)
	{
		if(!$value)
		{
		    if(!$value) return '';
		}
		if(strpos($value, ' '))
		{
			$s = ' ';
		}
		elseif(strpos($value, ','))
		{
			$s = ',';
		}
		$keywords = isset($s) ? array_unique(array_filter(explode($s, $value))) : array($value);
		foreach($keywords as $tag)
		{
			$tag = trim($tag);
			if($this->db->get_one("SELECT `tagid` FROM `".DB_PRE."tags` WHERE `tag`='$tag'"))
			{
				$this->db->query("UPDATE `".DB_PRE."tags` SET `usetimes`=`usetimes`+1,`lastusetime`=".TIME." WHERE `tag`='$tag'");
			}
			else
			{
				$this->db->query("REPLACE INTO `".DB_PRE."tags` (`tag`,`usetimes`,`lastusetime`,`module`) VALUES('$tag','1','".TIME."','dearcms')");
			}
		}
		return implode(' ', $keywords);
	}
