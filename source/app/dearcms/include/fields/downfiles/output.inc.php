function downfiles($field, $value)
{
	$contentid = $this->contentid;
	$result = '';
	$downloadtype = $this->fields[$field]['downloadtype'];
	$values = explode("\n",$value);
	foreach($values AS $k=>$v)
	{
		$v = explode("|",$v);
		$name = $v[0];
		$downurl = $v[1];
		$a_k = urlencode(dc_auth("i=$contentid&s=$serverurl&m=0&f=$downurl&d=$downloadtype", 'ENCODE', AUTH_KEY));
		$result .= "<a href='down.php?a_k=$a_k' target='_blank'>$name</a>";
	}
	return $result;
}
