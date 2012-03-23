	function keyword($field, $value)
	{
	    if($value === '') return '';
		$value = str_replace(array('\'','"'), array('',''), $value);
	    if(strpos($value, ' '))
		{
		    $tags = array_map('trim', explode(' ', $value));
			$tags = "'".implode("','", $tags)."'";
			$where = "k.`tag` IN($tags)";
		}
		else
		{
			$where = "k.`tag`='$value'";
		}
		return array(DB_PRE.'tags','contentid',$where);
	}
