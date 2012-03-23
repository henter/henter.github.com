    function userid($field, $value)
    {
	     return $value === '' ? '' : " `userid`='$value' "; 
    }
