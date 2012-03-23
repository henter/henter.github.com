<?php
class cat
{
	var $db;
	var $pages;
	var $number;
	var $table;

    function __construct()
    {
		global $db, $_userid;
		$this->db = &$db;
		$this->table = DB_PRE.'cat';
		$this->userid = $_userid;
              $this->menu = load('menu.class.php');
              $this->u = load('url.class.php');
    }

	function cat()
	{
		$this->__construct();
	}

	function get($catcode, $fields = '*')
	{
		$catcode = intval($catcode);
		return $this->db->get_one("SELECT $fields FROM `$this->table` WHERE `catcode`=$catcode");
	}

	function getbyid($cid, $fields = '*')
	{
		$cid = intval($cid);
		return $this->db->get_one("SELECT $fields FROM `$this->table` WHERE `cid`=$cid");
	}
    
	function getbyname($catname, $fields = '*')
	{
		return $this->db->get_one("SELECT $fields FROM `$this->table` WHERE `catname`='$catname'");
	}
    
    
	function add($data,$parentid)
	{
              if($parentid){ //子地区
                    $data[catcode] = $this->getmax($parentid);
                    if($this->get($data[catcode])) showmessage('系统出错！地区编号已经存在！');
              }else{ //一级地区
                    $data[catcode] = $this->getmax();
              }
		$this->db->insert($this->table, $data);
		$cid = $this->db->insert_id();
              $this->updateurl($data[catcode]);

		return $cid;
	}

	function edit($data,$catcode)
	{
		$this->db->update($this->table, $data,"catcode = $catcode");
		$cid = $this->db->insert_id();
		return $cid;
	}


	function delete($catcode)
	{
		$catcode = intval($catcode);
		return $this->db->query("DELETE FROM `$this->table` WHERE `catcode` LIKE '$catcode%'");
	}


	function listinfo($where = '', $order = '', $page = 1, $pagesize = 50)
	{
		if($where) $where = " WHERE $where";
		if($order) $order = " ORDER BY $order";
		$page = max(intval($page), 1);
              $offset = $pagesize*($page-1);
              $limit = " LIMIT $offset, $pagesize";
		$r = $this->db->get_one("SELECT count(*) as number FROM $this->table $where");
              $number = $r['number'];
              $this->pages = pages($number, $page, $pagesize);
		$array = array();
		$result = $this->db->query("SELECT * FROM $this->table $where $order $limit");
		while($r = $this->db->fetch_array($result))
		{
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
			$this->db->query("UPDATE `$this->table` SET `listorder`=$listorder WHERE `cid`=$id");
		}
		return true;
	}

	function disable($cid, $disabled)
	{
		$cid = intval($cid);
		if($cid < 1) return false;
		return $this->db->query("UPDATE `$this->table` SET `disabled`=$disabled WHERE `cid`=$cid");
	}

	function update($keyid, $data = array())
	{
		$cid = $this->cid($keyid);
		if($data)
		{
			if($cid)
            {
                $this->edit($cid, $data);
            }
			else
			{
				$data['keyid'] = $keyid;
				$this->add($data);
			}
		}
		elseif($cid)
		{
			$this->delete($cid);
		}
		return true;
	}

	function cid($keyid)
	{
		$r = $this->db->get_one("SELECT `cid` FROM `$this->table` WHERE `keyid`='$keyid' LIMIT 1");
		return $r ? $r['cid'] : false;
	}

	function get_child_cid($cid, $cids = array())
	{
		$cid = intval($cid);
		$result = $this->db->query("SELECT `cid`,`isfolder` FROM `$this->table` WHERE `parentid`=$cid");
		while($r = $this->db->fetch_array($result))
		{
                    $cids[] = $r['cid'];
                    if($r['isfolder']) $cids = $this->get_child_cid($r['cid'], $cids);
		}
		$this->db->free_result($result);
		return $cids;
	}

	function get_childs($catcode, $fields = '*')
	{
		$data = $this->db->select("SELECT $fields FROM `$this->table` WHERE `catcode` LIKE '$catcode%' AND `catcode` != $catcode ORDER BY `listorder`, `cid`");

		return $data;
	}


	function get_parent($cid, $menu = array(), $deep = 0)
	{
		$r = $this->db->get_one("SELECT `name`,`url`,`target`,`isfolder`,`cid`,`parentid` FROM `$this->table` WHERE `cid`='$cid'");
		if($r['parentid'] && $deep < 10)
		{
			$menu[] = $r;
			$menu = $this->get_parent($r['parentid'], $menu, ++$deep);
		}
		return $menu;
	}

        function getmax($a){
            $l=strlen($a);
            switch($l){
                case 0 : $newcatcode = 10;$a = 10;$b=99;break;
                case 2 : $newcatcode = $a.'01';$a .= '00';$b=intval($a)+99;break;
                case 4 : $newcatcode = $a.'01';$a .= '00';$b=intval($a)+99;break;
                //default : showmessage('出错，6位长度栏目编号不能作为父栏目！');
                default : showmessage('出错，三级栏目不能作为父栏目！');
            }

            $all = $this->db->select("SELECT `catcode` FROM `".DB_PRE."cat`  WHERE `catcode`>=$a AND `catcode`<=$b ");

            if(!$all){
                return intval($newcatcode);
            }else{
                $_maxcat = $this->db->select("SELECT `catcode` FROM `".DB_PRE."cat` WHERE  `catcode`>=$a AND `catcode`<=$b ORDER BY catcode DESC LIMIT 1");

                if($_maxcat) $maxcatcode = intval($_maxcat[0]['catcode']);
                $maxcatcode++;
                return $maxcatcode;
            }
        }


        function updateurl($catcode){
            global $URLRULE;
            $urlrule = $URLRULE['list'][3];
            eval("\$url = \"$urlrule\";");
            $this->u->updatecaturl($catcode,$url);
            return TRUE;
        }
}
?>