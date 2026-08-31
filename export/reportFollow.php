<?php
require_once __DIR__ . '/../includes/conn.php';
@session_start();
if ($_SESSION["user"] == "") {
	echo "<script>window.location = 'index.php';</script>";
	exit;
}
if (isset($_POST["FollowStock"])) {
	header("Content-Type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=ຕິດຕາມ ອຸປະກອນ.xls");
?>

	<!DOCTYPE html>
	<html lang="en">

	<head>

		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
		<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
		<link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">
	</head>
	<style type="text/css">
		body,
		td,
		th {
			font-family: "Phetsarath OT";
		}
	</style>



	<table class="table table-hover  " border="1">
		<thead>
			<tr style="font-size: 15px;color:blue">

				<th width="2%">ລຳດັບ</th>
				<th width="13%"><i class="fa-regular fa-location-dot"></i> ແຂວງ</th>
				<th width="25%"><i class="fa-duotone fa-server"></i> ລາຍການອຸປະກອນ</th>
				<th width="20%"><i class="fa-duotone fa-map-location-dot"></i> ໃຊ້ຢູ່ສະຖານທີ່</th>
				<th width="15%"><i class="fa-duotone fa-keyboard"></i> ຊີລຽວນາມເບີ</th>
				<th width="10%"><i class="fa-regular fa-sensor-triangle-exclamation"></i> ສະຖານະ</th>
				<th width="15%"><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່ສົ່ງແປງ</th>
				<th width="15%"><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່ສ້ອມແປງ</th>

			</tr>
		</thead><?php
				date_default_timezone_set("Asia/Bangkok");
				$Province = $_SESSION["Namepro"];
				$start = $_POST["start"];
				$end = $_POST["end"];
				$Choi = $_POST["Choi"];

				if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793" or $_SESSION["iduser"] == '51' or $_SESSION["iduser"] == '43' or $_SESSION["iduser"] == '41' or $_SESSION["iduser"] == '378' or $_SESSION["iduser"] == '387' or $_SESSION["iduser"] == '514' or $_SESSION["iduser"] == '423' or $_SESSION["iduser"] == '166' or $_SESSION["iduser"] == '167' or $_SESSION["iduser"] == '168' or $_SESSION["iduser"] == '624' or $_SESSION["iduser"] == '190' or $_SESSION["iduser"] == '197' or $_SESSION["iduser"] == '718' or $_SESSION["iduser"] == '816' or $_SESSION["iduser"] == '835' or $_SESSION["iduser"] == '836' or $_SESSION["iduser"] == '837' or $_SESSION["iduser"] == '177' or $_SESSION["iduser"] == '180' or $_SESSION["iduser"] == '181' or $_SESSION["iduser"] == '487' or $_SESSION["iduser"] == '686') {
					if ($Choi == 'ສ້ອມແປງ' and $_POST["stt"] <> '') {

						if ($_POST["stt"] == 'all') {
							$sql = "SELECT*FROM followequment 
WHERE Date_Goto <> '' and ((Date >='$start 00:00:00' and Date <='$end 23:59:59') or (Date_Fix >='$start 00:00:00' and Date_Fix <='$end 23:59:59')) and S_Status = 'ສ້ອມແປງ'
ORDER BY followequment.Date_Goto desc ";
						} else {
							$sql = "SELECT*FROM followequment 
WHERE Date_Goto <> '' and ((Date >='$start 00:00:00' and Date <='$end 23:59:59') or (Date_Fix >='$start 00:00:00' and Date_Fix <='$end 23:59:59')) and S_Status = 'ສ້ອມແປງ' and STT_Fix='" . $_POST["stt"] . "'
ORDER BY followequment.Date_Goto desc ";
						}
					} elseif ($Choi == 'ຍົກຍ້າຍ') {


						$sql = "SELECT*FROM followequment 
WHERE Date_Goto <> '' and ((Date >='$start 00:00:00' and Date <='$end 23:59:59') or (Date_Fix >='$start 00:00:00' and Date_Fix <='$end 23:59:59')) and S_Status = 'ຍົກຍ້າຍ'
ORDER BY followequment.Date_Goto desc ";
					} elseif ($Choi == 'ຖອນ') {


						$sql = "SELECT*FROM followequment 
WHERE Date_Goto <> '' and ((Date >='$start 00:00:00' and Date <='$end 23:59:59') or (Date_Fix >='$start 00:00:00' and Date_Fix <='$end 23:59:59')) and S_Status = 'ຖອນ'
ORDER BY followequment.Date_Goto desc ";
					}
				} elseif ($_SESSION["iduser"] <> "404" and $_SESSION["iduser"] <> "30" and $_SESSION["iduser"] <> "194" and $_SESSION["iduser"] <> "793" and $_SESSION["iduser"] <> '51' and $_SESSION["iduser"] <> '43' and $_SESSION["iduser"] <> '41' and $_SESSION["iduser"] <> '378' and $_SESSION["iduser"] <> '387' and $_SESSION["iduser"] <> '514' and $_SESSION["iduser"] <> '423' and $_SESSION["iduser"] <> '166' and $_SESSION["iduser"] <> '167' and $_SESSION["iduser"] <> '168' and $_SESSION["iduser"] <> '624' and $_SESSION["iduser"] <> '190' and $_SESSION["iduser"] <> '197' and $_SESSION["iduser"] <> '718' and $_SESSION["iduser"] <> '816' and $_SESSION["iduser"] <> '835' and $_SESSION["iduser"] <> '836' and $_SESSION["iduser"] <> '837' and $_SESSION["iduser"] <> '177' and $_SESSION["iduser"] <> '180' and $_SESSION["iduser"] <> '181' and $_SESSION["iduser"] <> '487' and $_SESSION["iduser"] <> '686') {
					if ($Choi == 'ສ້ອມແປງ') {
						$sql = "SELECT*FROM followequment 
WHERE followequment.Cat_Province LIKE '$Province' and Date_Goto <> '' and ((Date >='$start 00:00:00' and Date <='$end 23:59:59') or (Date_Fix >='$start 00:00:00' and Date_Fix <='$end 23:59:59')) and S_Status = 'ສ້ອມແປງ'
ORDER BY followequment.Date_Goto desc ";
					}
					if ($Choi == 'ຍົກຍ້າຍ') {
						$sql = "SELECT*FROM followequment 
WHERE followequment.Cat_Province LIKE '$Province' and Date_Goto <> '' and ((Date >='$start 00:00:00' and Date <='$end 23:59:59') or (Date_Fix >='$start 00:00:00' and Date_Fix <='$end 23:59:59')) and S_Status = 'ຍົກຍ້າຍ'
ORDER BY followequment.Date_Goto desc ";
					}
					if ($Choi == 'ຖອນ') {
						$sql = "SELECT*FROM followequment 
WHERE followequment.Cat_Province LIKE '$Province' and Date_Goto <> '' and ((Date >='$start 00:00:00' and Date <='$end 23:59:59') or (Date_Fix >='$start 00:00:00' and Date_Fix <='$end 23:59:59')) and S_Status = 'ຖອນ'
ORDER BY followequment.Date_Goto desc ";
					}
				}






				@mysqli_set_charset(@$conn, "utf8");
				$result = $conn->query($sql);

				?>
		<?php
		while ($row = $result->fetch_assoc()) {
			$id = $row['id'];
			$Name_Come = $row['Name_Come'];
			$S_Status = $row['S_Status'];
			if ($row['User_TMD'] <> '') {

				$show = 'style="color: green"';
			}
			if ($row['STT_Fix'] == 'ຕາຍ') {
				$colort = 'style="color: red"';
			} else {
				$colort = 'style="color: green"';
			}


			if ($S_Status == 'ຍົກຍ້າຍ') {
				$colort = 'style="color: red"';
			}

			$no++;

		?>
			<tbody>

				<tr style="font-size: 14px;">
					<td width="2%" class="text-primary"><?php echo $no ?></td>

					<td width="13%" class="text-primary"> <b><?php echo $row['Cat_Province'] ?></b></td>

					<td width="25%" class="text-primary"><?php echo $row['Name_Come'] ?></td>
					<td width="20%" <?php echo $colort ?>> <?php echo $row['address'] ?></td>
					<td width="15%" <?php echo $colort ?>> <?php echo $row['NumBer_S'] ?></td>

					<?php if ($S_Status == 'ຍົກຍ້າຍ') { ?>
						<td width="10%" <?php echo $colort ?>><strong>ໄປສາງ :</strong> <?php echo $row['Go_Province'] ?></td>
					<?php } elseif ($S_Status == 'ສ້ອມແປງ') { ?>
						<td width="10%" <?php echo $colort ?>> <?php echo $row['STT_Fix'] ?></td>
					<?php } elseif ($S_Status == 'ຖອນ') { ?>
						<td width="10%" <?php echo $colort ?>><strong></td>
					<?php } ?>
					<td width="15%" <?php echo $colort ?>> <?php echo $row['Date'] ?></td>
					<td width="15%" > <?php echo $row['Date_Fix'] ?></td>



				<?php } ?>

				</tr>

			</tbody>
	</table>
<?php } elseif (isset($_POST["buttonFollow"])) { ?>

	<head>

		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
		<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1/dist/jquery.slim.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
	</head>
	<style type="text/css">
		body,
		td,
		th {
			font-family: "Phetsarath OT";
		}
	</style>

	<body>

		<table class="table table-hover  ">
			<thead>
				<tr style="font-size: 15px;color:blue">
					<th width="2%">ລຳດັບ</th>
					<th width="13%"><i class="fa-regular fa-location-dot"></i> ແຂວງ</th>
					<th width="20%"><i class="fa-duotone fa-server"></i> ລາຍການອຸປະກອນ</th>
					<th width="15%"><i class="fa-duotone fa-map-location-dot"></i> ໃຊ້ຢູ່ສະຖານທີ່</th>
					<th width="15%"><i class="fa-duotone fa-keyboard"></i> ຊີລຽວນາມເບີ</th>
					<th width="10%"><i class="fa-regular fa-sensor-triangle-exclamation"></i> ສະຖານະ</th>
					<th width="10%"><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່ສົ່ງແປງ</th>
					<th width="10%"><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່ສ້ອມແປງ</th>

				</tr>
			</thead><?php
					date_default_timezone_set("Asia/Bangkok");
					$Province = $_SESSION["Namepro"];
					$start = $_POST["start"] ?? '';
					$end = $_POST["end"] ?? '';
					$Choi = $_POST["Choi"] ?? '';

					if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793" or $_SESSION["iduser"] == '51' or $_SESSION["iduser"] == '43' or $_SESSION["iduser"] == '41' or $_SESSION["iduser"] == '378' or $_SESSION["iduser"] == '387' or $_SESSION["iduser"] == '514' or $_SESSION["iduser"] == '423' or $_SESSION["iduser"] == '166' or $_SESSION["iduser"] == '167' or $_SESSION["iduser"] == '168' or $_SESSION["iduser"] == '624' or $_SESSION["iduser"] == '190' or $_SESSION["iduser"] == '197' or $_SESSION["iduser"] == '718' or $_SESSION["iduser"] == '816' or $_SESSION["iduser"] == '835' or $_SESSION["iduser"] == '836' or $_SESSION["iduser"] == '837' or $_SESSION["iduser"] == '177' or $_SESSION["iduser"] == '180' or $_SESSION["iduser"] == '181' or $_SESSION["iduser"] == '487' or $_SESSION["iduser"] == '686') {
						if ($Choi == 'ສ້ອມແປງ' and $_POST["stt"] <> '') {

							if ($_POST["stt"] == 'all') {
								$sql = "SELECT*FROM followequment 
WHERE Date_Goto <> '' and ((Date >='$start 00:00:00' and Date <='$end 23:59:59') or (Date_Fix >='$start 00:00:00' and Date_Fix <='$end 23:59:59')) and S_Status = 'ສ້ອມແປງ'
ORDER BY followequment.Date_Goto desc ";
							} else {
								$sql = "SELECT*FROM followequment 
WHERE Date_Goto <> '' and ((Date >='$start 00:00:00' and Date <='$end 23:59:59') or (Date_Fix >='$start 00:00:00' and Date_Fix <='$end 23:59:59')) and S_Status = 'ສ້ອມແປງ' and STT_Fix='" . $_POST["stt"] . "'
ORDER BY followequment.Date_Goto desc ";
							}
						} elseif ($Choi == 'ຍົກຍ້າຍ') {


							$sql = "SELECT*FROM followequment 
WHERE Date_Goto <> '' and ((Date >='$start 00:00:00' and Date <='$end 23:59:59') or (Date_Fix >='$start 00:00:00' and Date_Fix <='$end 23:59:59')) and S_Status = 'ຍົກຍ້າຍ'
ORDER BY followequment.Date_Goto desc ";
						} elseif ($Choi == 'ຖອນ') {


							$sql = "SELECT*FROM followequment 
WHERE Date_Goto <> '' and ((Date >='$start 00:00:00' and Date <='$end 23:59:59') or (Date_Fix >='$start 00:00:00' and Date_Fix <='$end 23:59:59')) and S_Status = 'ຖອນ'
ORDER BY followequment.Date_Goto desc ";
						}
					} elseif ($_SESSION["iduser"] <> "404" and $_SESSION["iduser"] <> "30" and $_SESSION["iduser"] <> "194" and $_SESSION["iduser"] <> "793" and $_SESSION["iduser"] <> '51' and $_SESSION["iduser"] <> '43' and $_SESSION["iduser"] <> '41' and $_SESSION["iduser"] <> '378' and $_SESSION["iduser"] <> '387' and $_SESSION["iduser"] <> '514' and $_SESSION["iduser"] <> '423' and $_SESSION["iduser"] <> '166' and $_SESSION["iduser"] <> '167' and $_SESSION["iduser"] <> '168' and $_SESSION["iduser"] <> '624' and $_SESSION["iduser"] <> '190' and $_SESSION["iduser"] <> '197' and $_SESSION["iduser"] <> '718' and $_SESSION["iduser"] <> '816' and $_SESSION["iduser"] <> '835' and $_SESSION["iduser"] <> '836' and $_SESSION["iduser"] <> '837' and $_SESSION["iduser"] <> '177' and $_SESSION["iduser"] <> '180' and $_SESSION["iduser"] <> '181' and $_SESSION["iduser"] <> '487' and $_SESSION["iduser"] <> '686') {
						if ($Choi == 'ສ້ອມແປງ') {
							$sql = "SELECT*FROM followequment 
WHERE followequment.Cat_Province LIKE '$Province' and Date_Goto <> '' and ((Date >='$start 00:00:00' and Date <='$end 23:59:59') or (Date_Fix >='$start 00:00:00' and Date_Fix <='$end 23:59:59')) and S_Status = 'ສ້ອມແປງ'
ORDER BY followequment.Date_Goto desc ";
						}
						if ($Choi == 'ຍົກຍ້າຍ') {
							$sql = "SELECT*FROM followequment 
WHERE followequment.Cat_Province LIKE '$Province' and Date_Goto <> '' and ((Date >='$start 00:00:00' and Date <='$end 23:59:59') or (Date_Fix >='$start 00:00:00' and Date_Fix <='$end 23:59:59')) and S_Status = 'ຍົກຍ້າຍ'
ORDER BY followequment.Date_Goto desc ";
						}
						if ($Choi == 'ຖອນ') {
							$sql = "SELECT*FROM followequment 
WHERE followequment.Cat_Province LIKE '$Province' and Date_Goto <> '' and ((Date >='$start 00:00:00' and Date <='$end 23:59:59') or (Date_Fix >='$start 00:00:00' and Date_Fix <='$end 23:59:59'))  and S_Status = 'ຖອນ'
ORDER BY followequment.Date_Goto desc ";
						}
					}






					@mysqli_set_charset(@$conn, "utf8");
					$result = $conn->query($sql);

					?>
			<?php
			while ($row = $result->fetch_assoc()) {
				$id = $row['id'];
				$Name_Come = $row['Name_Come'];
				$S_Status = $row['S_Status'];
				if ($row['User_TMD'] <> '') {

					$show = 'style="color: green"';
				}
				if ($row['STT_Fix'] == 'ຕາຍ') {
					$colort = 'style="color: red"';
				} else {
					$colort = 'style="color: green"';
				}


				if ($S_Status == 'ຍົກຍ້າຍ') {
					$colort = 'style="color: red"';
				}
				$no++;

			?>
				<tbody>

					<tr style="font-size: 14px;">
						<td width="2%" class="text-primary"><?php echo $no ?></td>

						<td width="13%" class="text-primary"> <b><?php echo $row['Cat_Province'] ?></b></td>

						<td width="20%" class="text-primary"><a href="step.php?id=<?php echo $row['id'] ?>&Name_Come=<?php echo $row['Name_Come'] ?>&S_Status=<?php echo $row['S_Status'] ?>" target="_blank"><?php echo $row['Name_Come'] ?></a></td>
						<td width="15%" <?php echo $colort ?>> <?php echo $row['address'] ?></td>
						<td width="10%" <?php echo $colort ?>> <?php echo $row['NumBer_S'] ?></td>

						<?php if ($S_Status == 'ຍົກຍ້າຍ') { ?>
							<td width="10%" <?php echo $colort ?>><strong>ໄປສາງ :</strong> <?php echo $row['Go_Province'] ?></td>
						<?php } elseif ($S_Status == 'ສ້ອມແປງ') { ?>
							<td width="10%" <?php echo $colort ?>><?php echo $row['STT_Fix'] ?></td>
						<?php } elseif ($S_Status == 'ຖອນ') { ?>
							<td width="10%" <?php echo $colort ?>><strong></td>
						<?php } ?>
						<td width="10%" <?php echo $colort ?>> <?php echo $row['Date'] ?></td>
						<td width="10%"> <?php echo $row['Date_Fix'] ?></td>



					<?php } ?>

					</tr>

				</tbody>
		</table>
	<?php } ?>

	</body>

	</html>