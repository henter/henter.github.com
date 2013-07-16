---
layout: post
date: 2013-04-24 11:19:56
title: Python备忘录
description: 记录python使用过程中的各种问题
tags: python
---


记录python使用过程中的各种问题，好记性不如烂博客。


SyntaxError: Non-ASCII character '\xe5' in file
----------
这个错误是python的默认编码文件是用的ASCII码，
将文件存成了UTF-8也没用，解决办法很简单，只要在文件开头加入

	# -*- coding: UTF-8 -*-
	
或者

	#coding=utf-8 

就能解决问题了。


<br />

字符串处理
---
常用

	str1 + str2 连接
	len(str) 长度
	str.trip() 去空格
	str.lstrip()
	str.rtrip()
	
	str.replace(old, new[, maxtimes]) 替换
	str[from:to] 截取
	
	'1,2,3,4,5'.split(',') 分割
	

判断

	str.startwith()
	str.endwith()
	str.upper()
	str.lower()
	str.capitalize()
	str.isalnum() 是否全字母或数字
	str.isalpha() 是否全字母
	str.isdigit() 是否全数字
	str.islower()
	str.isupper()
	
	'a' in 'abc'  True
	
查找

	 str.find(needle)
	 str.find(needle, start, end)
	 str.rfind(needle)
	 str.count(needle)
	 
	 尽量不要用 str.index(needle)，找不到时会抛异常，find返回-1
	 
正则

	p = re.compile(pattern)
	result  = p.match(str)

	下面这种用法与上面结果相同
	
	result = re.match(pattern, str)
	
	更多常用方法：
	search(pattern, str) 全部匹配 查找位置
	match(pattern, str)  开头匹配
	split(pattern, str)  分割
	findall(pattern, str)  查找全部
	sub(pattern, replace, str)  替换
	

文件、目录操作
------

文件读写
	
	handle = open('test.txt')
	print handle.read(maxbytes)
	
	读行
    file(filename)
	handle.readline(maxbytes)
	handle.readlines(maxlines)
	
	从头开始读
	handle.seek(0)
	
	写
	handle = open('test.txt', 'w')
	handle.write(str)  每次写一行
	handle.writelines(str)  多行一次写入
	
	不常用的
	handle.flush() 将缓冲区内容写入硬盘
	handle.tell() 返回文件操作标记的当前位置
	handle.next() 返回下一行
	
	关闭
	handle.close()
	
	
	打开文件方式参数，跟其它语言里面的类似
	r 读 默认
	w 写
	a 追加
	rb wb 二进制
	
	其它
	import os
	os.stat(file) 获取文件属性
	os.path.getsize(file) 获取文件大小

目录操作
	
	import os, shutil
	
	os.getcwd() 当前目录
	os.listdir(dir) 返回指定目录下所 有文件和目录名
	os.remove(file) 删除文件
	os.removedirs(dir) 删除目录 递归
	
	os.path.isfile(path)
	os.path.isdir(path)
	os.path.isabs(path) 是否为绝对路径
	os.path.exists(path) 路径是否存在
	
	path,file = os.path.split('home/henter/test.txt') => ('/home/henter', 'test.txt')
	f, ext = os.path.splitext() 分离扩展名
	os.path.dirname(path)
	os.path.basename()
	os.path.join(path1, path2)
	
	os.chmod(file)
	os.rename(old ,new)
	os.mkdirs('/home/henter/newdir/test/') 创建多级目录
	os.mkdir('newdir') 创建目录
	
	os.mkdir(dirname)  创建目录	
	shutil.copyfile(oldfile, newfile)复制文件 
	shutil.copy(olddir, newdir) 复制文件夹
	shutil.copytree(olddir, newdir) 复制目录，newdir必须不存在
	shutil.move(oldpos, newpos) 重命名文件或目录
	
	os.remove(file) 删除文件
	shutil.rmtree(dir) 删除目录
	os.chdir(path) 换路径
	
	os.system(command) 运行系统命令
	
数据编组
	
	可以用cPickle代替pickle
	
	import pickle
	
	保存
	handle = open('test.txt', 'w')
	data = ['data', 'for', 'write']
	pickle.dump(data, handle)
	handle.close()
	
	读取
	handle = open('test.txt', 'r')
	data = pickle.load(handle)
	handle.close()
	
	


<br /><br />

参考：

[使用 Python 模块 re 实现解析小工具](http://www.ibm.com/developerworks/cn/opensource/os-cn-pythonre/)

[Python内置的字符串处理函数整理](http://sjolzy.cn/Python-built-in-string-handling-functions-order.html)

