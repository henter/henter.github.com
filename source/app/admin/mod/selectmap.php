<?php
require dirname(__FILE__).'/../inc/common.inc.php';
    $mapkey = MAPKEY;
    $mapx = $_GET[mapx] ? $_GET[mapx] : '23.185813175302915';
    $mapy = $_GET[mapy] ? $_GET[mapy] : '113.17703247070312';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<title>Google Maps JavaScript API Example</title>
<script src="../images/js/jquery.js" type="text/javascript"></script>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?=$mapkey?>"
type="text/javascript" charset="utf-8"></script>


<script type="text/javascript">
//<![CDATA[
function load() {
    if (GBrowserIsCompatible()) {
        var px = <?=$mapx?>;
        var py = <?=$mapy?>;
        var zo = 12;
        var map = new GMap2(document.getElementById("map"));
        var point = new GLatLng(px, py);

        map.addControl(new GSmallMapControl());
        
        map.enableDoubleClickZoom();//允许鼠标双击放大(左键)和缩小(右键)
        map.enableScrollWheelZoom();//允许鼠标滚轮放大和缩小
        map.enableContinuousZoom(); //缩放按钮
        map.setCenter(point, zo);  //设置中心
      
        //点击获取地理坐标
        clickListener=GEvent.addListener(map, "click", function(marker,point)
         {
              if (marker) 
              {
                    map.removeOverlay(marker);
              }else{
                    map.clearOverlays();
                    map.addOverlay(new GMarker(point));
              }
              if(point)
              {
                parent.document.all.map.value = point.lat() + ',' + point.lng();
              }
         }
        ); 
    }
}


//]]>
</script>


</script>
  </head>
  <body onload="load()" onunload="GUnload()" style="margin:0;padding:0" scroll="no">

    <div id="map" style="width:100%;height:350px;"></div>
  </body>

</html>


    
    
    
    