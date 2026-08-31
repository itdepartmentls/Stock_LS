<?php
require_once __DIR__ . '/includes/conn.php';
@session_start();
if ($_SESSION["user"] == "") {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Stock.xls");
?>
 <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<style type="text/css">
body,td,th {
	font-family: "Phetsarath OT";
}
</style>
<style>
#customers {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
	font: "Phetsarath OT";
	
}

#customers td, #customers th {
    border: 1px solid #666;
    padding: 4px;
}

#customers tr:nth-child(even){background-color: #FFFFFF;}

#customers tr:hover {background-color: #0F0;}

#customers th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #FFFFFF;
    color: white;
}
body,td,th {
	font-family: "Phetsarath OT";
}
</style>
</head>

<body>


<?php
$sql="SELECT
id,
Item_code,
Items,
Vendor,
Use_For,
Type,
Unit,
Groupp,
Stock_TMD,
Section,
picture,
User_Stock
FROM stock";



//echo sql
mysqli_set_charset(@$conn,"utf8");
$result=$conn->query($sql);


//if ($result->num_rows>0) 

echo "<div >";
			
	
	echo "<table  width='100%' id='customers' cellspacing='0' border='1' >";
	
	echo "<tr  >";
	echo "<td width='5%'  ><div align='center'> <h4>Items</h4></div></td>";
	echo "<td width='5%'  ><div align='center'>  <h4>Item Code</h4></div></td>";
	echo "<td width='5%'  ><div align='center'> <h4>Vendor</h4></div></td>";
	echo "<td width='5%'  ><div align='center'> <h4>ໃຊ້ກັບອຸປະກອນ</h4></div></td>";
	echo "<td width='5%'  ><div align='center'> <h4>ຫົວໜ່ວຍ</h4></div></td>";
	echo "<td width='5%'  ><div align='center'> <h4>ຈຳນວນສູນກາງ</h4></div></td>";
	echo "<td width='5%'  ><div align='center'> <h4>ຈຳນວນ</h4></div></td>";
	echo "<td width='5%'  ><div align='center'> <h4>ກຸ່ມອຸປະກອນ</h4></div></td>";
	echo "<td width='5%'  ><div align='center'> <h4>ຮູບອູປະກອນ</h4></div></td>";
	echo "<td width='5%'  ><div align='center'> <h4>ພາກສ່ວນ</h4></div></td>";
	 echo "</tr>";
			
	 
	
	//$i=2;
	$l=1;
	while($row=$result->fetch_assoc())
	
	{
echo "<td width='10%'><div align='center'>$row[Items]</div></td>";
	echo "<td width='10%'><div align='center'>$row[Item_code]</div></td>";
		echo "<td width='10%'><div align='center'>$row[Vendor]</div></td>";
		echo "<td width='10%'><div align='center'>$row[Use_For]</div></td>";
		echo "<td width='10%'><div align='center'>$row[Type]</div></td>";
		echo "<td width='10%'><div align='center'>$row[Unit]</div></td>";
		echo "<td width='10%'><div align='center'>$row[Stock_TMD]</div></td>";
		echo "<td width='10%'><div align='center'>$row[Groupp]</div></td>";
		echo "<td width='10%'><div align='center'>$row[picture]</div></td>";
		echo "<td width='10%'><div align='center'>$row[Section]</div></td>";

     echo "</tr>";
	 
	
	 

}


	








?>
	</body>
</html>
