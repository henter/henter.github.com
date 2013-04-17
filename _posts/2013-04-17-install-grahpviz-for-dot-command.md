---
layout: post
date: 2013-04-17 14:45:19
title: failed to execute cmd：" dot -Tpng". stderr：`sh： dot：command not found`
tags: php xhprof
---

安装完`xhprof`后查看分析结果发现报错如下：
	
	failed to execute cmd: " dot -Tpng". stderr: `sh: dot: command not found '

原因：未安装图形化工具

解决如下：

	brew install graphviz


好记性不如烂博客，记录一下，利人利己。




来源：<http://www.kongch.com/2011/08/dot-command/>
	