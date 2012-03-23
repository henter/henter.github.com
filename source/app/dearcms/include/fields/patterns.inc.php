<?php 
$patterns = array(
                 array('pattern'=>'/^[0-9.-]+$/','name'=>'数字'),
                 array('pattern'=>'/^[0-9-]+$/','name'=>'整数'),
                 array('pattern'=>'/^[a-z]+$/i','name'=>'字母'),
                 array('pattern'=>'/^[0-9a-z]+$/i','name'=>'数字+字母'),
                 array('pattern'=>'/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/','name'=>'E-mail'),
                 array('pattern'=>'/^[0-9]{5,20}$/','name'=>'QQ'),
                 array('pattern'=>'/^http:\/\//','name'=>'超级链接'),
                 array('pattern'=>'/^(13|15)[0-9]{9}$/','name'=>'手机号码'),
                 array('pattern'=>'/^[0-9-]{6,13}$/','name'=>'电话号码'),
                 array('pattern'=>'/^[0-9]{6}$/','name'=>'邮政编码'),
            );
?>