<?php
class strreplace
{
	var $db;
	var $lang;
	var $mod;
	var $file;

    function __construct()
	{
		global $db,$LANG,$mod,$file;
		$this->db = &$db;
		$this->lang = $LANG;
		$this->mod = $mod;
		$this->file = $file;
	}

	function strreplace()
	{
		$this->__construct();
	}

	function replaceall($fromtable,$fromfield1,$condition,$type,$search,$replace,$addstr)
	{
		if(empty($fromtable))
		{
			showmessage($this->lang['the_datatable_to_replace_cannot_null']);
		}
		if(empty($fromfield1))
		{
			showmessage($this->lang['the_field_to_replace_cannot_null']);
		}
		$result = $this->db->query("SHOW COLUMNS FROM `$fromtable`");
		while($r = $this->db->fetch_array($result))
		{
			if($r['Key'] == 'PRI')
			{
				$priid = $r['Field'];
				break;
			}
		}
		$this->db->free_result($result);
		if(!$priid) showmessage($this->lang['no_primary_key_int_this_table']);
		$condition = $condition ? 'where '.stripslashes($condition) : '';

		if($type==1) //replace
		{

			if(empty($search))
			{
				showmessage($this->lang['the_content_to_replace_cannot_null']);
			}
            $sql = "UPDATE `$fromtable` SET `$fromfield1` = REPLACE($fromfield1,'$search','$replace')  $condition";
			$result = $this->db->query($sql);
			$this->db->free_result($result);
			showmessage($this->lang['replace_success']);
		}
		elseif($type==2) //ubb
		{
			if(empty($condition))
			{
				showmessage('替换条件不能为空');
			}
			require DC_ROOT.'include/ubb.func.php';
			$result = $this->db->query("SELECT `$fromfield1`,`$priid` FROM `$fromtable` $condition ");
			while($r = $this->db->fetch_array($result))
			{
				$r[$fromfield1] = ubb($r[$fromfield1]);
				$r[$fromfield1] = addslashes($r[$fromfield1]);
				$this->db->query("UPDATE `$fromtable` SET `$fromfield1` = '".$r[$fromfield1]."' WHERE $priid='".$r[$priid]."'");
			}
			$this->db->free_result($result);
			showmessage($this->lang['ubb_replace_success']);
		}
		elseif($type==3) // add on front
		{
			if(empty($addstr))
			{
				showmessage($this->lang['prefix_of_content_not_null']);
			}
			$result = $this->db->query("SELECT $fromfield1,$priid FROM $fromtable $condition ");
			while($r = $this->db->fetch_array($result))
			{
				$r[$fromfield1] = $addstr.$r[$fromfield1];
				$r[$fromfield1] = addslashes($r[$fromfield1]);
				$this->db->query("UPDATE `$fromtable` SET `$fromfield1` = '".$r[$fromfield1]."' WHERE `$priid` ='".$r[$priid]."'");
			}
			$this->db->free_result($result);
			showmessage($this->lang['replace_success']);
		}
		elseif($type==4) // add on front
		{
			if(empty($addstr))
			{
				showmessage($this->lang['extention_of_content_not_null']);
			}
			$result = $this->db->query("SELECT `$fromfield1`,`$priid` FROM `$fromtable` $condition ");
			while($r = $this->db->fetch_array($result))
			{
				$r[$fromfield1] = $r[$fromfield1].$addstr;
				$r[$fromfield1] = addslashes($r[$fromfield1]);
				$this->db->query("UPDATE `$fromtable` SET `$fromfield1` ='".$r[$fromfield1]."' WHERE $priid='".$r[$priid]."'");
			}
			$this->db->free_result($result);
			showmessage($this->lang['replace_success']);
		}
	}
}