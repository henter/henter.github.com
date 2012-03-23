<?php
defined('IN_DC') or exit('Access Denied');




switch($action)
{
        case 'save':
            if($dosubmit){
                $default_areacode = isset($areaselect) ? $default_areacode : 0;
                $default_catcode = isset($catselect) ? $default_catcode : 0;
                $setting['default_areacode'] = $default_areacode;
                $setting['default_catcode'] = $default_catcode;
                module_setting('dearcms', $setting);
                //将UCENTER项写入配置文件
                $uconfig = array(
                        'UC_APPID'=>$uconfig['appid'],
                        'UC_KEY'=>$uconfig['key'],
                        'UC_API'=>$uconfig['api'],
                        'UC_IP'=>$uconfig['ip'],
                        'UC_CONNECT'=>$uconfig['connect'],
                        'UC_DBHOST'=>$uconfig['dbhost'],
                        'UC_DBUSER'=>$uconfig['dbuser'],
                        'UC_DBPW'=>$uconfig['dbpw'],
                        'UC_DBNAME'=>$uconfig['dbname'],
                        'UC_DBCHARSET'=>$uconfig['dbcharset'],
                        'UC_DBTABLEPRE'=>$uconfig['dbname'].'.'.$uconfig['dbtablepre'],
            		);
                    set_config($uconfig);
                    cache_common();
                    showmessage("网站配置成功！");
            }
        break;



        case 'test_mail':
            require_once 'sendmail.class.php';
            $sendmail = new sendmail();
            $sendmail->set($mail_server, $mail_port, $mail_user, $mail_password, $mail_type, $mail_user);
            echo $sendmail->send($email_to, '邮件发送测试 - '.$DC['sitename'], '邮件发送测试！<br />'.$DC['mail_sign'], $mail_user) ? '邮件发送成功！' : $sendmail->error[0][1];
            exit;
        break;

        case 'test_uc':
        $link = mysql_connect($uc_dbhost, $uc_dbuser, $uc_dbpwd, 1)  or exit($LANG['can_not_connect_mysql_server']. mysql_error());
        @mysql_select_db($uc_dbname, $link) or exit($uc_dbname.$LANG['can_note_find_database']);
        @mysql_fetch_array(mysql_query("show tables like '{$uc_dbpre}members'")) or exit($LANG['invalid_tablepre'].$uc_dbpre);
        $db->select_db(DB_NAME);
        exit('success');
        break;


        default :
            @extract(new_htmlspecialchars($DC));

            if(!function_exists('ob_gzhandler')) $enablegzip = 0;
            $safe_mode = ini_get('safe_mode');

            if(!function_exists('imagepng') && !function_exists('imagejpeg') && !function_exists('imagegif'))
            {
                $gd = "<font color='red'>不支持GD库</font>";
                $enablegd = 0;
            }
            else
            {
                $gd = '支持GD库';
                $enablegd = 1;
            }
        if(function_exists('imagepng')) $gd .= "PNG ";
        if(function_exists('imagejpeg')) $gd .= " JPG ";
        if(function_exists('imagegif')) $gd .= " GIF ";
        
        
        include atpl('setting');
}
?>