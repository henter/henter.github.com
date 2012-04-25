<?php
/*
Name: 高分网公共插件
URI: http://www.gaofen.com/
Description: 高分网插件，主要提供公共函数库或公共数据。地区库数据来自DiscuzX
Version: 0.1
Author: Henter
Author URI: http://www.gaofen.com/
*/
include 'common.inc.php';
define('G_NAME',"gaofen");
define('G_PATH',WP_PLUGIN_DIR.'/'.G_NAME.'/');
define('G_ADMIN_PATH',G_PATH.'admin/');
define('G_INC_PATH',G_PATH.'inc/');
define('G_URL',plugins_url('/'.G_NAME.'/'));// http://wp.com/wp-content/plugins/gaofen/
define('G_CACHE_PATH',G_PATH.'cache/');
define('G_TPLTAG_CACHE_PATH',G_CACHE_PATH.'tpltag/');
define('G_TAG_CACHE_PATH',G_CACHE_PATH.'taglist/');
define('G_TPL_PATH',get_template_directory().'/gaofen/');
is_dir ( G_CACHE_PATH ) or @mkdir ( G_CACHE_PATH, 0777 );
is_dir ( G_TPLTAG_CACHE_PATH ) or @mkdir ( G_TPLTAG_CACHE_PATH, 0777 );
is_dir ( G_TAG_CACHE_PATH ) or @mkdir ( G_TAG_CACHE_PATH, 0777 );

$gaofen = stripslashes_deep(get_option('gaofen'));
$gaofen_hot = stripslashes_deep(get_option('gaofen_hot'));
$blogurl = get_option('home').'/';//虚拟首页
$appurl = get_option('siteurl').'/';//真正首页

define('MAPKEY',$gaofen['mapkey']);
define('USER_URL','http://my.gaofen.com');

global $user_identity, $user_level; //用户登录后用到的 用户名和等级

//文章flag属性 分别在下面的gaofen_post_att_display和gaofen_post_att_save函数中用到
$gaofen_post_flags = array('h'=>'头条','c'=>'首页','f'=>'幻灯','p'=>'图片','a'=>'特推','s'=>'推荐','b'=>'分站首页','x'=>'头条1','y'=>'头条2','z'=>'头条3');

//新版父栏目 使用通用的综合页 index_common.htm
$gaofen_common_cat = array(
    //中考
    32289,32293,32291,32300,32301,32297,32299,32298,32296,32290,35660,
    //小升初
    34562,34619,34620,34561,34590,34588,34556,34557,34737,34570,34558,
    //高考
    35376,41030,35390,35391,35389,35377,35428,35375,35378,
    //幼升小
    38367,38390,38393,38392,38400,38391,38394,38369,38368,38366,38374,38372,38377,38370,38420,
    //留学
    45906,45907,45908,45909,
);


//数据表
global $wpdb;
$wpdb->district = $wpdb->prefix.'gaofen_district';

require_once G_INC_PATH.'common.func.php';

require_once G_INC_PATH.'template.func.php';
require_once G_INC_PATH.'frontend.php';
require_once G_ADMIN_PATH.'subsite.php';//暂时去掉分站后台功能
require_once G_ADMIN_PATH.'setting.php';
require_once G_ADMIN_PATH.'district.php';
require_once G_ADMIN_PATH.'dic.php';
require_once G_ADMIN_PATH.'category.php';
require_once G_ADMIN_PATH.'tpl.php';
require_once G_ADMIN_PATH.'subdomain.php';

//临时的 高考冲刺
//require_once G_ADMIN_PATH.'chongci.php';


//禁止自动保存 禁用草稿需在wp-config.php中增加一行 define('WP_POST_REVISIONS', false);  
add_action('admin_print_scripts', 'plugin_deregister_autosave');
function plugin_deregister_autosave() {
	wp_deregister_script('autosave');
	echo '<!-- '.basename(__FILE__).' '.__FUNCTION__.'() -->';
}


//后台菜单
add_action('admin_menu', 'gaofen_menu');
function gaofen_menu() {
	if (function_exists('add_menu_page')) {
		add_menu_page('高分网', '高分网', 'publish_pages', 'gaofen', '', G_URL.'/images/icon.png');
	}
	if (function_exists('add_submenu_page')) {
		$loadke1 = add_submenu_page('gaofen', '高分网设置', '设置', 'publish_pages', 'gaofen','gaofen_show_setting_page');
		add_submenu_page('gaofen', '地区库', '地区库', 'publish_pages', 'gaofen_district','gaofen_show_district_page');
		add_submenu_page('gaofen', '词库', '词库', 'publish_pages', 'gaofen_dic','gaofen_show_dic_page');
		add_submenu_page('gaofen', '频道模板', '频道栏目模板', 'publish_pages', 'gaofen_tpl','gaofen_show_tpl_page');
		//$needsuggest = add_submenu_page('edit.php', '文章标签分组', '文章标签分组 ', 'publish_pages', 'gaofen_tag_group','gaofen_tag_group_page');
		$cookies_js = add_submenu_page('edit.php', '文章栏目', '<img src="'.G_URL.'/images/icon.png" style="margin-bottom:-3px;" /> 文章栏目 ', 'publish_pages', 'gaofen_category','gaofen_category_page');
              //工具菜单下
              add_management_page( '删除静态缓存', '删除静态缓存', 'publish_pages', 'gaofen_delete_static', 'gaofen_delete_static_page');
              //需要加载keditor
              $loadke2 = add_dashboard_page( '高分网首页', '首页设置', 'publish_pages', 'gaofen_index', 'gaofen_index_page');//在setting文件里面
              add_action("load-$loadke1", 'gaofen_load_ke');//加载ke编辑器
              add_action("load-$loadke2", 'gaofen_load_ke');//加载ke编辑器
              //add_action("load-$needsuggest", 'gaofen_load_suggest');//加载jquery suggest插件
		add_action("load-$cookies_js", 'gaofen_load_cookie_category');//加载cookies.js文件  Silen
                  
              //临时的 高考冲刺
              //add_dashboard_page( '高考冲刺文章', '高考冲刺文章', 'publish_pages', 'gaofen_chongci', 'gaofen_chongci_page');
	}

}
//加载cookies.js文件  Silen
function gaofen_load_cookie_category(){
	wp_enqueue_script('gaofenke',G_URL.'/js/cookies.js');
	
}
add_action("load-post-new.php", 'gaofen_load_cookie');

//加载编辑器js
function gaofen_load_ke(){
    wp_enqueue_script('gaofenkeabc',G_URL.'/js/ke/kindeditor-min.js');
}
//加载cookies.js文件  Sile
function gaofen_load_cookie(){
	if(!$_GET['post_type']){
	 	wp_enqueue_script('gaofenke',G_URL.'/js/post_edit_cookies.js');
	}
		//wp_enqueue_script('gaofenkeabc',G_URL.'/js/cookies.js');
}

//加载jquery suggest插件
function gaofen_load_suggest(){
    wp_enqueue_script('suggest');
}

//文章列表页加上手动更新链接 同时将文章查看链接设为新窗口打开
add_action('post_row_actions','gaofen_post_row_actions',10,2);
function gaofen_post_row_actions($actions,$post){
    global $appurl;

    $url = $appurl.'article/'.$post->ID.'.htm/cc';
    //修改提示语短一点 避免换行
    $actions['trash'] = str_replace('移至回收站','删除',$actions['trash']);
    
    $actions['rehtml'] = '<a href="'.$url.'" target="_blank"><font color="red">更新</font></a>';

    $actions['view'] = str_replace('rel="permalink"',' target="_blank" rel="permalink"',$actions['view']);
    return $actions;
}




/**
  * 修改http://wp.com/list/zhongkao/chuyi/zhishidian-chuyi类似的链接为http://wp.com/list/zhishidian-chuyi
  * 更快的办法是注释掉系统文件wp-include/category-template.php中第33行
  * 现在是解析当前URL 去掉中间不需要的父目录
  * 在\wordpress-subdomains\plugin\filters.php中调用 15:23 2011/1/12
  */
add_action( 'category_link', 'gaofen_category_url' );
function gaofen_category_url($url){
    //$url = 'http://wp.com/list/zhongkao/chuyi/zhishidian-chuyi';
    //去掉最后一位的/
    $url = (substr($url, -1, 1)=='/') ? substr($url,0,strlen($url)-1) : $url;
    $arr = explode('/',$url);
    $_thepos = count($arr)-1;
    $firstslug = $arr[4];//栏目第一个（zhongkao）
    $realslug = $arr[$_thepos];//最后一个（zhishidian-chuyi）
    if($firstslug != $realslug){
        for($i=4;$i<$_thepos;$i++){
            unset($arr[$i]);
        }
        return implode('/',$arr);
    }
    return $url;
}

//shortlink修改
add_filter('get_shortlink', 'gaofen_shortlink');
function gaofen_shortlink($link){
    $arr = explode('?p=',$link);
    return get_permalink($arr[1]);
}

//修改试题链接  前台获取的全部是id形式 改为跟后台查看一样
add_action('post_link','tiku_post_link',10,2);
function tiku_post_link($link,$post){
    global $wp_rewrite;
    if(!$wp_rewrite) $wp_rewrite = new WP_Rewrite();
    if($post->post_type == 'tiku'){
        //$slug = get_page_uri($post->ID);
        $slug = $post->post_name;
        $post_link = home_url( $wp_rewrite->front.'tiku/'.$slug );
        return $post_link;
    }
    return $link;
}

/**
  * 增加文章分页的quicktag
  */
add_action('admin_print_scripts', 'post_navigation_quicktags');
function post_navigation_quicktags() {
	wp_enqueue_script(
		'gaofen_custom_quicktags',
		plugin_dir_url(__FILE__) . 'js/post_navigation_quicktags.js',
		array('quicktags')
	);
}
/******************给编辑器加上分页按钮***************/
add_action('init', 'postpage_tinymce_addbuttons');
function postpage_tinymce_addbuttons() {
	if(!current_user_can('edit_posts') && ! current_user_can('edit_pages')) {
		return;
	}
	if(get_user_option('rich_editing') == 'true') {
		add_filter("mce_external_plugins", "postpage_tinymce_addplugin");
		add_filter('mce_buttons', 'postpage_tinymce_registerbutton');
	}
}
function postpage_tinymce_registerbutton($buttons) {
	array_push($buttons, 'separator', 'postpages');
	return $buttons;
}
function postpage_tinymce_addplugin($plugin_array) {
	$plugin_array['postpages'] = plugins_url('gaofen/js/editor_plugin.dev.js');
	return $plugin_array;
}
/******************给编辑器加上分页按钮***************/


//增加题库类型 用作临时导入数据用
add_action( 'init', 'tiku_post_type' );
function tiku_post_type() {
  register_post_type( 'tiku',
    array(
      'labels' => array(
        'name' => __( '试题库' ),
        'add_new' =>'添加试题',
        'add_new_item' =>'添加试题',
        'singular_name' => __( 'tiku' )
      ),
      //'supports' => array('title','editor','custom-fields','thumbnail'),
      'supports' => array('title','editor','thumbnail'),
      'taxonomies' => array('category'),//支持栏目选择
      'public' => true,
      'rewrite' => array('slug' => 'tiku')
    )
  );
}


//add_action('admin_head', 'remove_menu');
//删除顶级菜单
function remove_menu() {
    global $menu;
    //print_r($menu);
    //remove post top level menu
    //unset($menu[5]);
}
add_action('admin_head', 'remove_submenu');
//删除子菜单
function remove_submenu() {
    global $submenu;
    //print_r($submenu);
    unset($submenu['index.php'][10]);//删除更新菜单
        
    //unset($submenu['edit.php'][10]);//去掉添加文章菜单 全部在栏目页选择栏目发布
    unset($submenu['edit.php'][15]);//删除文章栏目菜单

    unset($submenu['edit.php?post_type=tiku'][15]);//删除题库栏目菜单
}


//如果在栏目页点发布文章 就去掉右侧栏目选择框 并显示当前所在栏目名
add_action("admin_menu", "gaofen_category_selected_metabox");
function gaofen_category_selected_metabox(){
    add_meta_box( "gaofen_post_category_box", "所属栏目", "gaofen_category_selected_metabox_display", "post", "side", "high" );//右侧 上方
	//增加一个box  用来判断是否继续增加文章  silen
	add_meta_box( "gaofen_post_box", "是否继续新增文章", "gaofen_post_add_once", "post", "side", "high" );//右侧 上方
    remove_meta_box('categorydiv','post','side');//去掉原来的栏目选择框
 
    //试题库专用
    remove_meta_box('categorydiv','tiku','side');//去掉试题库右侧的栏目选择框
    add_meta_box( "gaofen_tiku_category_box", "所属栏目", "gaofen_category_selected_tiku_metabox_display", "tiku", "side", "high" );//右侧 上方
    add_meta_box( "gaofen_tiku_attbox", "试题属性", "gaofen_tiku_attbox", "tiku", "side", "low" );//右侧 下方
	
}
function gaofen_tiku_attbox(){
    global $post;
    include G_CACHE_PATH.'course.php'; //$em_courses
    include G_CACHE_PATH.'edustep.php';//$em_edusteps
    $post_id = $post->ID;
    $myedu = get_post_meta($post_id,'tikuedu');
    $mycourse = get_post_meta($post_id,'tikucourse');

    $str = "年级：<select name='tiku_edu'>";
    foreach($em_edusteps AS $k=>$v){
        $selected = in_array($k,$myedu) ? ' selected' : '';
        $str .= "<option value='$k' $selected>";
        if($k%50000==0){
            $str .= "$v";
        }else{
            $str .= "  &nbsp;&nbsp; $v";
        }
    }
    $str .= "</select>";
    $str .= "<p>&nbsp;</p>";
    
    $str .= "科目：<select name='tiku_course'>";
    foreach($em_courses AS $k=>$v){
        $selected = in_array($k,$mycourse) ? ' selected' : '';
        $str .= "<option value='$k' $selected>";
        if($k%50000==0){
            $str .= "$v";
        }else{
            $str .= "  &nbsp;&nbsp; $v";
        }
    }
    $str .= "</select>";

    echo $str;
}
//保存题库属性
add_action('save_post', 'gaofen_tiku_att_save');
function gaofen_tiku_att_save($post_id){
    $edu = $_POST['tiku_edu'];
    $course = $_POST['tiku_course'];
    if($edu){
        update_post_meta($post_id, 'tikuedu', trim($edu));
    }
    if($course){
        update_post_meta($post_id, 'tikucourse', trim($course));
    }
}
//题库 栏目提示
function gaofen_category_selected_tiku_metabox_display(){
    //试题库栏目id
    $term_id = 3;
    if(!$term_id && $edit_postid){
    }else{
        $arr = get_term( $term_id, 'category' );
        if($arr->name){
            //修改时 会显示原来的栏目选择框，所以这个隐藏域去掉
            if(!$edit_postid) echo '<input type="hidden" name="post_category[]" value="'.$term_id.'" />';
            echo '<p style="line-height:25px;margin:15px;">栏目：<span style="fonts-zie:24px;color:red;">'.$arr->name.'</span></p>';
        }else{
            echo '<p style="fonts-zie:24px;color:red;margin:15px;">试题栏目出错，请<a href="edit.php?page=gaofen_category">重新选择</a>！</p>';
        }
    }
}


//右侧栏目选择提示
function gaofen_category_selected_metabox_display(){
    global $gaofen_sites;
    //global $wp_meta_boxes;print_r($wp_meta_boxes);
    $siteid = wp_get_current_user()->siteid;
    //修改文章时获取文章id并读取栏目id（第一个栏目）
    $edit_postid = intval($_GET['post']);

    if($edit_postid){
        $_category = get_the_category($edit_postid);
        foreach($_category AS $k=>$v){
            $cats[] = $v->term_id;
        }
        arsort($cats);//逆向排序 键值从大到小  也就是 分站的id在前面 主站的在后面
        //print_r($cats);exit;
        
        //分站栏目html
        $old_site_cat_html = '';
        //轮询栏目 判断属于主站或是分站
        foreach($cats AS $k=>$v){
            $_topcat = get_topmost_cat($v);
            //分站栏目
            if(array_search($_topcat->term_id, $gaofen_sites)){
                $_cat = get_category($v);
                $_sitename = $_topcat->name;
                $_catname = $_cat->name;
                $old_site_cat_html .= '<span id="selected_site_cat_'.$v.'" siteid="'.$_topcat->term_id.'"><a onclick="site_cat_del('.$v.');" id="site-cat-check-num-"'.$v.' class="ntdelbutton">X</a><input type="hidden" value="'.$v.'" name="post_category[]" />&nbsp;'.$_sitename.' -- '.$_catname.'</span>';
                $value2[] = $v;
            }else{//主站栏目
                $value1[] = $v;
            }
        }

    }//end edit

    $html = "";

//分站管理时
//由于分站编辑无权限修改属于多个站点的文章，所以这里只会有仅属于当前分站的文章
if($siteid){
    $value2 = $value2 ? $value2 : array(intval($_GET['catid']));
    $html .= "<p>选择栏目 <select name='post_category[]' id='select_one_category'><option value=''>请选择栏目</option>";
    $html .= _output_subcategory_select_option($siteid, $value2,array('disable_parents'=>true,'dis_cat_slug'=>site_data('slug')));
    $html .= "</select></p>";
    echo $html;
    
    //主站栏目隐藏域 （只会有一个栏目 所以$value1[0]）
    //if($value1) echo '<input type="hidden" value="'.$value1[0].'" name="post_category[]" />';
    //也有可能出现在其它分站 待续

}else{
    $value1 = $value1 ? $value1 : array(intval($_GET['catid']));
    $html .= "<p>主站栏目 <select name='post_category[]' id='select_one_category' onblur='gaofen_sel_once_cookie()'><option value=''>请选择栏目</option>";
    $html .= _output_subcategory_select_option(0, $value1,array('disable_parents'=>true,'dis_cat_slug'=>site_data('slug')));
    $html .= "</select></p>";
    echo $html;

    if($old_site_cat_html){
        echo "<div id='selected_cat_container' class='tagchecklist'>$old_site_cat_html</div>";
    }else{
        echo "<div id='selected_cat_container' class='tagchecklist' style='display:none;'></div>";
    }
    
    //当前文章栏目中分站的栏目id列
    $value2_str = implode(',',$value2);
    
    echo '<p>推 送 到：';
    foreach($gaofen_sites AS $k=>$v){
        if($v) echo "<a href=\"javascript:void(0);\" onclick=\"gaofen_site_cat_select($v,'$value2_str');\" class='button rbutton'>$k</a> ";
    }
    echo "</p>";
    echo "<p id='site_cat_container' style='display:none;list-style-type:none;height:50px;overflow-y:auto;'></p>";
}

?>
<script language="javascript">
function site_cat_add(catid,siteid){
    var catname = jQuery("#site_cat_" + catid).attr('catname');
    var html = '<span id="selected_site_cat_' + catid +'" siteid="'+siteid+'"><a onclick="site_cat_del('+catid+');" id="site-cat-check-num-"' + catid +' class="ntdelbutton">X</a><input type="hidden" value="'+catid+'" name="post_category[]" />&nbsp;'+catname+'</span>';
    //alert(html);
    jQuery("span[siteid="+siteid+"] ").remove();
    jQuery("#selected_cat_container").show();
    jQuery("#selected_cat_container").append(html);
    //jQuery("#selected_cat_container").addClass('updated');
}

function site_cat_del(catid,siteid){
    jQuery("#selected_site_cat_" + catid).remove();
    jQuery("#site_cat_" + catid).removeAttr("checked");
    if(jQuery("#selected_cat_container").html() == ''){
        jQuery("#selected_cat_container").hide();
    }
}
function gaofen_site_cat_select(catid,value){
    jQuery("#site_cat_container").show();
    jQuery("#site_cat_container").html('加载中...');
    jQuery.get(ajaxurl, { action: 'gaofen_site_cat_select_html', catid:catid, value:value},
    function(data){
        //alert(data);
        if(data){
            jQuery("#site_cat_container").css('height','250px');
            jQuery("#site_cat_container").html(data);
        }else{
            jQuery("#site_cat_container").html('<font color="red">暂无栏目数据！</font><br /><a href="edit-tags.php?taxonomy=category" target="_blank">点击这里添加</a>');
        }
    });
}

    //jQuery("#<?=$id?> option:disabled").css('color', '#CCC'); 
    jQuery("#<?=$id?>").change(function(){ 
             if(this[this.selectedIndex].disabled){ 
                this.selectedIndex = this.s||0; 
             }else{ 
                this.s = this.selectedIndex||0; 
             } 
    });
</script> 
<style>
    #site_cat_container li:hover{background-color: Highlight;color: HighlightText;}
    #selected_cat_container{padding-left:15px;background-color: lightYellow;border:1px #E6DB55 solid;border-bottom-left-radius: 3px 3px;border-bottom-right-radius: 3px 3px;border-top-left-radius: 3px 3px;border-top-right-radius: 3px 3px;padding:0.6em 1em;margin-left:0px;}
    #selected_cat_container span{clear:both;}
</style>
<?php
}

//ajax返回分站栏目选择的HTML
add_action('wp_ajax_gaofen_site_cat_select_html', 'gaofen_site_cat_select_html');
function gaofen_site_cat_select_html() {
   $catid = intval($_GET['catid']);
   $value = trim($_GET['value']);
    if(function_exists('_output_site_cat_list_select_html')){
        echo _output_site_cat_list_select_html($catid,$value);
    }
    exit;
}


//添加属性（flag、来源地址、转向地址、副标题）meta box到文章编辑页 
add_action("admin_menu", "gaofen_post_att_option");
function gaofen_post_att_option(){
    add_meta_box( "gaofen_box", "文章属性", "gaofen_post_att_display", "post", "normal", "low" );
    add_meta_box( "gaofen_box", "推荐属性", "gaofen_tiku_att_display", "tiku", "normal", "low" );
    remove_meta_box('postcustom','post','normal');//去掉自定义字段框
    remove_meta_box('commentsdiv','post','normal');//去掉评论框
}
//显示文章属性项
function gaofen_post_att_display(){
    global $post,$gaofen_post_flags;
    
    //分站无法使用‘首页’两个属性
    if(wp_get_current_user()->siteid){
        unset($gaofen_post_flags['c']);
    }
    
    $post_id = $post->ID;
    $myflags = get_post_meta($post_id,'_flag');
    if($myflags && !is_array($myflags)) $myflags = array($myflags);
    
    $shorttitle = get_post_meta($post_id,'shorttitle',true);
    $redirecturl = get_post_meta($post_id,'redirecturl',true);
    $myfromsitename = get_post_meta($post_id,'from_site_name',true);
    $myfromsiteurl = get_post_meta($post_id,'from_site_url',true);

    echo "<p style='margin:5px;'>简略标题：<input type='text' name='shorttitle' value='$shorttitle' size='50' /></p>";

    echo "<p style='margin:5px;'>跳转地址：<input type='text' name='redirecturl' value='$redirecturl' size='80'  /> 如果不为空，则直接转向该地址。</p>";

    echo "<p style='margin:5px;'>文章来源：网站名<input type='text' name='from_site_name' value='$myfromsitename' />网站地址<input type='text' name='from_site_url' value='$myfromsiteurl' size='40'/></p>";

    echo "<p style='margin:5px;'>推荐属性：";
    
    //分站无法使用‘首页’两个属性 加入隐藏域
    if(wp_get_current_user()->siteid){
        if(in_array('c',$myflags)) echo "<input type='hidden' name='flag[]' value='c' />";
    }
    $str = '';
    foreach($gaofen_post_flags AS $k=>$v){
        $str .= "<label style='margin-right:10px;'><input type='checkbox' name='flag[]' value='$k' ";
        $str .=  in_array($k,$myflags) ? ' checked' : '';
        $str .=  " /> $v($k)</label> ";
    }
    echo "$str</p>";
}
//显示试题属性项
function gaofen_tiku_att_display(){
    global $post,$gaofen_post_flags;
    $post_id = $post->ID;
    $myflags = get_post_meta($post_id,'_flag');

    echo "<p style='margin:5px;'>推荐属性：";
    $str = '';
    foreach($gaofen_post_flags AS $k=>$v){
        $str .= "<label style='margin-right:10px;'><input type='checkbox' name='flag[]' value='$k' ";
        $str .=  in_array($k,$myflags) ? ' checked' : '';
        $str .=  " /> $v($k)</label> ";
    }
    echo "$str</p>";
}
//添加文章时是否跳到添加页  Silen
function gaofen_post_add_once(){
	echo '<input type="checkbox" name="isnot_add" id="yes_add" onclick="gaofen_isnot_add_cookie(this)" value="once" /> 添加';
	//add_option('once', $value = '测试', $deprecated = '', $autoload = 'yes');
}
//添加文章后 跳转到所选栏目的发布页 优先级设置为最低，以免出现还没处理其它信息（flag之类）就直接跳转了
add_action('save_post', 'gaofen_goto_newpost_with_cat',9999);
function gaofen_goto_newpost_with_cat($post_id){
	global $wpdb;
    $catid = intval($_POST['post_category'][0]);
	$cat = $wpdb->get_row("SELECT * FROM `wp_terms` where term_id = '$catid'",ARRAY_A);
	
	//判断跳转时是否跳转到添加页面 Silen
	if($_POST['isnot_add'] == 'once'){
		$url = 'post-new.php';	
	}else{
		$url = 'edit.php?category_name='.$cat['slug'].'&post_type=post';
	} 
	//题库不跳转 编辑文章时不跳转
	if($catid && $_REQUEST['post_type'] != 'tiku' && $_GET['action'] != 'edit'){
		wp_redirect($url);
		exit;
	}
}


//保存文章属性到meta
add_action('save_post', 'gaofen_post_att_save');
function gaofen_post_att_save($post_id){
    global $gaofen_post_flags;
    
    //存在$_POST时执行，否则在定时发布 会出现文章属性失效的情况
    if(!$_POST) return;
    //快速编辑时 不处理文章属性
    if($_POST['action']=='inline-save') return ;
    
    $flag = $_POST['flag'];
    $shorttitle = $_POST['shorttitle'];
    $redirecturl = $_POST['redirecturl'];
    $from_site_name = $_POST['from_site_name'];
    $from_site_url = $_POST['from_site_url'];
    //print_r($_POST);exit;
    
    delete_post_meta($post_id, '_flag');
    if($flag){
        foreach($gaofen_post_flags AS $k=>$v){
            if(in_array($k,$flag)) add_post_meta($post_id, '_flag', $k);
        }
    }
    if($shorttitle) update_post_meta($post_id, 'shorttitle', $shorttitle);
    if($redirecturl) update_post_meta($post_id, 'redirecturl', $redirecturl);
    if($from_site_name) update_post_meta($post_id, 'from_site_name', $from_site_name);
    if($from_site_url) update_post_meta($post_id, 'from_site_url', $from_site_url);
}


//快速编辑 增加flag属性  此项与all-in-one-seo有冲突 会把此项重复四次 暂时去掉
//add_action('quick_edit_custom_box', 'flag_quickedit_box');
function flag_quickedit_box($fieldname) {
    global $gaofen_post_flags;
?>
<fieldset class="inline-edit-col-right">
    <div class="inline-edit-col">
		<b class="title">推荐属性（重新设置）</b>
        <?php
    //$post_id = $post->ID;
    //$myflags = get_post_meta($post_id,'_flag');
    $str = '';
    foreach($gaofen_post_flags AS $k=>$v){
        $str .= "<label style='margin-right:10px;float:left;'><input type='checkbox' name='flag[]' value='$k' ";
        //$str .=  in_array($k,$myflags) ? ' checked' : '';
        $str .=  " /> $v ($k)</label> ";
    }
    echo "<p style='margin:5px;'>$str</p>";
        ?>
	</div>
</fieldset>
<?php
}

//编辑文章时，在标题栏上面加入栏目导航
add_action( 'admin_notices', 'gaofen_edit_post_catlink' );
add_action( 'admin_head', 'gaofen_edit_post_catlink_css' );
function gaofen_edit_post_catlink(){
    $post_id = $_GET['post'];
    if($_SERVER['SCRIPT_NAME']=='/wp-admin/post.php' && $post_id && $_GET['action']=='edit'){
        add_action( 'category_link', 'gaofen_edit_post_catlink_filter',10,2 );
        echo "<p id='gaofen_edit_post_catlink'>";
        the_category(' &gt; ', 'multiple',$post_id);
        echo "</p>";
    }
}
//修改栏目链接为后台文章列表 指定栏目
function gaofen_edit_post_catlink_filter($link,$term_id){
    if($term_id == 3) return "edit.php?post_type=tiku&cat=".$term_id;
    return "edit.php?cat=".$term_id;
}
function gaofen_edit_post_catlink_css() {
    echo "
    <style type='text/css'>
    #gaofen_edit_post_catlink {
        position:absolute;
        margin: 18px 0 0 220px;
        font-size: 11px;
    }
    </style>
    ";
}


//增加文章列表栏
//add_action('load-edit.php','gaofen_flag_columns',1);
add_action('init','gaofen_flag_columns',1);//用init代替load-edit.php 解决快速编辑提交时 增加的栏也能显示
function gaofen_flag_columns(){
    global $gaofen_post_flags;
    $posttypecolumns = array('post','page');
	if ( !isset($_GET['post_type']) ) $post_type = 'post';
		else    $post_type = $_GET['post_type'];
		if(in_array($post_type,$posttypecolumns)) {
			if($post_type != 'page'){
				add_action('manage_posts_custom_column', 'gaofen_mrt_pccolumn', 10, 2);
				add_filter('manage_posts_columns', 'gaofen_filter_pcolumns');
			}
		}
}
//需要增加的栏
function gaofen_filter_pcolumns($c) {
    $c['flagname'] = '推荐属性';
    return $c;
}
//输出flagname
function gaofen_mrt_pccolumn($c, $post_id) {
    global $gaofen_post_flags;
    if( $c == 'flagname' ) {
        $f = get_post_meta($post_id,'_flag');//第三个参数默认为false，返回array，ture则返回第一个 string
        if(is_array($f)){
            foreach($f AS $v){
                if(in_array($v,$f)) echo '<font color=red>'.$gaofen_post_flags[$v].'</font> ';
            }
        }
    }
}




//用户资料页加上分站管理 也可以用personal_options显示在个人选项 personal_options_update更新
add_action('show_user_profile','gaofen_editor_perm_form');
add_action('edit_user_profile','gaofen_editor_perm_form');
function gaofen_editor_perm_form($profileuser){
    global $gaofen_sites,$current_user;
    $user_id = $profileuser->ID;
    $editor_category = get_user_meta($user_id,'editor_category',true);

    //当前登录用户是否为管理员
    $cu_role_is_ok = $current_user->caps['administrator'] && in_array('administrator',$current_user->roles);
    //被编辑用户是否为管理员或者编辑
    $role_is_ok = in_array('administrator',$profileuser->roles) || in_array('editor',$profileuser->roles);

    //只有超级管理员能修改此项 被编辑用户必须是管理员或者编辑
    if($cu_role_is_ok && $role_is_ok && !$current_user->siteid):

?>
<table class="form-table">
    <tr>
        <th>管理栏目</th>
        <td>
        <p>可站ctrl或shift多选，选取子栏目时，其父栏目也要同时选取。</p>
<?php
        echo "<select name='editor_category[]' multiple style='height:450px;width:350px;'>";
        echo "<option value=''>所有</option>";
        echo _output_subcategory_select_option(0,$editor_category,array('disable_parents'=>false));//不屏蔽父级栏目
        echo "</select>";
    ?>
        </td>
    </tr>
</table>
<?php
    endif;
}


//用户资料提交处理
add_action('personal_options_update','gaofen_editor_perm_update');
add_action('edit_user_profile_update','gaofen_editor_perm_update');
function gaofen_editor_perm_update($user_id){
    $editor_category = $_POST['editor_category'];
    if($editor_category){
        update_user_meta($user_id,'editor_category',$editor_category);
    }else{
        //为0或空 表示广州主站 删除meta
        delete_user_meta( $user_id, 'editor_category' );
    }
}



//取消插件升级的提示 wp-includes/functions.php 3863 && wp-includes/update.php 185
add_filter('site_transient_update_plugins','gaofen_disable_plugin_upgrade');
function gaofen_disable_plugin_upgrade($param){
    return ;
}

//取消编辑插件功能 wp-admin\includes\class-wp-plugins-list-table.php 386
add_filter('plugin_action_links','gaofen_disable_plugin_editor');
function gaofen_disable_plugin_editor($actions){
    unset($actions['edit']);
    return $actions;
}

//禁止批量编辑文章
add_filter('bulk_actions-edit-post','gaofen_disable_edit_post_batch');
function gaofen_disable_edit_post_batch($actions){
    unset($actions['edit']);
    return $actions;
}

//添加文章后的“查看文章”链接 修改为新窗口打开 wp-admin\edit-form-advanced.php 45 67
add_filter('post_updated_messages','gaofen_post_saved_link_newwin');
function gaofen_post_saved_link_newwin($messages){
    $msg = $messages['post'][6];
    $msg = str_replace('<a','<a target="_blank" ',$msg);
    $messages['post'][6] = $msg;
    return $messages;
}

//设置新后台用到的cookie
add_action( 'admin_init', 'goto_editor_cookie' , 0);
function goto_editor_cookie(){
    global $current_user;
        if(in_array('editor',$current_user->roles) || in_array('administrator',$current_user->roles)){
	    setcookie( 'editor_login', $current_user->data->user_login, time()+3600*24*30, SITECOOKIEPATH, COOKIE_DOMAIN );
        }
}
//提示新后台
add_action( 'admin_head', 'goto_editor_panel' , 0);
function goto_editor_panel(){
    global $current_user;
        if(in_array('editor',$current_user->roles) || in_array('administrator',$current_user->roles)){
            //$new_editor_notice = '<a href="http://cp.gaofen.com/" target="_blank" style="color:red;">全新的编辑后台正在测试，欢迎！http://cp.gaofen.com/</a>';
            //echo "<p style='position:absolute;margin:10px 0 0 800px;font-size: 12px;'>$new_editor_notice</p>";
        }
}



//取消文章作者链接
add_filter('the_author_posts_link', 'gaofen_disable_author_link');
function gaofen_disable_author_link($link){
    return strip_tags($link);
}

//去掉页面head中不需要的项
remove_action( 'init', '_wp_admin_bar_init' );
remove_action( 'wp_head',  'feed_links', 2);
remove_action( 'wp_head',  'feed_links_extra', 3);
remove_action( 'wp_head',  'rsd_link' );
remove_action( 'wp_head',  'wlwmanifest_link');
remove_action( 'wp_head',  'wp_generator');

/*
* app.gaofen.com跳转到www.gaofen.com
*/
//add_action('init', 'gaofen_fuck_url',0);
function gaofen_fuck_url() {
    global $topcatarr,$blogurl;

    $uriarr = explode('/',getenv( 'REQUEST_URI' ));
    $hostarr = explode('.',$_SERVER['HTTP_HOST']);

    //所有不是gaofen的域名 跳到403
    if($hostarr[1] != 'gaofen' && COOKIE_DOMAIN != '.wp.com'){
        header("HTTP/1.0 403 Access Forbidden");
        exit;
    }
    //跳转首页到www
    if(($hostarr[0] == 'app' || $hostarr[0] == 'home') && !$uriarr[1]){
        wp_redirect( $blogurl, 301 );
        exit;
    }

}

//获取某文章所属的主站栏目id
function get_mainsite_catid($post_id, $fortpl = true){
    global $gaofen_sites;
    $catarr = wp_get_post_categories($post_id);//array
    foreach((array)$catarr AS $catid){
            $_topcat = get_topmost_cat($catid);
            //fuck old version, for template
            if($fortpl && in_array($_topcat->slug, array('yuer2','xsc2','zhongkao2','gaokao2','liuxue2'))){
                continue;
            }
            //如果栏目的顶级栏目不属于分站  就是主站频道
            if(!in_array($_topcat->term_id, $gaofen_sites)){
                return $catid;
            }
    }
    return $catarr[0];
}


//frontpage params  index.php?gaofen_action=xxx
add_filter('query_vars', 'gaofen_query_vars');
function gaofen_query_vars( $qvars ) {
    $qvars[] = 'mybase64';//用于文章id base64处理
    $qvars[] = 'gaofen_action';
    $qvars[] = 'gaofen_update';
    $qvars[] = 'gaofen_page';//重要 用于名师库和学校库等 URI参数传递
    $qvars[] = 'q';//综合搜索
    return $qvars;
}

//request params from index.php
add_action('parse_request', 'gaofen_parse_request');
function gaofen_parse_request($wp) {
    //print_r($wp->query_vars); //$wp->query_vars['gaofen_action'];
    if (array_key_exists('gaofen_action', $wp->query_vars)  && $wp->query_vars['gaofen_action'] == 'ajax-handler') {
        // process the request.
        //echo $wp->query_vars['gaofen_action'];
    }
}

//数据库升级函数，手动 index.php?gaofen_update=1
add_action('parse_request', 'gaofen_update');
function gaofen_update($params){
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sql = "ALTER TABLE $wpdb->t ADD `hits` int(8) NOT NULL DEFAULT '0';";
    if($params->query_vars['gaofen_update']){
        if($wpdb->query($sql)){
            echo '数据库升级成功！';
        }else{
            echo '升级失败！';
        }
        exit;
    }
}

//安装插件
register_activation_hook(plugin_basename(__FILE__),'gaofen_install'); 
function gaofen_install () {
    global $wpdb;
    $district = $wpdb->district;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    if($wpdb->get_var("show tables like '$district'") != $district) {
        $sql = str_replace('dzx_common_district', $district, file_get_contents(G_INC_PATH.'gaofen_district.sql'));
        dbDelta($sql);
    }

}

//卸载
register_deactivation_hook(plugin_basename(__FILE__),'gaofen_uninstall'); 
function gaofen_uninstall() {
	global $wpdb;
        $table = $wpdb->district;
	// first remove all tables
	$wpdb->query("DROP TABLE IF EXISTS $table");
}
?>