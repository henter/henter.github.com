---
layout: post
title: Slack + Hubot 处理Android自动打包
description: 安卓测试自动化流程，用Hubot处理Slack和服务器通信，执行指定任务。本文主要介绍自动打包处理。
date: 2014-09-21 16:28:26
published: true
tags: slack ubuntu hubot android ant
---

[Slack](http://slack.com)是一款企业内部协作平台，能将各种分散的沟通、协作工具整合到一起，基本上市面上所有的知名第三方服务都能很好的集成到Slack，其它没被官方集成的服务也可以通过Slack提供的API集成，比如整合内部任务系统、ERP系统等等。

[Hubot](http://github.com/github/hubot)是Github推出的开源聊天机器人，能整合各类IM工具。可以通过写hubot coffee脚本来实现各种好玩的小工具~


下面开始介绍如何利用Slack和Hubot处理Android自动打包。

##Ubuntu环境处理Android打包

###安装工具
下载ADT

```
$ wget https://dl.google.com/android/adt/adt-bundle-linux-x86_64-20140702.zip
$ unzip adt-bundle-linux-x86_64-20140702.zip
$ sudo cp -r adt-bundle-linux-x86_64-20140702/sdk /opt/adt-sdk
```

安装ant

```
$ sudo apt-get install ant
```

安装jdk

```
$ sudo apt-get install openjdk-7-jre openjdk-7-jdk openjdk-7-jre-lib
```

设置环境变量，在你的profile文件内加入下面代码：

```
export ANT_HOME=/usr/share/ant
export ANDROID_SDK_HOME=/opt/adt-sdk/
export PATH=$PATH:$ANDROID_SDK_HOME/tools:$ANDROID_SDK_HOME/build-tools/android-4.4W:$ANDORID_SDK_HOME/platforms/android-20/
export JAVA_HOME=/usr/lib/jvm/java-7-openjdk-amd64
```
注：根据平台不同，上面的`java-7-openjdk-amd64`有可能为`java-7-openjdk-i386`


###检查环境

```
$ ant -version
Apache Ant(TM) version 1.8.2 compiled on December 3 2011
```

如果正常输出版本号则表示ok，如果提示报错，(如`tools.jar`找不到等)，跟环境有关，请按上面步骤检查。


###处理包签名

生成`keystore`（需设置密码），这里以`kudong`为例

```
$ keytool -genkey -v -keystore kudong.keystore -alias kudong -keyalg RSA --validity 10000
```


进入项目目录，新建文件`ant.properties`，输入以下内容：

```
key.store=上面保存的keystore文件路径
key.alias=kudong
key.store.password=上面设置的密码
key.alias.password=上面设置的密码
```

###更新项目

```
$ android update project --name kudong -t 1 -p /path/to/android/project
```
这里的`-t`参数可能会报错，需要用下面的命令查看当前存在的targets

```
$ android list targets
```

###开始打包

先跑一次测试

```
$ ant debug
```

如果报错，可能需要安装下面的库(android工具依赖库需要兼容32位)

```
$ sudo apt-get install libc6-i386 lib32stdc++6 lib32gcc1 lib32ncurses5 lib32z1
```

再次`ant debug`提示成功后，就可以打包了

```
$ ant release

...
...
-post-build:

release:

BUILD SUCCESSFUL
Total time: 6 seconds

```

打好的包在项目的`./bin`目录下


##安装Hubot

[Hubot](http://github.com/github/hubot)基于[NodeJs](http://nodejs.org) 和 [Coffeescript](http://coffeescript.org)，这里我们将用Hubot调用前面的`ant`打包工具。

###安装并创建hubot项目

```
1. 安装npm
	
	$ wget http://nodejs.org/dist/v0.10.32/node-v0.10.32.tar.gz
	$ tar zxvf node-v0.10.32.tar.gz
	$ cd node-v0.10.32
	$ ./configure
	$ make
	$ sudo make install

2. 安装hubot和coffee
	
	$ sudo npm install -g hubot coffee-script
	
3. 创建Hubot项目
	
	$ hubot --create myhubot
	$ cd myhubot
	$ hubot
	Hubot>

```

###用Hubot调用ant打包android项目

Hubot脚本需要用coffeescript编写，简化版如下：

```
module.exports = (robot) ->
  robot.respond /build/i, (msg) ->
    msg.send 'building, please wait...'

    command = 'cd /path/to/android/project; ant release'
    ret = require('child_process').exec(command)

    error_chunks = []
    ret.stdout.on 'data', (chunk) ->
      #msg.send chunk.toString()
    ret.stderr.on 'data', (chunk) ->
      error_chunks.push chunk.toString()
    ret.on 'exit', (code, signal) ->
      if code == 0
        msg.send 'build success'
      else
        msg.send error_chunks.pop()
```


###Slack与Hubot通信

修改`package.json`文件，在`dependencies`中加入slack:
	
	"hubot-slack": "~2.0.4"

然后运行

	$ npm install
	
设置Slack相关环境变量（token，团队名，机器人名）
这里我们的机器人名字叫：`kucat`

	export HUBOT_SLACK_TOKEN=你的Token
	export HUBOT_SLACK_TEAM=kudong
	export HUBOT_SLACK_BOTNAME=kucat

启动

	nohup ./bin/hubot --adapter slack --name kucat &

然后在Slack增加Hubot集成
填入Hubot URL：
`http://你的服务器IP:8080/`

这个时候在Slack任一频道内输入：

	kucat build

就可以看到返回数据了。


##最后

自动打包这个需求，其实完全可以通过Github的hook机制处理，还更方便快捷，不过这不是本文重点。

本文只是很简单的应用案例，Slack+Hubot还有更多更酷的玩法，后面慢慢讲~

