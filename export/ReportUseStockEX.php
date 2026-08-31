<?php
require_once __DIR__ . '/../includes/conn.php';
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=ລາຍງານການນຳໃຊ້ອຸປະກອນຂອງສາຂາ.xls");
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
        
	
	
	
	<style type="text/css">
body,td,th {
	font-family: "Phetsarath OT";
	font-size: 16;
}
    </style>
<?php
	date_default_timezone_set("Asia/Bangkok");
		$Pro=$_POST["Pro"];
		$start=$_POST["start"];
		$end=$_POST["end"];
		$Equment=$_POST["Equment"];
		$namePro=$_SESSION["Namepro"];

if($_SESSION["iduser"]=="404" or $_SESSION["iduser"]=="30" or $_SESSION["iduser"]=="194" or $_SESSION["iduser"]=="793" or $_SESSION["iduser"] == '51' or $_SESSION["iduser"] == '43' or $_SESSION["iduser"] == '41' or $_SESSION["iduser"] == '378' or $_SESSION["iduser"] == '387' or $_SESSION["iduser"] == '514' or $_SESSION["iduser"] == '423' or $_SESSION["iduser"] == '166' or $_SESSION["iduser"] == '167' or $_SESSION["iduser"] == '168' or $_SESSION["iduser"] == '624' or $_SESSION["iduser"] == '190' or $_SESSION["iduser"] == '197' or $_SESSION["iduser"] == '718' or $_SESSION["iduser"] == '816' or $_SESSION["iduser"] == '835' or $_SESSION["iduser"] == '836' or $_SESSION["iduser"] == '837' or $_SESSION["iduser"] == '177' or $_SESSION["iduser"] == '180' or $_SESSION["iduser"] == '181' or $_SESSION["iduser"] == '487' or $_SESSION["iduser"] == '686'){
		if($Pro=='' and $Equment==''){
$sql="SELECT*FROM usestock a
WHERE a.Date > '$start 00:00:00' and a.Date < '$end 23:59:59'
ORDER BY a.Date asc ";
			
		}
		elseif ($Pro<>'' and $Equment==''){
$sql="SELECT*FROM usestock a
WHERE a.Date > '$start 00:00:00' and a.Date < '$end 23:59:59' and a.Provinces like '$Pro'
ORDER BY a.Date asc ";
			
		}
		elseif ($Pro<>'' and $Equment<>''){
$sql="SELECT*FROM usestock a
WHERE a.Date > '$start 00:00:00' and a.Date < '$end 23:59:59' and a.Provinces like '$Pro' and a.Items like '$Equment'
ORDER BY a.Date asc ";
			
		}
		elseif ($Pro=='' and $Equment<>''){
$sql="SELECT*FROM usestock a
WHERE a.Date > '$start 00:00:00' and a.Date < '$end 23:59:59'  and a.Items like '$Equment'
ORDER BY a.Date asc ";
			
		}
	} else {
		
			if($Equment==''){
$sql="SELECT*FROM usestock a
WHERE a.Date > '$start 00:00:00' and a.Date < '$end 23:59:59' and a.Provinces like '$namePro'
ORDER BY a.Date asc ";
			
		}
	
		elseif ( $Equment<>''){
$sql="SELECT*FROM usestock a
WHERE a.Date > '$start 00:00:00' and a.Date < '$end 23:59:59' and a.Provinces like '$namePro' and a.Items like '$Equment'
ORDER BY a.Date asc ";
			
		}
	
		
		
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
        <th>ນຳໃຊ້ອຸປະກອນ</th>
        
        <th>ວັນທີ</th>
		  <th>ຈຳນວນໃຊ້ໄປ</th>
		  <th>ຫົວໜ່ວຍ</th>
		   <th>ຢູ່ສະຖານີ</th>
		  <th>ຊີລຽວນາມເບີ</th>
		  <th >ເລກທີ</th>
		  <th >ເລກທີ Ticket</th>
		  <th >ໝາຍເຫດ</th>
		  <th>ພາກສ່ວນ</th>
		  <th>ຜູ້ຕັດ Stock</th>
		  
		  
       
      </tr>
    </thead>
    <tbody>
		<?php
while($row = $result->fetch_assoc())
	{
	$id=$row['id'];
		
	
	
	?>
      <tr>
 <td align="center" ><?php echo $row['Provinces']?></td>
        <td align="center" ><?php echo $row['Items']?></td>
        <td align="center"><?php echo $row['Date']?></td>
		  <td align="center"><?php echo $row['Unit']?></td>
		  <td align="center"><?php echo $row['Type']?></td>
		  <td align="center"><?php echo $row['Station']?></td>
		  <td align="center"><?php echo $row['Series_Number']?></td>
		  
		   
		  <td align="center"><?php echo $row['OA']?></td>
		  <td align="center"><?php echo $row['Ticket']?></td>
		  <td align="center"><?php echo $row['Remark']?></td>
		  <td align="center"><?php echo $row['Section']?></td>
		  <td align="center"><?php echo $row['User']?></td>
		  
		  
		  
       
      
      </tr>
    </tbody>
	  
	  <?php }
		}
		else {
	
	
	 echo " <center><font color='#ff0000'><font size='24' >ບໍ່ມີຂໍ້ມູນ</center>";
} ?>
  </table>
</div>