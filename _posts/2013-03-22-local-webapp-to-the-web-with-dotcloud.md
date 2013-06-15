---
layout: post
title: 通过dotcloud代替localtunnel建立本地服务器
tags: dotcloud localtunnel 云平台
---


安装dotcloud cli

	sudo easy_install pip && sudo pip install dotcloud
	
	dotcloud setup

PS：我按以上步骤安装完后 `dotcloud`不能直接用

需要用 `/usr/local/share/python/dotcloud`代替

当然，你也可以用软链

	ln -s /usr/local/share/python/dotcloud /usr/local/bin/dotcloud
	

安装完dotcloud cli工具后，开始建立webapp，以`webtunnel`为例

先从github clone一个配置
	
	git clone https://github.com/3on/tunnel-on-dotcloud.git
	cd tunnel-on-dotcloud/
注：这个repo里面有个地方写错了，默认是无法直接运行的，修改如下

打开 `www/nginx.conf`文件，在root这一行（第7行）尾部加一个分号`;`


然后
	
	dotcloud create webtunnel

这个命令会提示`Connect the current directory to "webtunnel"?`，是否以当前目录做为app目录
也可以用下面这个命令手动执行
	
	dotcloud connect webtunnel
	
然后，push到dotcloud服务器

	dotcloud push

然后打开 http://webtunnel-你的用户名.dotcloud.com 

部署成功的话会显示 `www/index.html`的内容
	
	Grab host url


		dotcloud info tunnel.proxy


	

如果提示“Application Not Responding”，表示部署失败，检查问题重新部署。


然后，查看proxy信息

	`dotcloud info proxy`
	
过程如下

	➜$ dotcloud info proxy
	== proxy
	type:            static
	instances:       1
	reserved memory: N/A
	URLs:
	  - http://webtunnel-henter.dotcloud.com
	
	=== proxy.0
	datacenter:       Amazon-us-east-1d
	service revision: static/da4d1ea2eec9 (latest revision)
	revision:         rsync-1363850353553
	ports:
	  ssh: ssh://dotcloud@webtunnel-henter.azva.dotcloud.net:22481

开启本地服务器，如果没安装apache nginx等web服务器，可以用python开启一个简易服务器
	
	$ python -m SimpleHTTPServer
	Serving HTTP on 0.0.0.0 port 8000 ...
	
	
最后，通过ssh链接dotcloud，注意连接格式把里面的host和8000（本地web服务器端口）改成你自己的。

	ssh -i ~/.dotcloud_cli/dotcloud.key -l dotcloud -p 22481 webtunnel-henter.azva.dotcloud.net -R 8042:localhost:8000
	

参考

	http://blog.dotcloud.com/open-your-local-webapp-to-the-web-with-dotclo
	https://github.com/3on/tunnel-on-dotcloud
	http://docs.dotcloud.com/guides/flavors/
	http://docs.dotcloud.com/firststeps/install/