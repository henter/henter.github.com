    function catcode($field, $value)
    {
		$value = get_sql_catcode($value);
		$value = str_replace('AND','',$value);
		return $value === '' ? '' : " $value "; 
    }
