---
layout: post
date: 2013-04-18 17:56:49
title: Mac下快速切换代理设置（为方便翻墙）
tags: mac 翻墙
---

自从换到Mac系统后，一直在用`GoAgent`翻墙，方便快捷。

但是有个问题就是，翻墙后访问国内网站速度比较慢，比较不爽。

>PS，虽然此问题可以用goagent结合pac文件实现智能翻墙，但不在本文讨论之列。以后再折腾这个。


所以，现在的办法是，需要翻墙的时候，在网络设置里面勾选代理，如下图：
![Mac代理设置](/pic/mac-proxy-setting.png)

但是每次这样设置，比较麻烦，google之后，发现可以用网络设置里面的“位置（Location）”实现，即通过设置不用的位置，采用不同的设置方案，这样只要切换位置就可以了。如下图
![Mac切换位置](/pic/mac-switch-location.png)

### 但是！
这样还是有个问题，每次切换之后都会`断网`，然后`重新联网`，也很不爽。

然后我就想，能否快速修改代理设置呢？

经过一番搜索，找到了在命令行下快速修改网络设置的方法

让“Wi-Fi”这个网络链接打开WEB代理（HTTP）

	sudo networksetup -setwebproxystate 'Wi-Fi' on
	

让“Wi-Fi”这个网络链接打开WEB代理（HTTPS）

	sudo networksetup -setsecurewebproxystate 'Wi-Fi' on

关闭时，把`on`改为`off`即可。
其它网络服务要把`Wi-Fi`改成对应的名称。


剩下的就简单了，把命令写入sh文件，通过设置alias快速调用。

例如我的：

proxy.sh

	#!/bin/zsh

	#默认 开https  不开http
	proxy_on(){
	   networksetup -setsecurewebproxystate 'Wi-Fi' on
	   networksetup -setwebproxystate 'Wi-Fi' off
	}

	#关闭代理
	proxy_off(){
	   networksetup -setsecurewebproxystate 'Wi-Fi' off
	   networksetup -setsecurewebproxystate 'Wi-Fi' off
	}

	#打开http和https代理
	proxy_all(){
	   networksetup -setsecurewebproxystate 'Wi-Fi' on
	   networksetup -setsecurewebproxystate 'Wi-Fi' on
	}

	if [ "$1" = "on" ]; then
    	proxy_on
	elif [ "$1" = "off" ]; then
    	proxy_off
	elif [ "$1" = "all" ]; then
    	proxy_all
	else
    	printf "Usage: sh ~/.dotfiles/sh/proxy.sh {on|off|all}\n"
	fi

然后，修改`.zshrc`，加入别名

	# proxy切换
	alias proxy="sudo sh ~/.dotfiles/sh/proxy.sh on"
	alias proxy_off="sudo sh ~/.dotfiles/sh/proxy.sh off"
	alias proxy_all="sudo sh ~/.dotfiles/sh/proxy.sh all"

修改完后，重新载入
	
	source .zshrc

现在，就可以通过 `proxy` `proxy_off` `proxy_all`来自由切换代理设置了。


PS：
不习惯命令行的童鞋可以用我前面提到的智能翻墙方案，搜索`mac goagent pac`


<br />

参考：

[Mac OS X中通过程序修改系统网络设置的proxies](http://www.1mima.com/mac-os-x%E4%B8%AD%E9%80%9A%E8%BF%87%E7%A8%8B%E5%BA%8F%E4%BF%AE%E6%94%B9%E7%B3%BB%E7%BB%9F%E7%BD%91%E7%BB%9C%E8%AE%BE%E7%BD%AE%E7%9A%84proxies/)

<https://developer.apple.com/library/mac/#documentation/Darwin/Reference/ManPages/man8/networksetup.8.html>
