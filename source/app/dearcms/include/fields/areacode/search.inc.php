   
	function areacode($field, $value)
    {
	     return ($value === '' || !$value) ? '' : " `$field`='$value' "; 
    }