    function catcode($field, $value)
    {
	     return $value === '' ? '' : '".get_sql_catcode('.$value.')."'; 
    }
