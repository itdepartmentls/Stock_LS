<?php
require_once __DIR__ . '/../includes/conn.php';
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=ລາຍງານການຮັບອຸປະກອນ.xls");
?>
<?php
@session_start();
if ( $_SESSION["user"]=="" or  $_SESSION["Namepro"]=="" or $_SESSION["iduser"]==""  )
  {
	 echo "<script>window.location = 'index.php';</script>";
	 exit;
  }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
		<link rel="shortcut icon" href="image/logoETL.jpg">
	<link rel="stylesheet" href="css/vendor/bootstrap/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="js/vendor/bootstrap/bootstrap.bundle.min.js"></script>
		<link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">
		
        
        <!-- Favicon-->
     
        <!-- Core theme CSS (includes Bootstrap)-->
      
	
		<style>
    .bs-example{
        margin: 20px;
    }
</style>
	
		<style type="text/css">
body,td,th {
	font-family: "Phetsarath OT";
}
			.navbarr {
 

  position: fixed;
  
}
    </style>
<?php
	date_default_timezone_set("Asia/Bangkok");
		$Pro=$_POST["Pro"];
		$start=$_POST["start"];
		$end=$_POST["end"];

		if($Pro=='Center'){
$sql="SELECT*FROM stockinput a
WHERE a.Date > '$start 00:00:00' and a.Date < '$end 23:59:59'
ORDER BY a.Date asc ";
			
		}
		if ($Pro<>'Center' ){
$sql="SELECT*FROM stock_provinceinput a
WHERE a.Date_Pro > '$start 00:00:00' and a.Date_Pro < '$end 23:59:59' and a.Provinces like '$Pro'
ORDER BY a.Date_Pro asc";
		}
		
		if ($Pro=='all' ){
$sql="SELECT*FROM stock_provinceinput a
WHERE a.Date_Pro > '$start 00:00:00' and a.Date_Pro < '$end 23:59:59'
ORDER BY a.Provinces asc";
			
		}
		
	
	
		

		
		
mysqli_set_charset(@$conn,"utf8");
$result=$conn->query($sql);
		if ($result->num_rows > 0) {
			?>
		 <div align="center">     
  <table class="table-hover table-bordered table-sm"  border="1" >
    <thead>
      <tr class="text-body" bgcolor="#E8E8E8" align="center">
        <th>ສາງ</th>
        <th>ອຸປະກອນ</th>
        <th>ວັນທີ</th>
		  <th>ຂໍ້ມູນຮ້ານທີ່ຈັດຊື້</th>
		  <th>ຈຳນວນຮັບເຂົ້າ</th>
		  <th>ຫົວໜ່ວຍ</th>
		 
		  <th>ພາກສ່ວນ</th>
		  
		  
		  
       
      </tr>
    </thead>
    <tbody>
		<?php
while($row = $result->fetch_assoc())
	{
	$id=$row['id'];
		
	
	
	?>
      <tr>
		   <?php if ($Pro<>'Center' or  $Pro=='all'){ ?>
 <td align="center" ><?php echo $row['Provinces']?></td>
		  
		   <?php }
		  elseif ($Pro=='Center' ){ ?>
		  <td align="center" >ສູນກາງ</td>
		  <?php } ?>
        <td align="center" ><?php echo $row['Items']?></td>
		  <?php if ($Pro<>'Center'  or  $Pro=='all' ){ ?>
        <td align="center"><?php echo $row['Date_Pro']?></td>
		  <?php }
		  elseif ($Pro=='Center' ){ ?>
		  
        <td align="center"><?php echo $row['Date']?></td>
		  <?php } ?>
		  <td align="center"><?php echo $row['Vendor']?></td>
		  <td align="center" class="text-primary"><?php echo $row['Unit']?></td>
		  <td align="center"><?php echo $row['Type']?></td>
		
		  <td align="center"><?php echo $row['Section']?></td>
		  
		  
		  
		  
       
      
      </tr>
    </tbody>
	  
	  <?php }
		}
		else {
	
	
	 echo " <center><font color='#ff0000'><font size='24' >ບໍ່ມີຂໍ້ມູນ</center>";
} ?>
  </table>
</div>