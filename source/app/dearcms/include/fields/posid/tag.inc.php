    function posid($field, $value)
    {
		return ($value === '' || $value == 0) ? '' : array(DB_PRE.'content_position','contentid',"p.posid=$value");
	}
