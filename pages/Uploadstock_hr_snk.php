<?php
require_once __DIR__ . '/../includes/conn.php';
@session_start();
if ($_SESSION["user"] == "" or  $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
	echo "<script>window.location = 'index.php';</script>";
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
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
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta name="description" content="" />
	<meta name="author" content="" />
	<link rel="stylesheet" href="js/pro.min.js">
	<link rel="stylesheet" href="css/all.min.css">

	<title>ລະບົບສາງ</title>
	<!-- Favicon-->

	<!-- Core theme CSS (includes Bootstrap)-->
	<link href="css/styles.css" rel="stylesheet" />
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

	<?php
	$proo = $_SESSION["Namepro"];
	if ($_SESSION["iduser"] <> "404" and $_SESSION["iduser"] <> "30" and $_SESSION["iduser"] <> "194" and $_SESSION["iduser"] <> "793") {
		$sql = "SELECT
COUNT(a.S_Status) as 'cccount'

FROM request a

WHERE a.S_Status in ('1','2','3')  and a.Province ='$proo'
";
	} else {

		$sql = "SELECT
COUNT(a.S_Status) as 'cccount'

FROM request a

WHERE a.S_Status = '1'
";
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
				if ($_SESSION["iduser"] <> "204") {

				?><a class="list-group-item list-group-item-action list-group-item-light p-3" href="UseStock.php"><i class="fa fa-tasks" aria-hidden="true"></i>&nbsp; <strong>ນຳໃຊ້ອຸປະກອນ</strong></a> <?php
																																																		}

																																																			?>
				<a class="list-group-item list-group-item-action list-group-item-light p-3" href="follow.php"><i class="fa-regular fa-location-check"></i>&nbsp; <strong>ຕິດຕາມ ອຸປະກອນ</strong></a>
				<a class="list-group-item list-group-item-action list-group-item-light p-3" href="stock.php"><i class="fa-solid fa-cubes-stacked"></i>&nbsp; <strong>ສາງອຸປະກອນ</strong></a>

				<a class="list-group-item list-group-item-action list-group-item-light p-3" href="Report.php"><i class="fa fa-bar-chart" aria-hidden="true"></i>&nbsp; <strong>Report</strong></a>
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
			<div class="container-fluid">

				<?php
				date_default_timezone_set('Asia/Bangkok');
				$date_time = date('20y-m-d G:i');
				$col = 1;

				set_time_limit(3600);
				if (isset($_POST["import"])) {

					$fileName = $_FILES["file"]["tmp_name"];
					if ($_FILES["file"]["size"] > 0) {
						$file = fopen($fileName, "r");
						$i = 0;
						$col = 0;
						while (($column = fgetcsv($file, max, ",")) !== FALSE) {
							//echo $column[0];
							//Check
							if ($_POST['conf'] <> 'conf') {
								echo ("<br>
				<div class='container'>");
								echo '<h3>ກວດສອບຊື່ Items ກ່ອນການນຳເຊົ້າຂໍ້ມູນ Items=' . $column[0] . ' </h3>';
								echo ("</div>");
								break;
							}
							mysqli_set_charset(@$conn, "utf8");
							$sqlSelect = "SELECT id FROM stock where Items='" . $column[1] . "'  ";
							$result = mysqli_query($conn, $sqlSelect);
							if (mysqli_num_rows($result) > 0) {


								$i++;
							} else {
								//Insert
								$sqlInsert = "INSERT into stock (id,Items,Item_code,Vendor,Use_For,Type,Unit,Stock_TMD,Groupp,picture,Section,User_Stock)
                   values (NULL,'" . $column[0] . "','" . $column[1] . "','" . $column[2] . "','" . $column[3] . "','" . $column[4] . "','" . $column[5] . "','" . $column[6] . "','" . $column[7] . "','" . $column[8] . "','" . $column[9] . "','" . $_SESSION["user"] . " $date_time')";
								/*$result = mysqli_query($conn, $sqlInsert);*/
								$conn->query($sqlInsert);

								$sqlInsert2 = "INSERT into stockinput (id,Items,Item_code,Vendor,Use_For,Type,Unit,Stock_TMD,Groupp,picture,Section,Date,User,Remarkk)
                   values (NULL,'" . $column[0] . "','" . $column[1] . "','" . $column[2] . "','" . $column[3] . "','" . $column[4] . "','" . $column[5] . "','" . $column[6] . "','" . $column[7] . "','" . $column[8] . "','" . $column[9] . "','$date_time','" . $_SESSION["user"] . "','Upload')";
								/*$result = mysqli_query($conn, $sqlInsert);*/
								$conn->query($sqlInsert2);
							}
							//echo $lastre=$column[1].'-'.$column[2].'-'.$column[3].'<br>';	
							$col++;
							echo $col . '-';
							if ($col % 100 == 0) {
								echo '<br>';
							}
							if (! empty($result)) {
								$type = "success";
								$message = "ການນຳເຂົ້າຂໍ້ມູນສຳເລັດ ຂໍ້ມູນຊໍ້າຈຳນວນ " . $i . " ແຖວ";
								//echo $result;
							} else {
								$type = "error";
								$message = "ມີບັນຫາໃນການນຳເຂົ້າຂໍ້ມູນ ຂໍ້ມູນຊໍ້າຈຳນວນ " . $i . " ແຖວ";
							}
						}
						fclose($file);
					}
				}
				if (isset($_POST["stock"])) {

					$fileName = $_FILES["file"]["tmp_name"];
					if ($_FILES["file"]["size"] > 0) {
						$file = fopen($fileName, "r");
						$i = 0;
						$col = 0;
						while (($column = fgetcsv($file, max, ",")) !== FALSE) {
							echo $column[0];
							//Check


							if ($_POST['conf'] <> 'conf') {
								echo ("<br>
				<div class='container'>");
								echo '<h3>ກວດສອບຊື່ Items ກ່ອນການນຳເຊົ້າຂໍ້ມູນ Items=' . $column[0] . ' </h3>';
								echo ("</div>");
								break;
							}


							$sqlSelect = "SELECT id FROM stock where Items='" . $column[0] . "' ";
							/*$result = mysqli_query($conn, $sqlSelect);*/
							$conn->query($sqlSelect);
							$i++;





							$sqlUPDATE = "UPDATE stock 
				
			SET Unit = Unit + $column[5],User_Stock = '" . $_SESSION["user"] . " $date_time'
			
			where Items='" . $column[0] . "' 
			 ";
							/*$result = mysqli_query($conn, $sqlUPDATE);*/
							$conn->query($sqlUPDATE);

							$sqlInsert2 = "INSERT into stockinput (Items,Item_code,Vendor,Use_For,Type,Unit,Stock_TMD,Groupp,picture,Section,Date,User,Remarkk)
                   values ('" . $column[0] . "','" . $column[1] . "','" . $column[2] . "','" . $column[3] . "','" . $column[4] . "','" . $column[5] . "','" . $column[6] . "','" . $column[7] . "','" . $column[8] . "','" . $column[9] . "','$date_time','" . $_SESSION["user"] . "','Upload')";
							/*$result = mysqli_query($conn, $sqlInsert);*/
							$conn->query($sqlInsert2);





							//echo $lastre=$column[1].'-'.$column[2].'-'.$column[3].'<br>';	
							$col++;
							echo $col . '-';
							if ($col % 100 == 0) {
								echo '<br>';
							}
							if (! empty($result)) {
								$type = "success";
								$message = "ການນຳເຂົ້າຂໍ້ມູນສຳເລັດ  " . $i . " ແຖວ";
								echo $column[0];
							} else {
								$type = "error";
								$message = "ມີບັນຫາໃນການນຳເຂົ້າຂໍ້ມູນ ຂໍ້ມູນຊໍ້າຈຳນວນ " . $i . " ແຖວ";
							}
						}
						fclose($file);
					}
				}
				?>
				<!DOCTYPE html>
				<html>

				<head>
					<script src="jquery-3.2.1.min.js"></script>
					<meta charset="utf-8">
					<style>
						body {
							font-family: Phetsarath OT;
							width: 550px;
						}

						.outer-scontainer {
							background: #F0F0F0;
							border: #e0dfdf 1px solid;
							padding: 20px;
							border-radius: 2px;
						}

						.input-row {
							margin-top: 0px;
							margin-bottom: 20px;
						}

						.btn-submit {
							background: #333;
							border: #1d1d1d 1px solid;
							color: #f0f0f0;
							font-size: 0.9em;
							width: 100px;
							border-radius: 2px;
							cursor: pointer;
						}

						.outer-scontainer table {
							border-collapse: collapse;
							width: 100%;
						}

						.outer-scontainer th {
							border: 1px solid #dddddd;
							padding: 8px;
							text-align: left;
						}

						.outer-scontainer td {
							border: 1px solid #dddddd;
							padding: 8px;
							text-align: left;
						}

						#response {
							border: 1px solid #52A1EA;
							padding: 10px;
							margin-bottom: 10px;
							border-radius: 5px;
							background: #CECECE;
							font-size: 1.9em;
							display: none;
							background-size: 100% 100%;
						}

						.success {
							background: #c7efd9;
							border: #bbe2cd 1px solid;
						}

						.error {
							background: #fbcfcf;
							border: #f3c6c7 1px solid;
						}

						div#response.display-block {
							display: block;
						}

						body,
						td,
						th {
							font-family: "Phetsarath OT";
							width: inherit;
						}
					</style>
					<script type="text/javascript">
						$(document).ready(function() {
							$("#frmCSVImport").on("submit", function() {

								$("#response").attr("class", "");
								$("#response").html("");
								var fileType = ".csv";
								var regex = new RegExp("([a-zA-Z0-9\s_\\.\-:])+(" + fileType + ")$");
								if (!regex.test($("#file").val().toLowerCase())) {
									$("#response").addClass("error");
									$("#response").addClass("display-block");
									$("#response").html("File Upload ບໍ່ຖືກຕ້ອງ : <b>" + fileType + "</b> Files.");
									return false;
								}
								return true;
							});
						});
					</script>
					<meta charset="utf-8">
				</head>

				<body>
					<div class="container">
						<br>
						<!-- <h3>ນຳຂໍ້ມູນເຂົ້າ Stock <i class="fa fa-database" aria-hidden="true"></i></h3>
						<div id="response" class="<?php if (!empty($type)) {
														echo $type . " display-block";
													} ?>"><?php if (!empty($message)) {
																														echo $message;
																														echo "<script>alert('Import success!');</script>";
																													} ?></div> -->


<!-- 
						<form class="form-horizontal" action="" method="post"
							name="frmCSVImport" id="frmCSVImport" enctype="multipart/form-data">
							<div class="input-row">
								<label class="col-md-4 control-label">ເລືອກ CSV File</label>
								<input type="file" name="file" class="form-control"
									id="file" accept=".csv" required>
								<br>
								<input type="checkbox" name="conf" id="conf" value="conf">
								<label for="conf">ຢືນຢັນຂໍ້ມູນ</label>
								<label><button type="submit" id="submit" name="import" class="btn-primary form-control" onClick="this.form.submit();this.disabled=true;" style="width:300px "><i class="fa fa-plus-square-o" aria-hidden="true"></i> ເພີ່ມຂໍ້ມູນໃຫມ່ (.CSV)</button></label>
								<label><button type="submit" id="submit" name="stock" class="btn-success form-control" onClick="this.form.submit();this.disabled=true;" style="width:300px "><i class="fa fa-upload" aria-hidden="true"></i> ອັບເດດຂໍ້ມູນ (.CSV)</button></label>

							</div>

						</form> -->


					</div>

					<div class="container">
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
						<hr>
						<form action="Uploadstock_hr_snk.php" method="post">

							<label for="cust_id">
								<h4><strong>ຄົ້ນຫາອຸປະກອນ:</strong></h4><select class="cust_id related-post " name="Select" class="form-control" rows="4" cols="50" style="color:blue;width:1000px;" onchange="this.form.submit();">
									<option value="">ເລືອກລາຍການອຸປະກອນ</option>





									<?php
									$result = mysqli_query($conn, "
				SELECT
				*
					FROM stock where stock.Section = 'CANTEEN'
						order by stock.Items ;
			");

									while ($row = mysqli_fetch_assoc($result)) {
										echo '<option value="', $row['Items'], '">', $row['Items'], '</option>';
									}
									?>


								</select>
							</label>








							<form action="" method="post">
								<select hidden="" name="day" onchange="this.form.submit();">
									<option>Please select a date</option>
									<option value="Mon">Mon</option>
									<option value="Tue">Tue</option>

								</select>
							</form>




							<?php if (isset($_POST["Select"])) {
							?>
								<?php
								$select = $_POST["Select"];
								$result = mysqli_query($conn, "
				SELECT
				*
					FROM stock
						where  stock.Items like '$select' ;
			");

								while ($row = mysqli_fetch_assoc($result)) {

								?>







									<div class="container-fluid">

										<div class="row">
											<div align="left" class="col-sm-12 bg" style="font-size: 15px;"><strong>

													<hr><i class="fa-light fa-forklift fa-lg"></i> <?php echo $row['Items'] ?><a class="right" href="<?php echo $row['picture'] ?>" target="_blank"><img src="<?php echo $row['picture'] ?>" style="width:250px;height:250px;"></a><br><i class="fa-solid fa-globe fa-lg"></i> ຂະໜາດ: <?php echo $row['Vendor'] ?><br><i class="fa-light fa-network-wired fa-lg"></i> ພາກສ່ວນ: <?php echo $row['Section'] ?>
												</strong>
												<form action="Uploadstock_hr_snk.php" id="form3" name="form3" method="post">
													<div class="row">
														<input hidden="id" type="text" name="id" id="id" style="color:blue; width:100px " value="<?php echo $row['id'] ?>" />
														<div class="col-sm-3" style="background-color:lavender;"><label><strong>ສາງອຸປະກອນພະແນກສາງສຳນັກງານໃຫຍ່</strong><input type="text" name="Unit" id="Unit" class="form-control" value="<?php echo $row['Unit'] ?>" style="color:blue;" /></label></div>
														<div class="col-sm-3" style="background-color:lavender;"><label><strong>ສາງເຄື່ອງໃຊ້ຫ້ອງການສຳນັກງານໃຫຍ່</strong><input type="text" name="Stock_TMD" id="Stock_TMD" class="form-control" style="color:blue;" value="<?php echo $row['Stock_TMD'] ?>" /></label></div>
														<div class="col-sm-3" style="background-color:lavender;"><label><strong>ປະເພດ</strong><input type="text" name="Typee" id="Typee" class="form-control" style="color:blue;" value="<?php echo $row['Type'] ?>" /></label></div>
														<button id="Submit" style="width: 200px;" class="btn btn-primary" name="btnupdate" type="Submit" onclick="return confirm('ຕ້ອງການອັບເດດ Stock  ແທ້ບໍ່?')"><i class="fa-duotone fa-file-pen fa-lg"></i> ບັນທືກ</button>
													</div>





												</form>
											</div>

										</div>
									</div>
								<?php
								}

								?>





							<?php } ?>



						</form>
					</div>
					<?php if (isset($_POST["btnupdate"])) {
						$idd = $_POST["id"];
						$Unit = $_POST["Unit"];
						$Stock_TMD = $_POST["Stock_TMD"];
						$Typee = $_POST["Typee"];
						$sqlUPDATEst = "UPDATE stock
				
			SET Unit = '$Unit',
			Stock_TMD = '$Stock_TMD',
			Type = '$Typee',
			User_Stock = '" . $_SESSION["user"] . " $date_time'
			
			where stock.id ='$idd' 
			 ";
						/*$result = mysqli_query($conn, $sqlUPDATE);*/
						$conn->query($sqlUPDATEst);



					?>
						<script>
							window.location = "Uploadstock_hr_snk.php";
						</script>

					<?php } ?>


					<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
					<!-- Core theme JS-->
					<script src="js/scripts.js"></script>
				</body>

				</html>