<?php

Class tarlib 
{
	var $tarname = ''; 
	var $filehand = 0; 

	function checkcompress() 
	{ 
		global $_dofunc_open, $_dofunc_close, $_dofunc_read, $_dofunc_write; 
		if((substr($this->tarname, -7)=='.tar.gz') || (substr($this->tarname, -4)=='.tgz')) { 
			$_dofunc_open = 'gzopen'; 
			$_dofunc_close = 'gzclose'; 
			$_dofunc_read = 'gzread'; 
			$_dofunc_write = 'gzwrite'; 
		} else { 
			$_dofunc_open = 'fopen'; 
			$_dofunc_close = 'fclose'; 
			$_dofunc_read = 'fread'; 
			$_dofunc_write = 'fwrite'; 
		} 
	} 

	function mkdir($dir) { 
		$dirlist = explode('/', $dir); 
		$depth = count($dirlist)-1; 
		$dir = $dirlist[0]; 
		for($i = 0; $i<$depth; $i++) { 
			if(!is_dir($dir)) { 
				if($dir!='.')
					mkdir($dir, 0777); 
			} 
			$dir.= '/'.$dirlist[$i+1]; 
			$last = $off; 
		} 
	} 

	function checksum($binary_data_first, $binary_data_last = '') { 
		if($binary_data_last=='') { 
			$binary_data_last = $binary_data_first; 
		} 
		$checksum = 0; 
		for ($i=0; $i<148; $i++) { 
			$checksum += ord(substr($binary_data_first, $i, 1)); 
		} 
		for ($i=148; $i<156; $i++) { 
			$checksum += ord(' '); 
		} 
		for ($i=156, $j=0; $i<512; $i++, $j++) { 
			$checksum += ord(substr($binary_data_last, $j, 1)); 
		} 
		return $checksum; 
	} 
} 

Class tar extends tarlib { 

	var $filelist = array(); 
	var $filealias = array();
	
	function tar($tarname, $filelist,$filealias = array()) { 
		global $_dofunc_open, $_dofunc_close, $_dofunc_read, $_dofunc_write; 
		
		$this->tarname = $tarname; 
		$this->checkcompress(); 
		$this->filelist = is_array($filelist) ? $filelist : explode(' ', $filelist); 
		$this->filealias = $filealias;
		$this->create(); 
	} 
	
	function create() { 
		global $_dofunc_open, $_dofunc_close, $_dofunc_read, $_dofunc_write; 
		
		$this->filehand = $_dofunc_open($this->tarname, 'wb'); 
		
		$this->parse($this->filelist,$this->filealias); 
		$this->footer(); 
		
		$_dofunc_close($this->filehand); 
	}

	function parse($filelist,$alias = array()) { 
		global $_dofunc_open, $_dofunc_close, $_dofunc_read, $_dofunc_write; 
		
		$files = count($filelist); 
		for($i = 0; $i < $files; $i++) { 
			$filename = $filelist[$i]; 
			if(is_dir($filename)) { 
				$dirh = opendir($filename); 
				readdir($dirh); // '.' 
				readdir($dirh); // '..' 
				while($nextfile = readdir($dirh)) { 
					$temp_filelist[] = ($filename != '.') ? $filename.'/'.$nextfile : $nextfile; 
				} 
				$this->parse($temp_filelist); 
				closedir($dirh); 
				unset($dirh); 
				unset($temp_filelist); 
				unset($nextfile); 
				continue; 
			} 
			$filealias = ($alias[$i])?$alias[$i]:$filename;
			$this->parseFile($filename,$filealias); 
		} 
	} 

	function parseFile($filename,$alias = '') 
	{
		global $_dofunc_open, $_dofunc_close, $_dofunc_read, $_dofunc_write; 
		if (!$alias) $alias = $filename;
		$v_info = stat($filename); 
		$v_uid = sprintf('%6s ', DecOct($v_info[4])); 
		$v_gid = sprintf('%6s ', DecOct($v_info[5])); 
		$v_perms = sprintf('%6s ', DecOct(fileperms($filename))); 
		clearstatcache(); 
		$v_size = filesize($filename); 
		$v_size = sprintf('%11s ', DecOct($v_size)); 
		$v_mtime = sprintf('%11s', DecOct(filemtime($filename))); 
		
		$v_binary_data_first = pack('a100a8a8a8a12A12',$alias, $v_perms, $v_uid, $v_gid, $v_size, $v_mtime); 
		$v_binary_data_last = pack('a1a100a6a2a32a32a8a8a155a12', '', '', '', '', '', '', '', '', '', ''); 
		$_dofunc_write($this->filehand, $v_binary_data_first, 148); 
		
		$v_checksum = $this->checksum($v_binary_data_first, $v_binary_data_last); 
		
		$v_checksum = sprintf('%6s ', DecOct($v_checksum)); 
		$v_binary_data = pack('a8', $v_checksum); 
		$_dofunc_write($this->filehand, $v_binary_data, 8); 
		$_dofunc_write($this->filehand, $v_binary_data_last, 356); 
		
		$fp = fopen($filename, 'rb'); 
		while(($buffer = fread($fp, 512)) <> '') { 
			$binary_buffer = pack('a512', $buffer); 
			$_dofunc_write($this->filehand, $binary_buffer); 
		} 
	} 

	function footer() { 
		global $_dofunc_open, $_dofunc_close, $_dofunc_read, $_dofunc_write; 
		
		$v_binary_data = pack('a512', ''); 
		$_dofunc_write($this->filehand, $v_binary_data); 
	} 
} 
	

Class tarExtract extends tarlib { 

	var $extractDir = './extract'; 
	
	function tarExtract($tarname, $extractDir = './extract') { 
		global $_dofunc_open, $_dofunc_close, $_dofunc_read, $_dofunc_write; 
		
		$this->tarname = $tarname; 
		$this->extractDir = $extractDir; 
		$this->checkcompress(); 
		
		if(!is_dir($extractDir)) { 
			$this->mkdir($extractDir); 
		} 
		$this->extract(); 
	} 
	
	function extract() { 
		global $_dofunc_open, $_dofunc_close, $_dofunc_read, $_dofunc_write; 
		
		$this->filehand = $_dofunc_open($this->tarname, 'rb'); 
		while(($binary_buffer = fread($this->filehand, 512)) <> '') { 
			$file = $this->parseHeader($binary_buffer); 
			if(!$file['name']) continue; 
			$file['name'] = $this->extractDir.'/'.$file['name']; 
			$readtimes = floor($file['size']/512); 
			
			$this->mkdir($file['name']); 
			$fp = fopen($file['name'], 'wb'); 
			for($i = 0; $i<$readtimes; $i++) { 
				fwrite($fp, $_dofunc_read($this->filehand, 512)); 
			} 
			if(($lastsize = $file['size']%512)) { 
				fwrite($fp, $_dofunc_read($this->filehand, 512), $lastsize); 
			} 
			fclose($fp); 
		} 
		$_dofunc_close($this->filehand); 
	} 

	function parseHeader($header) { 
	
		$checksum = $this->checksum($header); 
		$data = unpack('a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1typeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor', $header); 
		
		$file['checksum'] = OctDec(trim($data['checksum'])); 

		$file['name'] = trim($data['filename']); 
		$file['mode'] = OctDec(trim($data['mode'])); 
		$file['uid'] = OctDec(trim($data['uid'])); 
		$file['gid'] = OctDec(trim($data['gid'])); 
		$file['size'] = OctDec(trim($data['size'])); 
		$file['mtime'] = OctDec(trim($data['mtime'])); 
		$file['type'] = $data['typeflag']; 
		
		return $file; 
	} 
}
