	function keyword($field, $value, $fieldinfo)
	{
		$infos = cache_read('keyword.php');
		return form::text($field, $field, $value, $type, 20, $css, $formattribute);
	}
