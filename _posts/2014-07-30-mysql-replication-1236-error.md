---
layout: post
title: MySQL主从同步的坑
description: Last_IO_Error Got fatal error 1236 from master when reading data from binary log
date: 2014-07-30 17:11:26
published: true
tags: mysql
---


配置完slave后，`show slave status\G;`报错如下

```
Got fatal error 1236 from master when reading data from binary log: 'Slave can not handle replication events with the checksum that master is configured to log; the first event 'mysql-bin.000002' at 120, the last event read from './mysql-bin.000002' at 120, the last byte read from './mysql-bin.000002' at 120
```

后来发现是因为主库MySQL版本是5.6， 从库是5.5

5.6的版本中加入了replication event checksum，主从复制时间校验功能，所以需要把这个关掉才能正常同步到5.5的slave

修改主库 `/etc/my.cnf`

增加下一行

```
binlog_checksum=none
```

重启mysql


现在再看从库status就正常了~


```
mysql> show slave status\G;
*************************** 1. row ***************************
               Slave_IO_State: Waiting for master to send event
                  Master_Host: 192.168.0.121
                  Master_User: replication
                  Master_Port: 3306
                Connect_Retry: 60
              Master_Log_File: mysql-bin.000003
          Read_Master_Log_Pos: 120
               Relay_Log_File: mysqld-relay-bin.000002
                Relay_Log_Pos: 266
        Relay_Master_Log_File: mysql-bin.000003
             Slave_IO_Running: Yes
            Slave_SQL_Running: Yes
              Replicate_Do_DB: kudong
          Replicate_Ignore_DB: mysql,information_schema
           Replicate_Do_Table:
       Replicate_Ignore_Table:
      Replicate_Wild_Do_Table:
  Replicate_Wild_Ignore_Table:
                   Last_Errno: 0
                   Last_Error:
                 Skip_Counter: 0
          Exec_Master_Log_Pos: 120
              Relay_Log_Space: 423
              Until_Condition: None
               Until_Log_File:
                Until_Log_Pos: 0
           Master_SSL_Allowed: No
           Master_SSL_CA_File:
           Master_SSL_CA_Path:
              Master_SSL_Cert:
            Master_SSL_Cipher:
               Master_SSL_Key:
        Seconds_Behind_Master: 0
Master_SSL_Verify_Server_Cert: No
                Last_IO_Errno: 0
                Last_IO_Error:
               Last_SQL_Errno: 0
               Last_SQL_Error:
  Replicate_Ignore_Server_Ids:
             Master_Server_Id: 1
1 row in set (0.00 sec)
```