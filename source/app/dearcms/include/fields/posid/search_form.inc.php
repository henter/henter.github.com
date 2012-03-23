	function posid($field, $value, $fieldinfo)
	{
		if(!defined('IN_ADMIN')) return false;
	    $POS = cache_read('pos.php');
		extract($fieldinfo);
		array_unshift($POS, '请选择');
		return form::select($POS, $field, $field, $value);
	}
