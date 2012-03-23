<?php
/*
    系统常用函数
*/


function GSET($key , $value, $group = null) {
	global $_G;
	$k = explode('.', $group === null ? $key : $group.'.'.$key);
	switch (count($k)) {
		case 1: $_G[$k[0]] = $value; break;
		case 2: $_G[$k[0]][$k[1]] = $value; break;
		case 3: $_G[$k[0]][$k[1]][$k[2]] = $value; break;
		case 4: $_G[$k[0]][$k[1]][$k[2]][$k[3]] = $value; break;
		case 5: $_G[$k[0]][$k[1]][$k[2]][$k[3]][$k[4]] =$value; break;
	}
	return true;
}

function G($key, $group = null) {
	global $_G;
	$k = explode('.', $group === null ? $key : $group.'.'.$key);
	switch (count($k)) {
		case 1: return isset($_G[$k[0]]) ? $_G[$k[0]] : null; break;
		case 2: return isset($_G[$k[0]][$k[1]]) ? $_G[$k[0]][$k[1]] : null; break;
		case 3: return isset($_G[$k[0]][$k[1]][$k[2]]) ? $_G[$k[0]][$k[1]][$k[2]] : null; break;
		case 4: return isset($_G[$k[0]][$k[1]][$k[2]][$k[3]]) ? $_G[$k[0]][$k[1]][$k[2]][$k[3]] : null; break;
		case 5: return isset($_G[$k[0]][$k[1]][$k[2]][$k[3]][$k[4]]) ? $_G[$k[0]][$k[1]][$k[2]][$k[3]][$k[4]] : null; break;
	}
	return null;
}

//获取app文件夹路径
function appath($app=''){
    if(!$app) return DCS.'app/';
    return DCS.'app/'.$app.'/';
}

//返回app的mod文件路径
function modpath($mod='index',$app=ROUTE_APP){
    return appath($app).'mod/'.$mod.'.inc.php';
}

//系统级错误
function system_error($message, $show = true, $save = true, $halt = true) {
    import('class.error',false);
    error::system_error($message, $show, $save, $halt);
}
//获取参数
function gpc($k, $type='G') {
	$type = strtoupper($type);
	switch($type) {
		case 'G': $var = &$_GET; break;
		case 'P': $var = &$_POST; break;
		case 'C': $var = &$_COOKIE; break;
		default:
			if(isset($_GET[$k])) {
				$var = &$_GET;
			} else {
				$var = &$_POST;
			}
			break;
	}
	return isset($var[$k]) ? $var[$k] : NULL;

}

//比特值
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}

//扩展名
function fileext($filename) {
	return addslashes(strtolower(trim(substr(strrchr($filename, '.'), 1, 10))));
}

//检查是否为蜘蛛
function checkrobot($useragent = '') {
	static $kw_spiders = 'Bot|Crawl|Spider|slurp|sohu-search|lycos|robozilla';
	static $kw_browsers = 'MSIE|Netscape|Opera|Konqueror|Mozilla';

	$useragent = empty($useragent) ? $_SERVER['HTTP_USER_AGENT'] : $useragent;

	if(!strexists($useragent, 'http://') && preg_match("/($kw_browsers)/i", $useragent)) {
		return false;
	} elseif(preg_match("/($kw_spiders)/i", $useragent)) {
		return true;
	} else {
		return false;
	}
}

//判断email
function isemail($email) {
	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}

//问题答案加密
function quescrypt($questionid, $answer) {
	return $questionid > 0 && $answer != '' ? substr(md5($answer.md5($questionid)), 16, 8) : '';
}

//随机字符
function random($length, $numeric = 0) {
	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed{mt_rand(0, $max)};
	}
	return $hash;
}

//字符查找
function strexists($string, $find) {
	return !(strpos($string, $find) === FALSE);
}

//毫秒时间
function dmicrotime() {
	return array_sum(explode(' ', microtime()));
}

//过滤非法字符
function filter_word($data = ''){
	global $DC;
	$filter_word = trim($DC['filter']);
	if(!$filter_word || (!$data && !$_GET && !$_POST)) return false;
	$filter_word = array_filter(array_map('trim', explode(" ", $filter_word)));
       if(!$filter_word) return false;
	$pattern = str_replace('\*', '.*', implode('|', array_map('preg_quote', $filter_word)));
	$data = array2string($_REQUEST);
	if($pattern && preg_match("/($pattern)/", $data, $m))
	{
		$pattern_word = $m[0];
		define('ILLEGAL_WORD', $pattern_word);
		unset($m[0]);
		$word = implode(' ', $m);
		$logdata = array(TIME, IP, $word, $pattern_word);
		$logfile = DC_ROOT.'data/filterlog/'.date('Ym', TIME).'.csv';
		$fp = fopen($logfile, 'a');
		fputcsv($fp, $logdata);
		fclose($fp);
		return true;
	}
	return false;
}

//获取当前页面URL，并且去掉指定参数(中间用空格隔开多个参数)，第二个参数是附加到url后面的被去掉的参数值，第三个参数是附加在后面的字符
function get_cururl($delparam,$value,$addparam=''){
    $alldelparam = explode(" ", $delparam);
    $action = SCRIPT_NAME;
    //$action .= URL;
    $action .= '?'.$_SERVER['QUERY_STRING'];    //得到地址栏中？后的内容
    if($delparam && is_array($alldelparam)){
        foreach($alldelparam AS $v){
            $action = preg_replace("/&".$v."\b[^\&]*/","",$action);
            $action = preg_replace("/\b".$v."\b[^\&]*\&*/","",$action);
        }
    }
    //被赋值的参数 排第一个
    if($value) $action .= "&amp;".$alldelparam[0]."=$value";
    return $action.$addparam;
}

function thumb($imgurl, $width = 100, $height = 100 ,$autocut = 1, $smallpic = 'images/nopic_small.gif'){
	global $image;
	if(empty($imgurl)) return $smallpic;
	if(!extension_loaded('gd') || strpos($imgurl, '://')) return $imgurl;
	if(!file_exists(DC_ROOT.$imgurl)) return 'images/nopic.gif';
	list($width_t, $height_t, $type, $attr) = getimagesize(DC_ROOT.$imgurl);
	if($width>=$width_t || $height>=$height_t) return $imgurl;
	$newimgurl = dirname($imgurl).'/thumb_'.$width.'_'.$height.'_'.basename($imgurl);
	if(file_exists(DC_ROOT.$newimgurl)) return $newimgurl;
	if(!is_object($image))
	{
		require_once 'image.class.php';
		$image = new image();
	}
	return $image->thumb(DC_ROOT.$imgurl, DC_ROOT.$newimgurl, $width, $height, '', $autocut) ? $newimgurl : $imgurl;
}

//转义
function daddslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			unset($string[$key]);
			$string[addslashes($key)] = daddslashes($val);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}


/**
* HTML转义字符
* @param $string - 字符串
* @return 返回转义好的字符串
*/
function dhtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dhtmlspecialchars($val);
		}
	} else {
              $string = filter_xss($string);
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1',
		str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}

//去掉slassh
function dstripslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dstripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}

//得到时间戳
function dmktime($date) {
	if(strpos($date, '-')) {
		$time = explode('-', $date);
		return mktime(0, 0, 0, $time[1], $time[2], $time[0]);
	}
	return 0;
}

//连接字符
function dimplode($array) {
	if(!empty($array)) {
		return "'".implode("','", is_array($array) ? $array : array($array))."'";
	} else {
		return 0;
	}
}

//字符长度
function dstrlen($str) {
	if(strtolower(CHARSET) != 'utf-8') {
		return strlen($str);
	}
	$count = 0;
	for($i = 0; $i < strlen($str); $i++){
		$value = ord($str[$i]);
		if($value > 127) {
			$count++;
			if($value >= 192 && $value <= 223) $i++;
			elseif($value >= 224 && $value <= 239) $i = $i + 2;
			elseif($value >= 240 && $value <= 247) $i = $i + 3;
	    	}
    		$count++;
	}
	return $count;
}

//临时调试通用
function debug($var = null) {
	echo '<pre>';
	if($var === null) {
		print_r($GLOBALS);
	} else {
		print_r($var);
	}
	exit();
}

/*
验证加密
parse_str(authcode($code, 'DECODE', UC_KEY), $get);
list($ec_contract, $ec_securitycode, $ec_partner, $ec_creditdirectpay) = explode("\t", authcode($settings['ec_contract'], 'DECODE', $_G['config']['security']['authkey']));
authcode("$member[password]\t$member[uid]", 'ENCODE');
*/
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	$key = md5($key != '' ? $key : getglobal('authkey'));
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

function format_textarea($string){
	return nl2br(str_replace(' ', '&nbsp;', htmlspecialchars($string)));
}

function format_js($string, $isjs = 1){
	$string = addslashes(str_replace(array("\r", "\n"), array('', ''), $string));
	return $isjs ? 'document.write("'.$string.'");' : $string;
}

function stripstr($str){
	return str_replace(array('..', "\n", "\r"), array('', '', ''), $str);
}

function set_cookie($var, $value = '', $time = 0){
	$time = $time > 0 ? $time : ($value == '' ? PHP_TIME - 3600 : 0);
	$s = $_SERVER['SERVER_PORT'] == '443' ? 1 : 0;
	$var = COOKIE_PRE.$var;
	$_COOKIE[$var] = $value;
	if(is_array($value)){
		foreach($value as $k=>$v){
			setcookie($var.'['.$k.']', $v, $time, COOKIE_PATH, COOKIE_DOMAIN, $s);
		}
	}else{
		setcookie($var, $value, $time, COOKIE_PATH, COOKIE_DOMAIN, $s);
	}
}

function get_cookie($var){
	$var = COOKIE_PRE.$var;
	return isset($_COOKIE[$var]) ? $_COOKIE[$var] : false;
}

function is_date($ymd, $sep='-'){
	if(empty($ymd)) return FALSE;
	list($year, $month, $day) = explode($sep, $ymd);
	return checkdate($month, $day, $year);
}

function dstrpos($string, &$arr, $returnvalue = false) {
	if(empty($string)) return false;
	foreach((array)$arr as $v) {
		if(strpos($string, $v) !== false) {
			$return = $returnvalue ? $v : true;
			return $return;
		}
	}
	return false;
}

function implodeids($array, $s = ','){
	if(empty($array)) return '';
	return is_array($array) ? implode($s, $array) : $array;
}

function check_in($id, $ids = '', $s = ','){
	if(!$ids) return false;
	$ids = explode($s, $ids);
	return is_array($id) ? array_intersect($id, $ids) : in_array($id, $ids);
}

//客户端IP
function ip() {
	$ip = $_SERVER['REMOTE_ADDR'];
	if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
		foreach ($matches[0] AS $xip) {
			if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
				$ip = $xip;
				break;
			}
		}
	}
	return $ip;
}


function str_cut($string, $length, $dot = '...'){
	$strlen = strlen($string);
	if($strlen <= $length) return $string;
	$string = str_replace(array('&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array(' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
	$strcut = '';
	if(strtolower(CHARSET) == 'utf-8')
	{
		$n = $tn = $noc = 0;
		while($n < $strlen)
		{
			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t < 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}
			if($noc >= $length) break;
		}
		if($noc > $length) $n -= $tn;
		$strcut = substr($string, 0, $n);
	}
	else
	{
		$dotlen = strlen($dot);
		$maxi = $length - $dotlen - 1;
		for($i = 0; $i < $maxi; $i++)
		{
			$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
		}
	}
	$strcut = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;'), $strcut);
	return $strcut.$dot;
}


function string2array($data){
	if($data == '') return array();
	eval("\$array = $data;");
	return $array;
}

function array2string($data, $isformdata = 1){
	if($data == '') return '';
	if($isformdata) $data = new_stripslashes($data);
	return addslashes(var_export($data, TRUE));
}

function hash_string($str){
	$str = str_pad($str, 10, 0, STR_PAD_LEFT);
	$str = base64_encode($str);
	$str = substr($str,-5,-3).substr($str,0,-2);
	return $str;
}

function xml_to_array($xml){
	$array = (array)(simplexml_load_string($xml));
	foreach ($array as $key=>$item){
		$array[$key]  = $this->struct_to_array((array)$item);
	}
	return $array;
}

/**
* 可以统计中文字符串长度的函数
*/
function abslength($str){
       $count = 0;
        $len = strlen($str);
         for($i=0; $i<$len; $i++,$count++)
             if(ord($str[$i])>=128)
                $i++;
         return $count;
} 
