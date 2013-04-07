---
layout: post
date: 2013-04-07 16:35:03
title: Git Submodule使用
tags: git
---

今天刚打开终端，系统提示更新`oh-my-zsh`，但是由于我之前把这些乱七八糟的配置全部放到自己的[dotfiles](https://github.com/henter/dotfiles)里面了，系统默认是更新不了的。

好吧，我偷懒了，之前弄`dotfiles`的时候没有用到`git submodule`，而是直接采取暴力。。copy文件。

今天总算花了点时间把dotfiles更新一下，顺便把`oh-my-zsh`作为git submodule处理。

至于什么是`Git submodule`，这里就不多废话了，简单一句话就是：
	Git Submodule可以把别人的git repo放到你目前git repo的任意位置。

所以我现在做的就是：
	把oh-my-zsh这个repo，放到我的dotfiles下面的oh-my-zsh目录。仅此而已。
	
开始
	
	$ cd ~/.dotfiles/
	git submodule add https://github.com/robbyrussell/oh-my-zsh.git oh-my-zsh
	…
	…
完成后，查看status会发现

	$ git status
	# On branch master
	# Changes to be committed:
	#   (use "git reset HEAD ..." to unstage)
	#
	#       new file:   .gitmodules
	#       new file:   oh-my-zsh

然后
	$ git add .
	$ git commit -m 'add submodule'
	$ git push

搞定。

PS：如果是手动修改了.gitmodules文件，则需要

	$ git submodule init
来告诉git你添加了submodule

最后一步，修改zshrc文件
	
	ZSH=$HOME/.dotfiles/oh-my-zsh

搞定。

我的`dotfiles` <https://github.com/henter/dotfiles> 
欢迎fork ～


里面包含了我自己用的如:

	vim配置
	oh-my-zsh
	zshrc
	鼠须管输入法
	sublime主题
	部分nginx配置
	goagent启动文件
	
反正各种乱七八糟，慢慢完善。