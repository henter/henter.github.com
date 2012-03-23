<?php
class admin{
	var $db;
	var $table;
	
	function __construct($action='') 
	{	
		global $db;
		$this->action = $action;
		$this->db = &$db;
		$this->table = DB_PRE.'admin';
		$this->table_member = DB_PRE.'member';
	}

	function get($id)
	{
		$id = intval($id);
		if($id < 1) return false;
		$result = $this->db->get_one("SELECT * FROM $this->table WHERE id=$id");
		return $result;
	}
    
    

	function cklogin($username,$password)
	{
            $password = md5($password);
            $result = $this->db->get_one("SELECT * FROM $this->table WHERE `username` = '$username' AND `password`='$password'");
            if($result){
                $logintime = TIME;
                $loginip = IP;
                $this->db->query("UPDATE $this->table SET `logintime`='$logintime',`loginip`='".IP."' WHERE `username`='$username'");
                return $result;
            }else{
                showmessage('用户名或密码错误！');
            }
	}

	//获取列表
	function manage($where="",$order="",$page=1,$pagesize = 20)
	{
		$order = ($order) ? $order : ' id DESC';
		$offset = ($page-1)*$pagesize;
		$offset = intval($offset);
		$pagesize = intval($pagesize);
		$sql = "SELECT * FROM $this->table $where ORDER BY $order LIMIT $offset, $pagesize";
		return $this->db->select($sql);
	}


	function add($info)
	{
		$this->db->insert($this->table, $info);
		$id = $this->db->insert_id();
		return $id;
	}

	function edit($id,$info)
	{
        	$this->db->update($this->table, $info," id=$id");
	}

	function delete($id)
	{
		if(is_array($id))
		{
			array_map(array(&$this, 'delete'), $id);
		}
		else
		{
			$id = intval($id);
			if($id < 1) return false;
			$form = $this->get($id);
			if(!$form) return false;
			$this->db->query("DELETE FROM $this->table WHERE id IN ($id)");
		}
		return true;
	}


	function page()
	{
		global $page,$pagesize,$key;
              $where = ($key) ? " WHERE `key`=$key" : "";
		$r = $this->db->get_one("SELECT COUNT(*) AS num FROM $this->table $where");
		$number=$r['num'];
		return pages($number, $page, $pagesize);
	}


    function checkname($username){
        $sql = "SELECT * FROM $this->table WHERE username = '$username'";
        return $this->db->select($sql);
    }




	function exists($table, $username)
	{
		$r = $this->db->get_one("SELECT `userid` FROM `$table` WHERE `username`='$username'");
		return $r ? $r['userid'] : false;
	}
    
	function check($username)
	{
		$userid = $this->exists($this->table, $username);
            if($userid)
		{
			$this->error('username_is_admin');
			return false;
		}
		$userid = $this->exists($this->table_member, $username);
            if(!$userid)
		{
			$this->error('admin_member_not_exists');
			return false;
		}
		return $userid;
	}
    
    
	function error($error)
	{
		$msg = array(
			         'username_is_admin'=>'该用户已经是管理员',
			         'username_is_not_admin'=>'该用户不是管理员',
			         'admin_member_not_exists'=>'用户名不存在',
			         'admin_add_is_null'=>'用户名不能为空',
			         'admin_edit_is_null'=>'用户名不能为空',
			         'admin_userid_wrong'=>'userid 参数错误',
					 'role_cant_be_null'=>'权限不能为空',
			        );
		$this->errormsg = $msg[$error];
	}

	function errormsg()
	{
		return $this->errormsg;
	}

}
?>