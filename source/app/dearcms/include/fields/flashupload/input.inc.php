	
	function flashupload($field, $value)
	{		
		$serverurl = $value['server'] ? $value['server'] : SITE_URL;		
		$values = explode("\n",$value['videourl']);
		foreach($values AS $k=>$v)
		{
			$v = explode("|",$v);
			if(!$v[0]) continue;
			$name = $v[0];
			$videourl = $v[1];
			$str_video .= $name.'|'.$videourl.'\n';
		}
		$str_video = str_replace('\n', ';', $str_video);
		$array['str_video'] = $str_video;
		$array['player'] = $value['player'];
		$array['server'] = $serverurl;
		$str_video = array2string($array);
		return $str_video;
	}