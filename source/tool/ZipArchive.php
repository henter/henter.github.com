<?php
class ZipArchive {
	const OVERWRITE = 1;
	const CREATE = 1;
	const EXCL = 1;
	const CHECKCONS = 1;

	private $handle = null;
	public $entrylist = array();
	
	public function __construct()
	{
		if (!extension_loaded('zlib')) {
			die("The extension 'zlib' couldn't be found.\n".
			"Please make sure your version of PHP was built ".
			"with 'zlib' support.\n");
			return false;
		}
	}
	public function open($filename, $flags=null)
	{
		$this->entrylist = array();
		$this->handle = @fopen($filename, 'rb');
		return $this->handle;
	}
	public function close()
	{
		if (!is_resource($this->handle)) {
			return false;
		}
		return @fclose($this->handle);
	}
	function extractTo($destination, $entries=null)
	{
		if (!is_resource($this->handle)) {
		    return false;
		}
		@rewind($this->handle);
		
		$orig_magic_quotes_status = 0;
		if (function_exists('get_magic_quotes_runtime')) {
		    if (($orig_magic_quotes_status = get_magic_quotes_runtime()))
		    {
		        @set_magic_quotes_runtime(0);
		    }
		}
		if ($entries) {
			$entries = (array) $entries;
		}
		
		if (!($entries_info = $this->readEndCentralDir())) {
		    return false;
		}
		$pos_entry = $entries_info['offset'];
		for ($i=0,$j=0; $i<$entries_info['entries']; $i++)
		{
			if (@fseek($this->handle, $pos_entry) == -1) {
				return false;
			}
			if (!($entry = $this->readFileEntry())) {
				return false;
			}
			$entry['index'] = $i;
			$pos_entry = ftell($this->handle);
			
			if ($entries && !in_array($entry['stored_filename'],$entries)) {
				$go = 0;
				foreach ($entries as $k) {
					$k = rtrim($k, '/').'/';
					if (substr($entry['stored_filename'], 0, strlen($k)) == $k) {
						$go = 1;
						break;
					}
				}
				if (!$go) continue;
			}
			
			if (($entry['compression'] != 8) && ($entry['compression'] != 0)) {
				$entry['status'] = 'unsupported_compression';
			}
			// encryption zip
			if (($entry['flag']&1) ==1) {
				$entry['status'] = 'unsupported_encryption';
			}
			$entry['folder'] = (($entry['external'] & 0x00000010) == 0x00000010);
			$this->entrylist[$j] = $entry;
			$entry = & $this->entrylist[$j++];
			if ($entry['status'] == 'ok') {
				$this->extractFile($entry, $destination);
			}
		}
		
		if ($orig_magic_quotes_status == 1) {
		    @set_magic_quotes_runtime(1);
		}
		return true;
	}
	
	private function checkDir($structure)
	{
		if (is_dir($structure) || $structure=='') {
			return true;
		}
		if (is_file($structure)) {
			return false;
		}
		if (!$this->checkDir(dirname($structure))) {
			return false;
		}
		return @mkdir($structure, 0755);
	}
	/**
	 * extract file to destination
	 *
	 * @param &array   $entry file entry info
	 * @param string   $destination
	 * @return boolean
	 */
	private function extractFile(&$entry, $destination)
	{
		if (@fseek($this->handle, $entry['offset']) == -1) {
			$entry['status'] = 'invalid offset';
			return false;
		}
		$binary_data = fread($this->handle, 30);
		if (strlen($binary_data) != 30) {
			$entry['status'] = 'invalid file entry';
			return false;
		}
		$header = unpack('Vid/vversion/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len', $binary_data);
		if ($header['id'] != 0x04034b50) {
			$entry['status'] = 'invalid file entry';
			return false;
		}
		if (fseek($this->handle, $header['filename_len'] + $header['extra_len'], SEEK_CUR) == -1)
		{
			$entry['status'] = 'invalid file entry';
			return false;
		}
		if ($destination != '') {
			$entry['filename'] = $destination .'/'. $entry['filename'];
		}
		if ($entry['status'] == 'ok') {
			if (file_exists($entry['filename'])) {
				if (is_dir($entry['filename'])) {
			        $entry['status'] = "already_a_directory";
			    }
			    elseif (!is_writeable($entry['filename'])) {
			        $entry['status'] = "write_protected";
			    }
			} else {
				if ($entry['folder'] || (substr($entry['filename'],-1) == '/')) {
					$dir_to_check = $entry['filename'];
				} elseif (!strstr($entry['filename'], '/')) {
					$dir_to_check = '';
				} else {
					$dir_to_check = dirname($entry['filename']);
				}
				if (!$this->checkDir($dir_to_check)) {
					$entry['status'] = "path_creation_fail";
				}
			}
		}
		if ($entry['status'] == 'ok' && ! $entry['folder']) {
			$buffer = @fread($this->handle, $entry['compressed_size']);
			if ($entry['compression'] && ($entry['flag'] & 1) != 1) {
				$buffer = @gzinflate($buffer);
			}
			if (($dest = @fopen($entry['filename'], 'wb')) == false)
			{
				$entry['status'] = "write_error";
				return false;
			}
			if ($buffer === false) {
				$entry['status'] = "error";
				return false;
			}
			@fwrite($dest, $buffer);
			@fclose($dest);
			@touch($entry['filename'], $entry['mtime']);
			@chmod($entry['filename'], 0755);
		}
		if ($entry['status'] == "aborted"){
			$entry['status'] = "skipped";
		}
		return true;
	}
	
	/**
	 * rend central file header info
	 *
	 * @return mixed
	 */
	private function readFileEntry()
	{
		$binary_data = fread($this->handle, 46);
		if (strlen($binary_data) != 46) {
			return false;
		}
		$header = unpack('Vid/vversion/vversion_extracted/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset', $binary_data);
		if ($header['id'] != 0x02014b50) {
			return false;
		}
		if ($header['filename_len'] != 0) {
			$header['filename'] = fread($this->handle, $header['filename_len']);
		} else {
			$header['filename'] = '';
		}
		if ($header['extra_len'] != 0) {
			$header['extra'] = fread($this->handle, $header['extra_len']);
		} else {
			$header['extra'] = '';
		}
		if ($header['comment_len'] != 0) {
			$header['comment'] = fread($this->handle, $header['comment_len']);
		} else {
			$header['comment'] = '';
		}
		if ($header['mdate'] && $header['mtime']) {
			$hour = ($header['mtime'] & 0xF800) >> 11;
			$minute = ($header['mtime'] & 0x07E0) >> 5;
			$seconde = ($header['mtime'] & 0x001F)*2;
			$year = (($header['mdate'] & 0xFE00) >> 9) + 1980;
			$month = ($header['mdate'] & 0x01E0) >> 5;
			$day = $header['mdate'] & 0x001F;
			$header['mtime'] = mktime($hour, $minute, $seconde, $month, $day, $year);
		} else {
			$header['mtime'] = time();
		}
		$header['stored_filename'] = $header['filename'];
		$header['status'] = 'ok';
		if (substr($header['filename'], -1) == '/') {
			$header['external'] = 0x00000010;
		}
		return $header;
	}
	
	/**
	 * read end central dir info
	 *
	 * @return mixed
	 */
	private function readEndCentralDir()
	{
		if (@fseek($this->handle, -1, SEEK_END) == -1)
		{
			return false;
		}
		$size = ftell($this->handle);
		$found = 0;
		if ($size > 26) {
			if (@fseek($this->handle, $size-22) == -1) {
				return false;
			}
			$binary_data = @fread($this->handle, 4);
			$data = @unpack('Vid', $binary_data);
			if ($data['id'] == 0x06054b50) {
				$found = 1;
			}
			$pos = ftell($this->handle);
		}
		if (!$found) {
			$maximum_size = 65557;// 0xFFFF + 22;
			if ($maximum_size > $size) {
				$maximum_size = $size;
			}
			$pos = $size-$maximum_size;
			if (@fseek($this->handle, $pos) == -1) {
				return false;
			}
			$bytes = 0x00000000;
			while ($pos < $size) {
				$byte = @fread($this->handle, 1);
				$bytes = ($bytes << 8) | ord($byte);
				if ($bytes == 0x504b0506) {
					$pos++;
					break;
				}
				$pos++;
			}
			if ($pos == $size) {
		        return false;
			}
		}
		$binary_data = fread($this->handle, 18);
		if (strlen($binary_data) != 18) {
		    return false;
		}
		$data = unpack('vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_size', $binary_data);
		if ($data['comment_size'] != 0) {
			$data['comment'] = fread($this->handle, $data['comment_size']);
		} else {
			$data['comment'] = '';
		}
		return $data;
	}
}