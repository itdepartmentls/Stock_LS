<?php
require_once __DIR__ . '/../includes/conn.php';
@session_start();
if ($_SESSION["user"] == "" ) {
	echo "<script>window.location = 'index.php';</script>";
	exit;
}
if (isset($_POST["BerkStock"])) {
	header("Content-Type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=ເບີກ-ອຸປະກອນ.xls");
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
		</style>
		<?php
		date_default_timezone_set("Asia/Bangkok");
		$Pro = $_POST["Pro"] ?? '';
		$start = $_POST["start"] ?? '';
		$end = $_POST["end"] ?? '';
		$Choi = $_POST["Choi"] ?? '';
		$ChoiRemark = $_POST["ChoiRemark"] ?? '';

		if ($Pro <> 'all' and $ChoiRemark == '') {
			$sql = "SELECT*FROM request a
WHERE a.$Choi > '$start 00:00:00' and a.$Choi < '$end 23:59:59' and a.Province like '$Pro'
ORDER BY a.$Choi asc";
		} elseif ($Pro <> 'all' and $ChoiRemark <> '') {
			$sql = "SELECT*FROM request a
WHERE a.$Choi > '$start 00:00:00' and a.$Choi < '$end 23:59:59' and a.Province like '$Pro' and a.S_Status ='$ChoiRemark'
ORDER BY a.$Choi asc";
		} elseif ($Pro == 'all' and $ChoiRemark == '') {
			$sql = "SELECT*FROM request a
WHERE a.$Choi > '$start 00:00:00' and a.$Choi < '$end 23:59:59'
ORDER BY a.Province asc";
		} elseif ($Pro == 'all' and $ChoiRemark <> '') {
			$sql = "SELECT*FROM request a
WHERE a.$Choi > '$start 00:00:00' and a.$Choi < '$end 23:59:59' and a.S_Status ='$ChoiRemark'
ORDER BY a.Province asc";
		}








		mysqli_set_charset(@$conn, "utf8");
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
		?>
			<div align="center">
				<table class="table table-hover table-bordered table-sm" border="1">
					<thead>
						<tr class="text-body" bgcolor="#E8E8E8" align="center">
							<th>ສາງ</th>
							<th>ອຸປະກອນ</th>

							<th>ວັນທີຂໍເບິກ</th>
							<th>ວັນທີເບິກ</th>

							<th>ຈຳນວນຂໍເບິກ</th>
							<th>ຈຳນວນເບິກ</th>
							<th>ຜູ້ເບີກເຄື່ອງ</th>
							






						</tr>
					</thead>
					<tbody>
						<?php
						while ($row = $result->fetch_assoc()) {

							$id = $row['id'];



						?>
							<tr style="font-size: 14px;">

								<td width="10%" align="center"><?php echo $row['Province']; ?></td>





								<td width="30%"><strong>ອຸປະກອນ:</strong> <?php echo $row['Items'] ?><br><strong>ຂໍ້ມູນຮ້ານທີ່ຈັດຊື້:</strong> <?php echo $row['Vendor'] ?><br><strong>ພາກສ່ວນ:</strong> <?php echo $row['Section'] ?><br><strong>ໃຊ້ໃນກຸ່ມ:</strong> <?php echo $row['Groupp'] ?><br><b>PR ເລກທີ:</b> <?= $row['OA_Out'] ?> </td>


								<td width="10%" align="center"><?php echo $row['dateRe'] ?></td>
								<td width="10%" align="center"><?php echo $row['DateComfirm'] ?></td>






								<td width="10%" align="center" class="text-success"><strong><?php echo $row['Unit']; ?> <?php echo $row['Type']; ?></strong></td>
								<td width="10%" align="center" class="text-primary"><strong><?php if ($row['S_Status'] == '1') {
																								echo ("ລໍຖ້າການເບິກ");
																							} elseif ($row['S_Status'] == '4') {
																								echo ("<p style='color: green' >" . $row['Give'] . " " . $row['Type'] . "</p>");
																							} elseif ($row['S_Status'] == '3') {
																								echo ("<p style='color: red' >ໄດ້ຖືກຍົກເລີກ</p>");
																							} elseif ($row['S_Status'] == '2') {
																								echo ("<p style='color: red' >" . $row['Give'] . " " . $row['Type'] . "<br>ສາຂາຍັງບໍ່ໄດ້ຮັບ</p>");
																							} ?></strong></td>
								<td width="20%" align="center"><?php echo $row['User_Confirm'] ?><br><strong>ໝາຍເຫດ:</strong> <?php echo $row['Remark'] ?></td>









							</tr>
					</tbody>

				<?php }
					} else {


						echo " <center><font color='#ff0000'><h5>No Data</h5></center>";
				?>

			<?php } ?>
				</table>
			</div>
		<?php } elseif (isset($_POST["buttonberk"])) { ?>


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
				$Choi = $_POST["Choi"] ?? '';
				$ChoiRemark = $_POST["ChoiRemark"] ?? '';
				$Equment1 = $_POST["Equment1"] ?? '';

				if ($Pro <> 'all' and $ChoiRemark == '') {
					
					$sql = "SELECT*FROM request a
WHERE a.$Choi > '$start 00:00:00' and a.$Choi < '$end 23:59:59' and a.Province like '$Pro' 
ORDER BY a.$Choi asc";
				} 

				if ($Equment1  =($_POST["Equment1"] ?? '')) {
					
				$sql = "SELECT*FROM request a
WHERE a.$Choi > '$start 00:00:00' and a.$Choi < '$end 23:59:59' and a.Province like '$Pro' and a.Items = '$Equment1'
ORDER BY a.$Choi asc";
			} 	
		
				elseif ($Pro <> 'all' and $ChoiRemark <> '') {
					$sql = "SELECT*FROM request a
WHERE a.$Choi > '$start 00:00:00' and a.$Choi < '$end 23:59:59' and a.Province like '$Pro' and a.S_Status ='$ChoiRemark'
ORDER BY a.$Choi asc";
				} elseif ($Pro == 'all' and $ChoiRemark == '') {
					$sql = "SELECT*FROM request a
WHERE a.$Choi > '$start 00:00:00' and a.$Choi < '$end 23:59:59'
ORDER BY a.Province asc";
				} elseif ($Pro == 'all' and $ChoiRemark <> '') {
					$sql = "SELECT*FROM request a
WHERE a.$Choi > '$start 00:00:00' and a.$Choi < '$end 23:59:59' and a.S_Status ='$ChoiRemark'
ORDER BY a.Province asc";
				}								
								
		
				









				mysqli_set_charset(@$conn, "utf8");
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
				?>
					<div align="center">
						<table class="table table-hover table-bordered table-sm">
							<thead>
								<tr align="center" class="text-dark BG " circle>
									<th>ສາງ</th>
									<th>ອຸປະກອນ</th>

									<th>ວັນທີຂໍເບິກ</th>
									<th>ວັນທີເບິກ</th>

									<th>ຈຳນວນຂໍເບິກ</th>
									<th>ຈຳນວນເບິກ</th>
									<th>ຜູ້ເບີກເຄື່ອງ</th>
							<th>ຜູ້ຮັບເຄື່ອງ</th>






								</tr>
							</thead>
							<tbody>
								<?php
								while ($row = $result->fetch_assoc()) {

									$id = $row['id'];



								?>
									<tr style="font-size: 14px;">

										<td width="10%" align="center"><?php echo $row['Province'] ?></td>





										<td width="30%"><strong>ອຸປະກອນ:</strong> <?php echo $row['Items'] ?><br><strong>ຂໍ້ມູນຮ້ານທີ່ຈັດຊື້:</strong> <?php echo $row['Vendor'] ?><br><strong>ພາກສ່ວນ:</strong> <?php echo $row['Section'] ?><br><strong>ໃຊ້ໃນກຸ່ມ:</strong> <?php echo $row['Groupp'] ?><br><b>PR ເລກທີ:</b> <?= $row['OA_Out'] ?> </td>


										<td width="10%" align="center"><?php echo $row['dateRe'] ?></td>
										<td width="10%" align="center"><?php echo $row['DateComfirm'] ?></td>






										<td width="10%" align="center" class="text-success"><strong><?php echo $row['Unit'] ?> <?php echo $row['Type'] ?></strong></td>
										<td width="10%" align="center" class="text-primary"><strong><?php if ($row['S_Status'] == '1') {
																										echo ("ລໍຖ້າການເບິກ");
																									} elseif ($row['S_Status'] == '4') {
																										echo ("<p style='color: green' >" . $row['Give'] . " " . $row['Type'] . "</p>");
																									} elseif ($row['S_Status'] == '3') {
																										echo ("<p style='color: red' >ໄດ້ຖືກຍົກເລີກ</p>");
																									} elseif ($row['S_Status'] == '2') {
																										echo ("<p style='color: red' >" . $row['Give'] . " " . $row['Type'] . "<br>ສາຂາຍັງບໍ່ໄດ້ຮັບ</p>");
																									} ?></strong></td>
										<td width="10%" align="center"><?php echo $row['User_Confirm'] ?><br><strong></strong> <?php echo $row['Remark'] ?></td>
										<td width="10%" align="center"><?php echo $row['User_Re'] ?></td>








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