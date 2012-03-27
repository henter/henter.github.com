<?php

class date extends object
{
     protected $timestamp,
               $year,
               $month,
               $day,
               $hour,
               $minute,
               $second,
               $yday,
               $mday,
               $wday,
               $weekday,
               $monthtext;

    public function __construct($date = null)
    {
    	$this->set_date($date);
    }

    public function totime($date = null)
    {
        if (is_string($date))
        {
        	if (!$date) return time();
        	$time = strtotime($date);
        	return $time == -1 ? time() : $time;
        } 
        elseif(is_null($date))
        {
            return time();
        } 
        elseif(is_numeric($date))
        {
            return $date;
        } 
        else
        {
        	return get_class($date) == 'date' ? $date->timestamp : time();
        }
    }

    public function valid($date)
    {
    	$this->set_date($date);
        return checkdate($this->month , $this->day , $this->year);
    }

    public function set_date($date)
    {
    	$time = $this->totime($date);
        $array = getdate($time);
        $this->timestamp    =   $array[0];
        $this->second       =   $array["seconds"];
        $this->minute       =   $array["minutes"];
        $this->hour         =   $array["hours"];
        $this->year         =   $array["year"];
        $this->month        =   $array["mon"];
        $this->day          =   $array["mday"];
        $this->mday          =   $array["mday"];
        $this->wday          =   $array["wday"];
        $this->yday         =   $array["yday"];
        $this->monthtext       =   $array["month"];
        $this->weekday       =   $array["weekday"];
        return ;
    }

    public function format($format = "%Y-%m-%d %H:%M:%S")
    {
    	return strftime($format, $this->timestamp);
    }

    public function is_leapyear()
    {
        return date('l', $this->timestamp);
    }

    public function diff($date, $elaps = 'd')
    {
    	$difftime = $this->totime($date) - $this->timestamp;
        $days = $difftime / 86400;
        switch ($elaps) 
        {
            case 'y':
                $return =  $days / 365;
                break;
            case 'm':
                $return =  $days / 30;
                break;
            case 'd':
                $return = $days;
                break;
            case 'w':
                $return =  $days / 7;
                break;
            case 'h':
                $return =  $days * 24;
                break;
            case 'i':
                $return =  $days * 1440;
                break;
            case 's':
                $return = $difftime;
                break;
        }
        return $return;
    }

    public function timediff($time, $precision = false)
    {
        if(!is_numeric($precision) && !is_bool($precision)) 
        {
            static $_diff = array('y'=>'年', 'm'=>'个月', 'd'=>'天', 'w'=>'周', 's'=>'秒', 'h'=>'小时', 'm'=>'分钟');
            return ceil($this->diff($time, $precision)).$_diff[$precision].'前';
        }
        $diff = abs($this->totime($time) - $this->timestamp);
        static $chunks = array(array(31536000, '年'), array(2592000, '个月'), array(604800, '周'), array(86400, '天'), array(3600, '小时'), array(60, '分钟'), array(1, '秒'));
        $count =0;
        $since = '';
        for($i = 0; $i < count($chunks); $i++)
        {
            if($diff>=$chunks[$i][0])
            {
                $num   =  floor($diff/$chunks[$i][0]);
                $since .= sprintf('%d'.$chunks[$i][1],$num);
                $diff =  (int)($diff-$chunks[$i][0]*$num);
                $count++;
                if(!$precision || $count>=$precision) 
                {
                    break;
                }
            }
        }
        return $since.'前';
    }

    public function firstday_of_week() 
    {
    	$wday = $this->wday === 0 ? 6 : $this->wday-1;
    	if ($wday) $this->add(-$wday, 'd');
        return (new date(mktime(0, 0, 0, $this->month, $this->day, $this->year)));
    }
    
    public function firstday_of_month() 
    {
        return (new date(mktime(0, 0, 0, $this->month, 1, $this->year)));
    }

    public function firstday_of_year()
    {
        return (new date(mktime(0, 0, 0, 1, 1, $this->year)));
    }

    public function lastday_of_month()
    {
        return (new date(mktime(0, 0, 0, $this->month + 1, 0, $this->year)));
    }

    public function lastday_of_year()
    {
        return (new date(mktime(0, 0, 0, 1, 0, $this->year + 1)));
    }

    public function maxday_of_month()
    {
        return date('d', mktime(0, 0, 0, $this->month + 1, 0, $this->year));
    }

    public function add($number = 0, $interval = 'd')
    {
        $hours =  $this->hour;
        $minutes =  $this->minute;
        $seconds =  $this->second;
        $month =  $this->month;
        $day =  $this->day;
        $year =  $this->year;
        switch ($interval)
        {
        	case 'y':
                $year += $number;
                break;
        	case 'm':
                $month += $number;
                break;
        	case 'd':
                $day += $number;
                break;
        	case 'h':
                $hours += $number;
                break;
        	case 'i':
                $minutes += $number;
                break;
        	case 's':
                $seconds += $number;
                break;
            case 'w':
                $day += ($number*7);
                break;
        }
        return (new date(mktime($hours, $minutes, $seconds, $month, $day, $year)));
    }

    public function __get($name)
    {
    	if ($name == 'date') return $this->year.'-'.$this->month.'-'.$this->day;
    	elseif (property_exists($this, $name)) return $this->$name;
    	else return null;
    }
    
    public function __tostring()
    {
        return $this->format();
    }
}
?>