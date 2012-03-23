<?php
//===================UCenter参数设置=====================
// 连接 UCenter 的方式: mysql/NULL, 默认为空时为 fscoketopen()
// mysql 是直接连接的数据库, 为了效率, 建议采用 mysql
define('UC_CONNECT', 'mysql');

// UCenter 数据库主机
define('UC_DBHOST', 'localhost');
// UCenter 数据库用户名
define('UC_DBUSER', 'root');
// UCenter 数据库密码
define('UC_DBPW', '');
// UCenter 数据库名称
define('UC_DBNAME', 'ucenter');
// UCenter 数据库字符集
define('UC_DBCHARSET', 'utf8');
// UCenter 数据库表前缀

define('UC_DBTABLEPRE', 'ucenter.uc_');
//----------------通信相关
// 当前应用的 ID
define('UC_APPID', '5');	
// 与 UCenter 的通信密钥, 要与 UCenter 保持一致
define('UC_KEY', 'dazheucenter');
// UCenter 的 URL 地址, 在调用头像时依赖此常量
define('UC_API', 'http://test.com/ucenter');
// UCenter 的字符集
define('UC_CHARSET', 'utf8');
// UCenter 的 IP, 当 UC_CONNECT 为非 mysql 方式时, 并且当前应用服务器解析域名有问题时, 请设置此值
define('UC_IP', '');

