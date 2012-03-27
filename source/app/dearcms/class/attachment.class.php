<?php 
class attachment
{
	var $db;
	var $table;
	var $table_shop;
	var $contentid;
	var $module;
	var $catcode;
	var $attachments;
	var $field;
	var $imageexts = array('gif', 'jpg', 'jpeg', 'png');
	var $uploadedfiles = array();
	var $downloadedfiles = array();
	var $error;

	function __construct($module = 'dearcms', $catcode = 0)
	{
		global $db;
		$this->db = &$db;
		$this->table = DB_PRE.'attachment';
		$this->table_shop= DB_PRE.'shop';
		$this->module = $module;
		$this->catcode = intval($catcode);
	}

	function attachment()
	{
		$this->__construct();
	}
    
	function get($aid, $fields = '*')
	{
		$aid = intval($aid);
		return $this->db->get_one("SELECT $fields FROM `$this->table` WHERE `aid`=$aid");
	}

	function upload($field, $alowexts = 'jpg|jpeg|gif|png|doc|docx|xls|ppt|pdf|txt|rar|zip', $maxsize = 0, $overwrite = 0)
	{
		global $_groupid;
		if((!UPLOAD_FRONT && $_groupid != 1) || !isset($_FILES[$field])) return false;
		$this->field = $field;
		$this->savepath = UPLOAD_ROOT.date('Y/md/');
		$this->alowexts = $alowexts;
		$this->maxsize = $maxsize;
		$this->overwrite = $overwrite;
		$uploadfiles = array();
		$description = isset($GLOBALS[$field.'_description']) ? $GLOBALS[$field.'_description'] : array();

		if(is_array($_FILES[$field]['error']))
		{
			$this->uploads = count($_FILES[$field]['error']);
			foreach($_FILES[$field]['error'] as $key => $error)
			{
				if($error === UPLOAD_ERR_NO_FILE) continue;
				if($error !== UPLOAD_ERR_OK)
				{
					$this->error = $error;
					return false;
				}
				$uploadfiles[$key] = array('tmp_name' => $_FILES[$field]['tmp_name'][$key], 'name' => $_FILES[$field]['name'][$key], 'type' => $_FILES[$field]['type'][$key], 'size' => $_FILES[$field]['size'][$key], 'error' => $_FILES[$field]['error'][$key], 'description'=>$description[$key]);
			}
		}
		else
		{
			$this->uploads = 1;
			if(!$description) $description = '';
			$uploadfiles[0] = array('tmp_name' => $_FILES[$field]['tmp_name'], 'name' => $_FILES[$field]['name'], 'type' => $_FILES[$field]['type'], 'size' => $_FILES[$field]['size'], 'error' => $_FILES[$field]['error'], 'description'=>$description);
		}
		if(!dir_create($this->savepath))
		{
			$this->error = '8';
			return false;
		}
		if(!is_dir($this->savepath))
		{
			$this->error = '8';
			return false;
		}
		@chmod($this->savepath, 0777);
		if(!is_writeable($this->savepath))
		{
			$this->error = '9';
			return false;
		}


		$aids = array();
		foreach($uploadfiles as $k=>$file)
		{
			$fileext = fileext($file['name']);
			if(!preg_match("/^(".$this->alowexts.")$/", $fileext))
			{
				$this->error = '10';
				return false;
			}
			if($this->maxsize && $file['size'] > $this->maxsize)
			{
				$this->error = '11';
				return false;
			}
			if(!$this->isuploadedfile($file['tmp_name']))
			{
				$this->error = '12';
				return false;
			}
			$savefile = $this->savepath.$this->getname($fileext);
			$savefile = preg_replace("/(php|phtml|php3|php4|jsp|exe|dll|asp|cer|asa|shtml|shtm|aspx|asax|cgi|fcgi|pl)(\.|$)/i", "_\\1\\2", $savefile);
			$filepath = preg_replace("|^".UPLOAD_ROOT."|", "", $savefile);

			if(!$this->overwrite && file_exists($savefile)) continue;
			$upload_func = UPLOAD_FUNC;
			if(@$upload_func($file['tmp_name'], $savefile))
			{
				$this->uploadeds++;
				@chmod($savefile, 0644);
				@unlink($file['tmp_name']);

                            //$filename = iconv("utf-8","gbk",$file['name']);//兼容编码
                            $filename = trim($filename) ? $filename : $file['name'];
				$uploadedfile = array('filename'=>$filename, 'filepath'=>$filepath, 'filetype'=>$file['type'], 'filesize'=>$file['size'], 'fileext'=>$fileext, 'description'=>$file['description']);
				//$uploadedfile = array('filename'=>$file['name'], 'filepath'=>$filepath, 'filetype'=>$file['type'], 'filesize'=>$file['size'], 'fileext'=>$fileext, 'description'=>$file['description']);

				$aids[] = $this->add($uploadedfile);
			}
		}
		return $aids;
	}

	function update_intr($contentid, $value = '', $length = 200)
	{
		$length = min(intval($length), 255);
		$value = trim($value);
		if($value)
		{
			$des = $this->db->get_one("SELECT `catcode`,`description` FROM ".DB_PRE."content WHERE `contentid`='$contentid'");
			if(trim($des['description']) && $des['catcode']!=13) return TRUE; //让打折信息的摘要随内容更新
			if(strpos($value, '<p>')!==false)
			{
				$sen_occ = strpos($value, '</p>');
				$value = substr($value, 0, $sen_occ+3);
			}
			elseif(strpos($value, '<br'))
			{
				$sen_occ = strpos($value, '<br');
				$value = substr($value, 0, $sen_occ);
			}
			$description = str_cut(str_replace("\n", '', strip_tags($value)), $length, '');
			$this->db->query("UPDATE ".DB_PRE."content SET `description`='$description' WHERE `contentid`='$contentid'");
		}
		return TRUE;
	}

	function update_thumb($contentid, $aid = 1)
	{
		$aid = max(intval($aid), 1);
		$id = $this->db->get_one("SELECT `thumb` FROM ".DB_PRE."content WHERE `contentid`=$contentid");
		if($id['thumb']) return true;
		$aid--; 
		$info = $this->db->get_one("SELECT `filepath` FROM `$this->table` WHERE `contentid`='$contentid' ORDER BY `aid` ASC LIMIT $aid, 1");
		if($info['filepath'])
		{
			if(strpos($info['filepath'], '://') === false) $path = UPLOAD_URL.$info['filepath'];
			$this->db->query("UPDATE ".DB_PRE."content SET `thumb`='$path' WHERE `contentid`='$contentid'");
		}
		return true;
	}

	function download($field, $value, $ext = 'gif|jpg|jpeg|bmp|png', $absurl = '', $basehref = '')
	{
		global $contentid;

		$this->field = $field;
		$dir = date('Y/md/', TIME);
		$uploadpath = DC_PATH.UPLOAD_URL.$dir;
		$uploaddir = UPLOAD_ROOT.$dir;
		dir_create($uploaddir);
		$string = stripslashes($value);
		if(!preg_match_all("/(href|src)=([\"|']?)([^ \"'>]+\.($ext))\\2/i", $string, $matches)) return $value;
		$remotefileurls = array();
		foreach($matches[3] as $matche)
		{
			if(strpos($matche, '://') === false) continue;
			$remotefileurls[$matche] = $this->fillurl($matche, $absurl, $basehref);
		}
		unset($matches, $string);
		$attachments = get_cookie('attachments');
		$remotefileurls = array_unique($remotefileurls);
		$oldpath = $newpath = array();
		$attachments = array_map('basename', $attachments);
		foreach($remotefileurls as $k=>$file)
		{
			if(strpos($file, '://') === false) continue;
			$filename = fileext($file);
			$file_name = basename($file);
			if($contentid)
			{
				$r = $this->db->get_one("SELECT `aid` FROM `".DB_PRE."attachment` WHERE `contentid`=$contentid AND `filename`='$file_name'");
				if($r['aid']) continue;
			}
			if(in_array($file_name, $attachments))
			{
				$aid = array_search($file_name, $attachments);
				$this->attachments[$this->field][$aid] = $file;
				continue;
			}
			$filename = $this->getname($filename);
			$newfile = $uploaddir.$filename;
			$upload_func = UPLOAD_FUNC;
			if(@$upload_func($file, $newfile))
			{
				$oldpath[] = $k;
				$newpath[] = $uploadpath.$filename;
				@chmod($newfile, 0777);
				$fileext = fileext($filename);
				$filetype = '';
				$image_type = 'IMAGETYPE_'.strtoupper($fileext);
				if(defined($image_type) && function_exists('image_type_to_mime_type'))
				{
					$filetype = image_type_to_mime_type(constant($image_type));
				}
				$filepath = $dir.$filename;
				$downloadedfile = array('filename'=>$filename, 'filepath'=>$filepath, 'filetype'=>$filetype, 'filesize'=>filesize($newfile), 'fileext'=>$fileext);
				$aid = $this->add($downloadedfile);
				$this->downloadedfiles[$aid] = $filepath;
			}
		}
		return str_replace($oldpath, $newpath, $value);
	}

	function listinfo($where, $fields = '*', $order = 'listorder,aid', $page = 1, $pagesize = 20)
	{
            global $page;
		if($where) $where = " WHERE $where";
		if($order) $order = " ORDER BY $order";
		$limit = '';
		if($page !== 0)
		{
			$page = max(intval($page), 1);
			$offset = $pagesize*($page-1);
			$limit = " LIMIT $offset, $pagesize";
			$r = $this->db->get_one("SELECT count(*) as number FROM $this->table $where");
			$number = $r['number'];
			$this->pages = pages($number, $page, $pagesize);
		}

		$i = 1;
		$array = array();
		$result = $this->db->query("SELECT $fields FROM `$this->table` $where $order $limit");
		while($r = $this->db->fetch_array($result))
		{
			$r['filepath'] = UPLOAD_URL.$r['filepath'];
			$r['thumb'] = $this->get_thumb($r['filepath']);
			$array[$i] = $r;
			$i++;
		}
		$this->number = $this->db->num_rows($result);
		$this->db->free_result($result);
		return $array;
	}

	function add($uploadedfile)
	{
		global $_userid;
		$uploadedfile['field'] = $this->field;
		$uploadedfile['module'] = $this->module;
		$uploadedfile['catcode'] = $this->catcode;
		$uploadedfile['userid'] = $_userid;
		$uploadedfile['uploadtime'] = TIME;
		$uploadedfile['uploadip'] = IP;
		$uploadedfile['isimage'] = in_array($uploadedfile['fileext'], $this->imageexts) ? 1 : 0;
		$uploadedfile = new_addslashes($uploadedfile);
		$this->db->insert($this->table, $uploadedfile);
		$aid = $this->db->insert_id();
		$uploadedfile['aid'] = $aid;
		$this->uploadedfiles[] = $uploadedfile;
		$this->attachments[$this->field][$aid] = $uploadedfile['filepath'];
		$attachments = get_cookie('attachments');
		$attachments[$aid] = $uploadedfile['filepath'];
		set_cookie('attachments', $attachments);
		return $aid;
	}

	function delete($where)
	{
		$result = $this->db->query("SELECT `filepath`,`isthumb` FROM `$this->table` WHERE $where ORDER BY `aid`");
		while($r = $this->db->fetch_array($result))
		{
			$image = UPLOAD_ROOT.$r['filepath'];
			@unlink($image);
			$thumbs = glob(dirname($image).'/*'.basename($image));
			if($thumbs) foreach($thumbs as $thumb) @unlink($thumb);
			if($r['isthumb'])
			{
				$thumb = $this->get_thumb($image);
				@unlink($thumb);
			}
		}
		$this->db->free_result($result);
		return $this->db->query("DELETE FROM `$this->table` WHERE $where");
	}

	function listorder($aid, $listorder)
	{
		$aid = intval($aid);
		$listorder = min(intval($listorder), 255);
		return $this->db->query("UPDATE `$this->table` SET `listorder`=$listorder WHERE `aid`=$aid");
	}

	function description($aid, $description)
	{
		$aid = intval($aid);
		return $this->db->query("UPDATE `$this->table` SET `description`='$description' WHERE `aid`=$aid");
	}

	function get_thumb($image)
	{
		return str_replace('.', '_thumb.', $image);
	}

	function set_thumb($aid)
	{
		$aid = intval($aid);
		return $this->db->query("UPDATE `$this->table` SET `isthumb`=1 WHERE `aid`=$aid");
	}


	function update($contentid, $field, $html = '')
	{
		if(!isset($this->attachments[$field]) && $html == '') return 0;
		$contentid = intval($contentid);
		$aids = '';
		$attachments = get_cookie('attachments');
		if($html && !empty($attachments) && empty($_SESSION['downfiles']) && empty($_SESSION['field_images']) && empty($_SESSION['field_image']))
		{
			$aids_del = array();
			foreach($attachments as $aid => $url)
			{
				if(!isset($this->downloadedfiles[$aid]) && strpos($html, $url) === false)
				{
					$aids_del[] = $aid;
				}
				else
				{
					$aids[] = $aid;
				}
			}
		}
		else
		{
			if(is_array($this->attachments[$field])) $aids = array_keys($this->attachments[$field]);
		}
		$aids = implodeids($aids);
		if($aids) $this->db->query("UPDATE `$this->table` SET `catcode`='$this->catcode',`contentid`=$contentid,`field`='$field' WHERE `aid` IN($aids)");
		if(is_array($attachments) && !empty($attachments))
		{
			foreach($attachments as $k=>$v)
			{
				$attachments[$k] = '';
			}
		}
		set_cookie('attachments', $attachments);
		unset($attachments,$_SESSION['downfiles'],$_SESSION['field_images']);		
		return $aids ? 1 : 0;
	}

	function getname($fileext)
	{
		return date('Ymdhis').rand(100, 999).'.'.$fileext;
	}

	function size($filesize)
	{
		if($filesize >= 1073741824)
		{
			$filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
		}
		elseif($filesize >= 1048576)
		{
			$filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
		}
		elseif($filesize >= 1024)
		{
			$filesize = round($filesize / 1024 * 100) / 100 . ' KB';
		}
		else
		{
			$filesize = $filesize . ' Bytes';
		}
		return $filesize;
	}

	function isuploadedfile($file)
	{
		return is_uploaded_file($file) || is_uploaded_file(str_replace('\\\\', '\\', $file));
	}

	function fillurl($surl, $absurl, $basehref = '')
	{
		if($basehref != '')
		{
			$preurl = strtolower(substr($surl,0,6));
			if($preurl=='http://' || $preurl=='ftp://' ||$preurl=='mms://' || $preurl=='rtsp://' || $preurl=='thunde' || $preurl=='emule://'|| $preurl=='ed2k://')
			return  $surl;
			else
			return $basehref.'/'.$surl;
		}
		$i = 0;
		$dstr = '';
		$pstr = '';
		$okurl = '';
		$pathStep = 0;
		$surl = trim($surl);
		if($surl=='') return '';
		//判断文档相对于当前的路径
		$urls = @parse_url(SITE_URL);
		$HomeUrl = $urls['host'];
		$BaseUrlPath = $HomeUrl.$urls['path'];
		$BaseUrlPath = preg_replace("/\/([^\/]*)\.(.*)$/",'/',$BaseUrlPath);
		$BaseUrlPath = preg_replace("/\/$/",'',$BaseUrlPath);
		$pos = strpos($surl,'#');
		if($pos>0) $surl = substr($surl,0,$pos);
		if($surl[0]=='/')
		{
			$okurl = 'http://'.$HomeUrl.'/'.$surl;
		}
		elseif($surl[0] == '.')
		{
			if(strlen($surl)<=2) return '';
			elseif($surl[0]=='/')
			{
				$okurl = 'http://'.$BaseUrlPath.'/'.substr($surl,2,strlen($surl)-2);
			}
			else
			{
				$urls = explode('/',$surl);
				foreach($urls as $u)
				{
					if($u=="..") $pathStep++;
					else if($i<count($urls)-1) $dstr .= $urls[$i].'/';
					else $dstr .= $urls[$i];
					$i++;
				}
				$urls = explode('/', $BaseUrlPath);
				if(count($urls) <= $pathStep)
				return '';
				else
				{
					$pstr = 'http://';
					for($i=0;$i<count($urls)-$pathStep;$i++)
					{
						$pstr .= $urls[$i].'/';
					}
					$okurl = $pstr.$dstr;
				}
			}
		}
		else
		{
			$preurl = strtolower(substr($surl,0,6));
			if(strlen($surl)<7)
			$okurl = 'http://'.$BaseUrlPath.'/'.$surl;
			elseif($preurl=="http:/"||$preurl=='ftp://' ||$preurl=='mms://' || $preurl=="rtsp://" || $preurl=='thunde' || $preurl=='emule:'|| $preurl=='ed2k:/')
			$okurl = $surl;
			else
			$okurl = 'http://'.$BaseUrlPath.'/'.$surl;
		}
		$preurl = strtolower(substr($okurl,0,6));
		if($preurl=='ftp://' || $preurl=='mms://' || $preurl=='rtsp://' || $preurl=='thunde' || $preurl=='emule:'|| $preurl=='ed2k:/')
		{
			return $okurl;
		}
		else
		{
			$okurl = eregi_replace("^(http://)",'',$okurl);
			$okurl = eregi_replace("/{1,}",'/',$okurl);
			return 'http://'.$okurl;
		}
	}

	function error()
	{
		$UPLOAD_ERROR = array(
		0 => '文件上传成功',
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
		//13 => '24小时内上传附件个数超出了系统限制',
		);
		return $UPLOAD_ERROR[$this->error];
	}
}
?>