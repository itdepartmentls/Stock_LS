<?php
require_once __DIR__ . '/conn.php';
@session_start();

if ( $_SESSION["user"]=="" or  $_SESSION["Namepro"]=="" or $_SESSION["iduser"]=="" )
  {
	 echo "<script>window.location = 'index.php';</script>";
	 exit;
  }



?>
<?php

  set_time_limit(360000);
  ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<style type="text/css">
body,td,th {
	font-family: "Phetsarath OT";
	font-size: 16px;
	color: #000;
}
#form1 table {
	font-weight: bold;
}
#form1 table {
	font-size: 24px;
}
.bd {
	color: #000;
}
.bg {
	color: #FFF;
}
.bg td {
	color: #00F;
}
.aa {
	color: #00F;
	font-size: 36px;
}
.aa {
	font-weight: bold;
	font-size: 20px;
	color: #003AFF;
}
a:link {
	color: #00F;
}
a:visited {
	color: #00F;
}
a:hover {
	color: #CCC;
}
a:countt {
	color: #CCC;
}
/* Paste this css to your style sheet file or under head tag */
/* This only works with JavaScript, 
if it's not present, don't show loader */
.no-js #loader { display: none;  }
.js #loader { display: block; position: absolute; left: 100px; top: 0; }
.se-pre-con {
	position: fixed;
	left: 0px;
	top: -200px;
	width: 100%;
	height: 200%;
	z-index: 9999;
	
}
#chartdiv {
	width		: 100%;
	height		: 400px;
	font-size	: 11px;
}	
</style>
<script src="js/jquery.min.js"></script>
<script src="js/modernizr.js"></script>
<script>
	//paste this code under head tag or in a seperate js file.
	// Wait for window load
	$(window).load(function() {
		// Animate loader off screen
		$(".se-pre-con").fadeOut("slow");;
	});
</script>
</head>

<body>

	
		
  <?php
flush();
	$yearr=date ('20y');
	$monthhh=date ('m');
	$dayyy=date ('20y-m-d');

	$catt=$_SESSION["Namepro"];
if($_SESSION["iduser"]=="404" or $_SESSION["iduser"]=="30" or $_SESSION["iduser"]=="194" or $_SESSION["iduser"]=="793"){	
$sql="SELECT
MONTH(usestock.Date) as 'mothnn',
usestock.Provinces as 'pro',
usestock.Items as 'Names',
count(if(usestock.Date LIKE '$yearr-01%',1,null)) as 'Month01',
count(if(usestock.Date LIKE '$yearr-02%',1,null)) as 'Month02',
count(if(usestock.Date LIKE '$yearr-03%',1,null)) as 'Month03',
count(if(usestock.Date LIKE '$yearr-04%',1,null)) as 'Month04',
count(if(usestock.Date LIKE '$yearr-05%',1,null)) as 'Month05',
count(if(usestock.Date LIKE '$yearr-06%',1,null)) as 'Month06',
count(if(usestock.Date LIKE '$yearr-07%',1,null)) as 'Month07',
count(if(usestock.Date LIKE '$yearr-08%',1,null)) as 'Month08',
count(if(usestock.Date LIKE '$yearr-09%',1,null)) as 'Month09',
count(if(usestock.Date LIKE '$yearr-10%',1,null)) as 'Month10',
count(if(usestock.Date LIKE '$yearr-11%',1,null)) as 'Month11',
count(if(usestock.Date LIKE '$yearr-12%',1,null)) as 'Month12'
from usestock 
GROUP BY pro
ORDER BY Month$monthhh DESC
";

}
	if($_SESSION["iduser"]<>"404" and $_SESSION["iduser"]<>"30" and $_SESSION["iduser"]<>"194" and $_SESSION["iduser"]<>"793"){ 
	$sql="SELECT
MONTH(usestock.Date) as 'mothnn',
usestock.Items as 'Names',
count(if(usestock.Date LIKE '$yearr-01%',1,null)) as 'Month01',
count(if(usestock.Date LIKE '$yearr-02%',1,null)) as 'Month02',
count(if(usestock.Date LIKE '$yearr-03%',1,null)) as 'Month03',
count(if(usestock.Date LIKE '$yearr-04%',1,null)) as 'Month04',
count(if(usestock.Date LIKE '$yearr-05%',1,null)) as 'Month05',
count(if(usestock.Date LIKE '$yearr-06%',1,null)) as 'Month06',
count(if(usestock.Date LIKE '$yearr-07%',1,null)) as 'Month07',
count(if(usestock.Date LIKE '$yearr-08%',1,null)) as 'Month08',
count(if(usestock.Date LIKE '$yearr-09%',1,null)) as 'Month09',
count(if(usestock.Date LIKE '$yearr-10%',1,null)) as 'Month10',
count(if(usestock.Date LIKE '$yearr-11%',1,null)) as 'Month11',
count(if(usestock.Date LIKE '$yearr-12%',1,null)) as 'Month12'
from usestock 
WHERE usestock.Provinces ='$catt'
GROUP BY Names
ORDER BY Month$monthhh DESC
LIMIT 5;";
	
}
	


//echo $sql;
	
	@mysqli_set_charset(@$conn,"utf8");
@$result = $conn->query($sql);
if($result->num_rows>0)
{
	
?>
<script src="js/amcharts.js"></script>
<script src="js/serial.js"></script>
<script src="js/light.js"></script>
<link rel="stylesheet" href="js/export.css" type="text/css" media="all" />
	<h4>Testt</h4>
<script>
var chart = AmCharts.makeChart( "chartdiv", {
  "type": "serial",
  "theme": "light",


  "dataProvider": [ 
	  <?php
	  @$result = $conn->query($sql);
	  while($row = $result->fetch_assoc() )  
			{
			$date=date_create($row[dates]);
			$dates=date_format($date,'d-m-Y');
	  		?>
	<?php  if($_SESSION["iduser"]<>"404" and $_SESSION["iduser"]<>"30" and $_SESSION["iduser"]<>"194" and $_SESSION["iduser"]<>"793"){  ?>
					{ 
    				"col": "<?=$row[Names]?>",
						"Month01": <?=floatval($row[Month01])?>,
						"Month02": <?=floatval($row[Month02])?>,
						"Month03": <?=floatval($row[Month03])?>,
						"Month04": <?=floatval($row[Month04])?>,
						"Month05": <?=floatval($row[Month05])?>,
						"Month06": <?=floatval($row[Month06])?>,
						"Month07": <?=floatval($row[Month07])?>,
						"Month08": <?=floatval($row[Month08])?>,
						"Month09": <?=floatval($row[Month09])?>,
						"Month10": <?=floatval($row[Month10])?>,
						"Month11": <?=floatval($row[Month11])?>,
						"Month12": <?=floatval($row[Month12])?>
						
  					},
	  <?php }?>
	  
	  	<?php  if($_SESSION["iduser"]=="404" or $_SESSION["iduser"]=="30" or $_SESSION["iduser"]=="194" or $_SESSION["iduser"]=="793"){  ?>
					{ 
    				"col": "<?=$row[pro]?>",
						"Month01": <?=floatval($row[Month01])?>,
						"Month02": <?=floatval($row[Month02])?>,
						"Month03": <?=floatval($row[Month03])?>,
						"Month04": <?=floatval($row[Month04])?>,
						"Month05": <?=floatval($row[Month05])?>,
						"Month06": <?=floatval($row[Month06])?>,
						"Month07": <?=floatval($row[Month07])?>,
						"Month08": <?=floatval($row[Month08])?>,
						"Month09": <?=floatval($row[Month09])?>,
						"Month10": <?=floatval($row[Month10])?>,
						"Month11": <?=floatval($row[Month11])?>,
						"Month12": <?=floatval($row[Month12])?>
						
  					},
	  <?php }?>

	  
  			<?php
  }
  ?>
  ],
	
	
  "valueAxes": [ {
    "gridColor": "#FFFFFF",
    "gridAlpha": 0.2,
    "dashLength": 0
  } ],
  "gridAboveGraphs": true,
  //"startDuration": 0.2,
  "chartCursor": {
		"enabled": true
	},
	"legend": {
		"enabled": true
	},
  "graphs": [
			 {
        "balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
		"labelText": "[[value]]",
    	"bullet": "round",
	  "color":"red",
    	"bulletSize": 15,
		"title": "(<?=$yearr?>-1 ມັງກອນ)",
    	"type": "smoothedLine",
		<?php if($monthhh<>'1'){ ?>"hidden" : true,<?php }?>
        "valueField": "Month01"
    } ,
	   {
        "balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
		"labelText": "[[value]]",
    	"bullet": "round",
	  "color":"red",
    	"bulletSize": 15,
		"title": "(<?=$yearr?>-2 ກຸມພາ)",
    	"type": "smoothedLine",
		<?php if($monthhh<>'2'){ ?>"hidden" : true,<?php }?>
        "valueField": "Month02"
		 
    } , {
        "balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
		"labelText": "[[value]]",
    	"bullet": "round",
	  "color":"red",
    	"bulletSize": 15,
		"title": "(<?=$yearr?>-3 ມີນາ)",
    	"type": "smoothedLine",
		<?php if($monthhh<>'3'){ ?>"hidden" : true,<?php }?>
        "valueField": "Month03"
    } ,
	   {
        "balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
		"labelText": "[[value]]",
    	"bullet": "round",
	  "color":"red",
    	"bulletSize": 15,
		"title": "(<?=$yearr?>-4 ເມສາ)",
    	"type": "smoothedLine",
	<?php if($monthhh<>'4'){ ?>"hidden" : true,<?php }?>
        "valueField": "Month04"
    } ,
	   {
        "balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
		"labelText": "[[value]]",
    	"bullet": "round",
	  "color":"red",
    	"bulletSize": 15,
		"title": "(<?=$yearr?>-5 ພຶດສະພາ)",
    	"type": "smoothedLine",
		<?php if($monthhh<>'5'){ ?>"hidden" : true,<?php }?>
        "valueField": "Month05"
    } ,
	   {
        "balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
		"labelText": "[[value]]",
    	"bullet": "round",
	  "color":"red",
    	"bulletSize": 15,
		"title": "(<?=$yearr?>-6 ມີຖຸນາ)",
    	"type": "smoothedLine",
		<?php if($monthhh<>'6'){ ?>"hidden" : true,<?php }?>
        "valueField": "Month06"
    } ,
	   {
        "balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
		"labelText": "[[value]]",
    	"bullet": "round",
	  "color":"red",
    	"bulletSize": 15,
		"title": "(<?=$yearr?>-7 ກໍລະກົດ)",
    	"type": "smoothedLine",
		<?php if($monthhh<>'7'){ ?>"hidden" : true,<?php }?>
        "valueField": "Month07"
    } ,
	   {
        "balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
		"labelText": "[[value]]",
    	"bullet": "round",
	  "color":"red",
    	"bulletSize": 15,
		"title": "(<?=$yearr?>-8 ສິງຫາ)",
    	"type": "smoothedLine",
		<?php if($monthhh<>'8'){ ?>"hidden" : true,<?php }?>
        "valueField": "Month08"
    } ,
	   {
        "balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
		"labelText": "[[value]]",
    	"bullet": "round",
	  "color":"red",
    	"bulletSize": 15,
		"title": "(<?=$yearr?>-9 ກັນຍາ)",
    	"type": "smoothedLine",
		<?php if($monthhh<>'9'){ ?>"hidden" : true,<?php }?>
        "valueField": "Month09"
    } ,
	   {
        "balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
		"labelText": "[[value]]",
    	"bullet": "round",
	  "color":"red",
    	"bulletSize": 15,
		"title": "(<?=$yearr?>-10 ຕຸລາ)",
    	"type": "smoothedLine",
		<?php if($monthhh<>'10'){ ?>"hidden" : true,<?php }?>
        "valueField": "Month10"
    } ,
	   {
        "balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
		"labelText": "[[value]]",
    	"bullet": "round",
	  "color":"red",
    	"bulletSize": 15,
		"title": "(<?=$yearr?>-11 ພະຈິກ)",
    	"type": "smoothedLine",
		<?php if($monthhh<>'11'){ ?>"hidden" : true,<?php }?>
        "valueField": "Month11"
    } ,
	   {
        "balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
		"labelText": "[[value]]",
    	"bullet": "round",
	  "color":"red",
    	"bulletSize": 15,
		"title": "(<?=$yearr?>-12 ທັນວາ)",
    	"type": "smoothedLine",
		<?php if($monthhh<>'12'){ ?>"hidden" : true,<?php }?>
        "valueField": "Month12"
    } 
			
			],
  "chartCursor": {
    "categoryBalloonEnabled": false,
    "cursorAlpha": 0,
    "zoomable": false
  },
  "categoryField": "col",
  "categoryAxis": {
    "gridPosition": "start"
  },
  "categoryAxis": {
    "gridPosition": "start",
    "labelRotation": 25
  },
  "export": {
    "enabled": false
  }
} );
</script>
<?php	
}
?>
<br />
<div id="chartdiv"></div>
</body>
</html>