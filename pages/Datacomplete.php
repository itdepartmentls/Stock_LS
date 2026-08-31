<?php
require_once __DIR__ . '/../includes/conn.php';
@session_start();
if ( $_SESSION["user"]=="" or  $_SESSION["Namepro"]=="" or $_SESSION["iduser"]==""  )
  {
	 echo "<script>window.location = 'index.php';</script>";
	 exit;
  }

?>

<html>
<head>
 
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
	<link rel="stylesheet" href="css/pages/Datacomplete.css">

<body>
	
	 <table class="table table-hover  "  >
    <thead>
      <tr  style="font-size: 15px;color:blue" >
        
        <th width="40%">trtr</th>
		  <th width="20%">trtr</th>
		  <th width="10%">trtr</th>
		  <th width="10%">trtr</th>
		  <th width="20%">trtr</th>

      </tr>
	 </thead><?php
		$Province=$_SESSION["Namepro"];
	
	if($_SESSION["iduser"]=="404" or $_SESSION["iduser"]=="30" or $_SESSION["iduser"]=="194" or $_SESSION["iduser"]=="793"){		
$sql="SELECT*FROM followequment 
WHERE Date_Goto <> ''
ORDER BY followequment.Date_Goto desc ";
	}
elseif($_SESSION["iduser"]<>"404" and $_SESSION["iduser"]<>"30" and $_SESSION["iduser"]<>"194" and $_SESSION["iduser"]<>"793"){		
$sql="SELECT*FROM followequment 
WHERE followequment.Cat_Province LIKE '$Province' and Date_Goto <> ''
ORDER BY followequment.Date_Goto desc ";
	}
		
	
		

		
		
@mysqli_set_charset(@$conn,"utf8");
$result=$conn->query($sql);
	
			?>
		<?php
while($row = $result->fetch_assoc())
	{
	$id=$row['id'];
	$Name_Come=$row['Name_Come'];
	if($row['User_TMD']<>''){
		
	$show='style="color: green"';
	}
	if($row['STT_Fix']=='ຕາຍ'){
		$colort='style="color: red"';
	}else{
		$colort='style="color: green"';
	}
	
		
	?>
				<tbody>  
 
	 <tr style="font-size: 14px;" >
 
        <td width="40%"   class="text-primary" ><a href="process.php?id=<?php echo $row['id']?>&Name_Come=<?php echo $row['Name_Come']?>" target="_blank"><strong><i class="fa-duotone fa-server"></i> ອຸປະກອນ :</strong> <?php echo $row['Name_Come']?><br><strong><i class="fa-regular fa-location-dot"></i> ຈາກແຂວງ :</strong> <?php echo $row['Cat_Province']?></a></td>
  <td  width="20%" <?php echo $colort?>><strong>ໃຊ້ຢູ່ສະຖານທີ່ :</strong> <?php echo $row['address']?></td>    
<td  width="10%" <?php echo $colort?>><strong>ຊີລຽວນາມເບີ :</strong> <?php echo $row['NumBer_S']?></td>		
<td  width="10%" <?php echo $colort?>><strong>ສະຖານະ :</strong> <?php echo $row['STT_Fix']?></td>
	
		  <td  width="20%" <?php echo $colort?>><strong>ວັນທີ່ :</strong> <?php echo $row['Date']?></td>
		 
		 
		
<?php }?>

      </tr>
	
    </tbody>
		</table>
</body>
</html>
