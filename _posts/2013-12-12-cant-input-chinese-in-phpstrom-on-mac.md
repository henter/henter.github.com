---
layout: post
title: Mac下PhpStrom无法输入中文的问题
description: 关于Mac下的Jetbrains类软件，如PhpStrom等无法输入中文的问题解决
date: 2013-12-12 21:11:26
published: true
tags: mac phpstrom jetbrains 工具
---


很早就遇到这个问题了，在用PhpStrom时无法输入中文。表现为，中文输入法状态栏上有中文显示，但是输入到IDE里面就是英文。

刚开始以为是输入法问题，换了QQ输入法、Squirrel鼠须管等，都没办法。

后来干脆放弃了，正好强迫自己在项目中使用英文写注释什么的。

现在由于项目内必须要用中文，google之，找了好几个方法试了才搞定。

有效方法如下：

	sudo vim /Applications/PhpStorm.app/bin/idea.vmoptions

在最后一行加上

	-J-Djava.awt.im.style=on-the-spot


然后，`Command+Q`完全退出PhpStrom

	切换当前输入法为英文状态

重新启动PhpStrom，OK了，可以正常输入中文。


参考：

http://www.blogjava.net/JAVA-HE/archive/2011/11/15/363772.html

https://code.google.com/p/rimeime/issues/detail?id=311
