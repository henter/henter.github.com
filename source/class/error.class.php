<?php
if(!defined('IN_DC')) {
	exit('Access Denied');
}

class error
{

	function system_error($message, $show = true, $save = true, $halt = true) {
		if(empty($message)) {
			$message = '未知错误';
		}

		list($showtrace, $logtrace) = error::debug_backtrace();

		if($save) {
			$messagesave = '<b>'.$message.'</b><br><b>PHP:</b>'.$logtrace;
			error::write_error_log($messagesave);
		}

		if($show) {
			error::show_error('system', "<li>$message</li>", $showtrace);
		}

		if($halt) {
			exit();
		} else {
			return $message;
		}
	}

	function template_error($message, $tplname) {
		$tplname = str_replace(DISCUZ_ROOT, '', $tplname);
		$message = $message.': '.$tplname;
		error::system_error($message);
	}

	function debug_backtrace() {
		$skipfunc[] = 'error::debug_backtrace';
		$skipfunc[] = 'error::db_error';
		$skipfunc[] = 'error::template_error';
		$skipfunc[] = 'error::system_error';
		$skipfunc[] = 'db_mysql->halt';
		$skipfunc[] = 'db_mysql->query';
		$skipfunc[] = 'DB::_execute';
        
		$skipfunc[] = 'system_error';
        

		$show = $log = '';
		$debug_backtrace = debug_backtrace();

		krsort($debug_backtrace);
		foreach ($debug_backtrace as $k => $error) {
			$file = str_replace(DCR, '', $error['file']);
			$func = isset($error['class']) ? $error['class'] : '';
			$func .= isset($error['type']) ? $error['type'] : '';
			$func .= isset($error['function']) ? $error['function'] : '';

			if(in_array($func, $skipfunc)) {
				continue;
			}
			$error[line] = sprintf('%04d', $error['line']);

			$show .= "<li>[$error[line]]".$file."($func)</li>";
			$log .= !empty($log) ? ' -> ' : '';$file.':'.$error['line'];
			$log .= $file.':'.$error['line'];
		}
		return array($show, $log);
	}

	function db_error($message, $sql) {
		global $_G;

		list($showtrace, $logtrace) = error::debug_backtrace();

		$title = 'db_'.$message;
		$title_msg =  'db_error_message';
		$title_sql = 'db_query_sql';
		$title_backtrace = 'backtrace';
		$title_help = 'db_help_link';

		$dberrno = DB::errno();
		$dberror = str_replace(DB::$pre,  '', DB::error());
		$sql = htmlspecialchars(str_replace(DB::$pre,  '', $sql));

		$msg = '<li>[Type] '.$title.'</li>';
		$msg .= $dberrno ? '<li>['.$dberrno.'] '.$dberror.'</li>' : '';
		$msg .= $sql ? '<li>[Query] '.$sql.'</li>' : '';

		error::show_error('db', $msg, $showtrace);
		unset($msg, $phperror);

		$errormsg = '<b>'.$title.'</b>';
		$errormsg .= "[$dberrno]<br /><b>ERR:</b> $dberror<br />";
		if($sql) {
			$errormsg .= '<b>SQL:</b> '.$sql;
		}
		$errormsg .= "<br />";
		$errormsg .= '<b>PHP:</b> '.$logtrace;

		error::write_error_log($errormsg);
		exit();

	}

	function show_error($type, $errormsg, $phpmsg = '') {
		global $_G;
		$host = $_SERVER['HTTP_HOST'];
		$phpmsg = trim($phpmsg);
		$title = $type == 'db' ? '数据库' : '系统';
		echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>$host - {$title}错误</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$_G['config']['charset']}" />
	<meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
	<style type="text/css">
	<!--
        body{width:800px;font-family: Microsoft YaHei ,Arial; font-size:12px;}
	.error { padding:10px;border:1px #C00 solid;background-color: #FFEBE8; list-style-type:none;color:red;}
	.msg  {padding:10px; border:1px #E6DB55 solid;list-style-type:none;background-color: lightYellow;color:#000;}
	.help {padding:10px; border:1px #9fcf9f solid;list-style-type:none;background-color: #dfffdf;color: #005f00;}
	-->
	</style>
</head>
<body>
<h1><font color='blue'>D</font><font color='red'>e</font><font color='orange'>a</font><font color='blue'>r</font><font color='green'>CMS</font> {$title}错误 </h1>
EOT;

            echo "<ul class='error'> $errormsg</ul>";
            if(!empty($phpmsg)) {
                echo "<ul class='msg'> $phpmsg </ul>";
            }

            //$endmsg = '更多详细 请访问 www.dearcms.com '.$host;
            $endmsg = '更多详细 请访问 <a href="http://www.dearcms.com" target="_blank">www.dearcms.com</a> ';
            echo "<p class='help'>$endmsg</p></body></html>";
            exit();
	}

	function clear($message) {
		return str_replace(array("\t", "\r", "\n"), " ", $message);
	}

	function write_error_log($message) {
		$message = error::clear($message);
		$time = time();
		$file =  DCD.'log/'.date("Ym").'_errorlog.php';
		$hash = md5($message);

		$uid = G('uid');
		$ip = G('clientip');

		$user = '<b>User:</b> uid='.intval($uid).'; IP='.$ip.'; RIP:'.$_SERVER['REMOTE_ADDR'];
		$uri = 'Request: '.htmlspecialchars(error::clear($_SERVER['REQUEST_URI']));
		$message = "<?PHP exit;?>\t{$time}\t$message\t$hash\t$user $uri\n";
		if($fp = @fopen($file, 'rb')) {
			$lastlen = 10000;
			$maxtime = 60 * 10;
			$offset = filesize($file) - $lastlen;
			if($offset > 0) {
				fseek($fp, $offset);
			}
			if($data = fread($fp, $lastlen)) {
				$array = explode("\n", $data);
				if(is_array($array)) foreach($array as $key => $val) {
					$row = explode("\t", $val);
					if($row[0] != '<?PHP exit;?>') continue;
					if($row[3] == $hash && ($row[1] > $time - $maxtime)) {
						return;
					}
				}
			}
		}
		error_log($message, 3, $file);
	}

}