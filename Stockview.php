<?php
require_once __DIR__ . '/includes/conn.php';
@session_start();
if ($_SESSION["user"] == "" or  $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
	echo "<script>window.location = 'index.php';</script>";
	exit;
}
?>
<meta charset="utf-8" />
<link rel="shortcut icon" href="image/logoETL.jpg">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="js/pro.min.js">
<link rel="stylesheet" href="css/all.css">
<link rel="stylesheet" href="css/all.min.css">
<title>ViewStock</title>


<!-- Favicon-->

<!-- Core theme CSS (includes Bootstrap)-->
<link href="css/styles.css" rel="stylesheet" />
<style type="text/css">
	body,
	td,
	th {
		font-family: "Phetsarath OT";
	}

	.navbarr {


		position: fixed;

	}

	.buttonright {
		float: right;
	}

	.cooloorr {
		background-image: linear-gradient(to top, #accbee 0%, #e7f0fd 100%);
	}
</style>
<?php
if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793" or $_SESSION["iduser"] == '41' or $_SESSION["iduser"] == '378' or $_SESSION["iduser"] == '387' or $_SESSION["iduser"] == '514' or $_SESSION["iduser"] == '423' or $_SESSION["iduser"] == '166' or $_SESSION["iduser"] == '167' or $_SESSION["iduser"] == '168' or $_SESSION["iduser"] == '624' or $_SESSION["iduser"] == '190' or $_SESSION["iduser"] == '197' or $_SESSION["iduser"] == '816' or $_SESSION["iduser"] == '835' or $_SESSION["iduser"] == '836' or $_SESSION["iduser"] == '837') {

?>
	<div class="container-fluid">

		<body onLoad="window.print()">


			<table class="table table-bordered  ">
				<thead>
					<tr class="text-primary cooloorr" style="font-size: 15px;">
						<th width="10%">Item Code</th>
						<th width="20%">Items</th>
						<!-- <th>ພາກສ່ວນ</th>-->
						<th width="10%">Vendor</th>

						<th width="20%">ສາງ ສູນກາງ</th>
						<th width="20%">ສາງ</th>
						<th width="20%">ຮູບອູປະກອນ</th>


					</tr>
				</thead>
				<tbody>
					<?php
					$input = $_POST['input'] ?? '';
					$Section = $_POST['Section'] ?? '';

					$sql = "SELECT
id,
Item_code,
Items,
Vendor,
Use_For,
Type,
Unit,
Stock_TMD,
Groupp,
Section,
picture,
User_Stock
FROM stock";
					if ($input <> '') {
						$sql = "SELECT
id,
Item_code,
Items,
Vendor,
Use_For,
Type,
Unit,
Stock_TMD,
Groupp,
Section,
picture,
User_Stock
FROM stock
WHERE stock.Items LIKE '$input%'";
					} elseif ($input <> '' and $Section <> '') {
						$sql = "SELECT
id,
Item_code,
Items,
Vendor,
Use_For,
Type,
Unit,
Stock_TMD,
Groupp,
Section,
picture,
User_Stock
FROM stock
WHERE stock.Items LIKE '$input%' and stock.Section LIKE '$Section'  ";
					} elseif ($Section <> '') {
						$sql = "SELECT
id,
Item_code,
Items,
Vendor,
Use_For,
Type,
Unit,
Stock_TMD,
Groupp,
Section,
picture,
User_Stock
FROM stock
WHERE stock.Section LIKE '$Section'  ";
					}


					mysqli_set_charset(@$conn, "utf8");
					$result = $conn->query($sql);
					while ($row = $result->fetch_assoc()) {

						$Itemms = $row['Items'];

						if ($row['Unit'] <= '5' and   $row['Stock_TMD'] <= '5') {

							$bgcolor = "#F0C0C1";
							$cl = "red";
						} else {
							$bgcolor = "#FFFFFF";
							$cl = "black";
						}

					?>
						<tr bgcolor="<?php echo $bgcolor ?>" style="font-size: 13px;">
							<td width="10%"><strong><?php echo $row['Item_code'] ?></strong></td>
							<td width="20%">
								<p style="font-size: 15px;"><a style="color: blue;" href='EditItemstock.php?Itemms=<?php echo $Itemms ?>'>
										<strong><?php echo $row['Items'] ?></a>
								<p><i class="fa-solid fa-network-wired"></i> ອຸປະກອນ: <?php echo $row['Use_For'] ?><br><i class="fa-duotone fa-layer-group"></i> ໃຊ້ກັບກຸ່ມ: <?php echo $row['Groupp'] ?></strong>
							</td>
							<!--<td><?php echo $row['Section'] ?></td>-->
							<td width="10%"><strong><?php echo $row['Vendor'] ?></strong></td>



							<td width="20%" align="center" style="color: <?php echo $cl ?>"><strong><?php echo $row['Unit'] ?> <?php echo $row['Type'] ?></strong></td>
							<td width="20%" align="center" style="color: <?php echo $cl ?>"><strong><?php echo $row['Stock_TMD'] ?> <?php echo $row['Type'] ?></strong></td>


							<td width="20%"><a href="<?php echo $row['picture'] ?>" target="_blank"><img src="<?php echo $row['picture'] ?>" style="width:128px;height:100px;"></a></td>

						</tr>
				</tbody>
			<?php } ?>

			</table>
	</div>
	</div>
<?php } else {

?>
	<div class="container-fluid">





		<body onLoad="window.print()">


			<table class="table table-bordered  ">
				<thead>
					<tr class="text-primary cooloorr" style="font-size: 15px;">
						<th width="10%">Item Code</th>
						<th width="40%">Items</th>
						<!-- <th>ພາກສ່ວນ</th>-->
						<th width="10%">Vendor</th>

						<th width="20%">ຈຳນວນ</th>

						<th width="20%">ຮູບອູປະກອນ</th>


					</tr>
				</thead>
				<tbody>
					<?php
					$input = $_POST['input'] ?? '';
					$Section = $_POST['Section'] ?? '';
					$provinceee = $_SESSION["Namepro"];

					$sql = "SELECT
id,
Item_code,
Items,
Vendor,
Use_For,
Type,
Unit,
Groupp,
Section,
picture,
User_Stock
FROM stock_province
WHERE stock_province.Provinces like '$provinceee'";
					if ($input <> '') {
						$sql = "SELECT
id,
Item_code,
Items,
Vendor,
Use_For,
Type,
Unit,
Groupp,
Section,
picture,
User_Stock
FROM stock_province
WHERE stock_province.Items LIKE '$input%' and stock_province.Provinces like '$provinceee'";
					} elseif ($input <> '' and $Section <> '') {
						$sql = "SELECT
id,
Item_code,
Items,
Vendor,
Use_For,
Type,
Unit,
Groupp,
Section,
picture,
User_Stock
FROM stock_province
WHERE stock_province.Items LIKE '$input%' and stock.Section LIKE '$Section' and stock_province.Provinces like '$provinceee'  ";
					} elseif ($Section <> '') {
						$sql = "SELECT
id,
Item_code,
Items,
Vendor,
Use_For,
Type,
Unit,
Groupp,
Section,
picture,
User_Stock
FROM stock_province
WHERE stock_province.Section LIKE '$Section' and stock_province.Provinces like '$provinceee'  ";
					}


					mysqli_set_charset(@$conn, "utf8");
					$result = $conn->query($sql);
					while ($row = $result->fetch_assoc()) {

						if ($row['Unit'] <= '5') {

							$bgcolor = "#F0C0C1";
							$cl = "red";
						} else {
							$bgcolor = "#FFFFFF";
							$cl = "black";
						}

					?>
						<tr bgcolor="<?php echo $bgcolor ?>" style="font-size: 13px;">
							<td width="10%"><strong><?php echo $row['Item_code'] ?></strong></td>
							<td width="40%">
								<strong>
									<p style="font-size: 15px;"><?php echo $row['Items'] ?></p><i class="fa-solid fa-network-wired"></i> ອຸປະກອນ: <?php echo $row['Use_For'] ?><br><i class="fa-duotone fa-layer-group"></i> ໃຊ້ກັບກຸ່ມ: <?php echo $row['Groupp'] ?>
								</strong>
							</td>
							<!--<td><?php echo $row['Section'] ?></td>-->
							<td width="10%"><strong><?php echo $row['Vendor'] ?></strong></td>



							<td width="20%" align="center" style="color: <?php echo $cl ?>"><strong><?php echo $row['Unit'] ?> <?php echo $row['Type'] ?></strong></td>



							<td width="20%"><a href="<?php echo $row['picture'] ?>" target="_blank"><img src="<?php echo $row['picture'] ?>" style="width:128px;height:100px;"></a></td>

						</tr>
				</tbody>
			<?php } ?>
			</table>
	</div>
	</div>
<?php } ?>


</div>
</div>


</div>
</div>