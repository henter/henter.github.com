---
layout: post
date: 2013-05-07 17:54:15
title: 详解基于MongoDB的地理位置查询，结合Symfony2演示
published: true
tags: mongodb geo symfony doctrine
---



原文章发布在InfoQ：[《深入浅出Symfony2 - 结合MongoDB开发LBS应用》](http://www.infoq.com/cn/articles/depth-study-of-Symfony2)

# 简介

随着近几年各类移动终端的迅速普及，基于地理位置的服务（LBS）和相关应用也越来越多，而支撑这些应用的最基础技术之一，就是基于地理位置信息的处理。我所在的项目也正从事相关系统的开发，我们使用的是Symfony2+Doctrine2 ODM+MongoDB的组合。

在具体开发过程中，我们不断发现社区里的一些关于使用MongoDB进行地理位置信息处理的技术文章甚少能够完整的说明操作和使用细节，有些文章还有不少的错误，结果导致许多开发者在实际使用的时候享受不到MongoDB带来的优势，严重的甚至走了错误的方向，这实在是很可惜的是一件事情。

所以我们将这些技术要点整理成文，希望能够通过本文的介绍和案例，详细向您解释如何使用MongoDB进行地理位置信息的查询和处理。在文章的开头，我们也会先介绍一下业界通常用来处理地理位置信息的一些方案并进行比较，让读者逐步了解使用mongoDB查询及处理地理位置信息的优势。

本文使用了Symfony2和Doctrine2作为Web应用的开发框架，对于想了解Symfony2的数据库操作的读者来说阅读本文也可以了解和掌握相关的技术和使用方法。

#1. LBS类应用特点

不管是什么LBS应用，一个共同的特点就是：他们的数据都或多或少包含了地理位置信息。而如何对这些信息进行查询、处理、分析，也就成为了支撑LBS应用的最基础也是最关键的技术问题。

而由于地理位置信息的特殊性，在开发中经常会有比较难以处理的问题出现，比如：由于用户所在位置的不固定性，用户可能会在很小范围内移动，而此时经纬度值也会随之变化，甚至在同一个位置，通过GPS设备获取到的位置信息也可能不一样。所以如果通过经纬度去获取周边信息时，就很难像传统数据库那样做查询并进行缓存。

对于这个问题，有读者可能会说有别的处理方案，没错，比如只按经纬度固定的几位小数点做索引，比如按矩阵将用户划分到某固定小范围的区域（可以参考后文将会提到的geohash）等方式，虽然可以绕个弯子解决，但或多或少操作起来比较麻烦，也会牺牲一些精度，甚至无法做到性能的最优化，所以不能算作是最佳的解决办法。

而最近几年，直接支持地理位置操作的数据库层出不穷，其操作友好、性能高的特性也开始被我们慢慢重视起来，其中的佼佼者当属MongoDB。

那MongoDB在地理位置信息的处理上有什么优势呢？下面我们通过一个简单的案例来对比一下各种技术方案之间进行进行地理位置信息处理的差异。

#2. 几个地理位置信息处理方案的对比和分析

###1. 确定功能需求

对于任何LBS应用来说，让用户寻找周围的好友可能都是一个必不可少的功能，我们就以这个功能为例，来看看各种处理方案之间的差异和区别。

我们假设有如下功能需求：

* 显示我附近的人
* 由近到远排序
* 显示距离


###2. 可能的技术方案

排除一些不通用和难以实现的技术，我们罗列出以下几种方案：

1. 基于MySQL数据库
2. 采用GeoHash索引，基于MySQL
3. MySQL空间存储（MySQL Spatial Extensions）
4. 使用MongoDB存储地理位置信息

我们一个个来分析这几种方案。

####方案1：基于MySQL数据库
MySQL是目前最流行、使用最广泛的数据库之一，它的使用也非常简单。对于大部分已经使用MySQL的网站来说，使用这种方案没有任何迁移和部署成本。

而在MySQL中查询“最近的人”也仅需一条SQL即可,

```
SELECT id, ( 6371 * acos( cos( radians(37) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(-122) ) + sin( radians(37) ) * sin( radians( lat ) ) ) ) AS distance FROM places HAVING distance < 25 ORDER BY distance LIMIT 0 , 100;
```
注：这条SQL查询的是在lat,lng这个坐标附近的目标，并且按距离正序排列，SQL中的distance单位为公里。

但使用SQL语句进行查询的缺点也显而易见，每条SQL的计算量都会非常大，性能将会是严重的问题。

先别放弃，我们尝试对这条SQL做一些优化。

可以将圆形区域抽象为正方形，如下图

![circle-square](pic/geo-circle-square-map.jpg)

根据维基百科上的球面计算公式，可以根据圆心坐标计算出正方形四个点的坐标。

然后，查询这个正方形内的目标点。
SQL语句可以简化如下：

```
SELECT * FROM places WHERE ((lat BETWEEN ? AND ?) AND (lng BETWEEN ? AND ?))
```

这样优化后，虽然数据不完全精确，但性能提升很明显，并且可以通过给lat lng字段做索引的方式进一步加快这条SQL的查询速度。对精度有要求的应用也可以在这个结果上再进行计算，排除那些在方块范围内但不在原型范围内的数据，已达到对精度的要求。

可是这样查询出来的结果，是没有排序的，除非再进行一些SQL计算。但那又会在查询的过程中产生临时表排序，可能会造成性能问题……

所以如果我们从整体来看，方案1是无法很好的满足需求的。

###方案2：GeoHash索引，基于MySQL

[GeoHash](http://en.wikipedia.org/wiki/Geohash)是一种地址编码，通过切分地图区域为小方块（切分次数越多，精度越高），它能把二维的经纬度编码成一维的字符串。也就是说，理论上geohash字符串表示的并不是一个点，而是一个矩形区域，只要矩形区域足够小，达到所需精度即可。（其实[MongoDB的索引也是基于geohash](http://blog.nosqlfan.com/html/1811.html)）

如：[wtw3ued9m](http://geohash.org/wtw3ued9m)就是目前我所在的位置，降低一些精度，就会是[wtw3ued](http://geohash.org/wtw3ued)，再降低一些精度，就会是[wtw3u](http://geohash.org/wtw3u)。
（点击链接查看坐标编码对应Google地图的位置）

所以这样一来，我们就可以在MySQL中用`LIKE ‘wtw3u%’`来限定区域范围查询目标点，并且可以对结果集做缓存。
更不会因为微小的经纬度变化而无法用上数据库的Query Cache。

这种方案的优点显而易见，仅用一个字符串保存经纬度信息，并且精度由字符串从头到尾的长度决定，可以方便索引。

但这种方案的缺点是：从geohash的编码算法中可以看出，靠近每个方块边界两侧的点虽然十分接近，但所属的编码会完全不同。实际应用中，虽然可以通过去搜索环绕当前方块周围的8个方块来解决该问题，但一下子将原来只需要1次SQL查询变成了需要查询9次，这样不仅增大了查询量，也将原本简单的方案复杂化了。

除此之外，这个方案也无法直接得到距离，需要程序协助进行后续的排序计算。

所以我们目前的结论是：方案2优于方案1，但仍无法满足需求。

###方案3：MySQL空间存储

MySQL的空间扩展（MySQL Spatial Extensions），它允许在MySQL中直接处理、保存和分析地理位置相关的信息，看起来这是使用MySQL处理地理位置信息的“官方解决方案”。但恰恰很可惜的是：它却不支持某些最基本的地理位置操作，比如查询在半径范围内的所有数据。它甚至连两坐标点之间的距离计算方法都没有（MySQL Spatial的distance方法在5.*版本中不支持）

官方指南的做法是这样的：

```
GLength(LineStringFromWKB(LineString(point1, point2)))
```	

这条语句的处理逻辑是先通过两个点产生一个LineString的类型的数据，然后调用GLength得到这个LineString的实际长度。

这么做虽然有些复杂，貌似也解决了距离计算的问题，但读者需要注意的是：这种方法计算的是欧式空间的距离，简单来说，它给出的结果是两个点在三维空间中的直线距离，不是飞机在地球上飞的那条轨迹，而是笔直穿过地球的那条直线。

所以如果你的地理位置信息是用经纬度进行存储的，你就无法简单的直接使用这种方式进行距离计算。

通过上述例子我们可以看到，MySQL的空间存储并不能满足我们的需求。

###方案4：使用MongoDB存储地理位置信息

MongoDB原生支持地理位置索引，可以直接用于位置距离计算和查询。
另外，它也是如今最流行的NoSQL数据库之一，他除了能够很好地支持地理位置计算之外，还拥有诸如面向集合存储、模式自由、高性能、支持复杂查询、支持完全索引等等特性。

而对于我们的需求，在MongoDB只需一个命令即可得到所需要的结果：

```
db.runCommand( { geoNear: "places", near: [ 121.4905, 31.2646 ], num:100 })
```

查询结果默认将会由近到远排序，而且查询结果也包含目标点对象、距离目标点的距离等信息。而由于geoNear是MongoDB原生支持的查询函数，所以性能上也做到了高度的优化，完全可以应付生产环境的压力。

所以不论是从开发的便捷程度，还是性能上的考虑，MongoDB都能轻而易举地解决问题。

###方案总结

通过这些方案的对比和分析，相信答案已经很明确了，基于MongoDB做附近查询是很方便的一件事情。而MongoDB在地理位置信息方面的表现远远不限于此，它还支持更多更加方便的功能，如范围查询、距离自动计算等。俗话说他山之石可以攻玉，MongoDB在地理位置处理上有着其独特的优势，也是基于MySQL的传统解决方案所不能比拟的。

那么接下来我们结合Symfony2来详细地演示一些使用MongoDB进行地理位置信息处理的例子。

#3. 结合Symfony2演示
我们简单说明演示程序搭建过程

####运行环境

本人当前环境：Nginx1.2 + PHP5.4 + MongoDB2.4.3 + Symfony2.1

建立`coordinate`和`places`两个document文件，前者是作为`places`内的一个embed字段.
为方便演示效果，这里同时设置了两个索引 2d 和 2dsphere

```
	Document/Coordinate.php

	/**
	 * @MongoDB\EmbeddedDocument
	 */
	class Coordinate {
    	/**
     	* @MongoDB\Field(type="float")
     	*/
    	public $longitude;
    	
    	/**
     	* @MongoDB\Field(type="float")
     	*/
    	public $latitude;
	
    	...
	}
	
	Document/Place.php
	
	/**
	* @MongoDB\Document(collection="places")
	* @MongoDB\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
	* @MongoDB\Indexes({
	*   @MongoDB\Index(keys={"coordinate"="2d"}),
	*   @MongoDB\Index(keys={"coordinate"="2dsphere"})
	* })
	*/
	class Place
	{
    	/**
     	*
     	* @MongoDB\Id(strategy="INCREMENT")
     	*/
    	protected $id;

    	/**
     	* @MongoDB\Field(type="string")
     	*/
    	protected $title;
		
    	/**
     	* @MongoDB\Field(type="string")
     	*/
    	protected $address;
    	
		/**
     	* @MongoDB\EmbedOne(targetDocument="HenterGEO\GEOBundle\Document\Coordinate")
     	*/
    	protected $coordinate;
    
    	/**
     	* @MongoDB\Distance
     	*/
    	public $distance;
    	
    	...
    }
```

坐标保存以longitude, latitude这个顺序（没有明确的限制和区别，但我们在此遵循官方的推荐）。

另外，为直观显示查询效果，默认使用百度地图标记查询数据。

####程序说明
我们用到的代码包是`doctrine/mongodb-odm-bundle`（下文称`ODM`），这个代码包提供了在Symfony2环境下的MongoDB数据库支持，使用这个代码包，可以让我们更加方便的在Symfony2环境下操作MongoDB数据库。。

ODM封装了MongoDB中常用的一些地理位置函数，如周边搜索和范围搜索。

ODM中的操作默认距离单位是`度`，只有`geoSphere`支持`弧度`单位（必须在参数中指定spherical(true)）

#4. MongoDB的地理位置查询

##注意事项
在开始介绍MongoDB的地理位置查询之前，请读者注意下面几点：

1. 下文大多数直接对MongoDB的数据库操作将使用Mongo Shell进行演示，而在演示网站页面和功能时，将结合Symfony2、Doctrine-MongoDB进行演示。
2. 本文演示所用的MongoDB版本为2.4.3，版本号比较新，所以某些查询方式在低版本里面并不支持，请读者注意自己的环境配置。
3. 以places这个collection为例，大部分例子都需要类似下面格式的测试数据支持：
```
	{
		"_id" : 2,
		"coordinate" : {
			"longitude" : 121.3449,
			"latitude" : 31.17528
		},
		"title" : "仅售75元，市场价210元的顶呱呱田鸡火锅3-4人套餐，无餐具费，冬日暖锅，欢迎品尝",
		"address" : "闵行区航新路634号"
	}
```

##地理位置索引：

	MongoDB地理位置索引常用的有两种。
	2d 平面坐标索引
		适用于基于平面的坐标计算。也支持球面距离计算，不过官方推荐使用2dsphere索引。
	
	2dsphere 几何球体索引
		适用于球面几何运算

关于两个坐标之间的距离，官方推荐2dsphere：

>MongoDB supports rudimentary spherical queries on flat 2d indexes for legacy reasons. In general, spherical calculations should use a 2dsphere index, as described in 2dsphere Indexes.
	
不过，只要坐标跨度不太大（比如几百几千公里），这两个索引计算出的距离相差几乎可以忽略不计。

建立索引：	

	> db.places.ensureIndex({'coordinate':'2d'})
	> db.places.ensureIndex({'coordinate':'2dsphere'})

##查询方式：

查询方式分三种情况：

1. Inclusion. 范围查询，如百度地图“视野内搜索”。
2. Inetersection. 交集查询。不常用。
3. Proximity. 周边查询，如“附近500内的餐厅”。
	
而查询坐标参数则分两种：

1. 坐标对（经纬度）
根据查询命令的不同，$maxDistance距离单位可能是 弧度 和 平面单位（经纬度的“度”）。距离：

		db.<collection>.find( { <location field> :
                         { $nearSphere: [ <x> , <y> ] ,
                           $maxDistance: <distance in radians>
                      } } )
		
2. GeoJson
$maxDistance距离单位默认为米。举例：
		
		db.<collection>.find( { <location field> :
                         { $nearSphere :
                           { $geometry :
                              { type : "Point" ,
                                coordinates : [ <longitude> , <latitude> ] } ,
                             $maxDistance : <distance in meters>
                   } } } )
                   

##案例A：附近的人
查询当前坐标附近的目标，由近到远排列。

可以通过`$near`或`$nearSphere`，这两个方法类似，但**默认**情况下所用到的索引和距离单位不同。

查询方式如下：

	> db.places.find({'coordinate':{$near: [121.4905, 31.2646]}})
	> db.places.find({'coordinate':{$nearSphere: [121.4905, 31.2646]}})
	
查询结果

	{ 
		"_id" : 115, 
		"coordinate" : { 
			"longitude" : 121.4915, 
			"latitude" : 31.25933 
		}, 
		"title" : "仅售148元，市场价298元的星程上服假日酒店全日房一间入住一天，节假日通用，精致生活，品质享受", 
		"address" : "虹口区天水路90号" 
	}
	
	…(100条)
	
上述查询坐标[121.4905, 31.2646]附近的100个点，从最近到最远排序。
	
默认返回100条数据，也可以用limit()指定结果数量，如

	> db.places.find({'coordinate':{$near: [121.4905, 31.2646]}}).limit(2)
	
指定最大距离 $maxDistance

	> db.places.find({'coordinate':{$near: [121.4905, 31.2646], $maxDistance:2}})
	
结合Symfony2进行演示：

这里用`near`，默认以`度`为单位，公里数除以111（关于该距离单位后文有详细解释）。

	/**
     * @Route("/near", name="near")
     * @Template()
     */
    public function nearAction(){

        $longitude = (float)$this->getRequest()->get('lon',121.4905);
        $latitude = (float)$this->getRequest()->get('lat',31.2646);
        //2km
        $max = (float)$this->getRequest()->get('max', 2);

        $places = $this->getPlaceRepository()->createQueryBuilder()
            ->field('coordinate')->near($longitude, $latitude)
            ->maxDistance($max/111)
            ->getQuery()->toarray();

        return compact('places','max','longitude','latitude');
    }
	
通过 domain.dev/near 访问，效果如下：

![near](pic/geo-near.png)

longitude: xxx, latitude: xxx为当前位置，我们在地图上显示了周边100条目标记录

##案例B：区域内搜索

MongoDB中的范围搜索（Inclusion）主要用`$geoWithin`这个命令，它又细分为3种不同类型，如下：

1. `$box` 矩形
2. `$center` 圆（平面），`$centerSphere`圆（球面）
4. `$polygon` 多边形

`$center`和`$centerSphere`在小范围内的应用几乎没差别（除非这个圆半径几百上千公里）。

下面我们介绍一下这三种查询的案例。

###矩形区域
这个比较常用，比如百度地图的视野内搜索（矩形）、或搜狗地图的“拉框搜索”

定义一个矩形范围，我们需要指定两个坐标，在MongoDB的查询方式如下：

	> db.places.find( 
		{ 
			coordinate : { 
				$geoWithin : { 
					$box :[ [ 121.44, 31.25 ] , [ 121.5005, 31.2846 ] ] 
				} 
			} 
		} 
	)
	
查询结果

	{ 
		"_id" : 90472, 
		"title" : "【鲁迅公园】仅售99元！酒店门市价288元的上海虹元商务宾馆客房一间入住一天（需持本人有效身份证件办理登记）：大床房/标准房（2选1）！不含早餐！不涉外！2012年9月29日-10月6日不可使用拉手券！可延迟退房至14:00！", 
		"address" : "上海市虹口区柳营路8号", 
		"coordinate" : { 
			"longitude" : 121.47, 
			"latitude" : 31.27145 
		} 
	}
	...
	...
	
Symfony2演示代码：

指定两个坐标点

    /**
     * @Route("/box", name="box")
     * @Template()
     */
    public function boxAction(){
        $request = $this->getRequest();

        $longitude = (float)$request->get('lon',121.462035);
        $latitude = (float)$request->get('lat',31.237641);

        $longitude2 = (float)$request->get('lon2',121.522098);
        $latitude2 = (float)$request->get('lat2',31.215284);

        $places = $this->getPlaceRepository()->createQueryBuilder()
            ->field('coordinate')->withinBox($longitude, $latitude, $longitude2, $latitude2)
            ->getQuery()->toarray();

        return compact('places','longitude','latitude', 'longitude2', 'latitude2');
    }

通过 domain.dev/box 访问，效果如下：

![box](pic/geo-box.png)

###圆形区域
应用场景有：地图搜索租房信息

查询以某坐标为圆心，指定半径的圆内的数据。

前面已提到，圆形区域搜索分为`$center`和`$centerSphere`这两种类型，它们的区别主要在于支持的索引和默认距离单位不同。

2d索引能同时支持`$center`和`$centerSphere`，2dsphere索引支持`$centerSphere`。关于距离单位，`$center`默认是度，`$centerSphere`默认距离是弧度。

查询方式如下：

	> db.places.find({'coordinate':{$geoWithin:{$centerSphere:[ [121.4905, 31.2646] ,0.6/111] }}})
	或
	> db.places.find({'coordinate':{$geoWithin:{$centerSphere:[ [121.4905, 31.2646] ,0.6/6371] }}})
	查询结果
	{ 
		"_id" : 115, 
		"coordinate" : { 
			"longitude" : 121.4915, 
			"latitude" : 31.25933 
		}, 
		"title" : "仅售148元，市场价298元的星程上服假日酒店全日房一间入住一天，节假日通用，精致生活，品质享受", 
		"address" : "虹口区天水路90号" 
	}
	...
	
Symfony2演示代码：

指定圆心坐标和半径

    /**
     * @Route("/center", name="center")
     * @Template()
     */
    public function centerAction(){
        $request = $this->getRequest();

        $longitude = (float)$request->get('lon',121.4905);
        $latitude = (float)$request->get('lat',31.2646);
        //10km
        $max = (float)$request->get('max', 10);

        $places = $this->getPlaceRepository()->createQueryBuilder()
            ->field('coordinate')->withinCenter($longitude, $latitude, $max/111)
            ->getQuery()->toarray();

        return compact('places','max','longitude','latitude');
    }
    
通过 domain.dev/center 访问，效果如下：
以longitude: xxx，latitude: xxx
为中心点，半径10km的圆内

![near](pic/geo-center.png)

###多边形
复杂区域内的查询，这个应用场景比较少见。
指定至少3个坐标点，查询方式如下（五边形）：

	> db.places.find( { coordinate : { $geoWithin : { $polygon : [ 
		[121.45183 , 31.243816] ,
    	[121.533181, 31.24344] ,
    	[121.535049, 31.208983] ,
		[121.448955, 31.214913] ,
		[121.440619, 31.228748]
	] } } } )
	
查询结果

	{ 
		"_id" : 90078, 
		"title" : "仅售9.9元，市场价38元的燕太太燕窝单人甜品餐，用耐心守候一盅炖品，用爱滋补一生情谊", 
		"address" : "河南南路489号香港名都购物广场1F125燕太太燕窝", 
		"coordinate" : { 
			"longitude" : 121.48912, 
			"latitude" : 31.22355 
		} 
	}
	...
	
Symfony2演示代码（这里为方便，我直接写死了5个坐标点）：

    /**
     * @Route("/polygon", name="polygon")
     * @Template()
     */
    public function polygonAction(){
        $points = [];
        $points[] = [121.45183,31.243816];
        $points[] = [121.533181,31.24344];
        $points[] = [121.535049,31.208983];
        $points[] = [121.448955,31.214913];
        $points[] = [121.440619,31.228748];

        $sumlon = $sumlat = 0;
        foreach($points as $p){
            $sumlon += $p[0];
            $sumlat += $p[1];
        }
        $center = [$sumlon/count($points), $sumlat/count($points)];

        $places = $this->getPlaceRepository()->createQueryBuilder()
            ->field('coordinate')->withinPolygon($points[0], $points[1], $points[2], $points[3], $points[4])
            ->getQuery()->toarray();

        return compact('places','points', 'center');
    }

通过 domain.dev/polygon 访问，效果如下：

![polygon](pic/geo-polygon.png)



##案例C：附近的餐厅

我们假设需要以当前坐标为原点，查询附近指定范围内的餐厅，并直接显示距离。

这个需求用前面提到的`$near`是可以实现的，但是距离需要二次计算。这里我们用`$geoNear`这个命令查询。

`$geoNear`与`$near`功能类似，但提供更多功能和返回更多信息，官方文档是这么解释的：

>The geoNear command provides an alternative to the $near operator. In addition to the functionality of $near, geoNear returns additional diagnostic information.

查询方式如下（关于下面的示例用到了distanceMultipler函数，后文会详细解释)：
	
	> db.runCommand( { geoNear: "places", near: [ 121.4905, 31.2646 ], spherical: true, maxDistance:1/6371, num:2 })
    {
        "ns" : "mongo_test.places",
        "near" : "1110001100111100001011010110010111001000110011111101",
        "results" : [
            {
                "dis" : 0.00009318095248858048,
                "obj" : {
                    "_id" : 115,
                    "coordinate" : {
                        "longitude" : 121.4915,
                        "latitude" : 31.25933
                    },
                    "title" : "仅售148元，市场价298元的星程上服假日酒店全日房一间入住一天，节假日通用，精致生活，品质享受",
                    "address" : "虹口区天水路90号"
                }
            },
            {
                "dis" : 0.00010610660597329082,
                "obj" : {
                    "_id" : 465,
                    "coordinate" : {
                        "longitude" : 121.48406,
                        "latitude" : 31.26202
                    },
                    "title" : "【四川北路】热烈庆祝康骏会馆成立8周年！仅售169元！市场价399元的康骏会馆四川北路一店（仅限3星级技师）全身精油按摩一人次！全程约90分钟！男女不限！仅限四川北路一店使用，非本市所有门店通用！拉手券消费仅限每日19:00前！健康有道，骏越万里！",
                    "address" : "虹口区四川北路1896号-1904号201室"
                }
            }
        ],
        "stats" : {
            "time" : 0,
            "btreelocs" : 0,
            "nscanned" : 18,
            "objectsLoaded" : 12,
            "avgDistance" : 0.00009964377923093564,
            "maxDistance" : 0.0001064199324957278
        },
        "ok" : 1
    }

可以看到返回了很多详细信息，如查询时间、返回数量、最大距离、平均距离等。
另外，`results`里面直接返回了距离目标点的距离`dis`。

Symfony2演示代码：

    /**
     * @Route("/distance", name="distance")
     * @Template()
     */
    public function distanceAction(){

        $longitude = (float)$this->getRequest()->get('lon',121.4905);
        $latitude = (float)$this->getRequest()->get('lat',31.2646);
        //2km
        $max = (float)$this->getRequest()->get('max', 2);

        $places = $this->getPlaceRepository()->createQueryBuilder()
            ->field('coordinate')
            ->geoNear($longitude, $latitude)
            ->spherical(true)
            ->distanceMultiplier(6371)
            ->maxDistance($max/6371)
            ->limit(100)
            ->getQuery()
            ->execute()
            ->toArray();

        return compact('places','longitude', 'latitude', 'max');
    }
    
通过 domian.dev/distance 访问，效果如下：
距离xxx米

![distance](pic/geo-distance.png)

##小结

前面演示的查询代码中，坐标都是按照 longitude, latitude这个顺序的。
这个是官方建议的坐标顺序，但是网上很多文档是相反的顺序，经测试发现，只要查询时指定的坐标顺序与数据库内的坐标顺序一致，出来的结果就是正确的，没有特定的先后顺序之分。

但鉴于官方文档的推荐，我在此还是建议大家按照官方推荐的顺序。

案例A的`$near`和案例B的`$center`从需求上看差不多，但是`$center`或`$centerSphere`是属于`$geoWithin`的类型，`$near`方法查询后会对结果集对距离进行排序,而`$geoWithin`是无序的。

<br />

常用的查询方式已经介绍完了，不常用的比如geoIntersect查询，这里不做介绍，但是已经包含在开源的演示程序里了。
有兴趣的读者可以自行测试研究。

下面介绍前文提到的距离单位等问题。


#5. 需要注意的问题

##索引

`$near`命令必须要求有索引。

`$geoWithin`可以无需索引，但是建议还是建立索引以提升性能。
	
##距离单位
MongoDB查询地理位置默认有3种距离单位：

* 米(meters)
* 平面单位(flat units，可以理解为经纬度的“一度”)
* 弧度(radians)。

通过GeoJSON格式查询，单位默认是米，通过其它方式则比较混乱，接下来我将我详细解释一下。

下面的查询语句指定距离内的目标：

	> db.places.find({'coordinate':{$near: [121.4905, 31.2646], $maxDistance:2}})
	
现在$maxDistance参数是2，但是如果我要查询如“附近500米内的餐厅”这样的需求，这个参数应该是多少？

关于距离计算，MongoDB的官方文档不够明确（至少产生误导），其仅仅提到了[弧度计算]((http://docs.mongodb.org/manual/tutorial/calculate-distances-using-spherical-geometry-with-2d-geospatial-indexes/)，未说明水平单位（度）计算。

而关于弧度计算，官方文档的说明是：
>
To convert:
distance to radians: divide the distance by the radius of the sphere (e.g. the Earth) in the same units as the distance measurement.
radians to distance: multiply the radian measure by the radius of the sphere (e.g. the Earth) in the units system that you want to convert the distance to.<br /><br />
The radius of the Earth is approximately 3,959 miles or 6,371 kilometers.
	
所以如果用弧度查询，则以公里数除以6371，如“附近500米的餐厅”：

	> db.runCommand( { geoNear: "places", near: [ 121.4905, 31.2646 ], spherical: true, $maxDistance: 0.5/6371 })

那如果不用弧度，以水平单位（度）查询时，距离单位如何处理？

经过一番研究，答案是以公里数除以111（推荐值），原因如下：

经纬度的一度，分为经度一度和纬度一度。
	
地球不同纬度之间的距离是一样的，地球子午线（南极到北极的连线）长度39940.67公里，纬度一度大约110.9公里
	
但是不同纬度的经度一度对应的长度是不一样的：
在地球赤道，一圈大约为40075KM,除以360度，每一个经度大概是：40075/360=111.32KM
上海，大概在北纬31度，对应一个经度的长度是：40075*sin(90-31)/360=95.41KM
北京在北纬40度，对应的是85KM
	
前面提到的参数111，这个值只是估算，并不完全准确，任意两点之间的距离，平均纬度越大，这个参数则误差越大。
详细原因可以参考wiki上的解释：http://en.wikipedia.org/wiki/Latitude

但是，即便如此，“度”这个单位只用于平面，但是地球是圆的，在大范围使用时会有误差（日常应用足够了）。
官方建议使用sphere查询方式，也就是说距离单位用弧度。

>The current implementation assumes an idealized model of a flat earth, meaning that an arcdegree of latitude (y) and longitude (x) represent the same distance everywhere. This is only true at the equator where they are both about equal to 69 miles or 111km. However, at the 10gen offices at { x : -74 , y : 40.74 } one arcdegree of longitude is about 52 miles or 83 km (latitude is unchanged). This means that something 1 mile to the north would seem closer than something 1 mile to the east.

$geoNear返回结果集中的`dis`，如果指定了`spherical`为`true`， `dis`的值为弧度，不指定则为度。

指定 spherical为true,结果中的dis需要乘以6371换算为km:

	> db.runCommand( { geoNear: "places", near: [ 121.4905, 31.2646 ], spherical: true, num:1 })
    {
        "ns" : "mongo_test.places",
        "near" : "1110001100111100001011010110010111001000110011111101",
        "results" : [
            {
                "dis" : 0.00009318095248858048,
                "obj" : {
                    "_id" : 115,
                    "coordinate" : {
                        "longitude" : 121.4915,
                        "latitude" : 31.25933
                    },
                    "title" : "仅售148元，市场价298元的星程上服假日酒店全日房一间入住一天，节假日通用，精致生活，品质享受",
                    "address" : "虹口区天水路90号"
                }
            }
			
        ],
        "stats" : {
            "time" : 0,
            "btreelocs" : 0,
            "nscanned" : 18,
            "objectsLoaded" : 12,
            "avgDistance" : 0.00009964377923093564,
            "maxDistance" : 0.0001064199324957278
        },
        "ok" : 1
    }

不指定sphercial，结果中的dis需要乘以111换算为km:
	
    > db.runCommand( { geoNear: "places", near: [ 121.4905, 31.2646 ], num:1 })
    {
        "ns" : "mongo_test.places",
        "near" : "1110001100111100001011010110010111001000110011111101",
        "results" : [
            {
                "dis" : 0.005364037658335473,
                "obj" : {
                    "_id" : 115,
                    "coordinate" : {
                        "longitude" : 121.4915,
                        "latitude" : 31.25933
                    },
                    "title" : "仅售148元，市场价298元的星程上服假日酒店全日房一间入住一天，节假日通用，精致生活，品质享受",
                    "address" : "虹口区天水路90号"
                }
            }	
			
        ],
        "stats" : {
            "time" : 0,
            "btreelocs" : 0,
            "nscanned" : 18,
            "objectsLoaded" : 12,
            "avgDistance" : 0.006150808243357531,
            "maxDistance" : 0.00695541352612983
        },
        "ok" : 1
    }

说到这里读者是不是已经有点迷糊了？没关系，在开发中其实你并不需要去知道各种距离单位的历史和使用它的原因，我在此为你总结了一张表，大部分常用的函数和所使用的距离单位都已经被我整理了出来，你只需要参考表上所列的距离单位直接使用即可。

查询命令			|	距离单位		|	说明
------------ 	| 	--------- 	| ------------
$near			|	度			| [官方文档](http://docs.mongodb.org/manual/reference/operator/near/)上关于这一点是错的
$nearSphere		|	弧度			|
$center			|	度			|
$centerSphere	|	弧度			|
$polygon		|	度			|
$geoNear		|	度或弧度		|指定参数spherical为true则为弧度，否则为度

如果坐标以GeoJSON格式，则单位都为米。

当然如果你的操作比较复杂，或者希望知道更加详细的对照关系，也可以参考官方的这个更详细的对比表格：<http://docs.mongodb.org/manual/reference/operator/query-geospatial/>


##单位自动换算

如上面两个`geoNear`示例，结果中的`dis`，前文已经提过这是与目标点的距离，但是这个距离单位是跟查询单位一致的，需要二次计算，不太方便。

而其实你可以直接在查询时指定 [distanceMultiplier](http://docs.mongodb.org/manual/tutorial/calculate-distances-using-spherical-geometry-with-2d-geospatial-indexes/#distance-multiplier) ，它会将这个参数乘以距离返回，如指定为6371，返回的就是公里数。

    > db.runCommand({ geoNear : "places", near : [121.4905, 31.2646], spherical : true, maxDistance : 1/6371, distanceMultiplier: 6371})
    {
        "ns" : "mongo_test.places",
        "near" : "1110001100111100001011010110010111001000110011111101",
        "results" : [
            {
                "dis" : 0.5936558483047463,
                "obj" : {
                    "_id" : 115,
                    "coordinate" : {
                        "longitude" : 121.4915,
                        "latitude" : 31.25933
                    },
                    "title" : "仅售148元，市场价298元的星程上服假日酒店全日房一间入住一天，节假日通用，精致生活，品质享受",
                    "address" : "虹口区天水路90号"
                }
            },
			
			…
			…
			
        ],
        "stats" : {
            "time" : 0,
            "btreelocs" : 0,
            "nscanned" : 15,
            "objectsLoaded" : 9,
            "avgDistance" : 0.6348305174802911,
            "maxDistance" : 0.0001064199324957278
        },
        "ok" : 1
    }
	
注意上面的结果中`dis`的值，已经是km单位的了。

#结语
通过前面的案例演示，相信大家对MongoDB的地理位置特性已经比较了解。

MongoDB还有很多很酷的功能，地址位置支持仅是其中一项。希望以后能有机会为各位读者介绍如何结合Symfony2使用MongoDB进行应用开发的更多案例。

文中的演示程序已经发布在了Github上，地址是<https://github.com/henter/HenterGEO>，读者可以直接使用。


参考：

<http://docs.mongodb.org/manual/>

<https://wiki.10gen.com/pages/viewpage.action?pageId=21268367&navigatingVersions=true>

<http://en.wikipedia.org/wiki/Radian>

<http://www.scribd.com/doc/2569355/Geo-Distance-Search-with-MySQL>

<http://www.phpchina.com/resource/manual/mysql/spatial-extensions-in-mysql.html>

<http://derickrethans.nl/spatial-indexes-mysql.html>

<http://dev.mysql.com/doc/refman/5.6/en/spatial-extensions.html>

<http://dev.mysql.com/doc/refman/4.1/en/functions-that-test-spatial-relationships-between-geometries.html#function_distance>

<http://blog.nosqlfan.com/html/1811.html> 

<http://en.wikipedia.org/wiki/Geohash>


