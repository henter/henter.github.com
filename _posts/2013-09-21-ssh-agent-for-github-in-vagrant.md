---
layout: post
date: 2013-09-21 16:13:19
title: 通过ssh agent在vagrant虚拟机内克隆github repo
tags: ssh vagrant github
---

习惯了用vagrant搭建虚拟开发环境，但是每次都要在虚拟机内克隆github项目，输入账号密码什么的，略显麻烦。

可以用ssh forward结合vagrant配置实现。

步骤如下：

1.加入ssh key(确保此key已经在github)

	$ ssh-add
	Identity added: /Users/henter/.ssh/id_rsa (/Users/henter/.ssh/id_rsa)
	
	或，手动指定key文件
	
	$ ssh-add ~/.ssh/id_rsa
	Identity added: /Users/henter/.ssh/id_rsa (/Users/henter/.ssh/id_rsa)
	
2.修改vagrant配置

	config.ssh.forward_agent = true
	
ok了，启动

	$ vagrant up; vagrant ssh
	…
	…

测试

	vagrant@henter-debian:~$ ssh -T git@github.com
	Hi henter! You've successfully authenticated, but GitHub does not provide shell access.
	
然后现在可以自由使用git clone或其它操作了。


参考：

https://help.github.com/articles/using-ssh-agent-forwarding