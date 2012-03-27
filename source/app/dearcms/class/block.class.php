<?php 
class block
{
	var $db;
	var $table;
	var $table_data;
	var $pages;

    function __construct()
    {
		global $db;
		$this->db = &$db;
		$this->table = DB_PRE.'block';
		$this->table_data = DB_PRE.'block_data';
    }

	function block()
	{
		$this->__construct();
	}

	function get($blockid, $fields = '*')
	{
		$blockid = intval($blockid);
		$r = $this->db->get_one("SELECT $fields FROM `$this->table` WHERE `blockid`=$blockid");
		if(!$r) return false;
		return $r;
	}

	function get_pageid($blockid)
	{
              $r = $this->db->get_one("SELECT * FROM `$this->table` WHERE `blockid`=$blockid");
		return $r ? $r['pageid'] : '';
	}

	function get_data($blockid, $areacode)
	{
		$blockid = intval($blockid);
		$areacode = intval($areacode);
		$r = $this->db->get_one("SELECT `id`,`isarray`,`data` FROM `$this->table_data` WHERE `blockid`=$blockid AND `areacode`=$areacode");
		if(!$r) return false;
		if($r['isarray']) $r['data'] = $r['data'] ? string2array($r['data']) : $r['data'];
		return $r;
	}

	function add($info)
	{
		if($info['name'] == '') return false;
		$this->db->insert($this->table, $info);
		$blockid = $this->db->insert_id();
		$this->set_template($blockid, $info[template]);
		return $blockid;
	}

	function edit($blockid, $info)
	{
		if($info['name'] == '') return false;
		$this->db->update($this->table, $info, " `blockid`='$blockid' ");
              $this->setdatatype($blockid); //更新data表的数据类型
		$this->set_template($blockid, $info[template]);
            return true;
	}
    
	function setdatatype($blockid)
	{
            if($blockid == '') return false;
            $r = $this->get($blockid);
            if($r){
                $sql = "UPDATE `$this->table_data` SET `isarray`=$r[isarray],`rows`=$r[rows] WHERE `blockid`=$blockid";
                return $this->db->query($sql);
            }
            return false;
	}

	function adddata($blockid, $info,$areacode=0)
	{
            $data = $info['data'];
		if(is_array($data))
		{
			$data = $this->strip_data($data);
			if($data) $data = array2string($data);
		}
             $info['blockid'] = $blockid;
             $info['areacode'] = $areacode;
             $info['data'] = $data;
             $this->db->insert($this->table_data, $info);
		$this->set_html($blockid,$areacode);
		return true;
	}


	function editdata($blockid, $info,$areacode=0,$id)
	{
            $data = $info['data'];
		if(is_array($data))
		{
			$data = $this->strip_data($data);
			if($data) $data = array2string($data);
		}
             $info['blockid'] = $blockid;
             $info['areacode'] = $areacode;
             $info['data'] = $data;
             $this->db->update($this->table_data, $info, "`blockid`=$blockid AND `areacode`=$areacode");
		$this->set_html($blockid,$areacode);
		return true;
	}



	function listinfo($where, $page = 1, $pagesize = 20)
	{
		if($where) $where = " WHERE $where";
		$page = max(intval($page), 1);
            $offset = $pagesize*($page-1);
            $limit = " LIMIT $offset, $pagesize";
		$r = $this->db->get_one("SELECT count(*) as `count` FROM `$this->table` $where");
            $number = $r['count'];
            $this->pages = pages($number, $page, $pagesize);
		$array = array();
		$result = $this->db->query("SELECT * FROM `$this->table` $where ORDER BY `blockid` DESC $limit");
		while($r = $this->db->fetch_array($result))
		{
			if($r['isarray'] && $r['data']) $r['data'] = string2array($r['data']);
			$array[] = $r;
		}
            $this->db->free_result($result);
		return $array;
	}
    
	function datalist($where, $page = 1, $pagesize = 20)
	{
		if($where) $where = " WHERE $where";
		$page = max(intval($page), 1);
            $offset = $pagesize*($page-1);
            $limit = " LIMIT $offset, $pagesize";
		$r = $this->db->get_one("SELECT count(*) as `count` FROM `$this->table` $where");
            $number = $r['count'];
            $this->pages = pages($number, $page, $pagesize);
		$array = array();
		$result = $this->db->query("SELECT * FROM `$this->table_data` $where ORDER BY `areacode` ASC $limit");
		while($r = $this->db->fetch_array($result))
		{
			if($r['isarray'] && $r['data']) $r['data'] = string2array($r['data']);
			$array[] = $r;
		}
            $this->db->free_result($result);
		return $array;
	}

	function disable($blockid, $disabled)
	{
		$blockid = intval($blockid);
		$r = $this->get($blockid);
		if(!$r) return false;
		$this->db->query("UPDATE $this->table SET `disabled`=$disabled WHERE `blockid`=$blockid");
		return true;
	}

	function delete($blockid)
	{
		if(is_array($blockid))
		{
			array_map(array(&$this, 'delete'), $blockid);
		}
		else
		{
			$r = $this->get($blockid);
			if(!$r) return false;
			//$this->db->query("DELETE FROM `$this->table` WHERE `blockid`='$blockid'");
			//$this->db->query("DELETE FROM `$this->table_data` WHERE `blockid`='$blockid'");
			$this->rm_html($blockid);
		}
		return true;
	}
    
	function deletedata($blockid,$areacode)
	{
            $r = $this->get($blockid);
            if(!$r) return false;
            $this->db->query("DELETE FROM `$this->table_data` WHERE `blockid`='$blockid' AND `areacode`='$areacode'");
            $this->rm_html($blockid,$areacode);
            return true;
	}

	function clear()
	{
		return $this->db->query("TRUNCATE TABLE `$this->table`");
	}




	function strip_data($data)
	{
		ksort($data);
		$array = array();
		foreach($data as $k=>$v)
		{
			//if($v['title'] && $v['url']) $array[] = $v; //只填写标题就行了
			if($v['title']) $array[] = $v;
		}
		return $array;
	}


	function checkpageid($pageid)
	{
		$r = $this->db->get_one("SELECT * FROM `$this->table` WHERE `pageid`='$pageid'");
		return $r ? false : 1;
	}
    
	function checkareacode($blockid,$areacode=0,$id=0)
	{
              $areacode = $areacode ? $areacode : 0;
              $where = $id ? " AND `id`!=$id " : '';
		$r = $this->db->get_one("SELECT * FROM `$this->table_data` WHERE `blockid`='$blockid' AND `areacode`=$areacode $where");
		return $r ? 1 : 0;
	}
    
	function refresh($blockid = '', $areacode = 0)
	{
		$where = '';
		if($blockid)	$where .= " AND `blockid`='$blockid' ";
		if($areacode) $where .= " AND `areacode`='$areacode' ";

		$array = $this->db->select("SELECT * FROM `$this->table_data` WHERE 1 $where ORDER BY `blockid`");

		foreach($array as $r)
		{
                    $bid = $r[blockid];
			extract($r);
			ob_start();
			if($isarray)
			{
				$data = $data ? string2array($data) : array();
				include template_block($bid);
			}
			else
			{
				echo $data;
			}
			createhtml(DC_ROOT.'data/block/'.$bid.'_'.$areacode.'.html');
		}
		return true;
	}

	function set_html($blockid,$areacode)
	{
		$r = $this->get($blockid);unset($r[data]);
		$rdata = $this->get_data($blockid,$areacode);
		if(!$r) return false;
		extract($r);extract($rdata);

		ob_start();
		if($isarray)
		{
			include template_block($blockid);
		}
		else
		{
			echo $data;
		}
		$result = createhtml(DC_ROOT.'data/block/'.$blockid.'_'.$areacode.'.html');
		return $result;
	}

	function get_html($blockid,$areacode)
	{
		$blockid = intval($blockid);
		return @file_get_contents(DC_ROOT.'data/block/'.$blockid.'_'.$areacode.'.html');
	}

	function rm_html($blockid,$areacode)
	{
            if(isset($areacode)){
                @unlink(DC_ROOT.'data/block/'.$blockid.'_'.$areacode.'.html');
                @unlink(TPL_ROOT.'block/'.$blockid.'.html');
                return true;
            }else{
                @unlink(DC_ROOT.'data/block/'.$blockid.'_'.$areacode.'.html');
                @unlink(TPL_ROOT.'block/'.$blockid.'.html');
                foreach(glob("{data/block/".$blockid."_*.html}",GLOB_BRACE) as $filename)
                {
                       @unlink($filename); //删除所有blockid下的静态文件
                }
                return true;
            }
	}

	function set_html_areacode($pageid, $areacode)
	{
		$data = '';
		$array = $this->db->select("SELECT `blockid` FROM `$this->table` WHERE `pageid`='$pageid' AND `disabled`=0 ORDER BY `blockid`", 'blockid');
		foreach($array as $blockid=>$v)
		{
			$data .= $this->get_html($blockid);
		}
		$file = DC_ROOT.'data/block/'.$pageid.'_'.$areacode.'.html';
		if($data) file_put_contents($file, $data);
		elseif(file_exists($file)) @unlink($file);
		return true;
	}

	function get_html_areacode($pageid, $areacode)
	{
		return @file_put_contents(DC_ROOT.'data/block/'.$pageid.'_'.$areacode.'.html');
	}

	function get_template_path($blockid)
	{
		return TPL_ROOT.'block/'.$blockid.'.html';
	}

	function get_template($blockid)
	{
		$tplfile = $this->get_template_path($blockid);
		return file_exists($tplfile) ? trim(file_get_contents($tplfile)) : '';
	}

	function set_template($blockid, $template)
	{
		$tplfile = $this->get_template_path($blockid);
		return @file_put_contents($tplfile, stripslashes($template));
	}

}
?>