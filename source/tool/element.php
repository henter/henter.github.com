<?php
class element extends form_element
{
	public static function role($id, $value, $attribute = null)
	{
		$settings = array();
		$settings['name'] = $settings['id'] = $id;
		$settings['value'] = $value;
		$settings['attribute'] = $attribute;
		foreach (table('role') as $roleid=>$v)
		{
			$settings['options'][$roleid] = $v['name'];
		}
		return parent::select($settings);
	}
	
	public static function department($dep_id = 0, $uni_id = 0, $disable = false)
	{
		$sel_arr = array();
		$sel_arr['name'] = 'deparmentid';
		$sel_arr['id'] = 'depid_'.$uni_id;
		$sel_arr['value'] = $dep_id;
		if($disable) $sel_arr['disabled'] = true;
		$roles = array();
		foreach (table('department') as $value)
		{
			$roles[$value['deparmentid']] = $value['name'];
		}
		$sel_arr['options'] = $roles;
		$roles = parent::select($sel_arr);
		return $roles;
	}
	
	public static function role_dropdown($name, $value=null, $departmentid = null, $attr = null, $tips = null)
	{
		$roles = array();
		if ($tips)
		{
			$roles[] = $tips;
		}
		
		if ($departmentid)
		{
			$data = loader::model('admin/role','system')->getsByDepartmentid($departmentid);
			foreach ($data as $k=>$v)
		    {
		        $roles[$v['roleid']] = $v['name'];
		    }
		}
		else
		{
			foreach (table('role') as $k=>$v)
			{
				$roles[$k] = $v['name'];
			}
		}
	    return parent::select(array(
	       'name'=>$name,
	       'value'=>$value,
	       'options'=>$roles,
	       'attribute'=>$attr
	    ));
	}
	
	public static function department_dropdown($name, $value = null, $attr = null, $tips = '-------')
	{
		import('helper.treeview');
		$treeview = new treeview(table('department'));
		$html = "<select name=\"$name\" $attr>\n";
		$html .= '<option value="">'.$tips.'</option>';
		$html .= $treeview->select(null, $value, '<option value="{$departmentid}" {$selected}>{$space}{$name}</option>');
		$html .= '</select>';
		return $html;
	}

	public static function sex($sex_id = MALE, $uni_id = 0, $disable = false)
	{
		$sel_arr = array();
		$sel_arr['name'] = 'sex';
		$sel_arr['id'] = 'sex_'.$uni_id;
		$sel_arr['value'] = $sex_id ? $sex_id : FEMALE;
		if($disable) $sel_arr['disabled'] = true;

		$sex = array(MALE=>'男', FEMALE=>'女');

		$sel_arr['options'] = $sex;
		return parent::select($sel_arr);
	}

	public static function category($id, $name, $value = null, $size = 1, $attr = null, $tips = '请选择', $priv = true)
	{
		$category = table('category');
		$catenum = count($category);
		if($catenum<30)
		{
			foreach ($category as $k=>$c)
			{
				$category[$k]['childids'] = $c['childids'] ? 1 : 0;

				if ($priv && !priv::category($c['catid']))
				{
					if (priv::category($c['catid'], true))
					{
						$category[$k]['catid'] = '';
					}
					else
					{
						unset($category[$k]);
						continue;
					}
				}
			}
			import('helper.treeview');
			$treeview = new treeview($category);
			$html = "<select name=\"$name\" id=\"$id\" size=\"$size\" $attr>\n";
			$html .= "<option value=\"\">".$tips."</option>\n";
			$html .= $treeview->select(null, $value, '<option value="{$catid}" childids="{$childids}" {$selected}>{$space}{$name}</option>');
			$html .= '</select>';
			return $html;
		}
		else
		{
			$catname = table('category', $value, 'name');
			echo '<style type="text/css">.cs_mlist{line-height:16px;white-space:nowrap;padding:2px 0 2px 2px;margin-right:2px;cursor:pointer}
			 .over{background-color:#FFFFCC}</style><input id="'.$id.'" name="'.$name.'" type="hidden" value="'.$value.'" /><a href="javascript:;" style="margin:0px 4px;text-decoration:underline" >'.($value?$catname:$tips).'</a>
             <div style="position:absolute;-moz-box-shadow:0 4px 10px #8B8B8B;z-index:20;background:#fff;display:none;border:1px #94C5E5 solid;min-width:91px;"><div style="padding:5px"><div class="cs_sb" style="height:15px;"> <input type="text" autocomplete="off" name="cateseek" maxlength="40" /> </div><div class="cs_mitem" style="margin-top:10px;"></div></div></div>
             <script type="text/javascript" src="'.ADMIN_URL.'apps/system/js/category_select.js"></script> 
             <script type="text/javascript">$("#'.$id.'").category(); </script>
             ';
		}
	}

	public static function check_category($id, $name, $value = null, $size = 1, $attr = null)
	{
		import('helper.treeview');
		$treeview = new treeview(table('category'));
		$html = "<select name=\"$name\" id=\"$id\" size=\"$size\" $attr>\n";
		$html .= '<option value="">请选择</option>';
		$html .= $treeview->select(null, $value, '<option value="{$catid}">{$space}{$name}</option>');
		$html .= '</select>';
		return $html;
	}

	public static function psn($id, $name, $value, $size = 30, $type = 'dir')
	{
		return "<input type=\"text\" name=\"$name\" id=\"$id\" value=\"$value\" size=\"$size\"/> <input type=\"button\" value=\"选择\" class=\"button_style_1\" onclick=\"psn.select('$id', '$type', '$value')\"/> <a href=\"javascript:;\" onclick=\"ct.assoc.open('?app=system&controller=psn&action=index','newtab')\">管理</a>";
	}
	
	public static function dsn_select($id, $value = null, $attribute = null)
	{
		$settings = array();
		$settings['name'] = $settings['id'] = $id;
		$settings['value'] = $value;
		$settings['attribute'] = $attribute;
		foreach (table('dsn') as $dsnid=>$v)
		{
			$settings['options'][$dsnid] = $v['name'];
		}
		return parent::select($settings);
	}

	public static function psn_select($id, $value, $attribute = null)
	{
		$settings = array();
		$settings['name'] = $settings['id'] = $id;
		$settings['value'] = $value;
		$settings['attribute'] = $attribute;
		foreach (table('psn') as $psnid=>$v)
		{
			$settings['options'][$psnid] = $v['name'];
		}
		return parent::select($settings);
	}

	public static function model($id, $name, $value, $attribute = null)
	{
		$settings = array();
		$settings['id'] = $id;
		$settings['name'] = $name;
		$settings['value'] = $value;
		$settings['attribute'] = $attribute;
		$settings['options'][0] = '请选择';
		foreach (table('model') as $modelid=>$v)
		{
			if($v['name'] == '辩论') continue;
			$settings['options'][$modelid] = $v['name'];
		}
		return parent::select($settings);
	}

	public static function model_checkbox($value = array(),$id = array())
	{
		$settings = array();
		$settings['name'] = $id;
		$settings['value'] = $value;
		$options = array();
		foreach (table('model') as $value)
		{
			$options[$value['modelid']] = $value['name'];
		}
		$settings['options'] = $options;
		
		return parent::checkbox($settings);
	}

	public static function guestbook_type($id, $name, $value = null, $size = 1, $attr = null)
	{
		import('helper.treeview');
		$treeview = new treeview(table('guestbook_type'));
		$html = "<select name=\"$name\" id=\"$id\" size=\"$size\" $attr>\n";
		$html .= '<option value="">类型</option>';
		$html .= $treeview->select(null, $value, '<option value="{$typeid}">{$name}</option>');
		$html .= '</select>';
		return $html;
	}

	public static function guestbook_type_radio($value = 1, $id = 'typeid')
	{
		$settings = array();
		$options = array();
		$settings['name'] = $settings['id'] = $id;
		$settings['value'] = $value;
		foreach (table('guestbook_type') as $value) {
			$options[$value['typeid']] = $value['name'];
		}
		$settings['options'] = $options;
		return parent::radio($settings);
	}
	
	public static function workflow($id, $value, $attribute = null)
	{
		$settings = array();
		$settings['name'] = $settings['id'] = $id;
		$settings['value'] = $value;
		$settings['attribute'] = $attribute;
		$settings['options'][] = '请选择';
		foreach (table('workflow') as $workflowid=>$v)
		{
			$settings['options'][$workflowid] = $v['name'];
		}
		return parent::select($settings)." <a href=\"javascript:;\" onclick=\"ct.assoc.open('?app=system&controller=workflow&action=index','newtab')\">管理</a>";
	}

	public static function channel($name,  $checkeds = array())
	{
		$category = table('category');
		import('helper.treeview');
		$treeview = new treeview($category);
		$html = $treeview->get(null, 'category_tree', '<li><input id="category_{$catid}" name="'.$name.'" type="checkbox" value="{$catid}" class="category_{$catid}_children" onclick="select_treeview_children_channel({$catid})" /><span id="{$catid}">{$name}</span>{$child}</li>');
		return $html;
	}

	public static function template($id, $name, $value, $size = 30)
	{
		return '<input type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'" size="'.$size.'" />
		<input type="button" onclick="template_select(\''.$id.'\')" class="button_style_1" value="选择">
		<img src="images/edit.gif" alt="编辑" width="16" height="16" class="hand" onclick="ct.assoc.open(\'?app=system&controller=template&action=edit&path=\'+$(\'#'.$id.'\').val(),\'newtab\');"/>';
	}
	/**
	 * @needs jquery.uploadify.js & cmtop.filemanager.js
	 */
	public static function thumb($name='thumb',$value = '',$size = 30)
	{
		$sid = str_replace('.','_',microtime(true));
		$html = '<div class="thumb_cell">
			<input type="text" name="'.$name.'" id="thumb_'.$sid.'"
				upbtn="#upbtn_'.$sid.'"
				filebtn="#filebtn_'.$sid.'"
				editbtn="#editbtn_'.$sid.'"
			size="'.$size.'" value="'.$value.'"/>
			<div id="upbtn_'.$sid.'"></div>
			<input class="button_style_1" type="button" id="filebtn_'.$sid.'" value="图像库"/>
			<input class="button_style_1" type="button" style="diaplay:none" id="editbtn_'.$sid.'" value="编辑"/>
		</div><script type="text/javascript">$(function(){
			setTimeout(function (){
				$("#thumb_'.$sid.'").imageInput();
			}, 10);
			$("#editbtn_'.$sid.'").css("display",$("#thumb_'.$sid.'").val()?"block":"none");
		});</script>';
		return $html;
	}
	
	public static function photo($name='photo',$value = '')
	{
		$photo = $value?$value:'nopic.jpg';
		$sid = str_replace('.','_',microtime(true));
		$html = '<div class="thumb_cell">
			<img width="120" id="preview_'.$sid.'" src="'.UPLOAD_URL.'avatar/'.$photo.'"/>
			<input type="hidden" name="'.$name.'" id="photo_'.$sid.'"
				upbtn="#upbtn_'.$sid.'"
				editbtn="#editbtn_'.$sid.'"
				preview="#preview_'.$sid.'"
			value="'.$value.'"/>
			<div id="upbtn_'.$sid.'"></div>
			<input class="button_style_1" type="button" id="editbtn_'.$sid.'" value="编辑"/>
		</div><script type="text/javascript">$(function(){
			setTimeout(function (){
				$("#photo_'.$sid.'").photoInput();
			}, 10);
			$("#editbtn_'.$sid.'").css("display","'.$value.'"!=""?"block":"none");
		});</script>';
		return $html;
	}

	public static function state($value = 0, $id = 'state')
	{
		$settings = array();
		$settings['name'] = $settings['id'] = $id;
		$settings['value'] = $value;
		$settings['options'] = array(0=>'启用', 1=> '禁用');
		return parent::radio($settings);
	}

	public static function charset($value='utf8', $id = 'state')
	{
		$settings = array();
		$settings['name'] = $settings['id'] = $id;
		$settings['value'] = $value;
		$settings['options'] = array('gbk'=>'GBK', 'gb2312'=>'gb2312', 'utf8'=>'UTF8','latin1'=>'latin1');
		return parent::radio($settings);
	}

	public static function member_groups($value = 5, $id='groupid',$defaultvalue = '')
	{
		$settings = array();
		$settings['name'] = $settings['id'] = $id;
		$settings['value'] = $defaultvalue;
		$options = array();
		$member_group = table('member_group');
		foreach ($member_group as $value) {
			$options[$value['groupid']] = $value['name'];
		}
		$settings['options'] = $options;
		return parent::select($settings);
	}

	public static function member_photo($userid = 0,$width = '80',$height = '80',$size = 'small')
	{
		static $photos;
		if(!isset($photos[$userid]))
		{
			$member = loader::model('member_front', 'member');
			$photos[$userid] = $member->get_photo($userid, $width, $height, $size);
		}
		return $photos[$userid];
	}

	public static function title($id = 'title', $value = '', $color = '', $size = 80, $maxlength = 80)
	{
		$html = array(
			parent::text(array(
				'id'=>$id,
				'name'=>$id,
				'value'=>$value,
				'size'=>$size,
				'maxlength'=>$maxlength,
				'class'=>'bdr inputtit_focus',
				'style'=>'width:478px'
			)),
			parent::hidden(array(
				'name'=>'color',
				'value'=>$color,
				'class'=>'color-input',
				'size'=>7,
				'attribute'=>'oninited="$(\'#'.$id.'\').css(\'color\', color)" onpicked="$(\'#'.$id.'\').css(\'color\', color)"',
			))
		);
		return implode('',$html);
	}

	public static function tag($id = 'tags', $value = '', $size = 60, $maxlength = 60)
	{
		$return =  parent::text(array(
			'id'=>$id,
			'name'=>$id,
			'value'=>$value,
			'size'=>$size,
			'maxlength'=>$maxlength,
			'attribute'=>'uncount=1'
		));
		if(extension_loaded('scws') || file_exists(FW_PATH.'helper'.DS.'pscws4.php'))
		{
			$return .= '&nbsp;&nbsp;<input type="button" class="button_style_1" value="提取关键字" onclick="get_tags()"></input>';
			$return .= '<script type="text/javascript">
			$(function(){
			$("#title").change(function(){
				if($("#tags").val()==""){
					get_tags();
				}
			})});
			function get_tags(){
				if ($("#title").val()){
					$.post("?app=system&controller=tag&action=get_tags", {"title":$("#title").val()}, function(response){
						if (response.state){
							$("#tags").val(response.data);
						}
					}, "json");
				}	
			}
			</script>';
		}
		return $return;
	}

	//商业版是推荐到所有区块，大众版改为推荐位
	public static function section($contentid = null)
	{
		$contentid = intval($contentid);
		if($contentid)
		{
			$db = & factory::db();
			$alraady = $db->select("SELECT s.sectionid, s.name FROM #table_section s
									JOIN #table_section_data d ON s.sectionid = d.sectionid
									WHERE d.contentid = $contentid ORDER BY s.`sort` DESC");
			foreach ($alraady AS $r)
			{
				$ids[] = $r['sectionid'];
				$text[] = $r['name'];
			}
			if($ids) $ids = implode(',', $ids);
			if($text) $text = implode(', ', $text);
		}
		$html = '<script type="text/javascript" src="apps/section/js/section.js"></script>';
		$html .= '<span class="f_l">';
		$html .= '<input type="hidden" name="sectionids" id="sectionids" value="'.$ids.'" />';
		$html .= '<input type="button" value="选择" class="button_style_1" onclick="section.commend()" /></span>';
		$html .= '<span id="commend_text" style="background:#FFCC66">'.$text.'</span><span id="upsection" style="display:none">&nbsp;&nbsp;<input type="checkbox" name="upsection">更新到区块</span>';
		$html .= '<script type="text/javascript">$(function(){$("#upsection").toggle($("#commend_text").html()!=""?true:false);})</script>';
		return $html;
	}
	

	public static function related($contentid = null)
	{
        $related = loader::model('admin/related', 'system');
      
        $relateds = $related->ls($contentid);
      
        $html = '<div class="expand mar_l_8">';
        
	        $html .= '  <div class="div_show">';
	        $html .= '    <input type="text" name="related_keywords" id="related_keywords" size="20" />';
	        $html .= '    <input type="button" name="related" value="搜索" class="button_style_1" onclick="related_select($(\'#related_keywords\').val())" />';	
	        $html .= '    <ul id="related_data">';
            foreach ($relateds as $i=>$d)
            {
               $html .= '<li><input type="hidden" name="related[]" id="related_'.$i.'" value="'.$d['title'].'|'.$d['thumb'].'|'.$d['url'].'|'.$d['time'].'|'.$d['cid'].'"/><a href="'.$d['url'].'" target="_blank">'.$d['title'].'</a></li>';
            }
	        $html .= '    </ul>';
	        $html .= '  </div>';
       
        $html .= '</div>';
        return $html;
	}

	public static function tips($message)
	{
		return '<img src="images/question.gif" width="16" height="16" class="tips hand" tips="'.$message.'" align="absmiddle"/>';
	}
	
	public static function weight($weight)
	{
		
		$instance = new setting();
		$setting = $instance->get('system');
		$setting = explode("\n",$setting['weight']);
		$points = array();
		while (list($key,$value) = each($setting)) {
			if($value) $temp = explode('|',trim($value));
			else break;
			$points[] = '"'.($temp[0]/100).'":"'.$temp[1].'"';
		}
		$points = '{'.implode(',',$points).'}';
		$weight = ($weight?$weight:intval(setting('system','defaultwt')))/100;
		echo '<script type="text/javascript" src="'.IMG_URL.'js/lib/cmstop.adjuster.js"></script>
		<div style="position:relative;background:url(css/images/weightr.gif) no-repeat scroll 45px 0px;width:670px;height:25px;">
            <input type="text" style="ime-mode: disabled;float:left" name="weight" value="'.($weight*100).'" size="3" />
            <div id="weight" style="width:600px;height:20px;position:absolute;left:56px;top:1px;"></div>
        </div>
		<script type="text/javascript">
		$(function(){
            $("#weight").slider({
            	isStep     : false,
            	stepConfig :'.$points.',
            	offset	   :'.$weight.',
            	onDragInit :function(h, t, p, c){
            		var length = p.length;
            		h.attr("tips", c["'.$weight.'"]?(100*'.$weight.'+"\uff1a"+c["'.$weight.'"]):"");
            		h.attrTips("tips", "tips_green", 200, "top");
            		for(var i=0;i<length;i++)
            			p[i][0].attr("msg",(p[i][1]*100)+"\uff1a"+p[i][2]).css("margin-top","2px").attrTips("msg", "tips_green", 200, "top");
            	},
            	onDrag	   :function(h,e,p){
            		h.parent().prev().val(parseInt(100*p));
            	},
            	onDragEnd  :function(h,e,percent,c){
            		if(c[1]){
            			h.attr("tips",percent*100+"\uff1a"+c[1]);
            			var evt = $.Event("mouseover");
            			var off = h.offset();
            			evt.pageX = off.left;
            			evt.pageY = off.top;
            			$.event.trigger(evt, [], h[0]);
            		}else h.attr("tips","");
            		h.parent().prev().val(parseInt(100*percent));
            	},
            	handleBg   : "url(css/images/weight.gif) no-repeat"
            }).prev().keyup(function(e){
            	if(e.keyCode<48 || e.keyCode>57) return;
            	var val = this.value;
            	if(parseInt(val)>100 || parseInt(val)<0) return;
            	$.slider.setPoint(val/100)})
            });
           </script>';
	}
	
	public static function status($id, $name, $value, $attr = null)
	{
		$options = array();
		$statuss = table('status');
		foreach ($statuss as $status=>$r)
		{
			$options[$status] = $r['name'];
		}
		return parent::select(array('id'=>$id, 'name'=>$name, 'value'=>$value, 'attr'=>$attr, 'options'=>$options));
	}
	
	public static function model_change($catid, $modelid)
	{
		$string = '<select id="changemodel" style="width:70px">';
		$models = table('model');
        foreach ($models as $mid=>$m)
        {
        	$m = table('model', $mid);
        	if (priv::aca($m['alias'], $m['alias'], 'index')) $string .= '<option value="'.$m['alias'].'" ico="'.$m['alias'].'" '.($modelid == $mid ? 'selected' : '').'>'.$m['name'].'</option>';
        }
        $string .= '</select>';
        return $string;
	}
}