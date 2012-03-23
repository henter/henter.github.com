	
	function flashupload($field, $value)
	{
		global $attachment;
		$contentid = $this->contentid;
		$result = '';
		$value = str_replace(array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;'),array('&', '"', "'", '<', '>') ,$value);
		eval("\$value = $value;");
		$values = explode(";",$value['str_video']);
		$t = $p = $value['player'];
		$server = $value['server'];
		foreach($values AS $k=>$v)
		{
			if(!$v) continue;
			$v = explode("|",$v);
			$name = $v[0];
			if(preg_match('/:\/\//i',$v[1]))
			{
				$videourl = trim($v[1]);
			}
			else
			{
				$videourl = $server.trim($v[1]);
			}
			if(!$t)
			{
				$fileext = fileext($videourl);
				if(preg_match("/rm|rmvb|avi|smi/",$fileext)){
					$p = 3;// playerid =3 采用 精美real播放器
				}
				elseif(preg_match("/flv/",$fileext)){
					$p = 4;//  playerid =4 采用 FLV 播放器
				}
				elseif(preg_match("/swf/",$fileext)){
					$p = 5;//  playerid =5 采用 FLASH 播放器
				}
				else{
					$p = 2;//playerid =2 采用window media player
				}
			}
			$number = $k+1;
			$a_k = urlencode(dc_auth("i=$contentid&m=0&p=$p&f=$videourl", 'ENCODE', AUTH_KEY));
			$result .= "<a href='play.php?a_k=$a_k' target='_blank'>$name</a>";
		}
		return $result;
	}