<?php
/**
* 可对日期进行加减计算
*/
class date
{
	/**
	* 年份，合法的年份是1970至2100年
	* @var int
	*/
	var $year;
	/**
	* 月份，合法的年份是1至12月
	* @var int
	*/
	var $month;
	/**
	* 天，1到31
	* @var int
	*/
	var $day;

	/**
	* 构造函数，初始化日期
	* @param string
	*/
	function date($date = '') 
	{
		$this->set_date($date);
	}

	/**
	* 设置日期
	* @param string
	*/
	function set_date($date = '')
	{
		if(is_date($date))
		{
			list($y, $m, $d) = explode('-', $date);
			$this->set_year($y);
			$this->set_month($m);
			$this->set_day($d);
		}
		else
		{
			$this->year = date('Y');
			$this->month = date('m');
			$this->day = date('d');
		}
	}

	/**
	* 设置年，1970到2100之间
	* @param int
	*/
	function set_year($year)
	{
		$year = ltrim(intval($year), '0');
		$this->year = ($year<=2100 && $year>=1970) ? $year : date('Y');
	}

	/**
	* 设置月，1到12之间
	* @param int
	*/
	function set_month($month) 
	{
		$month = ltrim(intval($month), '0');
		$this->month = ($month < 13 && $month > 0) ? $month : date('m');
	}

	/**
	* 设置天，1到31之间
	* @param int
	*/
	function set_day($day)
	{
		$day = ltrim(intval($day), '0');
		$this->day = ($this->year && $this->month && checkdate($this->month, $day, $this->year)) ? $day : date('d');
	}

	/**
	* 得到当前月份的第一天
	* @return int
	*/
	function get_firstday()
	{
		return 1;
	}

	/**
	* 得到当前月份的最后一天
	* @return int
	*/
	function get_lastday()
	{
		if($this->month==2)
		{
			$lastday = $this->is_leapyear($this->year) ? 29 : 28;
		}
		elseif($this->month==4 || $this->month==6 || $this->month==9 || $this->month==11)
		{
			$lastday = 30;
		}
		else
		{
			$lastday = 31;
		}
		return $lastday;
	}

	/**
	* 判断当前年份是否为闰年
	* @return bool
	*/
	function is_leapyear($year)
	{
		return date('L', $year);
	}

	/**
	* 天增加
	* @param int
	*/
	function dayadd($step = 1)
	{
		$step = intval($step)*86400;
		$time = $this->get_time()+$step;
		$this->year = date('Y', $time);
		$this->month = date('m', $time);
		$this->day = date('d', $time);
	}

	/**
	* 月份增加
	* @param int
	*/
	function monthadd($step = 1)
	{
		$step = intval($step);
		$totalmonth = $this->month + $step;
		$this->month = $totalmonth%12 == 0 ? 12 : $totalmonth%12 ;
		if($totalmonth > 12) $this->year += floor($totalmonth/12);
		if($this->day > $this->get_lastday()) $this->day = $this->get_lastday();
	}

	/**
	* 年份增加
	* @param int
	*/
	function yearadd($step = 1)
	{
		$step = intval($step);
		$this->year += $step;
		if($this->day > $this->get_lastday()) $this->day = $this->get_lastday();
	}

	/**
	* 返回当前年份
	* @return int
	*/
	function get_year()
	{
		return $this->year;
	}

	/**
	* 返回当前月份
	* @return int
	*/
	function get_month()
	{
		return $this->month;
	}

	/**
	* 返回当前天
	* @return int
	*/
	function get_day()
	{
		return $this->day;
	}

	/**
	* 返回当前日期
	* @return string
	*/
	function get_date()
	{
		return $this->year.'-'.$this->month.'-'.$this->day;
	}

	/**
	* 返回当前Unix 时间戳
	* @return int
	*/
	function get_time() 
	{
		return strtotime($this->get_date().' 23:59:59');
	}

	/**
	* 返回当前星期，从0到6
	* @return int
	*/
	function get_week()
	{
		return date('w', $this->get_time());
	}

	/**
	* 计算两个日期之间相差的天数
	* @return int
	*/
	function get_diff($date1, $date2)
	{
		$time1 = strtotime($date1);
		$time2 = strtotime($date2);
		return ceil(($time1-$time2)/86400);
	}
}
?>