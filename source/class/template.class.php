<?php
/*
 * 模板处理类
 */
 
/*
* 模板函数 对应模板语法中的 {template 模板名,模板目录绝对路径}
* 调用 include template(模板名,模板目录绝对路径,自定义模板缓存路径);
*/
function template($tplfile, $tpldir,$subdir='') {
	define ( 'IN_TPL', TRUE );
	$template = dc::loadclass('template');
	$template->tpl_refresh_time = TPL_REFRESH_TIME;
	$template->tpl_subdir = $subdir;
	return $template->tpl ( $tplfile, $tpldir );
}
class template {
	/**
	 * 模板缓存存放目录
	 *
	 * @var string
	 */
	var $tpl_subdir;
	/**
	 * 模板刷新时间[Template refresh time]
	 *
	 * @var int
	 */
	var $tpl_refresh_time;
	/**
	 * 返回编译后的模板文件[Return compiled file]
	 *
	 * @return string
	 */
	function tpl($file, $subdir) {
		$tplfile = $subdir . "/" . $file;
		$subdirname = basename ( $subdir );
            
              //模板缓存目录
		$compiledtpldir = TPL_CACHEPATH . $subdirname;

              //允许自定义模板缓存路径
              if($this->tpl_subdir){
		    $compiledtpldir = $this->tpl_subdir;
              }

              //模板缓存文件
		$compiledtplfile = $compiledtpldir . "/" . $file . ".tpl.php"; //构造编译文件[Define compile file]

              //如果目录不存在 创建
              dir_create(dirname($compiledtplfile));
		//模板缓存文件不存在或者模板文件比缓存更新或者创建日期超出刷新时间
		if (TPL_REFRESH && (! file_exists ( $compiledtplfile ) || @filemtime ( $tplfile ) > @filemtime ( $compiledtplfile ) || (time () - @filemtime ( $compiledtplfile ) > $this->tpl_refresh_time))) {
			$this->tpl_compile ( $tplfile, $compiledtplfile ); //编译模板[Compile template]
		}
		clearstatcache ();
		return $compiledtplfile;
	}
	/**
	 * 编译模板文件[Compile template]
	 *
	 * @return boolean
	 */
	function tpl_compile($tplfile, $compiledtplfile) {
		$str = $this->tpl_read ( $tplfile );
		$str = $this->tpl_parse ( $str );
		if ($this->tpl_write ( $compiledtplfile, $str )) {
			return true;
		}
		return false;
	}
	/**
	 * 解析模板文件[Parse template]
	 *
	 * 自定义模板语法说明 $tpl_parse_add[] = 'wptpl'; 
	 *                                 在自身插件用加入wptpl函数用于调用template
	 *                                 然后可以用wptpl('tplname');调用模板文件
	 * 自定义模板处理函数 $tpl_parse_func[] = 'my_tpl_parse'; 
	 *                                 将会调用my_tpl_parse函数解析模板（$str）
	 * @return string
	 */
	function tpl_parse($str) {

            $str = preg_replace ( "/([\n\r]+)\t+/s", "\\1", $str );
            $str = preg_replace ( "/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $str );
            $str = preg_replace ( "/\{template\s+(.+)\}/", "\n<?php include template(\\1); ?>\n", $str );
            $str = preg_replace ( "/[\n\r\t]*\{eval\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('<? \\1 ?>','')", $str );
            $str = preg_replace ( "/[\n\r\t]*\{php\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('<? \\1 ?>','')", $str );
            $str = preg_replace ( "/\{include\s+(.+)\}/", "\n<?php include \\1; ?>\n", $str );
            $str = preg_replace ( "/\{if\s+(.+?)\}/", "<? if(\\1) { ?>", $str );
            $str = preg_replace ( "/\{else\}/", "<? } else { ?>", $str );
            $str = preg_replace ( "/\{elseif\s+(.+?)\}/", "<? } elseif (\\1) { ?>", $str );
            $str = preg_replace ( "/\{\/if\}/", "<? } ?>", $str );
            $str = preg_replace("/\{for\s+(.+?)\}/","<?php for(\\1) { ?>",$str);
            $str = preg_replace("/\{\/for\}/","<?php } ?>",$str);
            $str = preg_replace ( "/\{loop\s+(\S+)\s+(\S+)\}/", "<? \$n=0;if(is_array(\\1)) foreach(\\1 AS \\2) { \$n++;?>", $str );
            $str = preg_replace ( "/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}/", "\n<? \$n=0;if(is_array(\\1)) foreach(\\1 AS \\2 => \\3) { \$n++;?>", $str );
            $str = preg_replace ( "/\{\/loop\}/", "<? } ?>\n", $str );

            //应用自定义模板处理函数
            if(is_array(G('tpl_parse_func'))){
                foreach(G('tpl_parse_func') AS $v){
			if(function_exists($v)) {
				$str = call_user_func($v,$str);
			}
                }
            }
            
            
            $str = preg_replace("/\{get\s+([^}]+)\}/e", "get_parse('\\1')", $str);
            $str = preg_replace("/\{\/get\}/","<?php } unset(\$DATA); ?>",$str);
    
            $str = preg_replace ( "/\{tpl\s+(.+)\}/", "\n<?php include tpl(\\1); ?>\n", $str );
            $str = preg_replace ( "/\{atpl\s+(.+)\}/", "\n<?php include atpl(\\1); ?>\n", $str );
            
            
            //允许app自定义模版数据调用 类似 {ad:list num='2'}
            $str = preg_replace("/\{(\w+):(\w+)\s+([^}]+)\}/ie", "dc_tpltag_cumstom('$1','$2', '$3', '$0')", $str);
            //这里的$DATA 要与dc_tpltag_custom中相对应
            $str = preg_replace("/\{\/(\w+)\}/", "<?php } unset(\$DATA); ?>", $str);
            
            
            
            //以下六行来自discuz
            $var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\-\>)?[a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
            $const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";
            $str = preg_replace("/$var_regexp/es", "stripvtags('<?=\\1?>')", $str);
            $str = preg_replace("/\<\?\=\<\?\=$var_regexp\?\>\?\>/es", "stripvtags('<?=\\1?>')", $str);
            $str = preg_replace("/\{$const_regexp\}/s", "<?=\\1?>", $str);
            $str = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $str);

            $str = preg_replace("/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))\}/","<?php echo \\1;?>",$str);
            $str = preg_replace("/\{\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))\}/","<?php echo \\1;?>",$str);
            $str = preg_replace("/\{(\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/","<?php echo \\1;?>",$str);
            $str = preg_replace("/\{(\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\}/es", "stripvtags('<?php echo \\1;?>','')",$str);
            $str = preg_replace("/\{([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)\}/s", "<?php echo \\1;?>",$str);


            
            $str = "<? if(!defined('IN_DC')) exit('Access Denied'); ?>\n" . $str; //防止直接浏览模板编译文件
            return $str;
	}
	/**
	 * 读取模板源文件[Read resource file]
	 *
	 * @return string
	 */
	function tpl_read($tplfile) {
		if ($fp = @fopen ( $tplfile, "r" ) or system_error( 'Can not open tpl file : '.basename($tplfile) )) {
			$str = fread ( $fp, filesize ( $tplfile ) );
			fclose ( $fp );
			return $str;
		}
		return false;
	}
	/**
	 * 写入模板编译文件[Write compiled file]
	 *
	 * @return boolean
	 */
	function tpl_write($compiledtplfile, $str) {
		if ($fp = @fopen ( $compiledtplfile, "w" ) or die ( 'Can not write tpl file : '.basename($compiledtplfile) )) {
			flock ( $fp, 3 );
			if (@fwrite ( $fp, $str ) or die ( 'Can not write tpl file : '.basename($compiledtplfile) )) {
				fclose ( $fp );
				return true;
			}
			fclose ( $fp );
		}
		return false;
	}

}

if (! function_exists ( 'stripvtags' )) {
	function stripvtags($expr, $statement='') {
		$expr = str_replace ( "\\\"", "\"", preg_replace ( "/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr ) );
		$statement = str_replace ( "\\\"", "\"", $statement );
		return $expr . $statement;
	}
}
if (! function_exists ( 'addquote' )) {
	function addquote($var) {
		return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
	}
}
?>