---
layout: post
title: Git rebase小计(转)
tags: git
---

git rebase，顾名思义，就是重新定义（re）起点（base）的作用，即重新定义分支的版本库状态。要搞清楚这个东西，要先看看版本库状态切换的两种情况：

我们知道，在某个分支上，我们可以通过git reset，实现将当前分支切换到本分支以前的任何一个版本状态，即所谓的“回溯”。即实现了本分支的“后悔药”。也即版本控制系统的初衷。

还有另一种情况，当我们的项目有多个分支的时候。我们除了在本地开发的时候可能会“回溯”外，也常常会将和自己并行开发的别人的分支修改添加到自 己本地来。这种情况下很常见。作为项目管理员，肯定会不断的合并各个子项目的补丁，并将最新版本推送到公共版本库，而作为开发人员之一，提交自己的补丁之 后，往往需要将自己的工作更新到最新的版本库，也就是说把别的分支的工作包含进来。

举个例子来说吧！
假设我们的项目初期只有一个master分支，然后分支上作过两次提交。这个时候系统只有一个master分支，他的分支历史如下：

	master0（初始化后的版本）
	||
	v
	master1（第一次提交后的版本）
	||
	v
	master2（第二次提交后的版本）

这个时候，我们可以**通过git reset将master分支**（工作目录、工作缓存或者是版本库）切换到master1或者master0版本，这就是前面所说的第一种情况。
假设我们这里把master分支通过git reset回溯到了master1状态。那么这个时候系统仍然只有一个master分支，分支的历史如下：

	master0（初始化后的版本）
	||
	v
	master1（第一次提交后的版本）

然后，我们在这里以master1为起点，创建了另一个分支test。那么对于test分支来说，他的第一个版本test0就和master1是同一个版本，此时项目的分支历史如下：

	master0（初始化后的版本）
	||
	v
	master1（第一次提交后的版本）===test0（test分支，初始化自master分支master1状态）

这个时候，我们分别对master分支、test分支作两次提交，此时版本库应该成了这个样子：

	master0（初始化后的版本）
	||
	v
	master1===test0==>test1===>test2
	||
	v
	master2===>master3

这个时候，通过第一种git reset的方式，可以将master分支的当前状态（master3）回溯到master分支的master0、master1、master2状态。 也可已将test分支当前状态（test2）回溯到test分支的test0、test1状态，以及test分支的父分支master的master0、 master1状态。

那么。如果我要让test分支从test0到test2之间所有的改变都添加到master分支来，使得master分支包含test分支的所有修改。这个时候就要用到git rebase了。

首先，我们切换到master分支，然后运行下面的命令，即可实现我们的要求：
	git rebase test

**这个时候，git做了些什么呢？**

	先将test分支的代码checkout出来，作为工作目录
	然后将master分支从test分支创建起的所有改变的补丁，依次打上。如果打补丁的过程没问题，rebase就搞定了
	
	如果打补丁的时候出现了问题，就会提示你处理冲突。处理好了，可以运行git rebase –continue继续直到完成
	
	如果你不想处理，你还是有两个选择，一个是放弃rebase过程（运行git rebase –abort），另一个是直接用test分支的取代当前分支的（git rebase –skip）。

此外，rebase还能够让你修订以前提交，这个功能日后再说。


=======================================

另外，使用git rebase不要滥用，否则会比较麻烦，需要注意的地方参考下面这篇：
[git 关于merge rebase(衍合)][notice]




转自：
<http://www.cnblogs.com/kym/archive/2010/08/12/1797937.html>

参考：
[使用 git rebase 避免無謂的 merge][howto]
[git 关于merge rebase(衍合)][notice]



[notice]: http://blog.csdn.net/jixiuffff/article/details/5970891
[howto]: http://ihower.tw/blog/archives/3843/
