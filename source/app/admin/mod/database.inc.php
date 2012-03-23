<?php
defined('IN_ADMIN') or exit('Access Denied');

@set_time_limit(0);
require_once DC_ROOT.'inc/database.class.php';
$database = new database();
require_once DC_ROOT.'inc/strreplace.class.php';
$strreplace = new strreplace();
require_once DC_ROOT.'inc/sql.func.php';

if(!isset($forward)) $forward = '?file=database&action=export';

$action = $action ? $action : 'export' ;


switch($action)
{
	case 'export':
		if($dosubmit)
		{
                  $database->export($tables,$sqlcompat,$sqlcharset,$sizelimit,$action,$fileid,$random,$tableid,$startfrom,$tabletype);
		}
		else
		{
			$alltables=$database->status();
			include atpl('database_export');
		}
	break;

	case 'import':
		if($dosubmit)
		{
			$database->import($pre);
		}
		else
		{
			$others = array();
			$sqlfiles = glob(DC_ROOT.'data/bakup/*.sql');
			if(is_array($sqlfiles))
			{
				$prepre = '';
				$info = $infos = $other = $others = array();
				foreach($sqlfiles as $id=>$sqlfile)
				{
					if(preg_match("/(dctables_[0-9]{8}_[0-9a-z]{4}_)([0-9]+)\.sql/i",basename($sqlfile),$num))
					{
						$info['filename'] = basename($sqlfile);
						$info['filesize'] = round(filesize($sqlfile)/(1024*1024), 2);
						$info['maketime'] = date('Y-m-d H:i:s', filemtime($sqlfile));
						$info['pre'] = $num[1];
						$info['number'] = $num[2];
						if(!$id) $prebgcolor = '#CFEFFF';
						if($info['pre'] == $prepre)
						{
						 $info['bgcolor'] = $prebgcolor;
						}
						else
						{
						 $info['bgcolor'] = $prebgcolor == '#CFEFFF' ? '#F1F3F5' : '#CFEFFF';
						}
						$prebgcolor = $info['bgcolor'];
						$prepre = $info['pre'];
						$infos[] = $info;
					}
					else
					{
						$other['filename'] = basename($sqlfile);
						$other['filesize'] = round(filesize($sqlfile)/(1024*1024),2);
						$other['maketime'] = date('Y-m-d H:i:s',filemtime($sqlfile));
						$others[] = $other;
					}
				}
			}
			include atpl('database_import');
		}
	break;
	
	case 'repair':
		if($dosubmit)
		{
			if(empty($tables))
			{
				showmessage('请选择要修复优化的表');
			}
			$database->repair($tables,$operation);
		}
		else
		{
			$tables = array();
			$query = $db->query("SHOW TABLES FROM `".DB_NAME."`");
			while($r = $db->fetch_row($query))
			{
				$tables[] = $r[0];
			}
			include atpl('database_repair');
		}
	break;

	case 'executesql':
		if($dosubmit)
		{
			$result=$database->executesql($operation, $sql);
			if($result === true)
            {
				showmessage('操作成功！', $forward);
            }
			elseif($result === false)
            {
				showmessage('操作失败！', $forward);
            }
			else
            {
                if(is_array($result) && !empty($result))
                {
                    $data = array();
                    $data = $result;
                    include atpl('database_executesql');
                }
			}
		}
		else
		{
		  include atpl('database_executesql');
		}
	break;

	case 'uploadsql':
		$database->uploadsql();
	break;

	case 'changecharset':
		$database->changecharset($tocharset,$filenames);
	break;

	case 'delete':
		$database->delete($filenames,'import');
	break;

	case 'down':
		$database->down($filename);
	break;

	case 'replace':
		if($job=='getfields')
		{
			$fields = '';
			if(!$tablename) $message='非法参数！';
			else
			{
				$result = $db->get_fields($tablename);
				foreach($result as $fields)
				{
					echo "<option value=$fields>$fields</option>";
				}
			}
			exit;
		}
		if($dosubmit)
		{
			$strreplace->replaceall($fromtable,$fromfield1,$condition,$type,$search,$replace,$addstr);
		}
		else
		{
			$query = $db->query("SHOW TABLES FROM `".DB_NAME."`");
			$tables ='';
			while($r = $db->fetch_row($query))
			{
				$table = $r[0];
				if(preg_match("/^".$CONFIG['tablepre']."/i", $table))
				{
					$tables.= "<option value='$table'>$table</option>";
				}
			}
			$referer = urlencode('?mod='.$mod.'&file='.$file.'&action='.$action);
			$type = '1';
			include atpl('database_replace');
		}
	break;
	
	case 'dbsolution':
		$db_array = array('content', 'attachment');
		if($dosubmit)
		{
			if($dbsolution)
			{
				foreach ($db_array as $dbname)
				{
					$result = $db->query("SHOW COLUMNS FROM `".DB_PRE.$dbname."`");
					while($r = $db->fetch_array($result))
					{	
						if(preg_match('/^varchar*/', $r['Type']))
						{
							$db->query("ALTER TABLE `".DB_PRE.$dbname."` CHANGE `".$r['Field']."` `".$r['Field']."` ".str_replace('varchar', 'char', $r['Type'])." NOT NULL ");
						}
					}
				}
			}
			else 
			{
				foreach ($db_array as $dbname)
				{
					$result = $db->query("SHOW COLUMNS FROM `".DB_PRE.$dbname."`");					
					while($r = $db->fetch_array($result))
					{	
						if(preg_match('/^char*/', $r['Type']))
						{
							$db->query("ALTER TABLE `".DB_PRE.$dbname."` CHANGE `".$r['Field']."` `".$r['Field']."` ".str_replace('char', 'varchar', $r['Type'])." NOT NULL ");
						}
					}
				}	
			}
			showmessage('转换成功', $forward);
		}
		else 
		{
			include atpl('database_solution');
		}
	break;
}
?>
