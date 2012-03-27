<?php
require_once 'attachment.class.php';

class pic extends attachment{



	function shoppicsadd($id,$num='1')
	{
		$id = intval($id);
		$this->db->query("UPDATE `$this->table_shop` SET pics=pics+$num WHERE `id`='$id' ");
		return true;
	}

    function getfor($shopid,$contentid){
        global $MODULE,$DC;
        if($shopid){
            $r = $this->db->get_one("SELECT `shopname` FROM `".DB_PRE."shop` WHERE id=$shopid");
            $return = $r ? "<a href='".$DC[siteurl].$MODULE[shop][url]."shop.php?id=$shopid' target='_blank'>$r[shopname]</a>" : '商铺不存在';
        }elseif($contentid){
            $r = $this->db->get_one("SELECT `title` FROM `".DB_PRE."content` WHERE contentid=$contentid");
            $return = $r ? "<a href='".$DC[siteurl].$MODULE[dearcms][url]."show.php?id=$contentid' target='_blank'>$r[title]</a>" : '内容不存在';
        }else{
            $return = '';
        }
        return $return;
    }

    function hits($tag)
    {
        return $this->db->query("UPDATE `$this->table` SET `hits`=`hits`+1,`lasthittime`=".TIME." WHERE `tag`='$tag'");
    }
    
    
	
	function status($aid, $status = 0)
	{
		$status = intval($status);
		if(is_array($aid))
		{
			$aid = implodeids($aid);
			$this->db->query("UPDATE `$this->table` SET `status`='$status' WHERE `aid` IN ($aid)");
		}
		else
		{
			$this->db->query("UPDATE `$this->table` SET `status`='$status' WHERE `aid`='$aid' ");
		}
		return true;
	}
    
    
	function del($id)
	{
		if(is_array($id))
		{
			array_map(array(&$this, 'del'), $id);
		}
		else
		{
			$id = intval($id);
                     $r = $this->db->get_one("SELECT `shopid` FROM `$this->table` WHERE `aid`=$id");
                     $shopid = intval($r[shopid]);
			$this->delete(" `aid`=$id");
                     $this->shoppicsadd($shopid,'-1');
		}
		return true;
	}
}
?>