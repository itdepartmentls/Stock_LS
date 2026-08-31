<?php
require_once __DIR__ . '/../includes/conn.php';
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=ເບິກອຸປະກອນ.xls");
?>
<?php
@session_start();
if ( $_SESSION["user"]=="" and  $_SESSION["Namepro"]=="" and $_SESSION["iduser"]==""  )
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
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
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
		$Choi=$_POST["Choi"];
		$Equment1 = $_POST["Equment1"];

		if ($Pro<>'all' ){
$sql="SELECT*FROM request a
WHERE a.$Choi > '$start 00:00:00' and a.$Choi < '$end 23:59:59' and a.Province like '$Pro' and a.Items = '$Equment1'
ORDER BY a.$Choi asc";
		}
		
		if ($Pro=='all' ){
$sql="SELECT*FROM request a
WHERE a.$Choi > '$start 00:00:00' and a.$Choi < '$end 23:59:59' and a.Items = '$Equment1'
ORDER BY a.Province asc";
			
		}
		
		
	
	
		

		
		
mysqli_set_charset(@$conn,"utf8");
$result=$conn->query($sql);
		if ($result->num_rows > 0) {
			?>
		 <div align="center">     
  <table class="table-hover table-bordered table-sm"   border="1"  >
    <thead>
      <tr class="text-body" bgcolor="#E8E8E8" align="center">
        <th>ສາງ</th>
        <th>ອຸປະກອນ</th>
		  <th>ຂໍ້ມູນຮ້ານທີ່ຈັດຊື້</th>
        <th>ວັນທີຂໍເບິກ</th>
		  <th>ວັນທີ່ເບິກອຸປະກອນ</th>
		  
		  <th>ຈຳນວນຂໍເບິກ</th>
		  <th>ຈຳນວນເບິກໃຫ້ໄດ້</th>
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
		 
 <td align="center" ><?php echo $row['Province']?></td>
		  
		  
		
		
	
        <td align="center" ><?php echo $row['Items']?></td>
		  <td align="center"><?php echo $row['Vendor']?></td>
	
        <td align="center"><?php echo $row['dateRe']?></td>
		   <td align="center"><?php echo $row['DateComfirm']?></td>
	
	
		  
       
		
		  
		  <td align="center" class="text-danger"><?php echo $row['Unit']?></td>
		  <td align="center" class="text-primary"><?php echo $row['Give']?></td>
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