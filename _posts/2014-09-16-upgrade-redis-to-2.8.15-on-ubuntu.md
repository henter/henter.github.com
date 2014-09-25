---
layout: post
date: 2014-09-16 13:25:53
title: 升级Redis2.2到2.8.15稳定版
description: 升级redis到新版，主要是为了aof相关变动
tags: ubuntu redis aof 升级
---

今天收到服务器报警邮件，是服务器硬盘快满了（超过90%），检查后发现`redis`的`aof`文件居然达到9G，这才想起来没有定期rewrite aof文件，于是干脆一不做二不休，升级到新版，免得手动处理aof（redis 2.4以上的版本会自动rewrite aof）

大致过程比较简单

卸载旧版

```
apt-get remove redis-server
```

下载官方稳定版编译安装

```
wget http://download.redis.io/redis-stable.tar.gz
tar xvzf redis-stable.tar.gz
cd redis-stable
make
sudo make install
```

拷贝文件到原来的位置（懒得改init脚本）

```
sudo cp /usr/local/bin/redis-* /usr/bin/
```

然后修改配置文件（aof bind daemon等等）。

另外，旧版的配置文件跟新版不兼容，比如`vm-enabled`等参数，新版中木有。

启动后，查看版本

```
$ redis-cli --version
redis-cli 2.8.15
```

然后回到开头的问题，aof文件过大的问题
只需执行`BGREWRITEAOF`命令即可：

```
redis> BGREWRITEAOF
Background append only file rewriting started
```

以上命令在旧版中也可以执行，但是新版中这个操作由Redis自行触发，就不用人为定期处理了。

可以通过以下参数来控制自动触发行为：

```
auto-aof-rewrite-percentage
auto-aof-rewrite-min-size
```

默认触发机制是当aof文件大小为基准大小（64mb）的两倍时，自动执行`BGREWRITEAOF`


完。

