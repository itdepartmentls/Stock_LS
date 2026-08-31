<?php
require_once __DIR__ . '/../includes/conn.php';
@session_start();
if ($_SESSION["user"] == "" ) {
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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha256-OFRAJNoaD8L3Br5lglV7VyLRf0itmoBzWUoM+Sji4/8=" crossorigin="anonymous"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>

	<link rel="stylesheet" href="js/pro.min.js">
	<link rel="stylesheet" href="css/all.css">
	<link rel="stylesheet" href="css/all.min.css">
	<title>ການດຳເນີນການ</title>

	<!-- Demo stylesheet -->

</head>
<style type="text/css">
	body,
	td,
	th {
		font-family: "Phetsarath OT";
	}

	.right {
		float: right;
	}
</style>

<body>
	<?php
	date_default_timezone_set("Asia/Bangkok");
	$idd = $_REQUEST['id'];
	$Name_Come = $_REQUEST['Name_Come'];
	$S_Status = $_REQUEST['S_Status'];


	$sql = "SELECT*FROM followequment 
WHERE followequment.id LIKE '$idd'  ";

	mysqli_set_charset(@$conn, "utf8");
	$result = $conn->query($sql);
	$row = $result->fetch_assoc();

	$sql2 = "SELECT*FROM stock 
WHERE stock.Items LIKE '$Name_Come'  ";

	mysqli_set_charset(@$conn, "utf8");
	$result2 = $conn->query($sql2);
	$row2 = $result2->fetch_assoc();
	if ($row['User_TMD'] <> '') {
		$theme = "btn-primary";
	} else {

		$theme = "btn-secondary";
	}
	if ($row['STT_Fix'] == 'ໃຊ້ໄດ້ປົກກະຕິ') {
		$themefix = "btn-primary";
	} elseif ($row['STT_Fix'] == 'ຕາຍ') {

		$themefix = "btn-danger";
	} elseif ($row['STT_Fix'] == '') {

		$themefix = "btn-secondary";
	}


	?>

	<?php
	if ($row['User_TMD'] == '' and ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793")) {
		date_default_timezone_set("Asia/Bangkok");
		$date_time = date('20y-m-d G:i:s');

		$sql = "UPDATE followequment SET User_TMD='" . $_SESSION["user"] . " $date_time' WHERE id='$idd'";

		if ($conn->query($sql) === TRUE) {
		}
	} ?>
	<?php
	if ($row['Team_FIx'] == '' and ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "51" or $_SESSION["iduser"] == "197" or $_SESSION["iduser"] == "190" or $_SESSION["iduser"] == "718")) {
		date_default_timezone_set("Asia/Bangkok");
		$date_time = date('20y-m-d G:i:s');

		$sql = "UPDATE followequment SET Team_FIx='" . $_SESSION["user"] . " $date_time' WHERE id='$idd'";

		if ($conn->query($sql) === TRUE) {
		}
	} ?>


	<div>

		<br>
		<!-- Nav tabs -->
		<?php if ($S_Status == 'ສ້ອມແປງ') { ?>
			<ul class="nav nav-tabs row" role="tablist">
				<li class="nav-item col-sm-2">
					<a class="nav-link active btn-success" data-toggle="tab" href="#home">1 <?php echo $row['Cat_Province'] ?></a>
				</li><img class="right" src="image/message.gif" style="width:40px;height:40px;">
				<li class="nav-item col-sm-2">
					<a class="nav-link <?php echo $theme ?>" data-toggle="tab" href="#menu1">2 <?php if ($row['User_TMD'] <> '') {
																									echo "ພະແນກຄຸ້ມຄອງເຕັກນິກ";
																								} else {
																									echo "ຍັງບໍ່ມີການຮັບຮູ້";
																								} ?></a>
				</li><img class="right" src="image/shipping.gif" style="width:40px;height:40px;">
				<li class="nav-item col-sm-2">
					<a class="nav-link <?php echo $themefix ?>" data-toggle="tab" href="#menu2">3 ກວດສອບ</a>
				</li><img class="right" src="image/repair-tools.gif" style="width:40px;height:40px;">

				<?php if ($row['STT_Fix'] == 'ໃຊ້ໄດ້ປົກກະຕິ') { ?><li class="nav-item col-sm-2">
						<a class="nav-link <?php echo $theme ?>" data-toggle="tab" href="#menu3">4 ຈັດສົ່ງ</a>
					</li><img class="right" src="image/crane.gif" style="width:40px;height:40px;"> <?php } ?>
			</ul>


			<!-- Tab panes -->
			<div class="tab-content ">
				<div id="home" class="container-xl tab-pane active"><br>
					<a class="right" href="<?php echo $row2['picture'] ?>" target="_blank"><img class="right" src="<?php echo $row2['picture'] ?>" style="width:200px;height:120px;"></a>
					<h6><i class="fa-solid fa-user-vneck-hair"></i> <strong><?php echo $row['UserProvince'] ?></strong></h6>
					<strong><i class="fa-regular fa-location-dot"></i> ຈາກສາງ :</strong> <?php echo $row['Cat_Province'] ?><br>
					<strong><i class="fa-duotone fa-server"></i> ອຸປະກອນ :</strong> <?php echo $row['Name_Come'] ?><br>
					<strong><i class="fa-light fa-keyboard"></i> ຊີລຽວນາມເບີ :</strong> <?php echo $row['NumBer_S'] ?><br>
					<strong><i class="fa-light fa-map-location-dot"></i> ໃຊ້ຢູ່ສະຖານທີ່ :</strong> <?php echo $row['address'] ?><br>
					<strong><i class="fa-light fa-sensor-triangle-exclamation"></i> ໝາຍເຫດ :</strong> <?php echo $row['Remark_Pro'] ?>
					<hr>
				</div>
				<div id="menu1" class="container-xl tab-pane fade"><br>
					<h6><i class="fa-solid fa-user-vneck-hair"></i> <strong>ຜູ້ຮັບ: <?php echo $row['User_TMD'] ?></strong></h6>
					<?php if ($row['Remark_TMD'] == '' and ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793")) { ?><strong>ເນື້ອໃນ</strong>:<br>
						<form action="process.php" name="form3" method="post">
							<input hidden="" type="text" name="id" value="<?php echo $row['id'] ?>">
							<input type="text" name="TMDRemark" class="form-control" required>
							<strong>ພາກສ່ວນສ້ອມແປງ</strong>:
							<select class="form-control" required name="section">
								<option value="">****ເລືອກພາກສ່ວນສ້ອມແປງ****</option>
								<option value="Km21">Km21</option>
								<option value="Internet">Internet</option>
								<option value="Power Supply">Power Supply</option>
								<option value="Transmission">Transmission</option>
								<option value="TSC ສະໜັບສະໜູນເຕັກນິກ">TSC ສະໜັບສະໜູນເຕັກນິກ</option>
							</select><br>

							<button type="submit" class="btn btn-info" name="TMDUPDate" style="width: 200px;"><i class="fa-sharp fa-solid fa-forward"></i> Submit</button>

						</form> <?php } else { ?>

						<strong><i class="fa-duotone fa-file-exclamation"></i> ເນື້ອໃນ</strong>:<?= $row['Remark_TMD']; ?><br>
						<strong><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່ສົ່ງກວດສອບ</strong>:<?= $row['Date_TMD']; ?><br>
						<strong><i class="fa-solid fa-hand-sparkles"></i> ພາກສ່ວນສ້ອມແປງ</strong>:<?= $row['Team_fixed']; ?>

					<?php } ?>
					<hr>
				</div>
				<div id="menu2" class="container-xl tab-pane fade"><br>
					<h6><i class="fa-solid fa-user-vneck-hair"></i> <strong>ຜູ້ກວດສອບ: <?php echo $row['Team_FIx'] ?></strong></h6>
					<?php if ($row['Team_FIx'] <> '' and $row['STT_Fix'] == ''  and ($_SESSION["iduser"] == "51" or $_SESSION["iduser"] == '41' or $_SESSION["iduser"] == '378' or $_SESSION["iduser"] == '387' or $_SESSION["iduser"] == '514' or $_SESSION["iduser"] == '423' or $_SESSION["iduser"] == '166' or $_SESSION["iduser"] == '167' or $_SESSION["iduser"] == '168' or $_SESSION["iduser"] == '624' or $_SESSION["iduser"] == '190' or $_SESSION["iduser"] == '197'
					 or $_SESSION["iduser"] == '816'or $_SESSION["iduser"] == '404')) { ?>

						<form action="process.php" name="form4" method="post">
							<input hidden="" type="text" name="id" value="<?php echo $row['id'] ?>">

							<label><strong><i class="fa-thin fa-square-poll-vertical"></i> ຜົນກວດສອບ</strong><select type="text" name="stt" id="stt" class="form-control" required />
								<option value="ໃຊ້ໄດ້ປົກກະຕິ">ໃຊ້ໄດ້ປົກກະຕິ</option>
								<option value="ຕາຍ">ຕາຍ</option>


								</select>
							</label><br>
							<strong><i class="fa-light fa-sensor-triangle-exclamation"></i> ວີທີການສ້ອມແປງ</strong><input type="text" name="fixRemark" class="form-control" required><br>
							<button type="submit" class="btn btn-info" name="CheckUPDate" style="width: 200px;"><i class="fa-sharp fa-solid fa-forward"></i> Submit</button>

						</form> <?php } elseif ($row['Date_Fix'] <> '' and $row['STT_Fix'] <> '') { ?>
						<strong><i class="fa-thin fa-square-poll-vertical"></i> ຜົນກວດສອບ: </strong> <?php echo $row['STT_Fix'] ?><br>
						<strong><i class="fa-light fa-sensor-triangle-exclamation"></i> ວີທີການສ້ອມແປງ:</strong> <?php echo $row['Remark_Fix'] ?><br>
						<strong><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່ຢືນຢັນກວດສອບ:</strong> <?php echo $row['Date_Fix'] ?><br>

					<?php } ?>
					<hr>
				</div>
				<div id="menu3" class="container-xl tab-pane fade"><br>
					<?php if (($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793") and $row['Go_Province'] == '') { ?>
						<form action="process.php" name="form56" method="post">
							<label><strong><i class="fa-sharp fa-solid fa-warehouse-full"></i> ສົ່ງໄປສາງ</strong>
								<input hidden="" type="text" name="Name_Come" value="<?php echo $row['Name_Come'] ?>">
								<input hidden="" type="text" name="id" value="<?php echo $row['id'] ?>">
								<input hidden="" type="text" name="formpro" value="<?php echo $row['Cat_Province'] ?>">
								<select required type="text" name="provinceee" id="provinceee" class="form-control" style="width: 300px;" />
								<option value="<?php echo $row['Cat_Province'] ?>"><?php echo $row['Cat_Province'] ?></option>
								<option value="ສູນກາງ">Head Office</option>
								<option value="SNK">Stock Sanakham</option>
								<option value="MN">Stock Meuong Nan</option>
								




								</select>
							</label>
							<label><button type="submit" class="btn btn-info" name="stockUPDate" style="width: 200px;"><i class="fa-solid fa-cloud-plus"></i> Submit</button></label>
						</form> <?php } elseif ($row['Go_Province'] <> '') { ?>
						<img class="right" src="image/complete.gif" style="width:200px;height:160px;">
						<strong><i class="fa-regular fa-location-dot"></i> ຈາກສາງ :</strong> <?php echo $row['Cat_Province'] ?>
						<hr>
						<strong><i class="fa-duotone fa-server"></i> ອຸປະກອນ :</strong> <?php echo $row['Name_Come'] ?>
						<hr>
						<strong><i class="fa-light fa-keyboard"></i> ຊີລຽວນາມເບີ :</strong> <?php echo $row['NumBer_S'] ?>
						<hr>
						<strong><i class="fa-thin fa-square-poll-vertical"></i> ຜົນກວດສອບ: </strong> <?php echo $row['STT_Fix'] ?>
						<hr>
						<strong><i class="fa-light fa-sensor-triangle-exclamation"></i> ວີທີການສ້ອມແປງ:</strong> <?php echo $row['Remark_Fix'] ?>
						<hr>
						<strong><i class="fa-sharp fa-solid fa-warehouse-full"></i> ສົ່ງກັບສາງ:</strong> <?php echo $row['Go_Province'] ?> <i class="fa-duotone fa-calendar-days"></i> <strong>ວັນທີ່:</strong> <?php echo $row['Date_Goto'] ?><br>

					<?php } ?>
					<hr>
				</div>
			</div>
		<?php } ?>
	</div>

	<?php
	if (isset($_POST["TMDUPDate"])) {
		date_default_timezone_set("Asia/Bangkok");
		$date_time = date('20y-m-d G:i:s');

		$sql = "UPDATE followequment SET Remark_TMD='" . $_POST['TMDRemark'] . "',Date_TMD='$date_time',Team_fixed='" . $_POST['section'] . "'
		 WHERE id='" . $_POST["id"] . "'";
		@mysqli_set_charset(@$conn, "utf8");
		if ($conn->query($sql) === TRUE) { ?>
			<script>
				window.top.location = "follow.php";
			</script>

	<?php }
	} ?>
	<?php
	if (isset($_POST["CheckUPDate"])) {
		date_default_timezone_set("Asia/Bangkok");
		$date_time = date('20y-m-d G:i:s');

		if ($_POST['stt'] == 'ໃຊ້ໄດ້ປົກກະຕິ') {
			$sql = "UPDATE followequment SET Remark_Fix='" . $_POST['fixRemark'] . "',Date_Fix='$date_time',STT_Fix='" . $_POST['stt'] . "' WHERE id='" . $_POST["id"] . "'";
			@mysqli_set_charset(@$conn, "utf8");
		} elseif ($_POST['stt'] == 'ຕາຍ') {
			@mysqli_set_charset(@$conn, "utf8");
			$sql = "UPDATE followequment SET Remark_Fix='" . $_POST['fixRemark'] . "',Date_Fix='$date_time',STT_Fix='" . $_POST['stt'] . "',Go_Province='ສະສາງ',Date_Goto='$date_time'
	WHERE id='" . $_POST["id"] . "'";

			$sql4488 = "SELECT*FROM followequment
WHERE followequment.id = '" . $_POST["id"] . "'";
			@mysqli_set_charset(@$conn, "utf8");
			$result4488 = $conn->query($sql4488);
			$rowinsert88 = $result4488->fetch_assoc();


			$sql11241 = "INSERT INTO stocklose (Name,
Number,
from_Pro,
To_Stock,
Date,
STT,
User,address_Use,comment)
VALUES ('" . $rowinsert88["Name_Come"] . "','" . $rowinsert88["NumBer_S"] . "','" . $rowinsert88["Cat_Province"] . "','ສະສາງ','$date_time','" . $_POST['stt'] . "','" . $_SESSION["user"] . " $date_time','" . $rowinsert88["address"] . "','" . $_POST['fixRemark'] . "')";
			$conn->query($sql11241);
			@mysqli_set_charset(@$conn, "utf8");
		}

		if ($conn->query($sql) === TRUE) {
		}
	?>
		<script>
			window.location = "follow.php";
		</script>

	<?php } ?>
	<?php
	if (isset($_POST["stockUPDate"])) {
		date_default_timezone_set("Asia/Bangkok");
		$date_time = date('20y-m-d G:i:s');

		$sql12112 = "UPDATE followequment
 SET Go_Province = '" . $_POST["provinceee"] . "',Date_Goto='$date_time'
 WHERE followequment.id LIKE '" . $_POST["id"] . "' ";
		$conn->query($sql12112);
		@mysqli_set_charset(@$conn, "utf8");





		if ($_POST['provinceee'] <> 'TMD' and $_POST['provinceee'] <> 'ສູນກາງ') {

			$sqll = "SELECT*FROM stock_province
WHERE stock_province.Items = '" . $_POST["Name_Come"] . "' and stock_province.Provinces = '" . $_POST["provinceee"] . "'";
			$result = $conn->query($sqll);


			if ($result->num_rows > 0) {

				$sql1 = "UPDATE stock_province
 SET Unit = Unit + '1',User_Stock = '" . $_SESSION["user"] . "',Date_Pro = '" . $date_time . "'
 WHERE stock_province.Items LIKE '" . $_POST["Name_Come"] . "' and stock_province.Provinces LIKE '" . $_POST["provinceee"] . "'";
				$conn->query($sql1);




				$sql44 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
				$result44 = $conn->query($sql44);
				$rowinsert = $result44->fetch_assoc();

				$sql1124 = "INSERT INTO stock_provinceinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro,Remarkk,from_pro)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "','ການສ້ອມແປງ','" . $_POST["formpro"] . "')";
				$conn->query($sql1124);

				@mysqli_set_charset(@$conn, "utf8");
			} else {

				$sql44 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
				$result44 = $conn->query($sql44);
				$rowinsert = $result44->fetch_assoc();

				$sql11 = "INSERT INTO stock_province (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "')";
				$conn->query($sql11);

				$sql1124 = "INSERT INTO stock_provinceinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro,Remarkk,from_pro)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "','ການສ້ອມແປງ','" . $_POST["formpro"] . "')";
				$conn->query($sql1124);


				@mysqli_set_charset(@$conn, "utf8");
			}
		}
		if ($_POST['provinceee'] == 'TMD') {








			$sql1212123 = "UPDATE stock
 SET stock.Stock_TMD = Stock_TMD + '1',User_Stock = '" . $_SESSION["user"] . "'
 WHERE stock.Items = '" . $_POST["Name_Come"] . "' ";
			$conn->query($sql1212123);




			$sql4455 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
			$result4455 = $conn->query($sql4455);
			$rowinserttmd = $result4455->fetch_assoc();

			$sql112455 = "INSERT INTO stockinput (Items,Item_code,Vendor,Use_For,Type,Stock_TMD,Groupp,picture,Section,Date,User,Remarkk,from_pro)
  
VALUES ('" . $rowinserttmd["Items"] . "','" . $rowinserttmd["Item_code"] . "','" . $rowinserttmd["Vendor"] . "','" . $rowinserttmd["Use_For"] . "','" . $rowinserttmd["Type"] . "','1','" . $rowinserttmd["Groupp"] . "','" . $rowinserttmd["picture"] . "','" . $rowinserttmd["Section"] . "','" . $date_time . "','" . $_SESSION["user"] . "','ການສ້ອມແປງ','" . $_POST["formpro"] . "')";
			$conn->query($sql112455);

			@mysqli_set_charset(@$conn, "utf8");
		}
		if ($_POST['provinceee'] == 'ສູນກາງ') {








			$sql1212123 = "UPDATE stock
 SET stock.Unit = Unit + '1',User_Stock = '" . $_SESSION["user"] . "'
 WHERE stock.Items = '" . $_POST["Name_Come"] . "' ";
			$conn->query($sql1212123);




			$sql4455 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
			$result4455 = $conn->query($sql4455);
			$rowinserttmd = $result4455->fetch_assoc();

			$sql112455 = "INSERT INTO stockinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,Date,User,Remarkk,from_pro)
  
VALUES ('" . $rowinserttmd["Items"] . "','" . $rowinserttmd["Item_code"] . "','" . $rowinserttmd["Vendor"] . "','" . $rowinserttmd["Use_For"] . "','" . $rowinserttmd["Type"] . "','1','" . $rowinserttmd["Groupp"] . "','" . $rowinserttmd["picture"] . "','" . $rowinserttmd["Section"] . "','" . $date_time . "','" . $_SESSION["user"] . "','ການສ້ອມແປງ','" . $_POST["formpro"] . "')";
			$conn->query($sql112455);

			@mysqli_set_charset(@$conn, "utf8");
		}



	?>
		<script>
			window.location = "follow.php";
		</script>

	<?php } ?>


	<?php if ($S_Status == 'ຍົກຍ້າຍ') { ?>


		<ul class="nav nav-tabs row" role="tablist">
			<li class="nav-item col-sm-2">
				<a class="nav-link active btn-success" data-toggle="tab" href="#home">1 <?php echo $row['Cat_Province'] ?></a>
			</li><img class="right" src="image/message.gif" style="width:40px;height:40px;">
			<li class="nav-item col-sm-2">
				<a class="nav-link <?php echo $theme ?>" data-toggle="tab" href="#menu1">2 <?php if ($row['User_TMD'] <> '') {
																								echo "ພະແນກຄຸ້ມຄອງເຕັກນິກ";
																							} else {
																								echo "ຍັງບໍ່ມີການຮັບຮູ້";
																							} ?></a>
			</li><img class="right" src="image/shipping.gif" style="width:40px;height:40px;">

			<?php if ($row['Date_TMD'] <> '') { ?><li class="nav-item col-sm-2">
					<a class="nav-link <?php echo $theme ?>" data-toggle="tab" href="#menu3">3 ຈັດສົ່ງ</a>
				</li><img class="right" src="image/crane.gif" style="width:40px;height:40px;"> <?php } ?>
		</ul>


		<!-- Tab panes -->
		<div class="tab-content ">
			<div id="home" class="container-xl tab-pane active"><br>
				<a class="right" href="<?php echo $row2['picture'] ?>" target="_blank"><img class="right" src="<?php echo $row2['picture'] ?>" style="width:200px;height:120px;"></a>
				<h6><i class="fa-solid fa-user-vneck-hair"></i> <strong><?php echo $row['UserProvince'] ?></strong></h6>
				<strong><i class="fa-regular fa-location-dot"></i> ຈາກສາງ :</strong> <?php echo $row['Cat_Province'] ?><br>
				<strong><i class="fa-duotone fa-server"></i> ອຸປະກອນ :</strong> <?php echo $row['Name_Come'] ?><br>
				<strong><i class="fa-light fa-keyboard"></i> ຊີລຽວນາມເບີ :</strong> <?php echo $row['NumBer_S'] ?><br>
				<strong><i class="fa-light fa-map-location-dot"></i> ໃຊ້ຢູ່ສະຖານທີ່ :</strong> <?php echo $row['address'] ?><br>
				<strong><i class="fa-light fa-sensor-triangle-exclamation"></i> ໝາຍເຫດ :</strong> <?php echo $row['Remark_Pro'] ?>
				<hr>
			</div>
			<div id="menu1" class="container-xl tab-pane fade"><br>
				<h6><i class="fa-solid fa-user-vneck-hair"></i> <strong>ຜູ້ຮັບ: <?php echo $row['User_TMD'] ?></strong></h6>
				<?php if ($row['Remark_TMD'] == '' and ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793")) { ?><strong>ເນື້ອໃນ</strong>:<br>
					<form action="process.php" name="form3302" method="post">
						<input hidden="" type="text" name="id" value="<?php echo $row['id'] ?>">
						<input type="text" name="TMDRemark" class="form-control" required><br>
						<button type="submit" class="btn btn-info" name="TMDUPDatemove" style="width: 200px;"><i class="fa-sharp fa-solid fa-forward"></i> Submit</button>

					</form> <?php } else { ?>

					<strong><i class="fa-duotone fa-file-exclamation"></i> ເນື້ອໃນ</strong>:<?= $row['Remark_TMD']; ?><br>
					<strong><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່ສົ່ງກວດສອບ</strong>:<?= $row['Date_TMD']; ?>

				<?php } ?>
				<hr>
			</div>

			<div id="menu3" class="container-xl tab-pane fade"><br>
				<?php if (($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793") and $row['Go_Province'] == '') { ?>
					<form action="process.php" name="form56" method="post">
						<label><strong><i class="fa-sharp fa-solid fa-warehouse-full"></i> ສົ່ງໄປສາງ</strong>
							<input hidden="" type="text" name="Name_Come" value="<?php echo $row['Name_Come'] ?>">
							<input hidden="" type="text" name="id" value="<?php echo $row['id'] ?>">
							<input hidden="" type="text" name="formpro" value="<?php echo $row['Cat_Province'] ?>">
							<select type="text" name="provinceee" id="provinceee" class="form-control" style="width: 300px;" />
							<option value="<?php echo $row['Cat_Province'] ?>"><?php echo $row['Cat_Province'] ?></option>
							<option value="ສູນກາງ">Head office</option>
							<option value="SNK">Stock sanakham</option>
							<option value="MN">Stock meuong nan</option>




							</select>
						</label>
						<label><button type="submit" class="btn btn-info" name="stockUPDatemove" style="width: 200px;"><i class="fa-solid fa-cloud-plus"></i> Submit</button></label>
					</form> <?php } elseif ($row['Go_Province'] <> '') { ?>
					<img class="right" src="image/complete.gif" style="width:200px;height:160px;">
					<strong><i class="fa-regular fa-location-dot"></i> ຈາກສາງ :</strong> <?php echo $row['Cat_Province'] ?>
					<hr>
					<strong><i class="fa-duotone fa-server"></i> ອຸປະກອນ :</strong> <?php echo $row['Name_Come'] ?>
					<hr>
					<strong><i class="fa-light fa-keyboard"></i> ຊີລຽວນາມເບີ :</strong> <?php echo $row['NumBer_S'] ?>
					<hr>
					<strong><i class="fa-thin fa-square-poll-vertical"></i> ຜົນກວດສອບ: </strong> <?php echo $row['STT_Fix'] ?>
					<hr>
					<strong><i class="fa-light fa-sensor-triangle-exclamation"></i> ວີທີການສ້ອມແປງ:</strong> <?php echo $row['Remark_Fix'] ?>
					<hr>
					<strong><i class="fa-sharp fa-solid fa-warehouse-full"></i> ສົ່ງກັບສາງ:</strong> <?php echo $row['Go_Province'] ?> <i class="fa-duotone fa-calendar-days"></i> <strong>ວັນທີ່:</strong> <?php echo $row['Date_Goto'] ?><br>

				<?php } ?>
				<hr>
			</div>
		</div>
	<?php } ?>
	<?php if ($S_Status == 'ຖອນ') { ?>


		<ul class="nav nav-tabs row" role="tablist">
			<li class="nav-item col-sm-2">
				<a class="nav-link active btn-success" data-toggle="tab" href="#home">1 <?php echo $row['Cat_Province'] ?></a>
			</li><img class="right" src="image/message.gif" style="width:40px;height:40px;">
			<li class="nav-item col-sm-2">
				<a class="nav-link <?php echo $theme ?>" data-toggle="tab" href="#menu1">2 <?php if ($row['User_TMD'] <> '') {
																								echo "ພະແນກຄຸ້ມຄອງເຕັກນິກ";
																							} else {
																								echo "ຍັງບໍ່ມີການຮັບຮູ້";
																							} ?></a>
			</li><img class="right" src="image/shipping.gif" style="width:40px;height:40px;">

			<?php if ($row['Date_TMD'] <> '') { ?><li class="nav-item col-sm-2">
					<a class="nav-link <?php echo $theme ?>" data-toggle="tab" href="#menu3">3 ຈັດສົ່ງ</a>
				</li><img class="right" src="image/crane.gif" style="width:40px;height:40px;"> <?php } ?>
		</ul>


		<!-- Tab panes -->
		<div class="tab-content ">
			<div id="home" class="container-xl tab-pane active"><br>
				<a class="right" href="<?php echo $row2['picture'] ?>" target="_blank"><img class="right" src="<?php echo $row2['picture'] ?>" style="width:200px;height:120px;"></a>
				<h6><i class="fa-solid fa-user-vneck-hair"></i> <strong><?php echo $row['UserProvince'] ?></strong></h6>
				<strong><i class="fa-regular fa-location-dot"></i> ຈາກສາງ :</strong> <?php echo $row['Cat_Province'] ?><br>
				<strong><i class="fa-duotone fa-server"></i> ອຸປະກອນ :</strong> <?php echo $row['Name_Come'] ?><br>
				<strong><i class="fa-light fa-keyboard"></i> ຊີລຽວນາມເບີ :</strong> <?php echo $row['NumBer_S'] ?><br>
				<strong><i class="fa-light fa-map-location-dot"></i> ໃຊ້ຢູ່ສະຖານທີ່ :</strong> <?php echo $row['address'] ?><br>
				<strong><i class="fa-light fa-sensor-triangle-exclamation"></i> ໝາຍເຫດ :</strong> <?php echo $row['Remark_Pro'] ?>
				<hr>
			</div>
			<div id="menu1" class="container-xl tab-pane fade"><br>
				<h6><i class="fa-solid fa-user-vneck-hair"></i> <strong>ຜູ້ຮັບ: <?php echo $row['User_TMD'] ?></strong></h6>
				<?php if ($row['Remark_TMD'] == '' and ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793")) { ?><strong>ເນື້ອໃນ</strong>:<br>
					<form action="process.php" name="form3302" method="post">
						<input hidden="" type="text" name="id" value="<?php echo $row['id'] ?>">
						<input type="text" name="TMDRemark" class="form-control" required><br>
						<button type="submit" class="btn btn-info" name="TMDUPDatemove" style="width: 200px;"><i class="fa-sharp fa-solid fa-forward"></i> Submit</button>

					</form> <?php } else { ?>

					<strong><i class="fa-duotone fa-file-exclamation"></i> ເນື້ອໃນ</strong>:<?= $row['Remark_TMD']; ?><br>
					<strong><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່ສົ່ງກວດສອບ</strong>:<?= $row['Date_TMD']; ?>

				<?php } ?>
				<hr>
			</div>

			<div id="menu3" class="container-xl tab-pane fade"><br>
				<?php if (($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793") and $row['Go_Province'] == '') { ?>
					<form action="process.php" name="form56" method="post">
						<label><strong><i class="fa-sharp fa-solid fa-warehouse-full"></i> ສົ່ງໄປສາງ</strong>
							<input hidden="" type="text" name="Name_Come" value="<?php echo $row['Name_Come'] ?>">
							<input hidden="" type="text" name="id" value="<?php echo $row['id'] ?>">
							<select type="text" name="provinceee" id="provinceee" class="form-control" style="width: 300px;" />
							<option value="<?php echo $row['Cat_Province'] ?>"><?php echo $row['Cat_Province'] ?></option>




							</select>
						</label>
						<label><button type="submit" class="btn btn-info" name="stockUPDaterestore" style="width: 200px;"><i class="fa-solid fa-cloud-plus"></i> Submit</button></label>
					</form> <?php } elseif ($row['Go_Province'] <> '') { ?>
					<img class="right" src="image/complete.gif" style="width:200px;height:160px;">
					<strong><i class="fa-regular fa-location-dot"></i> ຈາກສາງ :</strong> <?php echo $row['Cat_Province'] ?>
					<hr>
					<strong><i class="fa-duotone fa-server"></i> ອຸປະກອນ :</strong> <?php echo $row['Name_Come'] ?>
					<hr>
					<strong><i class="fa-light fa-keyboard"></i> ຊີລຽວນາມເບີ :</strong> <?php echo $row['NumBer_S'] ?>
					<hr>
					<strong><i class="fa-thin fa-square-poll-vertical"></i> ຜົນກວດສອບ: </strong> <?php echo $row['STT_Fix'] ?>
					<hr>
					<strong><i class="fa-light fa-sensor-triangle-exclamation"></i> ວີທີການສ້ອມແປງ:</strong> <?php echo $row['Remark_Fix'] ?>
					<hr>
					<strong><i class="fa-sharp fa-solid fa-warehouse-full"></i> ສົ່ງກັບສາງ:</strong> <?php echo $row['Go_Province'] ?> <i class="fa-duotone fa-calendar-days"></i> <strong>ວັນທີ່:</strong> <?php echo $row['Date_Goto'] ?><br>

				<?php } ?>
				<hr>
			</div>
		</div>
	<?php } ?>
	</div>
	<?php
	if (isset($_POST["stockUPDaterestore"])) {
		date_default_timezone_set("Asia/Bangkok");
		$date_time = date('20y-m-d G:i:s');

		$sql12112 = "UPDATE followequment
 SET Go_Province = '" . $_POST["provinceee"] . "',Date_Goto='$date_time'
 WHERE followequment.id LIKE '" . $_POST["id"] . "' ";
		$conn->query($sql12112);
		@mysqli_set_charset(@$conn, "utf8");





		if ($_POST['provinceee'] <> 'TMD' and $_POST['provinceee'] <> 'ສູນກາງ') {

			$sqll = "SELECT*FROM stock_province
WHERE stock_province.Items = '" . $_POST["Name_Come"] . "' and stock_province.Provinces = '" . $_POST["provinceee"] . "'";
			$result = $conn->query($sqll);


			if ($result->num_rows > 0) {

				$sql1 = "UPDATE stock_province
 SET Unit = Unit + '1',User_Stock = '" . $_SESSION["user"] . "',Date_Pro = '" . $date_time . "'
 WHERE stock_province.Items LIKE '" . $_POST["Name_Come"] . "' and stock_province.Provinces LIKE '" . $_POST["provinceee"] . "'";
				$conn->query($sql1);




				$sql44 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
				$result44 = $conn->query($sql44);
				$rowinsert = $result44->fetch_assoc();

				$sql1124 = "INSERT INTO stock_provinceinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro,Remarkk)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "','ຖອນ')";
				$conn->query($sql1124);

				@mysqli_set_charset(@$conn, "utf8");
			} else {

				$sql44 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
				$result44 = $conn->query($sql44);
				$rowinsert = $result44->fetch_assoc();

				$sql11 = "INSERT INTO stock_province (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "')";
				$conn->query($sql11);

				$sql1124 = "INSERT INTO stock_provinceinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro,Remarkk)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "','ຖອນ')";
				$conn->query($sql1124);


				@mysqli_set_charset(@$conn, "utf8");
			}
		}
		if ($_POST['provinceee'] == 'TMD') {








			$sql1212123 = "UPDATE stock
 SET stock.Stock_TMD = Stock_TMD + '1',User_Stock = '" . $_SESSION["user"] . "'
 WHERE stock.Items = '" . $_POST["Name_Come"] . "' ";
			$conn->query($sql1212123);




			$sql4455 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
			$result4455 = $conn->query($sql4455);
			$rowinserttmd = $result4455->fetch_assoc();

			$sql112455 = "INSERT INTO stockinput (Items,Item_code,Vendor,Use_For,Type,Stock_TMD,Groupp,picture,Section,Date,User,Remarkk,from)
  
VALUES ('" . $rowinserttmd["Items"] . "','" . $rowinserttmd["Item_code"] . "','" . $rowinserttmd["Vendor"] . "','" . $rowinserttmd["Use_For"] . "','" . $rowinserttmd["Type"] . "','1','" . $rowinserttmd["Groupp"] . "','" . $rowinserttmd["picture"] . "','" . $rowinserttmd["Section"] . "','" . $date_time . "','" . $_SESSION["user"] . "','ຖອນ','" . $_POST["form"] . "')";
			$conn->query($sql112455);

			@mysqli_set_charset(@$conn, "utf8");
		}
		if ($_POST['provinceee'] == 'ສູນກາງ') {








			$sql1212123 = "UPDATE stock
 SET stock.Unit = Unit + '1',User_Stock = '" . $_SESSION["user"] . "'
 WHERE stock.Items = '" . $_POST["Name_Come"] . "' ";
			$conn->query($sql1212123);




			$sql4455 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
			$result4455 = $conn->query($sql4455);
			$rowinserttmd = $result4455->fetch_assoc();

			$sql112455 = "INSERT INTO stockinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,Date,User,Remarkk)
  
VALUES ('" . $rowinserttmd["Items"] . "','" . $rowinserttmd["Item_code"] . "','" . $rowinserttmd["Vendor"] . "','" . $rowinserttmd["Use_For"] . "','" . $rowinserttmd["Type"] . "','1','" . $rowinserttmd["Groupp"] . "','" . $rowinserttmd["picture"] . "','" . $rowinserttmd["Section"] . "','" . $date_time . "','" . $_SESSION["user"] . "','ຖອນ')";
			$conn->query($sql112455);

			@mysqli_set_charset(@$conn, "utf8");
		}



	?>
		<script>
			window.location = "follow.php";
		</script>

	<?php } ?>
	<?php
	if (isset($_POST["TMDUPDatemove"])) {
		date_default_timezone_set("Asia/Bangkok");
		$date_time = date('20y-m-d G:i:s');

		$sql = "UPDATE followequment SET Remark_TMD='" . $_POST['TMDRemark'] . "',Date_TMD='$date_time' WHERE id='" . $_POST["id"] . "'";
		if ($conn->query($sql) === TRUE) {
		}
	?>
		<script>
			window.location = "follow.php";
		</script>

	<?php } ?>

	<?php
	if (isset($_POST["stockUPDatemove"])) {
		date_default_timezone_set("Asia/Bangkok");
		$date_time = date('20y-m-d G:i:s');

		$sql12112 = "UPDATE followequment
 SET Go_Province = '" . $_POST["provinceee"] . "',Date_Goto='$date_time'
 WHERE followequment.id LIKE '" . $_POST["id"] . "' ";
		$conn->query($sql12112);
		@mysqli_set_charset(@$conn, "utf8");





		if ($_POST['provinceee'] <> 'TMD' and $_POST['provinceee'] <> 'ສູນກາງ') {

			$sqll = "SELECT*FROM stock_province
WHERE stock_province.Items = '" . $_POST["Name_Come"] . "' and stock_province.Provinces = '" . $_POST["provinceee"] . "'";
			$result = $conn->query($sqll);


			if ($result->num_rows > 0) {

				$sql1 = "UPDATE stock_province
 SET Unit = Unit + '1',User_Stock = '" . $_SESSION["user"] . "',Date_Pro = '" . $date_time . "'
 WHERE stock_province.Items LIKE '" . $_POST["Name_Come"] . "' and stock_province.Provinces LIKE '" . $_POST["provinceee"] . "'";
				$conn->query($sql1);

				$sql12 = "UPDATE stock_province
 SET Unit = Unit - '1',User_Stock = '" . $_SESSION["user"] . "',Date_Pro = '" . $date_time . "'
 WHERE stock_province.Items LIKE '" . $_POST["Name_Come"] . "' and stock_province.Provinces LIKE '" . $_POST["formpro"] . "'";
				$conn->query($sql12);




				$sql44 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
				$result44 = $conn->query($sql44);
				$rowinsert = $result44->fetch_assoc();

				$sql1124 = "INSERT INTO stock_provinceinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro,Remarkk)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "','ຍົກຍ້າຍ')";
				$conn->query($sql1124);

				@mysqli_set_charset(@$conn, "utf8");
			} else {

				$sql44 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
				$result44 = $conn->query($sql44);
				$rowinsert = $result44->fetch_assoc();

				$sql11 = "INSERT INTO stock_province (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "')";
				$conn->query($sql11);

				$sql1124 = "INSERT INTO stock_provinceinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro,Remarkk)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "','ຍົກຍ້າຍ')";
				$conn->query($sql1124);

				$sql121 = "UPDATE stock_province
 SET Unit = Unit - '1',User_Stock = '" . $_SESSION["user"] . "',Date_Pro = '" . $date_time . "'
 WHERE stock_province.Items LIKE '" . $_POST["Name_Come"] . "' and stock_province.Provinces LIKE '" . $_POST["formpro"] . "'";
				$conn->query($sql121);


				@mysqli_set_charset(@$conn, "utf8");
			}
		}
		if ($_POST['provinceee'] == 'TMD') {








			$sql1212123 = "UPDATE stock
 SET stock.Stock_TMD = Stock_TMD + '1',User_Stock = '" . $_SESSION["user"] . "'
 WHERE stock.Items = '" . $_POST["Name_Come"] . "' ";
			$conn->query($sql1212123);




			$sql4455 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
			$result4455 = $conn->query($sql4455);
			$rowinserttmd = $result4455->fetch_assoc();

			$sql112455 = "INSERT INTO stockinput (Items,Item_code,Vendor,Use_For,Type,Stock_TMD,Groupp,picture,Section,Date,User,Remarkk)
  
VALUES ('" . $rowinserttmd["Items"] . "','" . $rowinserttmd["Item_code"] . "','" . $rowinserttmd["Vendor"] . "','" . $rowinserttmd["Use_For"] . "','" . $rowinserttmd["Type"] . "','1','" . $rowinserttmd["Groupp"] . "','" . $rowinserttmd["picture"] . "','" . $rowinserttmd["Section"] . "','" . $date_time . "','" . $_SESSION["user"] . "','ຍົກຍ້າຍ')";
			$conn->query($sql112455);

			$sql121 = "UPDATE stock_province
 SET Unit = Unit - '1',User_Stock = '" . $_SESSION["user"] . "',Date_Pro = '" . $date_time . "'
 WHERE stock_province.Items LIKE '" . $_POST["Name_Come"] . "' and stock_province.Provinces LIKE '" . $_POST["formpro"] . "'";
			$conn->query($sql121);

			@mysqli_set_charset(@$conn, "utf8");
		}
		if ($_POST['provinceee'] == 'ສູນກາງ') {








			$sql1212123 = "UPDATE stock
 SET stock.Unit = Unit + '1',User_Stock = '" . $_SESSION["user"] . "'
 WHERE stock.Items = '" . $_POST["Name_Come"] . "' ";
			$conn->query($sql1212123);




			$sql4455 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
			$result4455 = $conn->query($sql4455);
			$rowinserttmd = $result4455->fetch_assoc();

			$sql112455 = "INSERT INTO stockinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,Date,User,Remarkk)
  
VALUES ('" . $rowinserttmd["Items"] . "','" . $rowinserttmd["Item_code"] . "','" . $rowinserttmd["Vendor"] . "','" . $rowinserttmd["Use_For"] . "','" . $rowinserttmd["Type"] . "','1','" . $rowinserttmd["Groupp"] . "','" . $rowinserttmd["picture"] . "','" . $rowinserttmd["Section"] . "','" . $date_time . "','" . $_SESSION["user"] . "','ຍົກຍ້າຍ')";
			$conn->query($sql112455);

			$sql121 = "UPDATE stock_province
 SET Unit = Unit - '1',User_Stock = '" . $_SESSION["user"] . "',Date_Pro = '" . $date_time . "'
 WHERE stock_province.Items LIKE '" . $_POST["Name_Come"] . "' and stock_province.Provinces LIKE '" . $_POST["formpro"] . "'";
			$conn->query($sql121);

			@mysqli_set_charset(@$conn, "utf8");
		}



	?>
		<script>
			window.location = "follow.php";
		</script>

	<?php } ?>



	<script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>
	<script src="js/jquery-steps.js"></script>
	<script>
		$('#demo').steps({
			onFinish: function() {
				alert('Wizard Completed');
			}
		});
	</script>
</body>

</html>