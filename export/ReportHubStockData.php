<?php
require_once __DIR__ . '/../includes/conn.php';
@session_start();
if ($_SESSION["user"] == "" ) {
	echo "<script>window.location = 'index.php';</script>";
	exit;
}
?>
<?php
if (isset($_POST["buttonproHUb"])) {
	header("Content-Type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=ຮັບ ອຸປະກອນ.xls");
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
			.bs-example {
				margin: 20px;
			}
		</style>

		<style type="text/css">
			body,
			td,
			th {
				font-family: "Phetsarath OT";
			}

			.navbarr {


				position: fixed;

			}

			.right {
				float: right;
			}

			.circle {


				border-radius: 14px;

			}

			.BG {
				background-image: linear-gradient(120deg, #89f7fe 0%, #66a6ff 100%);
			}
		</style>
		<?php
		date_default_timezone_set("Asia/Bangkok");
		$Pro = $_POST["Pro"] ?? '';
		$start = $_POST["start"] ?? '';
		$end = $_POST["end"] ?? '';

		if ($Pro == 'Center') {
			$sql = "SELECT*FROM stockinput a
WHERE a.Date >= '$start 00:00:00' and a.Date <= '$end 23:59:59'
ORDER BY a.Date asc ";
		}
		if ($Pro <> 'Center') {
				$sql = "SELECT*FROM stock_provinceinput a
WHERE a.Date_Pro >= '$start' and a.Date_Pro <= '$end' and a.Provinces = '$Pro'
ORDER BY a.Date_Pro asc";
		}

		if ($Pro == 'all') {
			$sql = "SELECT*FROM stock_provinceinput a
WHERE a.Date_Pro >= '$start' and a.Date_Pro <= '$end'
ORDER BY a.Provinces asc";
		}
		if ($Pro <> 'all' and $Pro <> 'Center') {
		 	$sql = "SELECT*FROM stock_provinceinput a
WHERE a.Date_Pro >= '$start' and a.Date_Pro <= '$end' and a.Provinces = '$Pro' 
ORDER BY a.Provinces asc";
		}







		mysqli_set_charset(@$conn, "utf8");
		 $result = $conn->query($sql);
		if ($result->num_rows > 0) {
		?>
			<div align="center">
				<table class="table table-hover table-bordered table-sm" border="1">
					<thead>
						<tr align="center" class="text-dark BG " circle>
							<th>ສາງ</th>
							<th>ອຸປະກອນ</th>
							<th>ວັນທີ</th>

							<th>ຈຳນວນຮັບ</th>



							<th>ໄດ້ຈາກ</th>
							<th>User</th>




						</tr>
					</thead>
					<tbody>
						<?php
						while ($row = $result->fetch_assoc()) {
							$id = $row['id'];



						?>
							<tr>
								<?php if ($Pro <> 'Center' or  $Pro == 'all') { ?>
									<td width="10%" align="center"><?php echo $row['Provinces'] ?></td>

								<?php } elseif ($Pro == 'Center') { ?>
									<td width="10%" align="center">ສູນກາງ</td>
								<?php } ?>
								<td width="30%"><strong>ຊື່:</strong> <?php echo $row['Items'] ?><br><strong>ພາກສ່ວນ:</strong> <?php echo $row['Section'] ?><br><strong>ໃຊ້ສຳລັບ:</strong> <?php echo $row['Use_For'] ?><br><strong>ກຸ່ມ:</strong> <?php echo $row['Groupp'] ?> <br><strong>ຂໍ້ມູນຮ້ານທີ່ຈັດຊື້:</strong> <?php echo $row['Vendor'] ?><br><b>ເລກທີ:</b> <?= $row['OA'] ?></td>
								<?php if ($Pro <> 'Center'  or  $Pro == 'all') { ?>
									<td width="10%" align="center"><?php echo $row['Date_Pro'] ?></td>
								<?php } elseif ($Pro == 'Center') { ?>

									<td width="10%" align="center"><?php echo $row['Date'] ?></td>
								<?php } ?>

								<td width="10%" align="center" class="text-primary"><?php echo $row['Unit'] ?> <?php echo $row['Type'] ?></td>



								<td width="10%" align="center"><?php echo $row['Remarkk'] ?><br><?php echo $row['from_pro'] ?></td>
								<td width="10%" align="center"><?php echo $row['User_Stock'] ?? '' ?><?php echo $row['User'] ?? '' ?></td>






							</tr>
					</tbody>

				<?php }
					} else {


						echo " <center><font color='#ff0000'><h5>No Data</h5></center>";
				?>

			<?php } ?>
				</table>
			</div>
		<?php } ?>

		<?php
		if (isset($_POST["buttonhub"])) {

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
					.bs-example {
						margin: 20px;
					}
				</style>

				<style type="text/css">
					body,
					td,
					th {
						font-family: "Phetsarath OT";
					}

					.navbarr {


						position: fixed;

					}

					.right {
						float: right;
					}

					.circle {


						border-radius: 14px;

					}

					.BG {
						background-image: linear-gradient(120deg, #89f7fe 0%, #66a6ff 100%);
					}
				</style>
				<?php
				date_default_timezone_set("Asia/Bangkok");

				$Pro = $_POST["Pro"] ?? '';
				$start = $_POST["start"] ?? '';
				$end = $_POST["end"] ?? '';
				$Equment1 = $_POST["Equment1"] ?? '';

				// ສຳລັບສູນກາງ
				if ($Pro == 'Center') {
					$sql = "SELECT * FROM stockinput 
						WHERE Date >= '$start 00:00:00' AND Date <= '$end 23:59:59'";

				if (!empty($Equment1)) {
					$sql .= " AND Items = '$Equment1'";
				}

					$sql .= " ORDER BY Date ASC";
				}

				// ສຳລັບທຸກແຂວງ
				else if ($Pro == 'all') {
					$sql = "SELECT * FROM stock_provinceinput 
						WHERE Date_Pro >= '$start' AND Date_Pro <= '$end'";

				if (!empty($Equment1)) {
					$sql .= " AND Items = '$Equment1'";
				}

				$sql .= " ORDER BY Provinces ASC";
				}

				// ສຳລັບແຂວງສະເພາະ
				else {
					$sql = "SELECT * FROM stock_provinceinput 
						WHERE Date_Pro >= '$start' AND Date_Pro <= '$end' 
						AND Provinces LIKE '$Pro'";

				if (!empty($Equment1)) {
					$sql .= " AND Items = '$Equment1'";
				}

					$sql .= " ORDER BY Provinces ASC";
				}


				mysqli_set_charset(@$conn, "utf8");
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
				?>
					<div align="center">
						<table class="table table-hover table-bordered table-sm" border="1">
							<thead>
								<tr align="center" class="text-dark BG " circle>
									<th>ສາງ</th>
									<th>ອຸປະກອນ</th>
									<th>ວັນທີ</th>

									<th>ຈຳນວນຮັບ</th>



									<th>ໄດ້ຈາກ</th>
									<th>ຜູ້ຮັບ</th>




								</tr>
							</thead>
							<tbody>
								<?php
								while ($row = $result->fetch_assoc()) {
									$id = $row['id'];



								?>
									<tr style="font-size: 14px;">
										<?php if ($Pro <> 'Center' or  $Pro == 'all') { ?>
											<td width="10%" align="center"><?php echo $row['Provinces'] ?></td>

										<?php } elseif ($Pro == 'Center') { ?>
											<td width="10%" align="center">ສູນກາງ</td>
										<?php } ?>
										<td width="30%"><strong>ອຸປະກອນ:</strong> <?php echo $row['Items'] ?><br><strong>ພາກສ່ວນ:</strong> <?php echo $row['Section'] ?><br><strong>ໃຊ້ສຳລັບ:</strong> <?php echo $row['Use_For'] ?><br><strong>ກຸ່ມ:</strong> <?php echo $row['Groupp'] ?> <br><strong>ຂໍ້ມູນຮ້ານທີ່ຈັດຊື້:</strong> <?php echo $row['Vendor'] ?>
											<br><b>ເລກທີ:</b> <?= $row['OA'] ?>
										</td>
										<?php if ($Pro <> 'Center'  or  $Pro == 'all') { ?>
											<td width="10%" align="center"><?php echo $row['Date_Pro'] ?></td>
										<?php } elseif ($Pro == 'Center') { ?>

											<td width="10%" align="center"><?php echo $row['Date'] ?></td>
										<?php } ?>

										<td width="10%" align="center" class="text-primary"><?php echo $row['Unit'] ?> <?php echo $row['Type'] ?></td>



										<td width="10%" align="center"><?php echo $row['Remarkk'] ?><br><strong><i class="fa-duotone fa-location-dot"></i> <?php echo $row['from_pro'] ?></strong></td>
										<td width="10%" align="center"><?php echo $row['User_Stock'] ?? '' ?><?php echo $row['User'] ?? '' ?></td>






									</tr>
							</tbody>

						<?php }
							} else {


								echo " <center><font color='#ff0000'><h5>No Data</h5></center>";
						?>

					<?php } ?>
						</table>
					</div>
				<?php } ?>

				<?php if (isset($_POST["chartUserhub"])) {


					set_time_limit(360000);
				?>
					<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">

					<head>
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
						<title></title>
						<style type="text/css">
							body,
							td,
							th {
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
							.no-js #loader {
								display: none;
							}

							.js #loader {
								display: block;
								position: absolute;
								left: 100px;
								top: 0;
							}

							.se-pre-con {
								position: fixed;
								left: 0px;
								top: -200px;
								width: 100%;
								height: 200%;
								z-index: 9999;

							}

							#chartdiv {
								width: 100%;
								height: 400px;
								font-size: 11px;
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
					$yearr = date('20y');
					$monthhh = date('m');
					$dayyy = date('20y-m-d');
					$Proo = $_POST["Pro"] ?? '';
					$catt = $_SESSION["Namepro"];
					if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793" or$_SESSION["iduser"] == '51' or $_SESSION["iduser"] == '43' or $_SESSION["iduser"] == '41' or $_SESSION["iduser"] == '378' or $_SESSION["iduser"] == '387' or $_SESSION["iduser"] == '514' or $_SESSION["iduser"] == '423' or $_SESSION["iduser"] == '166' or $_SESSION["iduser"] == '167' or $_SESSION["iduser"] == '168' or $_SESSION["iduser"] == '624' or $_SESSION["iduser"] == '190' or $_SESSION["iduser"] == '197' or $_SESSION["iduser"] == '718' or $_SESSION["iduser"] == '816' or $_SESSION["iduser"] == '835' or $_SESSION["iduser"] == '836' or $_SESSION["iduser"] == '837' or $_SESSION["iduser"] == '177' or $_SESSION["iduser"] == '180' or $_SESSION["iduser"] == '181' or $_SESSION["iduser"] == '487' or $_SESSION["iduser"] == '686') {
						echo ($Proo);
						if ($Pro = ($_POST["Pro"] ?? '') == 'all') {
							$sql = "SELECT
MONTH(stock_provinceinput.Date_Pro) as 'mothnn',
stock_provinceinput.Items as 'Names',
stock_provinceinput.Provinces as 'pro',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-01%',1,null)) as 'Month01',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-02%',1,null)) as 'Month02',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-03%',1,null)) as 'Month03',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-04%',1,null)) as 'Month04',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-05%',1,null)) as 'Month05',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-06%',1,null)) as 'Month06',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-07%',1,null)) as 'Month07',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-08%',1,null)) as 'Month08',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-09%',1,null)) as 'Month09',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-10%',1,null)) as 'Month10',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-11%',1,null)) as 'Month11',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-12%',1,null)) as 'Month12'
from stock_provinceinput 
GROUP BY pro
ORDER BY Month$monthhh DESC";
						} else {
							$sql = "SELECT
MONTH(stock_provinceinput.Date_Pro) as 'mothnn',
stock_provinceinput.Items as 'Names',
stock_provinceinput.Provinces as 'pro',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-01%',1,null)) as 'Month01',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-02%',1,null)) as 'Month02',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-03%',1,null)) as 'Month03',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-04%',1,null)) as 'Month04',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-05%',1,null)) as 'Month05',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-06%',1,null)) as 'Month06',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-07%',1,null)) as 'Month07',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-08%',1,null)) as 'Month08',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-09%',1,null)) as 'Month09',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-10%',1,null)) as 'Month10',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-11%',1,null)) as 'Month11',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-12%',1,null)) as 'Month12'
from stock_provinceinput 
WHERE stock_provinceinput.Provinces ='$Proo'
GROUP BY pro
ORDER BY Month$monthhh DESC
LIMIT 5;";
						}
						if ($Proo == 'Center') {

							$sql = "SELECT
MONTH(stockinput.Date) as 'mothnn',
stockinput.Items as 'Namess',
count(if(stockinput.Date LIKE '$yearr-01%',1,null)) as 'Month01',
count(if(stockinput.Date LIKE '$yearr-02%',1,null)) as 'Month02',
count(if(stockinput.Date LIKE '$yearr-03%',1,null)) as 'Month03',
count(if(stockinput.Date LIKE '$yearr-04%',1,null)) as 'Month04',
count(if(stockinput.Date LIKE '$yearr-05%',1,null)) as 'Month05',
count(if(stockinput.Date LIKE '$yearr-06%',1,null)) as 'Month06',
count(if(stockinput.Date LIKE '$yearr-07%',1,null)) as 'Month07',
count(if(stockinput.Date LIKE '$yearr-08%',1,null)) as 'Month08',
count(if(stockinput.Date LIKE '$yearr-09%',1,null)) as 'Month09',
count(if(stockinput.Date LIKE '$yearr-10%',1,null)) as 'Month10',
count(if(stockinput.Date LIKE '$yearr-11%',1,null)) as 'Month11',
count(if(stockinput.Date LIKE '$yearr-12%',1,null)) as 'Month12'
from stockinput 
GROUP BY Namess
ORDER BY Month$monthhh DESC";
						}
					}
					if ($_SESSION["iduser"] <> "404" and $_SESSION["iduser"] <> "30" and $_SESSION["iduser"] <> "194" and $_SESSION["iduser"] <> "793" and $_SESSION["iduser"] <> '51' and $_SESSION["iduser"] <> '43' and $_SESSION["iduser"] <> '41' and $_SESSION["iduser"] <> '378' and $_SESSION["iduser"] <> '387' and $_SESSION["iduser"] <> '514' and $_SESSION["iduser"] <> '423' and $_SESSION["iduser"] <> '166' and $_SESSION["iduser"] <> '167' and $_SESSION["iduser"] <> '168' and $_SESSION["iduser"] <> '624' and $_SESSION["iduser"] <> '190' and $_SESSION["iduser"] <> '197' and $_SESSION["iduser"] <> '718' and $_SESSION["iduser"] <> '816' and $_SESSION["iduser"] <> '835' and $_SESSION["iduser"] <> '836' and $_SESSION["iduser"] <> '837' and $_SESSION["iduser"] <> '177' and $_SESSION["iduser"] <> '180' and $_SESSION["iduser"] <> '181' and $_SESSION["iduser"] <> '487' and $_SESSION["iduser"] <> '686') {
						$sql = "
SELECT
MONTH(stock_provinceinput.Date_Pro) as 'mothnn',
stock_provinceinput.Items as 'Names',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-01%',1,null)) as 'Month01',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-02%',1,null)) as 'Month02',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-03%',1,null)) as 'Month03',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-04%',1,null)) as 'Month04',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-05%',1,null)) as 'Month05',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-06%',1,null)) as 'Month06',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-07%',1,null)) as 'Month07',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-08%',1,null)) as 'Month08',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-09%',1,null)) as 'Month09',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-10%',1,null)) as 'Month10',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-11%',1,null)) as 'Month11',
count(if(stock_provinceinput.Date_Pro LIKE '$yearr-12%',1,null)) as 'Month12'
from stock_provinceinput 
WHERE stock_provinceinput.Provinces ='Khammouan'
GROUP BY Names
ORDER BY Month02 DESC
LIMIT 5;";
					}



					//echo $sql;

					@mysqli_set_charset(@$conn, "utf8");
					@$result = $conn->query($sql);
					if ($result->num_rows > 0) {

				?>
					<script src="js/amcharts.js"></script>
					<script src="js/serial.js"></script>
					<script src="js/light.js"></script>
					<link rel="stylesheet" href="css/pages/export.css" type="text/css" media="all" />
					<script>
						var chart = AmCharts.makeChart("chartdiv", {
							"type": "serial",
							"theme": "light",


							"dataProvider": [
								<?php
								@$result = $conn->query($sql);
								while ($row = $result->fetch_assoc()) {
									$date = date_create($row[dates]);
									$dates = date_format($date, 'd-m-Y');
								?>
									<?php if ($_SESSION["iduser"] <> "404" and $_SESSION["iduser"] <> "30" and $_SESSION["iduser"] <> "194" and $_SESSION["iduser"] <> "793" and $_SESSION["iduser"] <> '51' and $_SESSION["iduser"] <> '43' and $_SESSION["iduser"] <> '41' and $_SESSION["iduser"] <> '378' and $_SESSION["iduser"] <> '387' and $_SESSION["iduser"] <> '514' and $_SESSION["iduser"] <> '423' and $_SESSION["iduser"] <> '166' and $_SESSION["iduser"] <> '167' and $_SESSION["iduser"] <> '168' and $_SESSION["iduser"] <> '624' and $_SESSION["iduser"] <> '190' and $_SESSION["iduser"] <> '197' and $_SESSION["iduser"] <> '718' and $_SESSION["iduser"] <> '816' and $_SESSION["iduser"] <> '835' and $_SESSION["iduser"] <> '836' and $_SESSION["iduser"] <> '837' and $_SESSION["iduser"] <> '177' and $_SESSION["iduser"] <> '180' and $_SESSION["iduser"] <> '181' and $_SESSION["iduser"] <> '487' and $_SESSION["iduser"] <> '686') {  ?> {
											"col": "<?= $row[Names] ?>",
											"Month01": <?= floatval($row[Month01]) ?>,
											"Month02": <?= floatval($row[Month02]) ?>,
											"Month03": <?= floatval($row[Month03]) ?>,
											"Month04": <?= floatval($row[Month04]) ?>,
											"Month05": <?= floatval($row[Month05]) ?>,
											"Month06": <?= floatval($row[Month06]) ?>,
											"Month07": <?= floatval($row[Month07]) ?>,
											"Month08": <?= floatval($row[Month08]) ?>,
											"Month09": <?= floatval($row[Month09]) ?>,
											"Month10": <?= floatval($row[Month10]) ?>,
											"Month11": <?= floatval($row[Month11]) ?>,
											"Month12": <?= floatval($row[Month12]) ?>

										},
									<?php } ?>

									<?php if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793" or$_SESSION["iduser"] == '51' or $_SESSION["iduser"] == '43' or $_SESSION["iduser"] == '41' or $_SESSION["iduser"] == '378' or $_SESSION["iduser"] == '387' or $_SESSION["iduser"] == '514' or $_SESSION["iduser"] == '423' or $_SESSION["iduser"] == '166' or $_SESSION["iduser"] == '167' or $_SESSION["iduser"] == '168' or $_SESSION["iduser"] == '624' or $_SESSION["iduser"] == '190' or $_SESSION["iduser"] == '197' or $_SESSION["iduser"] == '718' or $_SESSION["iduser"] == '816' or $_SESSION["iduser"] == '835' or $_SESSION["iduser"] == '836' or $_SESSION["iduser"] == '837' or $_SESSION["iduser"] == '177' or $_SESSION["iduser"] == '180' or $_SESSION["iduser"] == '181' or $_SESSION["iduser"] == '487' or $_SESSION["iduser"] == '686') {


									?>

										<?php if ($Proo == 'all') { ?> {
												"col": "<?= $row[pro] ?>",
												"Month01": <?= floatval($row[Month01]) ?>,
												"Month02": <?= floatval($row[Month02]) ?>,
												"Month03": <?= floatval($row[Month03]) ?>,
												"Month04": <?= floatval($row[Month04]) ?>,
												"Month05": <?= floatval($row[Month05]) ?>,
												"Month06": <?= floatval($row[Month06]) ?>,
												"Month07": <?= floatval($row[Month07]) ?>,
												"Month08": <?= floatval($row[Month08]) ?>,
												"Month09": <?= floatval($row[Month09]) ?>,
												"Month10": <?= floatval($row[Month10]) ?>,
												"Month11": <?= floatval($row[Month11]) ?>,
												"Month12": <?= floatval($row[Month12]) ?>

											},
										<?php } elseif ($Proo <> 'all' and $Proo <> 'Center') { ?>

											{
												"col": "<?= $row[Names] ?>",
												"Month01": <?= floatval($row[Month01]) ?>,
												"Month02": <?= floatval($row[Month02]) ?>,
												"Month03": <?= floatval($row[Month03]) ?>,
												"Month04": <?= floatval($row[Month04]) ?>,
												"Month05": <?= floatval($row[Month05]) ?>,
												"Month06": <?= floatval($row[Month06]) ?>,
												"Month07": <?= floatval($row[Month07]) ?>,
												"Month08": <?= floatval($row[Month08]) ?>,
												"Month09": <?= floatval($row[Month09]) ?>,
												"Month10": <?= floatval($row[Month10]) ?>,
												"Month11": <?= floatval($row[Month11]) ?>,
												"Month12": <?= floatval($row[Month12]) ?>

											},








										<?php } elseif ($Proo == 'Center') { ?> {
												"col": "<?= $row[Namess] ?>",
												"Month01": <?= floatval($row[Month01]) ?>,
												"Month02": <?= floatval($row[Month02]) ?>,
												"Month03": <?= floatval($row[Month03]) ?>,
												"Month04": <?= floatval($row[Month04]) ?>,
												"Month05": <?= floatval($row[Month05]) ?>,
												"Month06": <?= floatval($row[Month06]) ?>,
												"Month07": <?= floatval($row[Month07]) ?>,
												"Month08": <?= floatval($row[Month08]) ?>,
												"Month09": <?= floatval($row[Month09]) ?>,
												"Month10": <?= floatval($row[Month10]) ?>,
												"Month11": <?= floatval($row[Month11]) ?>,
												"Month12": <?= floatval($row[Month12]) ?>

											},
									<?php }
									} ?>




								<?php
								}
								?>
							],


							"valueAxes": [{
								"gridColor": "#FFFFFF",
								"gridAlpha": 0.2,
								"dashLength": 0
							}],
							"gridAboveGraphs": true,
							//"startDuration": 0.2,
							"chartCursor": {
								"enabled": true
							},
							"legend": {
								"enabled": true
							},
							"graphs": [{
									"balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
									"labelText": "[[value]]",
									"bullet": "round",
									"color": "red",
									"bulletSize": 15,
									"title": "(<?= $yearr ?>-1 ມັງກອນ)",
									"type": "smoothedLine",
									<?php if ($monthhh <> '1') { ?> "hidden": true,
									<?php } ?> "valueField": "Month01"
								},
								{
									"balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
									"labelText": "[[value]]",
									"bullet": "round",
									"color": "red",
									"bulletSize": 15,
									"title": "(<?= $yearr ?>-2 ກຸມພາ)",
									"type": "smoothedLine",
									<?php if ($monthhh <> '2') { ?> "hidden": true,
									<?php } ?> "valueField": "Month02"

								}, {
									"balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
									"labelText": "[[value]]",
									"bullet": "round",
									"color": "red",
									"bulletSize": 15,
									"title": "(<?= $yearr ?>-3 ມີນາ)",
									"type": "smoothedLine",
									<?php if ($monthhh <> '3') { ?> "hidden": true,
									<?php } ?> "valueField": "Month03"
								},
								{
									"balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
									"labelText": "[[value]]",
									"bullet": "round",
									"color": "red",
									"bulletSize": 15,
									"title": "(<?= $yearr ?>-4 ເມສາ)",
									"type": "smoothedLine",
									<?php if ($monthhh <> '4') { ?> "hidden": true,
									<?php } ?> "valueField": "Month04"
								},
								{
									"balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
									"labelText": "[[value]]",
									"bullet": "round",
									"color": "red",
									"bulletSize": 15,
									"title": "(<?= $yearr ?>-5 ພຶດສະພາ)",
									"type": "smoothedLine",
									<?php if ($monthhh <> '5') { ?> "hidden": true,
									<?php } ?> "valueField": "Month05"
								},
								{

									"balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
									"labelText": "[[value]]",
									"bullet": "round",
									"color": "red",
									"bulletSize": 15,
									"title": "(<?= $yearr ?>-6 ມີຖຸນາ)",
									"type": "smoothedLine",
									<?php if ($monthhh <> '6') { ?> "hidden": true,
									<?php } ?> "valueField": "Month06"
								},
								{
									"balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
									"labelText": "[[value]]",
									"bullet": "round",
									"color": "red",
									"bulletSize": 15,
									"title": "(<?= $yearr ?>-7 ກໍລະກົດ)",
									"type": "smoothedLine",
									<?php if ($monthhh <> '7') { ?> "hidden": true,
									<?php } ?> "valueField": "Month07"
								},
								{
									"balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
									"labelText": "[[value]]",
									"bullet": "round",
									"color": "red",
									"bulletSize": 15,
									"title": "(<?= $yearr ?>-8 ສິງຫາ)",
									"type": "smoothedLine",
									<?php if ($monthhh <> '8') { ?> "hidden": true,
									<?php } ?> "valueField": "Month08"
								},
								{
									"balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
									"labelText": "[[value]]",
									"bullet": "round",
									"color": "red",
									"bulletSize": 15,
									"title": "(<?= $yearr ?>-9 ກັນຍາ)",
									"type": "smoothedLine",
									<?php if ($monthhh <> '9') { ?> "hidden": true,
									<?php } ?> "valueField": "Month09"
								},
								{
									"balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
									"labelText": "[[value]]",
									"bullet": "round",
									"color": "red",
									"bulletSize": 15,
									"title": "(<?= $yearr ?>-10 ຕຸລາ)",
									"type": "smoothedLine",
									<?php if ($monthhh <> '10') { ?> "hidden": true,
									<?php } ?> "valueField": "Month10"
								},
								{
									"balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
									"labelText": "[[value]]",
									"bullet": "round",
									"color": "red",
									"bulletSize": 15,
									"title": "(<?= $yearr ?>-11 ພະຈິກ)",
									"type": "smoothedLine",
									<?php if ($monthhh <> '11') { ?> "hidden": true,
									<?php } ?> "valueField": "Month11"
								},
								{
									"balloonText": " [[category]]: <b>[[value]] ຄັ້ງ",
									"labelText": "[[value]]",
									"bullet": "round",
									"color": "red",
									"bulletSize": 15,
									"title": "(<?= $yearr ?>-12 ທັນວາ)",
									"type": "smoothedLine",
									<?php if ($monthhh <> '12') { ?> "hidden": true,
									<?php } ?> "valueField": "Month12"
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
						});
					</script>
				<?php
					}
				?>
				<br />
				<div id="chartdiv"></div>

			<?php } ?>