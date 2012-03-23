<?php
class upload
{
	var $savepath;
	var $alowexts;
	var $maxsize;
	var $overwrite;
	var $files = array();
	var $uploads = 0;
	var $uploadeds = 0;
	var $imageexts = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
	var $uploadedfiles = array();
	var $error;

	function upload($inputname, $savepath = '', $savename = '', $alowexts = 'jpg|jpeg|gif|bmp|png|doc|docx|xls|ppt|pdf|txt|rar|zip', $maxsize = 0, $overwrite = 0)
	{
		if(!isset($_FILES[$inputname]) && !is_array($_FILES)) return false;
		$savepath = str_replace("\\", '/', $savepath);
		$this->set_savepath($savepath);
		$this->savename = $savename;
		$this->alowexts = $alowexts;
		$this->maxsize = $maxsize;
		$this->overwrite = $overwrite;
		$this->uploads = count($_FILES[$inputname]['name']);
		if(1 == $this->uploads)
		{
			$this->uploads = 1;
			$uploadfiles[0] = array('tmp_name' => $_FILES[$inputname]['tmp_name'], 'name' => $_FILES[$inputname]['name'], 'type' => $_FILES[$inputname]['type'], 'size' => $_FILES[$inputname]['size'], 'error' => $_FILES[$inputname]['error']);
		}
		else
		{
			foreach($_FILES[$inputname]['name'] as $key => $error) 
			{
				$uploadfiles[$key] = array('tmp_name' => $_FILES[$inputname]['tmp_name'][$key], 'name' => $_FILES[$inputname]['name'][$key], 'type' => $_FILES[$inputname]['type'][$key], 'size' => $_FILES[$inputname]['size'][$key], 'error' => $_FILES[$inputname]['error'][$key], 'description'=>$description[$key]);
			}
			
		}
		
		if(!is_dir($this->savepath) && !mkdir($this->savepath, 0777))
		{
			$this->error = 8;
			return false;
		}

		@chmod($this->savepath, 0777);
		if(!is_writeable($this->savepath) && ($this->savepath != '/'))
		{
			$this->error = 9;
			return false;
		}
		$this->files = $uploadfiles;
		return $this->files;
	}

	function up()
	{
		if(empty($this->files)) return false;
		foreach($this->files as $k=>$file)
		{
			$fileext = fileext($file['name']);
			
			if(!preg_match("/^(".$this->alowexts.")$/", $fileext))
			{
				$this->error = 10;
				return false;
			}
			if($this->maxsize && $file['size'] > $this->maxsize)
			{
				$this->error = 11;
				return false;
			}
			if(!$this->isuploadedfile($file['tmp_name']))
			{
				$this->error = 12;
				return false;
			}
			if(!dir_create($this->savepath))
			{
				$this->error = 8;
				return false;
			}
			$savename = $this->savename ? $this->savename : $file['name'];
			$savefile = DC_ROOT.$this->savepath.$savename;
			if(!$this->overwrite && file_exists($savefile)) continue;
			if(move_uploaded_file($file['tmp_name'], $savefile) || @copy($file['tmp_name'], $savefile))
			{
				$this->uploadeds++;
				@chmod($savefile, 0644);
				@unlink($file['tmp_name']);
				$this->uploadedfiles[] = array('saveto'=>$savefile, 'filename'=>$file['name'], 'filepath'=>$filepath, 'filetype'=>$file['type'], 'filesize'=>$file['size'], 'fileext'=>$fileext, 'description'=>$file['description']);
			}
		}
		return $this->uploadedfiles;
	}

	/**
     * 设置保存路径
     * @param string 文件保存路径：以 "/" 结尾
     */
    function set_savepath($savepath)
    {
		$savepath = str_replace("\\", "/", $savepath);
	    $savepath = substr($savepath,-1)=="/" ? $savepath : $savepath."/";
        $this->savepath = $savepath;
		return $this->savepath;
    }

	function isuploadedfile($file)
	{
		return is_uploaded_file($file) || is_uploaded_file(str_replace('\\\\', '\\', $file));
	}

	function error()
	{
		$UPLOAD_ERROR = array(0 => '文件上传成功',
							  1 => '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值',
							  2 => '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值',
							  3 => '文件只有部分被上传',
							  4 => '没有文件被上传',
							  5 => '',
							  6 => '找不到临时文件夹。',
							  7 => '文件写入临时文件夹失败',
							  8 => '附件目录创建不成功',
							  9 => '附件目录没有写入权限',
							  10 => '不允许上传该类型文件',
							  11 => '文件超过了管理员限定的大小',
							  12 => '非法上传文件',
							  13 => '发现同名文件',
							 );
		return $UPLOAD_ERROR[$this->error];
	}
}
?>