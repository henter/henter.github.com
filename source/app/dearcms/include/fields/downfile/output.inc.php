function downfile($field, $value)
{
	$contentid = $this->contentid;
	$mode = $this->fields[$field]['mode'];
	$result = '';
	if($mode)
	{
		$servers = $this->fields[$field]['servers'];
		$downloadtype = $this->fields[$field]['downloadtype'];
		$servers = explode("\n",$servers);
		foreach($servers AS $k=>$server)
		{
			$server = explode("|",$server);
			$serverurl = $server[1];
			$a_k = urlencode(dc_auth("i=$contentid&s=$serverurl&m=1&f=$value&d=$downloadtype", 'ENCODE', AUTH_KEY));
			$result .= "<a href='down.php?a_k=$a_k' target='_blank'>$server[0]</a>";
		}
	}
	else
	{
		$a_k = urlencode(dc_auth("i=$contentid&m=0&f=$value", 'ENCODE', AUTH_KEY));
		$result = "<a href='down.php?a_k=$a_k' target='_blank'>点击下载</a>";
	}
	return $result;
}
