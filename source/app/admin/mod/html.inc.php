<?php 
defined('IN_DC') or exit('Access Denied');
define('CREATCATHTML',TRUE);

@set_time_limit(600);
$areaclass = load('area.class.php');
$html = load('html.class.php');
$u = load('url.class.php');

$submenu = array(
	array('生成首页', "?file=html&op=index"),
	array('生成文章栏目', "?file=html&op=cat"),
	array('生成文章内容', "?file=html&op=show"),
	array('生成商铺栏目', "?file=html&op=shoplist"),
	array('生成商铺页', "?file=html&op=shopshow"),
	array('删除所有静态文件', "javascript:confirmurl(\"?file=$file&action=deleteall\", \"此操作将删除所有静态页面，确定吗？\")"),
    
);
$menu = admin_menu('生成静态', $submenu);
$op = $op ? $op : 'cat';

$notcomplete = cache_read('html_areas.php');


switch($action)
{
    case 'index':
        if($dosubmit){
                if(!$areacode){
                    if(!$nextarea){
                        foreach($AREA as $k=>$id)
                        {
                            if(strlen($k)==2) $areas[] = $k;
                        }
                        if($areas) cache_write('html_indexareas.php', $areas);
                    }
                   $areas = cache_read('html_indexareas.php');
                   $area = array_shift($areas);
                   cache_write('html_indexareas.php', $areas);
                   $area or showmessage('总算生成完了！ = =!', "?file=$file");
                   
                   $areaname = areaname($area);
                    foreach($AREA as $k=>$id)
                    {
                        if(substr($k,0,strlen($area)) == $area){
                            $html->index($k);
                        }
                    }
                    showmessage("<font color='red'>$areaname</font>  地区更新成功，继续下一个地区...","?file=$file&action=$action&nextarea=1&dosubmit=1");
                
                }else{
                    $areaname = areaname($areacode);
                    foreach($AREA as $k=>$id)
                    {
                        if(substr($k,0,strlen($areacode)) == $areacode){
                            $html->index($k);
                        }
                    }
                    showmessage("$areaname 首页更新成功！", "?file=$file");
                }
        }else{
                include atpl('html');
        }
    break;



    case '_area':
            $module or showmessage('模块参数错误！');
            $params = "page=$page&pages=$pages&total=$total";
            if($creatarea){
                cache_delete('html_areas.php');
                cache_delete('html_cats.php');
                creat_area_cache($select_areacode);
                set_cookie('selectcatcode',$select_catcode);
                $creatcat = 1;
            }

            if($nextcat){
                 $url = "?file=$file&action=_cat&area=$area&module=$module&nextarea=$nextarea&nextcat=$nextcat&creatcat=$creatcat&$params";
	        Header("Location: $url");
                showmessage("进入栏目",$url,200);
            }else{ //如果不是同一地区下的多个栏目生成
               $areas = cache_read('html_areas.php');
               $area = array_shift($areas);
               cache_write('html_areas.php', $areas);
           }
           
           if(!$area){
                cache_delete('html_areas.php');
                cache_delete('html_cats.php');
                showmessage('总算生成完了！ = =!', "?file=$file");
           }

          $areaname = areaname($area);

          $url = "?file=$file&action=_cat&area=$area&cat=$cat&module=$module&nextarea=$nextarea&nextcat=$nextcat&$params";
	   Header("Location: $url");
         showmessage("地区转向 $area ($areaname)",$url,200);

    break;

    case '_cat':
            $module or showmessage('模块参数错误！');
            $params = "page=$page&pages=$pages&total=$total";
            if(!file_exists(CACHE_PATH.'html_cats.php') || $creatcat) creat_cat_cache($module);

           if(!$cat || $nextcat){
               $cats = cache_read('html_cats.php');
               $cat = array_shift($cats);
               if($cats){
                    cache_write('html_cats.php', $cats);
               }else{
                    cache_delete('html_cats.php');
               }
           }

           if(!$cat && !$area){
                cache_delete('html_areas.php');
                cache_delete('html_cats.php');
                showmessage('总算生成完了！ = =!', "?file=$file");
           }

           if($module == 'dearcms'){
	        $url = "?file=$file&action=cat&cat=$cat&area=$area&nextarea=$nextarea&nextcat=$nextcat&dosubmit=1&$params";
           }elseif($module == 'shop'){
	        $url = "?file=$file&action=shoplist&cat=$cat&area=$area&nextarea=$nextarea&nextcat=$nextcat&dosubmit=1&$params";
           }

          $catname = catname($cat);
           Header("Location: $url");
           showmessage("栏目转向 $cat ($catname)",$url,200);
    break;



    //文章栏目
    case 'cat':
		if($dosubmit)
		{
                     $areacode = $area;
                     $catcode = $cat;
                     $areaname = areaname($areacode,1);
                     $catname = catname($catcode,1);

			$cids = cache_read('html_cats.php');
				$page = max(intval($page), 1);
				$offset = $pagesize*($page-1) ? $pagesize*($page-1) : 1;
				if($page == 1)
				{
					$_contents = $db->get_one("SELECT COUNT(*) AS `count` FROM `".DB_PRE."content` WHERE catcode LIKE '$catcode%' AND status=99");
                                   $contents = $_contents['count'];
					$total = ceil($contents/2);
					$pages = ceil($total/$pagesize);
				}
				$max = max($offset+$pagesize, $total);
                            $max = $max ? $max : 1;

				for($i=$offset; $i<=$max; $i++)
				{
					$html->cat($catcode, $i, $areacode);
				}

				if($pages > $page)
				{
					$page++;
					$percent = round($max/$total, 2)*100;
					$message = "正在更新 <font color='blue'>$catname</font> 栏目，共需更新 <font color='red'>$total</font> 个网页<br />已更新 <font color='red'>{$max}</font> 个网页（<font color='red'>{$percent}%</font>）";
					$forward = "?file=$file&action=_area&area=$areacode&cat=$catcode&module=dearcms&page=$page&pages=$pages&total=$total";
                                   //if(!is_ie()) Header("Location: $forward");
				}
				elseif($cids)
				{
					$message = "<font color='blue'><font color='red'>$areaname</font> $catname</font> 栏目更新完成！";
					$forward = "?file=$file&action=_area&area=$areacode&module=dearcms&nextcat=1";
                                   //if(!is_ie()) Header("Location: $forward");
				}
				else
				{
					cache_delete('html_cats.php');
					$message = "<font color='red'>$areaname</font> 更新完成，继续下一个地区...";
                                    $forward = "?file=$file&action=_area&select_catcode=$select_catcode&module=dearcms&nextarea=1";
				}

				showmessage($message, $forward, 200);
		}
		else
		{
			include atpl('html');
                }
		break;


    //文章内容
    case 'show':
		if($dosubmit)
		{

			if($type == 'lastinput')
			{
				$offset = 0;
			}
			else
			{
				$currentpage = max(intval($currentpage), 1);
				$offset = $pagesize*($currentpage-1);
			}
		    $where = ' WHERE status=99 ';
			$order = 'DESC';
			
			if(!isset($first) && $catcode > 0) 
			{
				$where .= " AND `catcode` LIKE '$catcode%'  ";
				$first = 1;
			}
			elseif($first)
			{
				$where .= " AND `catcode` LIKE '$catcode%' ";
			}
			else
			{
				$first = 0;
			}
			if($type == 'lastinput' && $number)
			{
				$offset = 0;
				$pagesize = $number;
				$order = 'DESC';
			}
			elseif($type == 'date')
			{
				if($fromdate)
				{
					$fromtime = strtotime($fromdate.' 00:00:00');
					$where .= " AND `inputtime`>=$fromtime ";
				}
				if($todate)
				{
					$totime = strtotime($todate.' 23:59:59');
					$where .= " AND `inputtime`<=$totime ";
				}
			}
			elseif($type == 'id')
			{
				$fromid = intval($fromid);
				$toid = intval($toid);
				if($fromid) $where .= " AND `contentid`>=$fromid ";
				if($toid) $where .= " AND `contentid`<=$toid ";
			}
			if(!isset($total) && $type != 'lastinput')
			{
				$totalcount = $db->get_one("SELECT COUNT(*) AS `count` FROM `".DB_PRE."content` $where");
                            $total = $totalcount['count'];
				$pages = ceil($total/$pagesize);
				$start = 1;
			}

			$data = $db->select("SELECT `contentid` FROM `".DB_PRE."content` $where ORDER BY `contentid` $order LIMIT $offset,$pagesize");

			foreach($data as $r)
			{
				$html->show($r['contentid']);
			}
			if($pages > $currentpage)
			{
				$currentpage++;
				$creatednum = $offset + count($data);
				$percent = round($creatednum/$total, 2)*100;
				$message = "共需更新 <font color='red'>$total</font> 条信息<br />已完成 <font color='red'>{$creatednum}</font> 条（<font color='red'>{$percent}%</font>）";
				$forward = $start ? "?file=html&type=$type&dosubmit=1&first=$first&action=$action&fromid=$fromid&toid=$toid&fromdate=$fromdate&todate=$todate&pagesize=$pagesize&currentpage=$currentpage&pages=$pages&total=$total" : preg_replace("/&currentpage=([0-9]+)&pages=([0-9]+)&total=([0-9]+)/", "&currentpage=$currentpage&pages=$pages&total=$total", URL);;
			}
			else
			{
				$message = "更新完成！";
				$forward = '?file=html&action=show';
			}
			showmessage($message, $forward);
		}
		else
		{
			include atpl('html');
        }
	break;



    //商铺列表
    case 'shoplist':
		if($dosubmit)
		{
                     $areacode = $area;
                     $catcode = $cat;
                     $areaname = areaname($areacode,1);
                     $catname = catname($catcode,1);

			$cids = cache_read('html_cats.php');
				$page = max(intval($page), 1);
				$offset = $pagesize*($page-1) ? $pagesize*($page-1) : 1;
				if($page == 1)
				{
					$_contents = $db->get_one("SELECT COUNT(*) AS `count` FROM `".DB_PRE."shop` WHERE catcode LIKE '$catcode%' AND status=1");
                                   $contents = $_contents['count'];
					$total = ceil($contents/$DC[num_shop]);
					$pages = ceil($total/$pagesize);
				}
				$max = max($offset+$pagesize, $total);
                            $max = $max ? $max : 1;

				for($i=$offset; $i<=$max; $i++)
				{
					$html->shoplist($catcode, $i, $areacode);
				}

				if($pages > $page)
				{
					$page++;
					$percent = round($max/$total, 2)*100;
					$message = "正在更新 <font color='blue'>$catname</font> 栏目，共需更新 <font color='red'>$total</font> 个网页<br />已更新 <font color='red'>{$max}</font> 个网页（<font color='red'>{$percent}%</font>）";
					$forward = "?file=$file&action=_area&area=$areacode&cat=$catcode&module=shop&page=$page&pages=$pages&total=$total";
                                    //if(!is_ie()) Header("Location: $forward");
				}
				elseif($cids)
				{
					$message = "<font color='blue'><font color='red'>$areaname</font> $catname</font> 栏目更新完成！";
					$forward = "?file=$file&action=_area&area=$areacode&module=shop&nextcat=1";
                                   //if(!is_ie()) Header("Location: $forward");
				}
				else
				{
					cache_delete('html_cats.php');
					$message = "<font color='red'>$areaname</font> 更新完成，继续下一个地区...";
                                    $forward = "?file=$file&action=_area&select_catcode=$select_catcode&module=shop&nextarea=1";
				}

				showmessage($message, $forward, 200);
		}
		else
		{
			include atpl('html');
                }
		break;



    //商铺内容
    case 'shopshow':
		if($dosubmit)
		{
			if($type == 'lastinput')
			{
				$offset = 0;
			}
			else
			{
				$currentpage = max(intval($currentpage), 1);
				$offset = $pagesize*($currentpage-1);
			}
		    $where = ' WHERE status=1 ';
			$order = 'DESC';
			
			if(!isset($first) && $catcode > 0) 
			{
				$where .= " AND `catcode` LIKE '$catcode%'  ";
				$first = 1;
			}
			elseif($first)
			{
				$where .= " AND `catcode` LIKE '$catcode%' ";
			}
			else
			{
				$first = 0;
			}
			if($type == 'lastinput' && $number)
			{
				$offset = 0;
				$pagesize = $number;
				$order = 'DESC';
			}
			elseif($type == 'date')
			{
				if($fromdate)
				{
					$fromtime = strtotime($fromdate.' 00:00:00');
					$where .= " AND `inputtime`>=$fromtime ";
				}
				if($todate)
				{
					$totime = strtotime($todate.' 23:59:59');
					$where .= " AND `inputtime`<=$totime ";
				}
			}
			elseif($type == 'id')
			{
				$fromid = intval($fromid);
				$toid = intval($toid);
				if($fromid) $where .= " AND `id`>=$fromid ";
				if($toid) $where .= " AND `id`<=$toid ";
			}
			if(!isset($total) && $type != 'lastinput')
			{
				$totalcount = $db->get_one("SELECT COUNT(*) AS `count` FROM `".DB_PRE."shop` $where");
                            $total = $totalcount['count'];
				$pages = ceil($total/$pagesize);
				$start = 1;
			}
			$data = $db->select("SELECT `id` FROM `".DB_PRE."shop` $where ORDER BY `id` $order LIMIT $offset,$pagesize");
			foreach($data as $r)
			{
				$html->shopshow($r['id']);
			}

			if($pages > $currentpage)
			{
				$currentpage++;
				$creatednum = $offset + count($data);
				$percent = round($creatednum/$total, 2)*100;
				$message = "共需更新 <font color='red'>$total</font> 条信息<br />已完成 <font color='red'>{$creatednum}</font> 条（<font color='red'>{$percent}%</font>）";
				$forward = $start ? "?file=html&type=$type&dosubmit=1&first=$first&action=$action&fromid=$fromid&toid=$toid&fromdate=$fromdate&todate=$todate&pagesize=$pagesize&currentpage=$currentpage&pages=$pages&total=$total" : preg_replace("/&currentpage=([0-9]+)&pages=([0-9]+)&total=([0-9]+)/", "&currentpage=$currentpage&pages=$pages&total=$total", URL);;
			}
			else
			{
				$message = "更新完成！";
				$forward = '?file=html&action=shopshow';
			}
			showmessage($message, $forward);
		}
		else
		{
			include atpl('html');
        }
	break;

        case 'deleteall' :
            dir_delete(DC_ROOT.'html/');
            showmessage('删除成功！', "?file=$file");
        break; 

        case 'huyohtml' :
            set_cookie('huyohtml',1);
            showmessage('设置成功，开始生成...', "?file=$file");
        break; 


    default :
        include atpl('html');
}


function creat_area_cache($select_areacode){
    global $AREA;
    if($select_areacode){
        foreach($AREA as $k=>$v)
        {
            if(substr($k,0,strlen($select_areacode)) == $select_areacode) $areas[] = $k;
        }
    }else{
        foreach($AREA as $k=>$v)
        {
            $areas[] = $k;
        }
    }
    if($areas) cache_write('html_areas.php', $areas);//写入缓存
    return $areas;
}

function creat_cat_cache($module){
    global $CAT;
    $select_catcode = get_cookie('selectcatcode');
    if($select_catcode){
        foreach($CAT as $k=>$v)
        {
            if(substr($k,0,strlen($select_catcode)) == $select_catcode) $cats[] = $k;
        }
    }else{
        foreach($CAT as $k=>$v)
        {
            if($v['module']==$module) $cats[] = $k;
        }
    }
    if($cats) cache_write('html_cats.php', $cats);//写入缓存
    return $cats;
}
?>