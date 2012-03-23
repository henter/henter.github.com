	
	function datetime($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value)
		{
			if($defaulttype == 0)
			{
				$value = '';
				$dateformat == 'datetime' ? 'Y-m-d H:i:s' : 'Y-m-d';
				$isdatetime = $dateformat == 'datetime' ? 1 : 0;
				if($dateformat == 'int')
				{
					if($format=='Y-m-d H:i:s' || $format=='Y-m-d H:i')
					$isdatetime = 1;
					else $isdatetime = 0;
				}
			}
			elseif($defaulttype == 1)
			{
				
				$df = $dateformat == 'datetime' ? 'Y-m-d H:i:s' : 'Y-m-d';
				$isdatetime = $dateformat == 'datetime' ? 1 : 0;
				if($dateformat == 'int')
				{
					$df = $format;
					if($format=='Y-m-d H:i:s' || $format=='Y-m-d H:i')
					$isdatetime = 1;
					else $isdatetime = 0;
				}
				
				$value = date($df, TIME);
			}
			else
			{
				$value = $defaultvalue;
			}
		}
		else
		{
			if(substr($value, 0, 10) == '0000-00-00') $value = '';
			if($dateformat == 'int')
			{
				$value = date('Y-m-d H:i:s', $value);
				if($format=='Y-m-d H:i:s' || $format=='Y-m-d H:i')
				$isdatetime = 1;
				else $isdatetime = 0;
			}
		}
		$str = form::date("info[$field]", $value, $isdatetime);
		return $str;
	}
