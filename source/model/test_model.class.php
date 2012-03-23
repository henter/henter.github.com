<?php
/**
 * 内容模型数据库操作类
 */
dc::loadclass('model');
class test_model extends model {
	public $table_name = '';
	public $category = '';
	public function __construct() {
		parent::__construct();
		//$this->url = pc_base::load_app_class('url', 'content');
	}
	public function set_model($modelid) {
		$this->model = getcache('model', 'commons');
		$this->modelid = $modelid;
		$this->table_name = $this->db_tablepre.$this->model[$modelid]['tablename'];
		$this->model_tablename = $this->model[$modelid]['tablename'];
	}
	/**
	 * 添加内容
	 * 
	 * @param $datas
	 * @param $isimport 是否为外部接口导入
	 */
	public function add_content($data,$isimport = 0) {
		$this->search_db = pc_base::load_model('search_model');
		$modelid = $this->modelid;
		require_once CACHE_MODEL_PATH.'content_input.class.php';
        require_once CACHE_MODEL_PATH.'content_update.class.php';
		$content_input = new content_input($this->modelid);
		$inputinfo = $content_input->get($data,$isimport);

		$systeminfo = $inputinfo['system'];
		$modelinfo = $inputinfo['model'];

		if($data['inputtime'] && !is_numeric($data['inputtime'])) {
			$systeminfo['inputtime'] = strtotime($data['inputtime']);
		} elseif(!$data['inputtime']) {
			$systeminfo['inputtime'] = SYS_TIME;
		} else {
			$systeminfo['inputtime'] = $data['inputtime'];
		}

		if($data['updatetime'] && !is_numeric($data['updatetime'])) {
			$systeminfo['updatetime'] = strtotime($data['updatetime']);
		} elseif(!$data['updatetime']) {
			$systeminfo['updatetime'] = SYS_TIME;
		} else {
			$systeminfo['updatetime'] = $data['updatetime'];
		}
		$systeminfo['username'] = $data['username'] ? $data['username'] : param::get_cookie('admin_username');
		$systeminfo['sysadd'] = defined('IN_ADMIN') ? 1 : 0;
		
		//自动提取摘要
		if(isset($_POST['add_introduce']) && $systeminfo['description'] == '' && isset($modelinfo['content'])) {
			$content = stripslashes($modelinfo['content']);
			$introcude_length = intval($_POST['introcude_length']);
			$systeminfo['description'] = str_cut(str_replace(array("\r\n","\t",'[page]','[/page]','&ldquo;','&rdquo;'), '', strip_tags($content)),$introcude_length);
			$systeminfo['description'] = addslashes($systeminfo['description']);
		}
		//自动提取缩略图
		if(isset($_POST['auto_thumb']) && $systeminfo['thumb'] == '' && isset($modelinfo['content'])) {
			$content = $content ? $content : stripslashes($modelinfo['content']);
			$auto_thumb_no = intval($_POST['auto_thumb_no']) * 3;
			if(preg_match_all("/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png))\\2/i", $content, $matches)) {
				
				$systeminfo['thumb'] = $matches[$auto_thumb_no][0];
			}
		}
		//主表
		$tablename = $this->table_name = $this->db_tablepre.$this->model_tablename;
		if($isimport) $systeminfo = new_addslashes($systeminfo);
		$id = $modelinfo['id'] = $this->insert($systeminfo,true);
		$this->update($systeminfo,array('id'=>$id));
		//更新URL地址
		if($data['islink']==1) {
			$urls = $_POST['linkurl'];
		} else {
			$urls = $this->url->show($id, 0, $systeminfo['catid'], $systeminfo['inputtime'], $data['prefix'],$inputinfo,'add');
		}
		$this->table_name = $tablename;
		$this->update(array('url'=>$urls[0]),array('id'=>$id));
		//附属表
		$this->table_name = $this->table_name.'_data';
		if($isimport) $modelinfo = new_addslashes($modelinfo);
		$this->insert($modelinfo);
		//添加统计
		$this->hits_db = pc_base::load_model('hits_model');
		$hitsid = 'c-'.$modelid.'-'.$id;
		$this->hits_db->insert(array('hitsid'=>$hitsid,'updatetime'=>SYS_TIME));
		//更新到全站搜索
		$this->search_api($id,$inputinfo);
		//更新栏目统计数据
		$this->update_category_items($systeminfo['catid'],'add',1);
		//调用 update
		$content_update = new content_update($this->modelid,$id);
		$content_update->update($data);

		//发布到其他栏目
		if($id && isset($_POST['othor_catid']) && is_array($_POST['othor_catid'])) {
			$r = $this->get_one(array('id'=>$id));
			foreach ($_POST['othor_catid'] as $cid=>$_v) {
				$this->set_catid($cid);
				$mid = $this->category[$cid]['modelid'];
				if($modelid==$mid) {
					//相同模型的栏目插入新的数据
					$systeminfo['catid'] = $cid;
					$newid = $modelinfo['id'] = $this->insert($systeminfo,true);
					$this->table_name = $tablename.'_data';
					$this->insert($modelinfo);

					if($data['islink']==1) {
						$urls = $_POST['linkurl'];
					} else {
						$urls = $this->url->show($newid, 0, $cid, $systeminfo['inputtime'], $data['prefix'],$inputinfo,'add');
					}
					$this->table_name = $tablename;
					$this->update(array('url'=>$urls[0]),array('id'=>$newid));
				} else {
					//不同模型插入转向链接地址
					$newid = $this->insert(
					array('title'=>$systeminfo['title'],
						'style'=>$systeminfo['style'],
						'thumb'=>$systeminfo['thumb'],
						'keywords'=>$systeminfo['keywords'],
						'description'=>$systeminfo['description'],
						'status'=>$systeminfo['status'],
						'catid'=>$cid,'url'=>$urls[0],
						'sysadd'=>1,
						'username'=>$systeminfo['username'],
						'inputtime'=>$systeminfo['inputtime'],
						'updatetime'=>$systeminfo['updatetime'],
						'islink'=>1
					),true);
					$this->table_name = $this->table_name.'_data';
					$this->insert(array('id'=>$newid));
				}
				$hitsid = 'c-'.$mid.'-'.$newid;
				$this->hits_db->insert(array('hitsid'=>$hitsid,'updatetime'=>SYS_TIME));
			}
		}
		//更新附件状态
		if(pc_base::load_config('system','attachment_stat')) {
			$this->attachment_db = pc_base::load_model('attachment_model');
			$this->attachment_db->api_update(stripslashes($modelinfo['content']),'c-'.$systeminfo['catid'].'-'.$id);
		}
		return $id;
	}
	/**
	 * 修改内容
	 * 
	 * @param $datas
	 */
	public function edit_content($data,$id) {
		$model_tablename = $this->model_tablename;
		//前台权限判断
		if(!defined('IN_ADMIN')) {
			$_username = param::get_cookie('_username');
			$us = $this->get_one(array('id'=>$id,'username'=>$_username));
			if(!$us) return false;
		}
		
		$this->search_db = pc_base::load_model('search_model');
													
		require_once CACHE_MODEL_PATH.'content_input.class.php';
        require_once CACHE_MODEL_PATH.'content_update.class.php';
		$content_input = new content_input($this->modelid);
		$inputinfo = $content_input->get($data);

		$systeminfo = $inputinfo['system'];
		$modelinfo = $inputinfo['model'];
		if($data['inputtime'] && !is_numeric($data['inputtime'])) {
			$systeminfo['inputtime'] = strtotime($data['inputtime']);
		} elseif(!$data['inputtime']) {
			$systeminfo['inputtime'] = SYS_TIME;
		} else {
			$systeminfo['inputtime'] = $data['inputtime'];
		}
		
		if($data['updatetime'] && !is_numeric($data['updatetime'])) {
			$systeminfo['updatetime'] = strtotime($data['updatetime']);
		} elseif(!$data['updatetime']) {
			$systeminfo['updatetime'] = SYS_TIME;
		} else {
			$systeminfo['updatetime'] = $data['updatetime'];
		}
		if($data['islink']==1) {
			$systeminfo['url'] = $_POST['linkurl'];
		} else {
			//更新URL地址
			$urls = $this->url->show($id, 0, $systeminfo['catid'], $systeminfo['inputtime'], $data['prefix'],$inputinfo,'edit');
			$systeminfo['url'] = $urls[0];
		}
		
		//自动提取摘要
		
		if(isset($_POST['add_introduce']) && $systeminfo['description'] == '' && isset($modelinfo['content'])) {
			$content = stripslashes($modelinfo['content']);
			$introcude_length = intval($_POST['introcude_length']);
			$systeminfo['description'] = str_cut(str_replace(array("\r\n","\t",'[page]','[/page]','&ldquo;','&rdquo;'), '', strip_tags($content)),$introcude_length);
			$systeminfo['description'] = addslashes($systeminfo['description']);
		}
		//自动提取缩略图
		if(isset($_POST['auto_thumb']) && $systeminfo['thumb'] == '' && isset($modelinfo['content'])) {
			$content = $content ? $content : stripslashes($modelinfo['content']);
			$auto_thumb_no = intval($_POST['auto_thumb_no']) * 3;
			if(preg_match_all("/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png))\\2/i", $content, $matches)) {
				
				$systeminfo['thumb'] = $matches[$auto_thumb_no][0];
			}
		}

		//主表
		$this->table_name = $this->db_tablepre.$model_tablename;
		$this->update($systeminfo,array('id'=>$id));
		
		//附属表
		$this->table_name = $this->table_name.'_data';
		$this->update($modelinfo,array('id'=>$id));
		$this->search_api($id,$inputinfo);
		//调用 update
		
		$content_update = new content_update($this->modelid,$id);
		$content_update->update($data);
		//更新附件状态
		if(pc_base::load_config('system','attachment_stat')) {
			$this->attachment_db = pc_base::load_model('attachment_model');
			$this->attachment_db->api_update(stripslashes($modelinfo['content']),'c-'.$systeminfo['catid'].'-'.$id);
		}
		return true;
	}
	public function status($ids = array(), $status = 99) {
		if(is_array($ids) && !empty($ids)) {
			foreach($ids as $id) {
				$this->update(array('status'=>$status),array('id'=>$id));
			}
		} else {
			$this->update(array('status'=>$status),array('id'=>$ids));
		}
		return true;
	}
	/**
	 * 删除内容
	 * @param $id 内容id
	 * @param $file 文件路径
	 * @param $catid 栏目id
	 */
	public function delete_content($id,$file,$catid = 0) {
		//删除主表数据
		$this->delete(array('id'=>$id));
		//删除从表数据
		$this->table_name = $this->table_name.'_data';
		$this->delete(array('id'=>$id));
		if($file) @unlink(PHPCMS_PATH.$file);
		//重置默认表
		$this->table_name = $this->db_tablepre.$this->model_tablename;
		//更新栏目统计
		$this->update_category_items($catid,'delete');
	}
	
	
	private function search_api($id = 0, $data = array(), $action = 'update') {
		$type_arr = getcache('type_model','search');
		$typeid = $type_arr[$this->modelid];
		if($action == 'update') {
			$fulltext_array = getcache('model_field_'.$this->modelid,'model');
			foreach($fulltext_array AS $key=>$value){
				if($value['isfulltext']) {
					$fulltextcontent .= $data['system'][$key] ? $data['system'][$key] : $data['model'][$key];
				}
			}
			$this->search_db->update_search($typeid ,$id, $fulltextcontent,$data['system']['title'],$data['inputtime']);
		} elseif($action == 'delete') {
			$this->search_db->delete_search($typeid ,$id);
		}
	}
	/**
	 * 获取单篇信息
	 * 
	 * @param $catid
	 * @param $id
	 */
	public function get_content($catid,$id) {
		$catid = intval($catid);
		$id = intval($id);
		if(!$catid || !$id) return false;
		$this->category = getcache('category_content','commons');
		if(isset($this->category[$catid]) && $this->category[$catid]['type'] == 0) {
			$modelid = $this->category[$catid]['modelid'];
			$this->set_model($modelid);
			$r = $this->get_one(array('id'=>$id));
			//附属表
			$this->table_name = $this->table_name.'_data';
			$r2 = $this->get_one(array('id'=>$id));
			if($r2) {
				return array_merge($r,$r2);
			} else {
				return $r;
			}
		}
		return true;
	}
	/**
	 * 设置catid 所在的模型数据库
	 * 
	 * @param $catid
	 */
	public function set_catid($catid) {
		$catid = intval($catid);
		if(!$catid) return false;
		if(empty($this->category)) $this->category = getcache('category_content','commons');
		if(isset($this->category[$catid]) && $this->category[$catid]['type'] == 0) {
			$modelid = $this->category[$catid]['modelid'];
			$this->set_model($modelid);
		}
	}
	
	private function update_category_items($catid,$action = 'add',$cache = 0) {
		$this->category_db = pc_base::load_model('category_model');
		if($action=='add') {
			$this->category_db->update(array('items'=>'+=1'),array('catid'=>$catid));
		}  else {
			$this->category_db->update(array('items'=>'-=1'),array('catid'=>$catid));
		}
		if($cache) $this->cache_items();
	}
	
	public function cache_items() {
		$datas = $this->category_db->select(array('modelid'=>$this->modelid),'catid,type,items',10000);
		$array = array();
		foreach ($datas as $r) {
			if($r['type']==0) $array[$r['catid']] = $r['items'];
		}
		setcache('category_items_'.$this->modelid, $array,'commons');
	}
}
?>