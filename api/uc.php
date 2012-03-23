<?php
define('UC_VERSION', '1.0.0');		//UCenter 版本标识

define('API_DELETEUSER', 1);		//用户删除 API 接口开关
define('API_RENAMEUSER', 1);		//用户改名 API 接口开关
define('API_UPDATEPW', 1);		//用户改密码 API 接口开关
define('API_GETTAG', 1);		//获取标签 API 接口开关
define('API_SYNLOGIN', 1);		//同步登录 API 接口开关
define('API_SYNLOGOUT', 1);		//同步登出 API 接口开关
define('API_UPDATEBADWORDS', 1);	//更新关键字列表 开关
define('API_UPDATEHOSTS', 1);		//更新域名解析缓存 开关
define('API_UPDATEAPPS', 1);		//更新应用列表 开关
define('API_UPDATECLIENT', 1);		//更新客户端缓存 开关
define('API_UPDATECREDIT', 1);		//更新用户积分 开关
define('API_GETCREDITSETTINGS', 1);	//向 UCenter 提供积分设置 开关
define('API_UPDATECREDITSETTINGS', 1);	//更新应用积分设置 开关

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');

require '../inc/common.inc.php';
$member = load('member.class.php','member');
require_once DC_ROOT.'member/api/member_api.class.php';
$member_api = new member_api();

parse_str(uc_authcode($code, 'DECODE', UC_KEY), $arr) ;

//print_r($arr);exit;






//if(TIME - intval($arr['time']) > 3600) 	exit('Authracation has expiried');
if(empty($arr)) exit('Invalid Request');

$action = $arr['action'];
if ($action=='test') exit('1');

if ($action=='deleteuser')
{
	exit('Authracation has expiried');
}
if($action=='updatepw')
{
	!API_UPDATEPW && exit(API_RETURN_FORBIDDEN);
	//更改用户密码
	exit(API_RETURN_SUCCEED);
}

if($action == 'synlogin')
{
	$userid = $member->get_userid($arr['username']);
	$userinfo = $member->get($userid);
	if(!$userinfo){
		$uc_userinfo=uc_get_user($arr['username']);
		if($uc_userinfo[0]>0){
            
                    //检测userid是否冲突
	            $userid_exist = $member->get($uc_userinfo[0]);
                    if(!$userid_exist){
                        $arr_member['userid'] = $uc_userinfo[0];
                    }
                    //
                    
			$arr_member['touserid'] = $uc_userinfo[0];
			$arr_member['username'] = $uc_userinfo[1];
			$arr_member['password'] = md5($password) ;
			$arr_member['email'] = $uc_userinfo[2];
        		$arr_member['modelid'] = 10;
        		$arr_member['groupid'] = 1;


		      $userid = $member_api->add($arr_member);
                    //$userid = $member->register($arr_member);
			$userinfo = $member->get($userid);
		}
	}
    
   
    //print_r($userinfo);exit;

    if(!$userinfo){exit(0);}
    extract($userinfo);
    header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
    set_cookie('auth', uc_authcode($userid."\t".$username, 'ENCODE'));

    //set_cookie('username', $member->escape($username));
    //set_cookie('userid', $userid);

    exit('1');
}

if($action=='synlogout')
{
    header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
    set_cookie('auth', '');
    set_cookie('userid', '');
    set_cookie('username', '');
    exit('1');
}

if($action == 'getcreditsettings') 
{
	!API_GETCREDITSETTINGS && exit(API_RETURN_FORBIDDEN);
	$credits = array(
        1 => array('点数', '点'),
        2 => array('金钱', '元'),
	);
	echo uc_serialize($credits);
}

if($action == 'updateapps') 
{
	if(!API_UPDATEAPPS) {
		return API_RETURN_FORBIDDEN;
	}
	include_once H_ROOT.'uc_client/lib/xml.class.php';
	$post = xml_unserialize(file_get_contents('php://input'));

	$cachefile = H_ROOT.'uc_client/data/cache/apps.php';
	$fp = fopen($cachefile, 'w');
	$s = "<?php\r\n";
	$s .= '$_CACHE[\'apps\'] = '.var_export($post, TRUE).";\r\n";
	fwrite($fp, $s);
	fclose($fp);
	exit(API_RETURN_SUCCEED);
}

if($action == 'updatecreditsettings') {

	!API_UPDATECREDITSETTINGS && exit(API_RETURN_FORBIDDEN);
	$outextcredits = array();
	foreach($arr['credit'] as $appid => $credititems) {
		if($appid == UC_APPID) {
			foreach($credititems as $value) {
				$outextcredits[$value['appiddesc'].'|'.$value['creditdesc']] = array(
					'creditsrc' => $value['creditsrc'],
					'title' => $value['title'],
					'unit' => $value['unit'],
					'ratio' => $value['ratio']
				);
			}
		}
	}
	cache_write('creditsettings.php', $outextcredits);

	exit('1');

}
?>