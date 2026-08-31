<?php
require_once __DIR__ . '/conn.php';
@session_start();
if ($_SESSION["user"] == "" or  $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
	echo "<script>window.location = 'index.php';</script>";
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="shortcut icon" href="image/logoETL.jpg">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta name="description" content="" />
	<meta name="author" content="" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha256-OFRAJNoaD8L3Br5lglV7VyLRf0itmoBzWUoM+Sji4/8=" crossorigin="anonymous"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>

	<!-- bootstrap v5 -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta name="description" content="" />
	<meta name="author" content="" />
	<link rel="stylesheet" href="js/pro.min.js">
	<link rel="stylesheet" href="css/all.css">
	<link rel="stylesheet" href="css/all.min.css">

	<title>ການລາຍງານ</title>
	<!-- Favicon-->

	<!-- Core theme CSS (includes Bootstrap)-->
	<link href="css/styles.css" rel="stylesheet" />
	<style type="text/css">
		body,
		td,
		th {
			font-family: "Phetsarath OT";
		}
	</style>
	<script type="text/javascript">
		$(document).ready(function() {
			$('.cust_id').select2();
		});
	</script>
	<script language="JavaScript" type="text/JavaScript">
		<!--
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
	</script>
	<?php
	// Load jQuery library from google.
	$jqLib = 'https://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js';
	?>
	<style>
		body {
			font-family: Phetsarath OT;
		}

		/* Style the tab */
		.tab {
			overflow: hidden;
			border: 1px solid #ccc;
			background-color: #f1f1f1;
		}

		/* Style the buttons inside the tab */
		.tab button {
			background-color: inherit;
			float: left;
			border: none;
			outline: none;
			cursor: pointer;
			padding: 14px 16px;
			transition: 0.3s;
			font-size: 17px;
		}

		/* Change background color of buttons on hover */
		.tab button:hover {
			background-color: #ddd;
		}

		/* Create an active/current tablink class */
		.tab button.active {
			background-color: lightcyan;
		}

		/* Style the tab content */
		.tabcontent {
			display: none;
			padding: 6px 12px;
			border: 1px solid #ccc;
			border-top: none;
		}

		.right {
			float: right;
		}

		.circle {


			border-radius: 14px;

		}
	</style>
	<?php
	$proo = $_SESSION["Namepro"];
	if (
		$_SESSION["iduser"] <> "404" and $_SESSION["iduser"] <> "30" and $_SESSION["iduser"] <> "2" and
		$_SESSION["iduser"] <> "194" and $_SESSION["iduser"] <> "793"
	) {
		$sql = "SELECT COUNT(a.S_Status) as 'cccount' FROM request a
		WHERE a.S_Status in ('1','2','3')  and a.Province ='$proo'
		";
	} else {
		$sql = "SELECT COUNT(a.S_Status) as 'cccount' FROM request a
		WHERE a.S_Status = '1'";
	}
	mysqli_set_charset(@$conn, "utf8");
	$result = $conn->query($sql);
	$row = $result->fetch_assoc();
	?>
</head>

<body>
	<div class="d-flex" id="wrapper">
		<!-- Sidebar-->
		<div class="border-end bg-white" id="sidebar-wrapper">
			<div class="sidebar-heading border-bottom bg-light"><img src="image/logoETL.jpg" class="img-fluid" alt="ETL" width="80">ລະບົບສາງ LS</div>
			<div class="list-group list-group-flush">
				<a class="list-group-item list-group-item-action list-group-item-light p-3" href="Dasborad.php"><i class="fa fa-handshake-o" aria-hidden="true"></i>&nbsp; <strong>ຂໍເບິກອຸປະກອນ</strong> <span class="badge badge-pill badge-danger right" style="font-size: 15px;"><strong><?php echo ($row['cccount']); ?></strong></span></a>
				<?php
				if ($_SESSION["iduser"] <> "404" and $_SESSION["iduser"] <> "30" and $_SESSION["iduser"] <> "194" and $_SESSION["iduser"] <> "793") {

				?><a class="list-group-item list-group-item-action list-group-item-light p-3" href="UseStock.php"><i class="fa fa-tasks" aria-hidden="true"></i>&nbsp; <strong>ນຳໃຊ້ອຸປະກອນ</strong></a> <?php
																																																		}

																																																			?>
				<a class="list-group-item list-group-item-action list-group-item-light p-3" href="follow.php"><i class="fa-regular fa-location-check"></i>&nbsp; <strong>ຕິດຕາມ ອຸປະກອນ</strong></a>

				<a class="list-group-item list-group-item-action list-group-item-light p-3" href="stock.php"><i class="fa-solid fa-cubes-stacked"></i>&nbsp; <strong>ສາງອຸປະກອນ</strong></a>

				<a class="list-group-item list-group-item-action list-group-item-light p-3" href="http://lslao.com.la:901/"><i class="bi bi-tools"></i>&nbsp; <strong>ອຸປະກອນໝູນວຽນ</strong></a>
				<a class="list-group-item list-group-item-action list-group-item-light p-3" href="Report.php"><i class="fa fa-bar-chart" aria-hidden="true"></i>&nbsp; <strong>Report</strong></a>
				<a class="list-group-item list-group-item-action list-group-item-light p-3" href="picture/index.php"><i class="bi bi-images" aria-hidden="true"></i>&nbsp; <strong>Image Stock</strong></a>
				<br>
			</div>


		</div>
		<!-- Page content wrapper-->
		<div id="page-content-wrapper">
			<!-- Top navigation-->
			<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
				<div class="container-fluid">
					&nbsp;&nbsp;&nbsp;<button class="btn btn-primary" id="sidebarToggle"><i class="fa fa-eye-slash" aria-hidden="true"></i> Hide Menu</button>
					<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
					<div class="collapse navbar-collapse" id="navbarSupportedContent">
						<ul class="navbar-nav ms-auto mt-2 mt-lg-0">
							<li class="nav-item active"><a class="nav-link text-primary" href="logout.php"><strong><i class="fa fa-users" aria-hidden="true"></i> User : <?= $_SESSION["user"] ?> </strong></a></li>


						</ul>
					</div>
				</div>
			</nav>
			<!-- Page content-->
			<script>
				function resizeIframe(obj) {
					obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
				}
			</script>








			<div class="tab">
				<button class="tablinks  " onclick="openCity(event, 'Userstock')" id="defaultOpen"><strong><i class="fa-duotone fa-arrows-rotate text-primary"></i> ການນຳໃຊ້ ອຸປະກອນ</strong></button>
				<button class="tablinks" onclick="openCity(event, 'HubEqument')"><strong><i class="fa-duotone fa-cubes-stacked text-primary"></i> ການຮັບ ອຸປະກອນເຂົ້າສາງ</strong></button>
				<button class="tablinks" onclick="openCity(event, 'BerkStock')"><strong><i class="fa-duotone fa-cart-shopping text-primary"></i> ເບິກ ອຸປະກອນ</strong></button>
				<button class="tablinks" onclick="openCity(event, 'follow')"><strong><i class="fa-duotone fa-location-crosshairs text-primary"></i> ຕິດຕາມ ອຸປະກອນ</strong></button>
				<button class="tablinks" onclick="openCity(event, 'reportTotal')"><strong><i class="fa-duotone fa-location-crosshairs text-primary"></i> ສະຫຼູບການລາຍງານ</strong></button>
			</div>


			<div id="Userstock" class="tabcontent">

				<form id="form1" name="form1" method="post" action="ReportUseStock.php" target="ReportUseStock">
					<?php
					if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "2" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793") {

					?>
						<label for="Pro"><strong><i class="fa-thin fa-list-dropdown"></i> ສາຂາສາງ</strong>
							<select name="Pro" class="form-control">
								<option value="">ທັງໝົດສາງ</option>



								<?php
								$result = mysqli_query($conn, "
									SELECT
										DISTINCT usestock.Provinces
										from usestock ;
													");

								while ($row = mysqli_fetch_assoc($result)) {
									echo '<option value="', $row['Provinces'], '">', $row['Provinces'], '</option>';
								}
								?>


							</select>
						</label>
					<?php } ?>
					<label for="cust_id"><strong>ລາຍການອຸປະກອນ</strong>
						<select class="cust_id related-post " name="Equment" class="form-control" rows="4" cols="50" style="color:blue;width:450px;">
							<option value="">ທັງໝົດ</option>



							<?php
							$namePro = $_SESSION["Namepro"];
							if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "2" or $_SESSION["iduser"] == "793") {
								$result = mysqli_query($conn, "
								SELECT
									DISTINCT Items
									from usestock ;
												");

								while ($row = mysqli_fetch_assoc($result)) {
									echo '<option value="', $row['Items'], '">', $row['Items'], '</option>';
								}
							} else {


								$result = mysqli_query($conn, "
									SELECT
										DISTINCT Items
										from usestock
										where usestock.Provinces like '$namePro';
													");

								while ($row = mysqli_fetch_assoc($result)) {
									echo '<option value="', $row['Items'], '">', $row['Items'], '</option>';
								}
							}
							?>


						</select>
					</label>

					<label><strong>ພາກສ່ວນ </strong>
						<select class="form-control" name="Section" id="Section">

							<option value="ທຸກພາກສ່ວນ">ທຸກພາກສ່ວນ</option>
							<?php
							$resultww = mysqli_query($conn, "SELECT DISTINCT a.Section as 'Section' FROM stock a ;");
							while ($row = mysqli_fetch_assoc($resultww)) {
								echo '<option value="', $row['Section'], '">', $row['Section'], '</option>';
							}
							?>

						</select>
					</label>

					<label for="date"><strong><i class="fa-duotone fa-calendar-days"></i> ແຕ່ວັນທີ່</strong>
						<input type="date" name="start" id="start" class="form-control" style="width:130px;" /></label>
					<label for="date"><strong><i class="fa-duotone fa-calendar-days"></i> ຫາວັນທີ່</strong>
						<input type="date" name="end" id="end" class="form-control" style="width:130px;" /></label>

					<label><button type="submit" name="button" class="btn btn-primary" onclick="form1.action='ReportUseStock.php';  return true;"><i class="fa fa-search" aria-hidden="true"></i> ຄົ້ນຫາຂໍ້ມູນ</button></label>
					<label><button type="submit" name="buttonpro" id="buttonpro" onclick="form1.action='ReportUseStock.php';" class="btn btn-success  " /><i class="fa-sharp fa-solid fa-file-excel"></i></button></label>
					<label><button type="submit" name="chartUser" id="chartUser" onclick="form1.action='ReportUseStock.php';" class="btn btn-secondary  " /><i class="fa-duotone fa-chart-mixed"></i></button></label>




				</form>

				<script>
					function resizeIframe(obj) {
						obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
					}
				</script>



				<div align="center">
					<iframe class="circle" src="ReportUseStock.php" name="ReportUseStock" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
					</iframe>
				</div>




			</div>





			<div id="HubEqument" class="tabcontent">
				<label>
					<form id="form2" name="form2" method="post" action="ReportHubStockData.php" target="ReportHubStockData">
						<?php if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793" or $_SESSION["iduser"] == "2") { ?>
							<label for="Pro"><strong><i class="fa-thin fa-list-dropdown"></i>ສາງ</strong>
								<select name="Pro" class="form-control">
									<option value="all">ທັງໝົດສາງ</option>
									<option value="Center">ສູນກາງ</option>

									<?php
									$result = mysqli_query($conn, "
										SELECT DISTINCT stock_provinceinput.Provinces
											from stock_provinceinput ;");

									while ($row = mysqli_fetch_assoc($result)) {
										echo '<option value="', $row['Provinces'], '">', $row['Provinces'], '</option>';
									}
									?>


								</select>
							</label> <?php } else { ?>
							<select hidden="" name="Pro" class="form-control">
								<option value="<?= $_SESSION["Namepro"] ?>"><?= $_SESSION["Namepro"] ?></option>
							</select>

						<?php } ?>

						<label for="cust_id"><strong>ລາຍການຮັບອຸປະກອນ</strong>
							<select class="cust_id related-post " name="Equment1" class="form-control" rows="4" cols="50" style="color:blue;width:450px;">
								<option value="">ທັງໝົດ</option>
								<?php
								$namePro = $_SESSION["Namepro"];
								if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "2" or $_SESSION["iduser"] == "793") {
									$result = mysqli_query($conn, "SELECT DISTINCT Items from usestock ;");
									while ($row = mysqli_fetch_assoc($result)) {
										echo '<option value="', $row['Items'], '">', $row['Items'], '</option>';
									}
								} else {
									$result = mysqli_query($conn, "SELECT DISTINCT Items from usestock
											where usestock.Provinces like '$namePro';");
									while ($row = mysqli_fetch_assoc($result)) {
										echo '<option value="', $row['Items'], '">', $row['Items'], '</option>';
									}
								}
								?>
							</select>
						</label>




						<label for="date"><strong><i class="fa-duotone fa-calendar-days"></i> ແຕ່ວັນທີ່</strong>
							<input type="date" name="start" id="start" class="form-control" /></label>
						<label for="date"><strong><i class="fa-duotone fa-calendar-days"></i> ຫາວັນທີ່</strong>
							<input type="date" name="end" id="end" class="form-control" /></label>

						<label><button type="submit" name="buttonhub" class="btn btn-primary" onclick="form2.action='ReportHubStockData.php';  return true;"><i class="fa fa-search" aria-hidden="true"></i> ຄົ້ນຫາຂໍ້ມູນ</button></label>
						<label><button type="submit" name="buttonproHUb" id="buttonproHUb" onclick="form2.action='ReportHubStockData.php';" class="btn btn-success  " /><i class="fa-sharp fa-solid fa-file-excel"></i></button></label>
						<label><button type="submit" name="chartUserhub" id="chartUserhub" onclick="form2.action='ReportHubStockData.php';" class="btn btn-secondary  " /><i class="fa-duotone fa-chart-mixed"></i></button></label>



					</form>
				</label>


				<script>
					function resizeIframe(obj) {
						obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
					}
				</script>

				<div align="center">


					<iframe class="circle" src="ReportHubStockData.php" name="ReportHubStockData" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
					</iframe>
				</div>
			</div>

			<div id="BerkStock" class="tabcontent">

				<form id="form33" name="form33" method="post" action="ReporberkData.php" target="ReporberkData">
					<?php
					if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793" or $_SESSION["iduser"] == "2") {

					?>
						<label for="Pro"><strong><i class="fa-thin fa-list-dropdown"></i>ສາງ</strong>
							<select name="Pro" class="form-control">
								<option value="all">ທັງໝົດແຂວງ</option>
								<?php
								$result = mysqli_query($conn, "SELECT DISTINCT request.Province from request ;");
								while ($row = mysqli_fetch_assoc($result)) {
									echo '<option value="', $row['Province'], '">', $row['Province'], '</option>';
								}
								?>


							</select>
						</label>
					<?php } else { ?>
						<label for="Pro"><strong><i class="fa-thin fa-list-dropdown"></i>ສາງ</strong>
							<select name="Pro" class="form-control">
								<option value="<?= $_SESSION["Namepro"] ?>"><?= $_SESSION["Namepro"] ?></option>
							</select>
						</label>
					<?php }  ?>


					<label for="cust_id"><strong>ລາຍການອຸປະກອນເບີກ</strong>
						<select class="cust_id related-post " name="Equment1" class="form-control" rows="4" cols="50" style="color:blue;width:450px;">
							<option value="">ທັງໝົດ</option>
							<?php
							$namePro = $_SESSION["Namepro"];
							if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "2" or $_SESSION["iduser"] == "793") {
								$result = mysqli_query($conn, "SELECT DISTINCT Items from usestock ;");
								while ($row = mysqli_fetch_assoc($result)) {
									echo '<option value="', $row['Items'], '">', $row['Items'], '</option>';
								}
							} else {
								$result = mysqli_query($conn, "SELECT DISTINCT Items from usestock
										where usestock.Provinces like '$namePro';");
								while ($row = mysqli_fetch_assoc($result)) {
									echo '<option value="', $row['Items'], '">', $row['Items'], '</option>';
								}
							}
							?>
						</select>
					</label>
					<label><strong>ປະເພດວັນທີ່</strong><select name="Choi" class="form-control">
							<option value="dateRe">ວັນທີ່ຂໍເບິກ</option>
							<option value="DateComfirm">ວັນທີ່ເບິກ</option>
						</select>
					</label>
					<label><strong></strong><select name="ChoiRemark" class="form-control">
							<option value="">.....</option>
							<option value="2">ຍັງບໍ່ໄດ້ຮັບ</option>
							<option value="3">ໄດ້ຖືກຍົກເລີກ</option>
						</select>
					</label>
					<label for="date"><strong><i class="fa-duotone fa-calendar-days"></i> ແຕ່ວັນທີ່</strong>
						<input type="date" name="start" id="start" class="form-control" /></label>
					<label for="date"><strong><i class="fa-duotone fa-calendar-days"></i> ຫາວັນທີ່</strong>
						<input type="date" name="end" id="end" class="form-control" /></label>

					<label><button type="submit" name="buttonberk" class="btn btn-primary" onclick="form33.action='ReporberkData.php';  return true;"><i class="fa fa-search" aria-hidden="true"></i> ຄົ້ນຫາຂໍ້ມູນ</button></label>
					<label><button type="submit" name="BerkStock" id="BerkStock" onclick="form33.action='ReporberkData.php'; return true;" class="btn btn-success  " /><i class="fa-sharp fa-solid fa-file-excel"></i></button></label>
				</form>
				<script>
					function resizeIframe(obj) {
						obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
					}
				</script>

				<div align="center">
					<iframe src="ReporberkData.php" name="ReporberkData" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
					</iframe>
				</div>
			</div>



			<div id="follow" class="tabcontent">

				<form id="form34" name="form34" method="post" action="reportFollow.php" target="reportFollow">
					<?php
					if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "195" or $_SESSION["iduser"] == "793" or $_SESSION["iduser"] == "2") {

					?>
						<label for="Pro"><strong><i class="fa-thin fa-list-dropdown"></i>ສາງ</strong>
							<select name="Pro" class="form-control">
								<option value="all">ທັງໝົດສາງ</option>



								<?php
								$result = mysqli_query($conn, " SELECT DISTINCT request.Province from request ;");
								while ($row = mysqli_fetch_assoc($result)) {
									echo '<option value="', $row['Province'], '">', $row['Province'], '</option>';
								}
								?>


							</select>
						</label>
					<?php } else { ?>
						<label for="Pro"><strong><i class="fa-thin fa-list-dropdown"></i>ສາງ</strong>
							<select name="Pro" class="form-control">
								<option value="<?= $_SESSION["Namepro"] ?>"><?= $_SESSION["Namepro"] ?></option>






							</select>
						</label>
					<?php }  ?>

					<label><strong>ສະຖານະ</strong><select name="Choi" class="form-control" required>
							<option value="">---ກະລຸນາເລືອກ---</option>
							<option value="ສ້ອມແປງ">ສ້ອມແປງ</option>
							<option value="ຍົກຍ້າຍ">ຍົກຍ້າຍ</option>
							<option value="ຖອນ">ຖອນ</option>
						</select>
					</label>
					<label><strong>ສະຖານະ(<b style="color: red;">ສະເພາະຢືມເຄື່ອງ</b>)</strong><select name="stt" class="form-control">
							<option value="all">---ທຸກສະຖານະ---</option>
							<option value="ໃຊ້ງານໄດ້">ໃຊ້ງານໄດ້</option>
							<option value="ຕາຍ">ຕາຍ</option>


						</select>
					</label>
					<label for="date"><strong><i class="fa-duotone fa-calendar-days"></i> ແຕ່ວັນທີ່</strong>
						<input type="date" name="start" id="start" required="required" class="form-control" /></label>
					<label for="date"><strong><i class="fa-duotone fa-calendar-days"></i> ຫາວັນທີ່</strong>
						<input type="date" name="end" id="end" required="required" class="form-control" /></label>

					<label><button type="submit" name="buttonFollow" class="btn btn-primary" onclick="form34.action='reportFollow.php';  return true;"><i class="fa fa-search" aria-hidden="true"></i> ຄົ້ນຫາຂໍ້ມູນ</button></label>
					<label><button type="submit" name="FollowStock" id="FollowStock" onclick="form34.action='reportFollow.php'; return true;" class="btn btn-success  " /><i class="fa-sharp fa-solid fa-file-excel"></i></button></label>


				</form>

				<script>
					function resizeIframe(obj) {
						obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
					}
				</script>

				<div align="center">

					<br>
					<iframe src="reportFollow.php" name="reportFollow" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
					</iframe>
				</div>
			</div>


			<div id="reportTotal" class="tabcontent">

				<form id="form33" name="form33" method="post" action="ReporDataTotal.php" target="ReporDataTotal">
					<?php
					if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793" or $_SESSION["iduser"] == "2") {

					?>
						<label for="Pro"><strong><i class="fa-thin fa-list-dropdown"></i>ສາງ</strong>
							<select name="Pro" class="form-control">
								<option value="all">ທັງໝົດແຂວງ</option>
								<?php
								$result = mysqli_query($conn, "SELECT DISTINCT request.Province from request ;");
								while ($row = mysqli_fetch_assoc($result)) {
									echo '<option value="', $row['Province'], '">', $row['Province'], '</option>';
								}
								?>


							</select>
						</label>
					<?php } else { ?>
						<label for="Pro"><strong><i class="fa-thin fa-list-dropdown"></i>ສາງ</strong>
							<select name="Pro" class="form-control">
								<option value="<?= $_SESSION["Namepro"] ?>"><?= $_SESSION["Namepro"] ?></option>
							</select>
						</label>
					<?php }  ?>


					<label for="cust_id"><strong>ລາຍການອຸປະກອນ</strong>
						<select class="cust_id related-post " name="Equment1" class="form-control" rows="4" cols="50" style="color:blue;width:450px;">
							<option value="">ທັງໝົດ</option>
							<?php
							$namePro = $_SESSION["Namepro"];
							if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "2" or $_SESSION["iduser"] == "793") {
								$result = mysqli_query($conn, "SELECT DISTINCT Items from stock ;");
								while ($row = mysqli_fetch_assoc($result)) {
									echo '<option value="', $row['Items'], '">', $row['Items'], '</option>';
								}
							} else {
								$result = mysqli_query($conn, "SELECT DISTINCT Items from stock
						where stock.Provinces like '$namePro';");
								while ($row = mysqli_fetch_assoc($result)) {
									echo '<option value="', $row['Items'], '">', $row['Items'], '</option>';
								}
							}
							?>
						</select>
					</label>
					<label><strong>ປະເພດວັນທີ່</strong><select name="Choi" class="form-control">
							<option value="dateRe">ວັນທີ່ຂໍເບິກ</option>
							<option value="DateComfirm">ວັນທີ່ເບິກ</option>
						</select>
					</label>
					<label><strong></strong><select name="ChoiRemark" class="form-control">
							<option value="">.....</option>
							<option value="2">ຍັງບໍ່ໄດ້ຮັບ</option>
							<option value="3">ໄດ້ຖືກຍົກເລີກ</option>
						</select>
					</label>
					<label for="date"><strong><i class="fa-duotone fa-calendar-days"></i> ແຕ່ວັນທີ່</strong>
						<input type="date" name="start" id="start" class="form-control" /></label>
					<label for="date"><strong><i class="fa-duotone fa-calendar-days"></i> ຫາວັນທີ່</strong>
						<input type="date" name="end" id="end" class="form-control" /></label>

					<label><button type="submit" name="buttonberk" class="btn btn-primary" onclick="form33.action='ReporDataTotal.php';  return true;"><i class="fa fa-search" aria-hidden="true"></i> ຄົ້ນຫາຂໍ້ມູນ</button></label>
					<label><button type="submit" name="BerkStock" id="BerkStock" onclick="form33.action='ReporDataTotal.php'; return true;" class="btn btn-success  " /><i class="fa-sharp fa-solid fa-file-excel"></i></button></label>
				</form>
				<script>
					function resizeIframe(obj) {
						obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
					}
				</script>

				<div align="center">
					<iframe src="ReporDataTotal.php" name="ReporDataTotal" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
					</iframe>
				</div>
			</div>







			<script>
				function openCity(evt, cityName) {
					var i, tabcontent, tablinks;
					tabcontent = document.getElementsByClassName("tabcontent");
					for (i = 0; i < tabcontent.length; i++) {
						tabcontent[i].style.display = "none";
					}
					tablinks = document.getElementsByClassName("tablinks");
					for (i = 0; i < tablinks.length; i++) {
						tablinks[i].className = tablinks[i].className.replace(" active", "");
					}
					document.getElementById(cityName).style.display = "block";
					evt.currentTarget.className += " active";
				}

				// Get the element with id="defaultOpen" and click on it
				document.getElementById("defaultOpen").click();
			</script>



		</div>
		<!-- Bootstrap core JS-->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
		<!-- Core theme JS-->
		<script src="js/scripts.js"></script>
</body>

</html>