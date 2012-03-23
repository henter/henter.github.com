<?php 
defined('IN_DC') or exit('Access Denied');
$u = load('url.class.php');

$submenu = array(
	array('更新内容URL', "?file=$file&op=content"),
	array('更新商铺URL', "?file=$file&op=shop"),
	array('更新栏目URL', "?file=$file&op=cat"),
);
$menu = admin_menu('更新URL', $submenu);
$op = $op ? $op : 'content';

switch($action)
{
    case 'index':
        $filesize = $html->index();
	    showmessage('网站首页更新成功！<br />大小：'.sizecount($filesize));
    break;

    case 'content' :
        if($dosubmit)
        {
        	if($type == 'lastinput')
        	{
        		$offset = 0;
        	}
        	else
        	{
        		$page = max(intval($page), 1);
        		$offset = $pagesize*($page-1);
        	}
        	$where = ' WHERE status=99 ';
        	$order = 'ASC';
        	if($catcode) $where .= " AND `catcode` LIKE '$catcode%'  ";
        	
        	if(!isset($firest)){
        		$firest = 1;
              }else{
        		$firest = 0;
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

        	$data = $db->select("SELECT `contentid`, `url` FROM `".DB_PRE."content` $where ORDER BY `contentid` $order LIMIT $offset,$pagesize");
        	foreach($data as $r)
        	{
        		$url = $u->show($r['contentid']);
        		$u->updateurl($r['contentid'],$url);
        	}
        	if($pages > $page)
        	{
        		$page++;
        		$creatednum = $offset + count($data);
        		$percent = round($creatednum/$total, 2)*100;
        		$message = "共需更新 <font color='red'>$total</font> 条信息<br />已完成 <font color='red'>{$creatednum}</font> 条（<font color='red'>{$percent}%</font>）";
        		$forward = $start ? "?file=url&type=$type&dosubmit=1&firest=$firest&action=$action&fromid=$fromid&toid=$toid&fromdate=$fromdate&todate=$todate&pagesize=$pagesize&page=$page&pages=$pages&total=$total" : preg_replace("/&page=([0-9]+)&pages=([0-9]+)&total=([0-9]+)/", "&page=$page&pages=$pages&total=$total", URL);;
        	}
        	else
        	{
        		$message = "内容URL更新完成！";
        		$forward = '?file=url';
        	}
        	showmessage($message, $forward);
        }
    break;


    case 'shop' :
        if($dosubmit)
        {
        	if($type == 'lastinput')
        	{
        		$offset = 0;
        	}
        	else
        	{
        		$page = max(intval($page), 1);
        		$offset = $pagesize*($page-1);
        	}
        	$where = ' WHERE status=1 ';
        	$order = 'ASC';
        	if($catcode) $where .= " AND `catcode` LIKE '$catcode%'  ";
        	
        	if(!isset($firest)){
        		$firest = 1;
              }else{
        		$firest = 0;
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

        	$data = $db->select("SELECT `id`, `url` FROM `".DB_PRE."shop` $where ORDER BY `id` $order LIMIT $offset,$pagesize");
        	foreach($data as $r)
        	{
        		$url = $u->shopshow($r['id']);
        		$u->updateshopurl($r['id'],$url);
        	}
        	if($pages > $page)
        	{
        		$page++;
        		$creatednum = $offset + count($data);
        		$percent = round($creatednum/$total, 2)*100;
        		$message = "共需更新 <font color='red'>$total</font> 条信息<br />已完成 <font color='red'>{$creatednum}</font> 条（<font color='red'>{$percent}%</font>）";
        		$forward = $start ? "?file=url&type=$type&dosubmit=1&firest=$firest&action=$action&fromid=$fromid&toid=$toid&fromdate=$fromdate&todate=$todate&pagesize=$pagesize&page=$page&pages=$pages&total=$total" : preg_replace("/&page=([0-9]+)&pages=([0-9]+)&total=([0-9]+)/", "&page=$page&pages=$pages&total=$total", URL);;
        	}
        	else
        	{

        		$message = "商铺URL更新完成！";
        		$forward = '?file=url';
        	}
        	showmessage($message, $forward);
        }
    break;
    

    //栏目
    case 'cat':
		if($dosubmit)
		{
                     $where = $catcode ? " WHERE `catcode` LIKE '$catcode%' " : '';
                	$data = $db->select("SELECT `catcode`, `url` FROM `".DB_PRE."cat` $where");
                	foreach($data as $r)
                	{
                		//$url = $u->cat($r['catcode'],1);
                            $catcode = $r['catcode'];
                            $urlrule = $URLRULE['list'][3];
                            eval("\$url = \"$urlrule\";");
                		$u->updatecaturl($r['catcode'],$url);
                	}
                    cache_cat();
                    $message = "栏目URL更新完成！";
                    $forward = '?file=url&op=cat';
                    showmessage($message, $forward);
		}
		else
		{
			include atpl('html');
                }
		break;

    
    default :
        include atpl('url');
}




?>