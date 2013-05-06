---
layout: post
date: 2013-05-06 17:54:15
title: MongoDB地理位置查询的Maxdistance单位
published: false
tags: mongodb php doctrine
---

通过Mongodb地址位置查询可以很容易查到类似“附近500内的人“

例如（mongo shell下）：
	
	db.places.find({loc:{$near:[30,40], $maxDistance: 1})
	
我在Symfony里面通过Doctrine查询如下：
	
	$data = $this->getDMManager()
		->createQueryBuilder("WeimiWebBundle:Feed")
        ->field('coordinate')->near($la,$lo)
        ->maxDistance($maxdistance)
        ->sort('updated_at', 'desc')
        ->getQuery()
        ->execute();
        

问题来了

maxdistance的单位是什么？ 米？公里？
如果我要查询附近500米的人，maxdistance的值是多少？

经过一番搜索，在MongoDB的官方网站找到下面两篇文档

[$maxDistance](http://docs.mongodb.org/manual/reference/operator/maxDistance/)


参考：

<http://stackoverflow.com/questions/7837731/units-to-use-for-maxdistance-and-mongodb>