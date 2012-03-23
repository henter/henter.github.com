	function image($field, $value)
	{
		$aid = intval($GLOBALS[$field.'_aid']);
		if($aid < 1) return false;
		$_SESSION['field_image'] = 1;
		return $this->db->query("UPDATE `".DB_PRE."attachment` SET `contentid`=$this->contentid WHERE `aid`=$aid");
	}
