<?php
class database
{
	var $db;
	var $lang;
	var $file;

	function database()
	{
		global $db,$LANG,$file;
		$this->db = &$db;
		$this->lang = $LANG;
		$this->file = $file;
	}

	function export($tables,$sqlcompat,$sqlcharset,$sizelimit,$action,$fileid,$random,$tableid,$startfrom,$tabletype)
	{
		$dumpcharset = $sqlcharset ? $sqlcharset : str_replace('-', '', CHARSET);
		$fileid = isset($fileid) ? $fileid : 1;
		if($fileid==1 && $tables)
		{
			if(!isset($tables) || !is_array($tables)) showmessage('请选择要备份的数据表！');
			$random = mt_rand(1000, 9999);
			cache_write('bakup_tables.php', $tables);
		}
		else
		{
			if(!$tables = cache_read('bakup_tables.php')) showmessage('请选择要备份的数据表！');
		}
		if($this->db->version() > '4.1')
		{
			if($sqlcharset)
			{
				$this->db->query("SET NAMES '".$sqlcharset."';\n\n");
			}
			if($sqlcompat == 'MYSQL40')
			{
				$this->db->query("SET SQL_MODE='MYSQL40'");
			}
			elseif($sqlcompat == 'MYSQL41')
			{
				$this->db->query("SET SQL_MODE=''");
			}
		}
		$tabledump = '';
		$tableid = isset($tableid) ? $tableid - 1 : 0;
		$startfrom = isset($startfrom) ? intval($startfrom) : 0;
		for($i = $tableid; $i < count($tables) && strlen($tabledump) < $sizelimit * 1000; $i++)
		{
			global $startrow;
			$offset = 100;
			if(!$startfrom)
			{
				if($tables[$i]!=DB_PRE.'session')
				{
					$tabledump .= "DROP TABLE IF EXISTS `$tables[$i]`;\n";
				}
				$createtable = $this->db->query("SHOW CREATE TABLE `$tables[$i]` ");
				$create = $this->db->fetch_row($createtable);
				$tabledump .= $create[1].";\n\n";
				if($sqlcompat == 'MYSQL41' && $this->db->version() < '4.1')
				{
					$tabledump = preg_replace("/TYPE\=([a-zA-Z0-9]+)/", "ENGINE=\\1 DEFAULT CHARSET=".$dumpcharset, $tabledump);
				}
				if($this->db->version() > '4.1' && $sqlcharset)
				{
					$tabledump = preg_replace("/(DEFAULT)*\s*CHARSET=[a-zA-Z0-9]+/", "DEFAULT CHARSET=".$sqlcharset, $tabledump);
				}

				if($tables[$i]==DB_PRE.'session')
				{
					$tabledump = str_replace("CREATE TABLE `".DB_PRE."session`", "CREATE TABLE IF NOT EXISTS `".DB_PRE."session`", $tabledump);
				}
			}
			$numrows = $offset;
			while(strlen($tabledump) < $sizelimit * 1000 && $numrows == $offset)
			{
				if($tables[$i]==DB_PRE.'session' || $tables[$i]==DB_PRE.'member_cache') break;
				$rows = $this->db->query("SELECT * FROM `$tables[$i]` LIMIT $startfrom, $offset");
				$numfields = $this->db->num_fields($rows);
				$numrows = $this->db->num_rows($rows);
				while ($row = $this->db->fetch_row($rows))
				{
					$comma = "";
					$tabledump .= "INSERT INTO `$tables[$i]` VALUES(";
					for($j = 0; $j < $numfields; $j++)
					{
						$tabledump .= $comma."'".mysql_escape_string($row[$j])."'";
						$comma = ",";
					}
					$tabledump .= ");\n";
				}
				$this->db->free_result($rows);
				$startfrom += $offset;
			}
			$tabledump .= "\n";
			$startrow = $startfrom;
			$startfrom = 0;
		}

		if(trim($tabledump))
		{
			$tabledump = "# DearCMS bakfile\n# version:Henter 1.0\n# time:".date('Y-m-d H:i:s')."\n# type:DC\n# Henter:http://henter.me\n# --------------------------------------------------------\n\n\n".$tabledump;
			$tableid = $i;
			$filename = $tabletype.'_'.date('Ymd').'_'.$random.'_'.$fileid.'.sql';
			$altid = $fileid;
			$fileid++;
			$bakfile = DC_ROOT.'data/bakup/'.$filename;
			if(!is_writable(DC_ROOT.'data/bakup/')) showmessage('数据无法备份到服务器！请检查 ./data/bakup 目录是否可写。', $forward);
			file_put_contents($bakfile, $tabledump);
			@chmod($bakfile, 0777);
			if(!EXECUTION_SQL) $filename = '分卷：'.$altid.'#';
			showmessage('备份文件'." $filename ".'写入成功', '?file='.$this->file.'&action='.$action.'&sizelimit='.$sizelimit.'&sqlcompat='.$sqlcompat.'&sqlcharset='.$sqlcharset.'&tableid='.$tableid.'&fileid='.$fileid.'&startfrom='.$startrow.'&random='.$random.'&dosubmit=1&tabletype='.$tabletype.'&allow='.$allow);
		}
		else
		{
		   cache_delete('bakup_tables.php');
		   showmessage('数据库备份完毕！','?file=database&action=export');
		}
	}

	function import($filename)
	{
		global $fileid;
		if($filename && fileext($filename)=='sql')
		{
			$filepath = DC_ROOT.'data/bakup/'.$filename;
			if(!file_exists($filepath)) showmessage('对不起，'." $filepath ".'不存在');
			$sql = file_get_contents($filepath);
			sql_execute($sql);
			showmessage("$filename ". '中的数据已经成功导入到数据库！');
		}
		else
		{
			$fileid = $fileid ? $fileid : 1;
			$pre = $filename;
			$filename = $filename.$fileid.'.sql';
			$filepath = DC_ROOT.'data/bakup/'.$filename;
			if(file_exists($filepath))
			{
				$sql = file_get_contents($filepath);
				sql_execute($sql);
				$fileid++;
				showmessage('数据文件'." $filename ".'导入成功！',"?file=".$this->file."&action=import&pre=".$pre."&fileid=".$fileid."&dosubmit=1");
			}
			else
			{
				showmessage('数据库恢复成功！','?file=database&action=import');
			}
		}
	}

	function repair($tables,$operation)
	{
		$tables = is_array($tables) ? implode(',',$tables) : $tables;
		if($tables && in_array($operation,array('repair','optimize')))
		{
			$this->db->query("$operation TABLE $tables");
			showmessage('操作成功！','?file=database&action=repair');
		}
		else
		{
			showmessage('select_rep_table','?file=database&action=repair');
		}
	}

	function executesql($operation,$sql)
	{
        global $db;
		if($operation == 'file')
		{
	            require_once DC_ROOT.'inc/upload.class.php';
			$savepath = 'data/bakup/';
			$upload = new upload('uploadfile',$savepath,'','sql','4096000',1);
			if(!$upload->up())
			{
				showmessage($upload->error());
			}
			$sql = file_get_contents($upload->uploadedfiles[0][saveto]);
			dir_delete($savepath.date('Y'));
                if(trim($sql) != '') sql_execute($sql);
		}
        if(empty($sql))
        {
            return false;
        }
        //sql执行
        $sql = stripslashes($sql);
        $sql = str_replace("\\", "", $sql);
        $sql = str_replace("\r", "", $sql);
        $query_items = split(";[ \t]{0,}\n",$sql);
        foreach ($query_items as $key=>$value)
        {
            if (empty($value))
            {
                unset($query_items[$key]);
            }
        }
        if(count($query_items) > 1)
        {
            foreach ($query_items as $key=>$value)
            {
                if(!$result=$db->query($value, 'SILENT'))
                {
                    return false;
                }
            }
            return true; //退出函数
        }
        else
        {
            if (preg_match("/^(?:UPDATE|DELETE|TRUNCATE|ALTER|DROP|FLUSH|INSERT|REPLACE|SET|CREATE)\\s+/i", $sql))
            {
                $result = $db->query($sql);
                return $result;
            }
            else
            {
                 $result = $db->query($sql);
                 $data=array();
				 while($r=$db->fetch_array($result))
                 {
                    $data[]=$r;
                 }
                 return $data;
            }
        }
	}

	function uploadsql()
	{
		require_once DC_ROOT.'inc/upload.class.php';
		$savepath = 'data/bakup/';
		$upload = new upload('uploadfile',$savepath,'','sql','4096000',1);
		if(!$upload->up())
		{
			showmessage($upload->error());
		}
		$name = basename($savepath.$upload->uploadedfiles[0][filepath]);
		copy($savepath.$upload->uploadedfiles[0][filepath],$savepath.$name);
		dir_delete($savepath.date('Y'));
		showmessage('上传成功');
	}

	function changecharset($tocharset, $filenames)
	{
		if(empty($tocharset)) showmessage('请选择字符集转换类型！');
		$charsets = explode('2',$tocharset);
		$from = $charsets[0];
		$to = $charsets[1];
		if($filenames)
		{
			if(is_array($filenames))
			{
				foreach($filenames as $filename)
				{
					if(fileext($filename)=='sql')
					{
						$str = file_get_contents(DC_ROOT.'data/bakup/'.$filename);
						$str = str_charset($from, $to, $str);
						file_put_contents(DC_ROOT.'data/bakup/'.$to.$filename, $str);
						@chmod(DC_ROOT.'data/bakup/'.$to.$filename, 0777);
					}
				}
			}
			else
			{
				if(fileext($filenames)=='sql')
				{
					$str = file_get_contents(DC_ROOT.'data/bakup/'.$filenames);
					$str = str_charset($from, $to, $str);
					file_put_contents(DC_ROOT.'data/bakup/'.$to.$filenames, $str);
					@chmod(DC_ROOT.'data/bakup/'.$to.$filenames, 0777);
				}
			}
			showmessage('操作成功');
		}
		else
			showmessage('请选择字符集转换类型！');
	}

	function delete($filenames,$action)
	{
		if($filenames)
		{
			if(is_array($filenames))
			{
				foreach($filenames as $filename)
				{
					if(fileext($filename)=='sql')
					{
						@unlink(DC_ROOT.'data/bakup/'.$filename);
					}
				}
				showmessage('操作成功', '?mod='.$this->mod.'&file='.$this->file.'&action='.$action);
			}
			else
			{
				if(fileext($filenames)=='sql')
				{
					@unlink(DC_ROOT.'data/bakup/'.$filenames);
					showmessage('操作成功', '?mod='.$this->mod.'&file='.$this->file.'&action='.$action);
				}
			}
		}
		else
			showmessage('请选择要删除的文件');
	}

	function down($filename)
	{
		$fileext = fileext($filename);
		if($fileext != 'sql')
		{
			showmessage('对不起，只允许下载sql格式的文件！');
		}
		file_down(DC_ROOT.'data/bakup/'.$filename);

	}

	function status()
	{
		$results = $this->db->select("SHOW TABLE STATUS FROM `".DB_NAME."`");
		$dc = array();
		$other = array();
		foreach($results as $table)
		{
			$name = $table['Name'];
			$row = array('name'=>$name,'rows'=>$table['Rows'],'size'=>$table['Data_length']+$row['Index_length']);
			if(strpos($name, DB_PRE) === 0)
				$dc[] = $row;
			else
				$other[] = $row;
		}
		return array('dctables'=>$dc, 'othertables'=>$other);
	}
}
?>
