<?php
/*
    文件IO处理
*/
class IO{
    //替换文件路径中的反斜线
    function path($path){
        $path = str_replace('\\', '/', $path);
        return rtrim($path,'/').'/';
    }
    
    //写入文件 或追加写入
    function write($file, $data, $append = false){
        if (!file_exists($file)){
            if (!self::mkdir(dirname($file))) return false;
        }
        $len  = false;
        $mode = $append ? 'ab' : 'wb';
        $fp = @fopen($file, $mode);
        if (!$fp) {
            exit("Can not open file $file !");
        }
        flock($fp, LOCK_EX);
        $len = @fwrite($fp, $data);
        flock($fp, LOCK_UN);
        @fclose($fp);
        return $len;
    }
    
    //读取文件
    function read($file){
        if (!file_exists($file)) return false;
        if (!is_readable($file)) return false;
        if (function_exists('file_get_contents')){
            return file_get_contents($file);
        }else{
            return (($contents = file($file))) ? implode('', $contents) : false; 
        }
    }
    
    //递归创建目录
    function mkdir($dir, $mode = 0777, $makeindex = TRUE){
        if(!is_dir($dir)) {
            self::mkdir(dirname($dir));
            @mkdir($dir, $mode);
            if(!empty($makeindex)) {
                @touch($dir.'/index.html'); @chmod($dir.'/index.html', 0777);
            }
        }
        return true;
    }
    
    //完全删除目录或文件
    function rm($dir){
        $dir = self::path($dir);
        if(!is_dir($dir)) return @unlink($dir);
        $list = glob($dir.'*');
        foreach($list as $v){
            is_dir($v) ? self::rm($v) : @unlink($v);
        }
        return @rmdir($dir);
    }
    
    //修改目录文件的访问和修改时间
    function touch($path, $mtime = TIME, $atime = TIME){
        if(!is_dir($path)) return false;
        $path = self::path($path);
        if(!is_dir($path)) touch($path, $mtime, $atime);
        $files = glob($path.'*');
        foreach($files as $v){
            is_dir($v) ? self::touch($v, $mtime, $atime) : touch($v, $mtime, $atime);
        }
        return true;
    }
    
    //列出指定目录下指定扩展名的文件
    function ls($path = '.', $exts = '', $list = array()){
        $path = self::path($path);
        $files = glob($path.'*');
        foreach($files as $v){
            $fileext = fileext($v);
            if(!$exts || preg_match("/\.($exts)/i", $v)){
                $list[] = $v;
                if(is_dir($v)){
                    $list = self::ls($v, $exts, $list);
                }
            }
        }
        return $list;
    }
    
    //列出目录 不包含上级目录
    function namelist($path='.'){
        if ($handle = opendir($path)) {
            $dirarray = array();
            while (($file = readdir($handle)) !== false) {
                if($file !="." && $file !=".."){
                    $dirarray[] = $file;
                }
            }
            closedir($handle);
        }
        return $dirarray;
    }

    //目录树
    function tree($path='', $list=array()){
        $files = glob($path.'*');
        foreach($files as $v){
            if($v !="." && $v !=".."){
                if(is_dir($v)){
                    $list[] = $v;
                    $list = self::tree($v.'/', $list);
                }
            }
        }
        return $list;
    }
    
    //目录详细信息
    function info($path='',$key=false){
        $path = realpath($path);
        if (!$path) false;
        $result = array(
            "name"		=> basename($path),
            "location"	=> $path,
            "type"		=> is_file($path) ? 1 : (is_dir($path) ? 0 : -1),
            "size"		=> filesize($path),
            "access"	=> fileatime($path),
            "modify"	=> filemtime($path),
            "change"	=> filectime($path),
            "read"		=> is_readable($path),
            "write"		=> is_writable($path)
        );
        clearstatcache();
        return $key ? $result[$key] : $result;
    }
    
}