	function image($field, $value)
	{
            return $value ? $value : 'images/nopic.gif'; //henter修改 直接返回图片地址
		if($value !='')
		{
			$value = '<img src="'.$value.'" border="0">';
		}
		else
		{
			$value = '<img src="images/nopic.gif" border="0">';
		}
		return $value;
	}
