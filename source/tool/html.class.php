<?php
class html
{
    var $url;

    function __construct()
    {
		$this->url = load('url.class.php');
		if(!defined('CREATEHTML')) define('CREATEHTML', 1);
    }

	function html()
	{
		$this->__construct();
	}

        //首页生成
	function index($areacode)
	{
		global $DC;
		//if(!$DC['html']) return true;
		extract($GLOBALS, EXTR_SKIP);
              $adminset_citycode = $areacode;
              require 'subsite.php';

		$head['title'] = areaname($areacode).'_'.$DC['sitename'].'-'.$DC['meta_title'];
		$head['keywords'] = $DC['meta_keywords'];
		$head['description'] = $DC['meta_description'];
		ob_start();
		include template("index",'dc','main','default');
		$file = DC_ROOT.$this->url->index($areacode);
		return createhtml($file);
	}

        //文章列表页生成
	function cat($catcode, $page = 0, $areacode)
	{
            global $CAT;
		extract($GLOBALS, EXTR_SKIP);
		if(!isset($CAT[$catcode])) return false;
              $cat = $catcode;
              $c = load('content.class.php','dearcms','inc/admin');

              $C = $CAT[$catcode];
              $setting = string2array($CAT[$catcode]['setting']);
              extract($C);extract($setting);
              $adminset_citycode = $areacode;
              require 'subsite.php';
              
            //信息列表
            $infos = $c->listinfo(" `catcode` LIKE '$cat%' AND `areacode` LIKE '$areacode%' AND `status`=99 ", '`listorder` DESC,`contentid` DESC', $page);
            $pages = $c->pages;
        
              $head['title'] = $catname.'-'.($title ? $title : $DC['sitename']);
		$head['keywords'] = $keywords;
		$head['description'] = $description;

		$template = $template ? $template : 'list';
		$file_a = $this->url->cat($catcode, $page, $areacode);
		$file = DC_ROOT.$file_a;
		ob_start();
		include template($template,'dc','main','default');
		return createhtml($file);
	}


        //文章内容页生成
	function show($contentid, $is_update_related = 0)
	{
		global $MODEL,$CAT;
		extract($GLOBALS, EXTR_SKIP);
		require_once CACHE_MODEL_PATH.'content_output.class.php';
		$c = load('content.class.php','dearcms','inc/admin');
		$out = new content_output();

		$r = $c->get($contentid);
		if(!$r) return false;
		if($r['catcode']) $catcode = $r['catcode'];
              $C = $CAT[$catcode];
              $cat = $catcode;


		if($is_update_related)
		{
			$pages = 5;
			foreach($CAT as $k=>$v)
			{
                        if(substr($k,0,2) == substr($catcode,0,2)){  //更新栏目页
				if($k == 0) continue;
			       $this->index($k);
				for($i=0; $i<=$pages; $i++)
				{
					$this->cat($k, $i);
				}
                        }
			}
		}
        
		if($r['status'] != 99) return true;
		$show_url_path = $this->url->show($r['contentid']);
		$data = $out->get($r);
              extract($data);

              $adminset_citycode = $r[areacode];
              require 'subsite.php';

		$template = $template ? $template : 'show';
		$head['keywords'] = str_replace(' ', ',', $r['keywords']);
		$head['description'] = $r['description'];

		$page = $page ? $page : 1;
		$head['title'] = $title.'_'.$C['catname'].'_'.$DC['sitename'];
		$file = DC_ROOT.$show_url_path;
		ob_start();
        
		include template($template,'dc','main','default');
	       return createhtml($file);
	}



        //商铺列表页生成
	function shoplist($catcode, $page = 0,$areacode)
	{
            global $CAT;
		extract($GLOBALS, EXTR_SKIP);
		if(!isset($CAT[$catcode])) return false;
              $cat = $catcode;
              require 'commonlist.php';
              $shop = load('shop.class.php','shop');
              $catclass = load('cat.class.php');
              $catlen = strlen($cat);
              
            //$catsub 下级栏目
            foreach($CAT AS $k=>$r){
                if((strlen($k)==$catlen+2) && (substr($k,0,$catlen)==$cat)){
                    $_catsub['catcode'] = $r[catcode];
                    $_catsub['catname'] = $r[catname];
                    $_catsub['items'] = $r[items];
                    $catsub[] = $_catsub;
                }
            }

            $page = $page ? $page : 1;
            $pagesize = $DC[num_shop] ? $DC[num_shop] : 10;
            $shoplist = $shop->listinfo(" status=1 AND `areacode` LIKE '$areacode%' AND `catcode` LIKE '$catcode%' ", 'id DESC', $page,$pagesize);
            $pages = $shop->pages;

              $adminset_citycode = $areacode;
              require 'subsite.php';
              
            $head['title'] = catname($cat).'_'.$DC['sitename'];
            $head['keywords'] = catname($cat).','.$DC['sitename'];
            
	       $file_a = $this->url->cat($catcode, $page, $areacode);
		$file = DC_ROOT.$file_a;
		ob_start();
		include template('list','shop');
		return createhtml($file);
	}



        //商铺详细页生成
	function shopshow($id, $is_update_related = 0)
	{

		global $CAT;
		extract($GLOBALS, EXTR_SKIP);
              require_once CACHE_MODEL_PATH.'shop_output.class.php';
		if(!is_a($shop, 'shop'))
		{
			$shop = load('shop.class.php','shop');
		}
             $out = new shop_output();
		$info = $shop->get($id);
              $info['modelid'] = 11; //商铺模型ID

		if(!$info) return false;
		if($info['catcode']) $catcode = $info['catcode'];
              $cat = $catcode;
              $catlen = strlen($cat);
              $C = $CAT[$catcode];

              $adminset_citycode = $info[areacode];
              require 'subsite.php';
              
		if($is_update_related)
		{
			$pages = 5;
			foreach($CAT as $k=>$v)
			{
                        if(substr($k,0,2) == substr($catcode,0,2)){  //更新栏目页
				if($k == 0) continue;
			       $this->index($k);
				for($i=0; $i<=$pages; $i++)
				{
					$this->shoplist($k, $i);
				}
                        }
			}
		}

		if($info['status'] != 1) return true;

    //商铺数据调用 start
		$data = $out->get($info);
		extract($data);
                $seo = string2array($info[setting]);
                 // 商铺风格路径
                $template = $template ? $template : 'default'; 
                //栏目
                $C = $CAT[$cat];
                $CS = getcatset($cat);
                $sorts = getcatset($cat,'sorts');
                $suitplaces = getcatset($cat,'suitplaces');

$attachment = load('attachment.class.php');
$_img = $attachment->get($info['aid']);
$shopimg = 'uploadfile/'.$_img['filepath'];


                $head['title'] = $shopname.'_'.$seo[title].'_'.$DC['sitename'];
                $head['keywords'] = $shopname.','.$seo[keywords].','.$DC['sitename'];
                $head['description'] = $seo[description].$M['name'].'_'.$M['seo_description'].'_'.$DC['sitename'];
    //商铺数据调用 end
			$page = $page ? $page : 1;
			$title = strip_tags($title);
			$shopurl = $this->url->shopshow($info['id']);
			$file = DC_ROOT.$shopurl;
			ob_start();

		       include template('shop','shop','shop',$template);
			return createhtml($file);
	}


	function delete($contentid, $table)
	{
		global $db;
		$contentid = intval($contentid);
		if(!$contentid) return FALSE;
		$r = $db->get_one("SELECT * FROM `".DB_PRE."content` c, `$table` b WHERE c.contentid=b.contentid AND c.`contentid`=$contentid");
	       $fileurl = $this->url->show($contentid);
		@unlink(DC_ROOT.$fileurl);
		return TRUE;
	}
    
	function deleteshop($shopid)
	{
		global $db;
		$shopid = intval($shopid);
		if(!$shopid) return FALSE;
	       $fileurl = $this->url->shopshow($shopid);
		@unlink(DC_ROOT.$fileurl);
		return TRUE;
	}
}
?>