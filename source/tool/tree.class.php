<?php
/**
* 通用的树型类，可以生成任何树型结构
*/
class tree {
	/**
	* 生成树型结构所需要的2维数组
	* @var array
	*/
	public $arr = array();

	/**
	* 生成树型结构所需修饰符号，可以换成图片
	* @var array
	*/
	public $icon = array('│','├','└');
	public $nbsp = "&nbsp;";

	/**
	* @access private
	*/
	public $ret = '';

	/**
	* 构造函数，初始化类
	* @param array 2维数组，例如：
	* array(
	*      1 => array('id'=>'1','parentid'=>0,'name'=>'一级栏目一'),
	*      2 => array('id'=>'2','parentid'=>0,'name'=>'一级栏目二'),
	*      3 => array('id'=>'3','parentid'=>1,'name'=>'二级栏目一'),
	*      4 => array('id'=>'4','parentid'=>1,'name'=>'二级栏目二'),
	*      5 => array('id'=>'5','parentid'=>2,'name'=>'二级栏目三'),
	*      6 => array('id'=>'6','parentid'=>3,'name'=>'三级栏目一'),
	*      7 => array('id'=>'7','parentid'=>3,'name'=>'三级栏目二')
	*      )
	*/
	public function init($arr=array()){
       $this->arr = $arr;
	   $this->ret = '';
	   return is_array($arr);
	}

    /**
	* 得到父级数组
	* @param int
	* @return array
	*/
	public function get_parent($myid){
		$newarr = array();
		if(!isset($this->arr[$myid])) return false;
		$pid = $this->arr[$myid]['parentid'];
		$pid = $this->arr[$pid]['parentid'];
		if(is_array($this->arr)){
			foreach($this->arr as $id => $a){
				if($a['parentid'] == $pid) $newarr[$id] = $a;
			}
		}
		return $newarr;
	}

    /**
	* 得到子级数组
	* @param int
	* @return array
	*/
	public function get_child($myid){
		$a = $newarr = array();
		if(is_array($this->arr)){
			foreach($this->arr as $id => $a){
				if($a['parentid'] == $myid) $newarr[$id] = $a;
			}
		}
		return $newarr ? $newarr : false;
	}

    /**
	* 得到当前位置数组
	* @param int
	* @return array
	*/
	public function get_pos($myid,&$newarr){
		$a = array();
		if(!isset($this->arr[$myid])) return false;
        $newarr[] = $this->arr[$myid];
		$pid = $this->arr[$myid]['parentid'];
		if(isset($this->arr[$pid])){
		    $this->get_pos($pid,$newarr);
		}
		if(is_array($newarr)){
			krsort($newarr);
			foreach($newarr as $v){
				$a[$v['id']] = $v;
			}
		}
		return $a;
	}

    /**
	* 得到树型结构
	* @param int ID，表示获得这个ID下的所有子级
	* @param string 生成树型结构的基本代码，例如："<option value=\$id \$selected>\$spacer\$name</option>"
	* @param int 被选中的ID，比如在做树型下拉框的时候需要用到
	* @return string
	*/
	public function get_tree($myid, $str, $sid = 0, $adds = '', $str_group = ''){
		$number=1;
		$child = $this->get_child($myid);
		if(is_array($child)){
		    $total = count($child);
			foreach($child as $id=>$a){
				$j=$k='';
				if($number==$total){
					$j .= $this->icon[2];
				}else{
					$j .= $this->icon[1];
					$k = $adds ? $this->icon[0] : '';
				}
				$spacer = $adds ? $adds.$j : '';
				$selected = $id==$sid ? 'selected' : '';
				@extract($a);
				$parentid == 0 && $str_group ? eval("\$nstr = \"$str_group\";") : eval("\$nstr = \"$str\";");
				$this->ret .= $nstr;
				$nbsp = $this->nbsp;
				$this->get_tree($id, $str, $sid, $adds.$k.$nbsp,$str_group);
				$number++;
			}
		}
		return $this->ret;
	}
    /**
	* 同上一方法类似,但允许多选
	*/
	public function get_tree_multi($myid, $str, $sid = 0, $adds = ''){
		$number=1;
		$child = $this->get_child($myid);
		if(is_array($child)){
		    $total = count($child);
			foreach($child as $id=>$a){
				$j=$k='';
				if($number==$total){
					$j .= $this->icon[2];
				}else{
					$j .= $this->icon[1];
					$k = $adds ? $this->icon[0] : '';
				}
				$spacer = $adds ? $adds.$j : '';
				
				$selected = $this->have($sid,$id) ? 'selected' : '';
				@extract($a);
				eval("\$nstr = \"$str\";");
				$this->ret .= $nstr;
				$this->get_tree_multi($id, $str, $sid, $adds.$k.'&nbsp;');
				$number++;
			}
		}
		return $this->ret;
	}
	 /**
	* @param integer $myid 要查询的ID
	* @param string $str   第一种HTML代码方式
	* @param string $str2  第二种HTML代码方式
	* @param integer $sid  默认选中
	* @param integer $adds 前缀
	*/
	public function get_tree_category($myid, $str, $str2, $sid = 0, $adds = ''){
		$number=1;
		$child = $this->get_child($myid);
		if(is_array($child)){
		    $total = count($child);
			foreach($child as $id=>$a){
				$j=$k='';
				if($number==$total){
					$j .= $this->icon[2];
				}else{
					$j .= $this->icon[1];
					$k = $adds ? $this->icon[0] : '';
				}
				$spacer = $adds ? $adds.$j : '';
				
				$selected = $this->have($sid,$id) ? 'selected' : '';
				@extract($a);
				if (empty($html_disabled)) {
					eval("\$nstr = \"$str\";");
				} else {
					eval("\$nstr = \"$str2\";");
				}
				$this->ret .= $nstr;
				$this->get_tree_category($id, $str, $str2, $sid, $adds.$k.'&nbsp;');
				$number++;
			}
		}
		return $this->ret;
	}
	
	/**
	 * 同上一类方法，jquery treeview 风格，可伸缩样式（需要treeview插件支持）
	 * @param $myid 表示获得这个ID下的所有子级
	 * @param $effected_id 需要生成treeview目录数的id
	 * @param $str 末级样式
	 * @param $style 目录样式 默认 filetree 可增加其他样式如'filetree treeview-famfamfam'
	 * @param $recursion 递归使用 外部调用时为FALSE
	 */
    function get_treeview($myid,$effected_id='example',$str="<span class='file'>\$name</span>", $str2="<span class='folder'>\$name</span>" ,$style='filetree ' ,$recursion=FALSE) {
        $child = $this->get_child($myid);
        if(!defined('EFFECTED_INIT')){
           $effected = ' id="'.$effected_id.'"';
           define('EFFECTED_INIT', 1);
        } else {
           $effected = '';
        }
        if(!$recursion) $this->str .='<ul'.$effected.'  class="'.$style.'">';
        foreach($child as $id=>$a) {
        	@extract($a);
        	$floder_status = isset($folder) ? ' class="'.$folder.'"' : '';
            $this->str .= $recursion ? '<ul><li'.$floder_status.'>' : '<li'.$floder_status.'>';
            $recursion = FALSE;
            if($this->get_child($id)){
            	eval("\$nstr = \"$str2\";");
            	$this->str .= $nstr;
                $this->get_treeview($id,$effected_id,$str,$str2,$style,TRUE);
            } else {
                eval("\$nstr = \"$str\";");
                $this->str .= $nstr;
            }
            $this->str .=$recursion ? '</li></ul>': '</li>';
        }
        if(!$recursion)  $this->str .='</ul>';
        return $this->str;
    }
	private function have($list,$item){
		return(strpos(',,'.$list.',',','.$item.','));
	}
}
?>