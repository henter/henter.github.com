<?php
class content
{
	var $db;
	var $table;
	var $model_table;
	var $table_cat;
	var $ishtml = 0;
	var $userid = 0;
	var $userid_sql = '';
	var $pages;
	var $number;
	var $html;
	var $s;
	var $is_update_related = 1;

    function __construct()
    {
		global $db, $MODULE;
		$this->db = &$db;
		$this->table = DB_PRE.'content';
		$this->table_tag = DB_PRE.'tags';
		$this->table_cat = DB_PRE.'cat';
              $this->u = load('url.class.php');
    }

	function content()
	{
		$this->__construct();
	}

	function set_catcode($catcode)
	{
		global $CAT, $MODEL;
		if(!isset($CAT[$catcode])) return false;
		$modelid = $CAT[$catcode]['modelid'];
		if(!isset($MODEL[$modelid])) return false;
		$this->modelid = $modelid;
		$this->model_table = DB_PRE.'c_'.$MODEL[$modelid]['tablename'];
		return true;
	}

	function set_userid($userid)
	{
		$this->userid = intval($userid);
		$this->userid_sql = " AND $this->table.`userid`=$this->userid ";
	}

	function hits($contentid)
	{
            $id = intval($id);
            $this->db->query("UPDATE `$this->table` SET `hits`=`hits`+1 WHERE `contentid`=$contentid");
            return true;
	}

	function get($contentid, $tablecount = 2)
	{
		$contentid = intval($contentid);
		$data = $this->db->get_one("SELECT * FROM `$this->table` WHERE `contentid`=$contentid $this->userid_sql");
		if($data)
		{
			if($tablecount >= 2)
			{
				$this->set_catcode($data['catcode']);
				if(!$this->model_table) return false;
				$data2 = $this->db->get_one("SELECT * FROM `$this->model_table` WHERE `contentid`=$contentid");
				if($tablecount == 2 && is_array($data) && is_array($data2)) $data = array_merge($data, $data2);
			}
		}
		return $data;
	}

	function add($data, $cat_selected = 0, $isimport = 0)
	{
		global $_userid, $_username,$CAT, $MODEL,$DC;
            if(!$this->set_catcode($data['catcode'])) return false;

		require_once CACHE_MODEL_PATH.'content_input.class.php';
            require_once CACHE_MODEL_PATH.'content_update.class.php';
		$content_input = new content_input($this->modelid);
		$inputinfo = $content_input->get($data, $isimport);
		$systeminfo = $inputinfo['system'];
		$modelinfo = $inputinfo['model'];

		if(!$systeminfo['username']) $systeminfo['username'] = $_username;
		if(!$systeminfo['userid']) $systeminfo['userid'] = $_userid;

		if($data['inputtime'] && !is_numeric($data['inputtime']))
		{
			$systeminfo['inputtime'] = strtotime($data['inputtime']);
		}
		elseif(!$data['inputtime'])
		{
			$systeminfo['inputtime'] = TIME;
		}
		else
		{
			$systeminfo['inputtime'] = $data['inputtime'];
		}

		if($data['updatetime'] && !is_numeric($data['updatetime']))
		{
			$systeminfo['updatetime'] = strtotime($data['updatetime']);
		}
		elseif(!$data['updatetime'])
		{
			$systeminfo['updatetime'] = TIME;
		}
		else
		{
			$systeminfo['updatetime'] = $data['updatetime'];
		}


		$this->db->insert($this->table, $systeminfo);
		$contentid = $this->db->insert_id();
            $modelinfo['contentid'] = $contentid;
		$this->db->insert($this->model_table, $modelinfo);

		$content_update = new content_update($this->modelid, $contentid);
		$content_update->update($data);
        
              updatecatitems($data['catcode']);

              $this->updateurl($contentid);
              if($DC['html']){
                    $html = load('html.class.php');
                    $html->show($contentid);
                    $html->cat($data['catcode']);
              }
		return $contentid;
	}

	function edit($contentid, $data)
	{
		global $MODEL,$old_catcode,$DC;
        if(!$this->set_catcode($data['catcode'])) return false;
		if($old_catcode && $old_catcode!=$data['catcode'])
		{
			$html = load('html.class.php');
			$html->delete($contentid, $this->model_table);
		}
		require_once CACHE_MODEL_PATH.'content_input.class.php';
        require_once CACHE_MODEL_PATH.'content_update.class.php';
		if(!$MODEL[$this->modelid]['isrelated']) $this->is_update_related = 0;

		$content_input = new content_input($this->modelid);
		$inputinfo = $content_input->get($data);
		$systeminfo = $inputinfo['system'];
		$modelinfo = $inputinfo['model'];

		if($data['inputtime'])
		{
			$systeminfo['inputtime'] = strtotime($data['inputtime']);
		}
		else
		{
			$systeminfo['inputtime'] = TIME;
		}
		$systeminfo['updatetime'] = TIME;

		$this->db->update($this->table, $systeminfo, "`contentid`=$contentid $this->userid_sql");
		unset($systeminfo['status']);

		if($modelinfo) $this->db->update($this->model_table, $modelinfo, "`contentid`=$contentid");

		$content_update = new content_update($this->modelid, $contentid);
		$content_update->update($data);

              if($DC['html']){
                    $html = load('html.class.php');
                    $html->show($contentid);
                    $html->cat($data['catcode']);
              }
		return true;
	}

	function listinfo($where = '', $order = '`listorder` DESC,`contentid` DESC', $page = 1, $pagesize = 50)
	{
		if($where) $where = " WHERE $where $this->userid_sql";
		if($order) $order = " ORDER BY $order";
		$page = max(intval($page), 1);
        $offset = $pagesize*($page-1);
        $limit = " LIMIT $offset, $pagesize";
        $numbertmp = $this->db->get_one("SELECT count(*) AS `count` FROM `$this->table` $where");
        $number = $numbertmp[count];
        $this->pages = pages($number, $page, $pagesize);
		$array = array();
		$result = $this->db->query("SELECT * FROM `$this->table` $where $order $limit");

		while($r = $this->db->fetch_array($result))
		{
		    $r['inputtime'] = $r['inputtime'];
		    $r['updatetime'] = $r['updatetime'];
			$array[] = $r;
		}
		$this->number = $this->db->num_rows($result);

            $this->db->free_result($result);
		return $array;
	}

	function listorder($info)
	{
		if(!is_array($info)) return false;
		foreach($info as $id=>$listorder)
		{
			$id = intval($id);
			$listorder = intval($listorder);
			$this->db->query("UPDATE `$this->table` SET `listorder`=$listorder WHERE `contentid`=$id $this->userid_sql");
		}
		return true;
	}

	function search($sql, $page = 1, $pagesize = 20, $setting = array())
	{
		$page = max(intval($page), 1);
		$offset = $number*($page-1);
		$sql_count = preg_replace("/^SELECT([^(]+)FROM(.+)(ORDER BY.+)$/i", "SELECT COUNT(*) AS `count` FROM\\2", $sql);
              $counttmp = $this->db->get_one($sql_count);
		$count = $counttmp[count];
		$this->pages = pages($count, $page, $number);
		$data = array();
		$result = $db->query("$sql LIMIT $offset, $number");
		while($r = $db->fetch_array($result))
		{
			$data[] = $r;
		}
		$db->free_result($result);
		return $data;
	}

	function output($data)
	{
		require_once CACHE_MODEL_PATH.'content_output.class.php';
		$output = new content_output();
		return $output->get($data);
	}



	function search_api($contentid)
	{
		global $MODULE,$MODEL,$CAT;
		if(!isset($MODULE['search'])) return false;
		if(!is_object($this->s)) $this->s = load('search.class.php', 'search', 'include');
		$r = $this->get($contentid);
		if(!$r) return false;
		$modelid = $CAT[$r['catcode']]['modelid'];
		if(!$MODEL[$modelid]['enablesearch']) return false;
            $type = $MODEL[$modelid]['tablename'];
		$this->s->set_type($type);
		if($r['searchid'])
		{
			if($r['status'] == 99)
			{
				$fulltext_array = cache_read($modelid.'_fields.inc.php',CACHE_MODEL_PATH);
				foreach($fulltext_array AS $key=>$value)
				{
					if($value['isfulltext']) $fulltextcontent .= $r[$key].' ';
				}
				$this->s->update($r['searchid'], $r['title'], $fulltextcontent, $r['url']);
			}
			else
			{
				$this->s->delete($r['searchid']);
			}
		}
		elseif($r['status']==99)
		{
			$fulltext_array = cache_read($modelid.'_fields.inc.php',CACHE_MODEL_PATH);
			foreach($fulltext_array AS $key=>$value)
			{
				if($value['isfulltext']) $fulltextcontent .= $r[$key].' ';
			}
			$searchid = $this->s->add($r['title'], $fulltextcontent, $r['url']);
			if(!$searchid) return false;
            $this->db->query("UPDATE `$this->table` SET `searchid`=$searchid WHERE `contentid`=$contentid");
		}
		return true;
	}

    function contentid($contentid, $userid = 0, $allow_status = array())
    {
		$where = "`contentid` IN(".implodeids($contentid).")";
		$where .= $allow_status ? " AND `status` IN(".implodeids($allow_status).")" : '';
		$where .=  $this->userid_sql;
		$array = array();
		$result = $this->db->query("SELECT `contentid` FROM `$this->table` WHERE $where");
		while($r = $this->db->fetch_array($result))
		{
			$array[] = $r['contentid'];
		}
        $this->db->free_result($result);
		return is_array($contentid) ? $array : $array[0];
    }

	function delete($contentid)
	{
		global $MODULE,$attachment,$DC;
		if(is_array($contentid))
		{
			array_map(array(&$this, 'delete'), $contentid);
		}
		else
		{
			$contentid = intval($contentid);

			$data = $this->db->get_one("SELECT `catcode` FROM `$this->table` WHERE `contentid`=$contentid $this->userid_sql");
			if($data)
			{

				$this->set_catcode($data['catcode']);
                             if($DC['html']){
                                   $html = load('html.class.php');
                                   $html->delete($contentid,$this->model_table);
                             }

				$this->db->query("DELETE `$this->table`,`$this->model_table` FROM `$this->table`,`$this->model_table` WHERE $this->table.contentid=$this->model_table.contentid AND $this->table.contentid=$contentid $this->userid_sql");
                            //分类信息数量减1
                            //$this->db->query("UPDATE `$this->table_cat` SET `items`=`items`-1 WHERE `catcode`='".$data['catcode']."'");
                            updatecatitems($data['catcode'],'-1');

				if($this->db->affected_rows())
				{
					$this->db->query("DELETE FROM `$this->table_tag` WHERE `contentid`=$contentid");
					if(!is_object($attachment))
					{
						require_once 'attachment.class.php';
						$attachment = new attachment('dearcms', $data['catcode']);
					}
					$attachment->delete("`contentid`=$contentid");
					if(isset($MODULE['digg']))
					{
						//$digg = load('digg.class.php', 'digg', 'include');
						//$digg->delete($contentid);
					}
					if(isset($MODULE['mood']))
					{
						//$this->db->query("DELETE FROM `".DB_PRE."mood_data` WHERE contentid=$contentid");
					}
					if(isset($MODULE['comment']))
					{
						//$this->db->query("DELETE FROM `".DB_PRE."comment` WHERE keyid='phpcms-content-title-$contentid'");
					}
                    
				}
			}
		}
		return true;
	}

	function clear()
	{
		@set_time_limit(600);
		$result = $this->db->query("SELECT `contentid` FROM `$this->table` WHERE `status`=0");
		while($r = $this->db->fetch_array($result))
		{
			$this->delete($r['contentid']);
		}
        $this->db->free_result($result);
		return true;
	}

	function restore($contentid)
	{
		return $this->status($contentid, 99);
	}

	function restoreall()
	{
		@set_time_limit(600);
		$result = $this->db->query("SELECT `contentid` FROM `$this->table` WHERE `status`=0");
		while($r = $this->db->fetch_array($result))
		{
			$this->status($r['contentid'], 99);
		}
        $this->db->free_result($result);
		return true;
	}

	function count($where = '')
	{
		if($where) $where = " WHERE $where $this->userid_sql";
              $counttmp = $this->db->get_one("SELECT count(*) AS `count` FROM `$this->table` $where");
              return $counttmp[count];
	}


	function status($contentid, $status, $is_admin = 0)
	{
		global $MODULE;
		if(!$contentid) return false;
		$status = intval($status);
		$contentids = implodeids($contentid);
		$is_update = $this->db->query("UPDATE `$this->table` SET `status`=$status WHERE `contentid` IN($contentids) $this->userid_sql");

		return $is_update;
	}

	function get_count($contentid)
	{
		$contentid = intval($contentid);
		$tmp = $this->db->get_one("SELECT count(*) AS `count`  FROM `$this->table` WHERE `contentid`=$contentid");
		return $tmp[count];
	}


	function get_contentid($title)
	{
		$info = $this->db->get_one("SELECT `contentid` FROM `$this->table` WHERE `title`='$title'");
		if($info['contentid']) return TRUE;
		else return FALSE;
	}


	function move($id = '', $targetcatcode = 0, $iscatcode = 0)
	{
		if($iscatcode)
		{
			if(!is_array($id)) return false;
			$ids = implode(',',$id);
			$r = $this->db->select("SELECT `contentid` FROM `$this->table` WHERE `catcode` IN ($ids)", 'contentid');
			$contentids = array_keys($r);
			array_map(array($this, 'move'), $contentids, array_fill(0, count($contentids), $targetcatcode));
		}
		else
		{
			if(strpos($id, ',')!==false)
			{
				$contentids = explode(',', $id);
				array_map(array($this, 'move'), $contentids, array_fill(0, count($contentids), $targetcatcode));
			}
			$contentid = intval($id);
			$r = $this->db->get_one("SELECT `catcode`, `islink` FROM `$this->table` WHERE `contentid`=$contentid");
			$this->db->query("UPDATE `$this->table_cat` SET `items`=`items`-1 WHERE `catcode`=$r[catcode]");
			$this->set_catcode($r['catcode']);
			if($this->ishtml && !$r['islink'])
			{
				if(!is_object($html))
				{
					$html = load('html.class.php');
				}
				$html->delete($contentid, $this->model_table);
			}
			$this->db->query("UPDATE `$this->table` SET `catcode`='$targetcatcode' WHERE `contentid`=$contentid");
			$info = $this->url->show($contentid, 0, $targetcatcode);
			$this->db->query("UPDATE `$this->table` SET `url`='$info[1]' WHERE `contentid`=$contentid");
			$this->db->query("UPDATE `$this->table_cat` SET `items`=`items`+1 WHERE `catcode`=$targetcatcode");
		}
		return true;
	}



	function add_typeid($contentid = 0, $typeid = 0)
	{
		$contentid = intval($contentid);
		$typeid = intval($typeid);
		if(!$contentid || !$typeid) return false;
		$this->db->query("UPDATE $this->table SET `typeid`=$typeid WHERE `contentid`=$contentid");
		return true;
	}

	function update_search($catcode, $i)
	{
		$info = $this->db->get_one("SELECT `contentid` FROM `$this->table` WHERE `catcode`='$catcode' AND `status`=99 ORDER BY `contentid` DESC LIMIT $i, 1");
		$this->search_api($info['contentid']);
		return true;
	}


        function updateurl($contentid){
            $url = $this->u->show($contentid);
            $this->u->updateurl($contentid,$url);
            return TRUE;
        }
}
?>