<?php
require_once __DIR__ . '/includes/conn.php';
@session_start();
if ($_SESSION["user"] == "" or  $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
	echo "<script>window.location = 'index.php';</script>";
	exit;
}
// ອັບເດດ: ເພີ່ມສະແດງ "ເລກທີ່ແຜນ" (request.planing_number) ຄ້າງຢູ່ຂ້າງ PR ເລກທີ
//         ໃນລາຍການລໍຖ້າ admin ອະນຸມັດ/ເບິກ (ທັງ 2 ບລັອກ: admin ທົ່ວໄປ ແລະ iduser 204)
// ອັບເດດ (ຮອບນີ້): ປ່ຽນຈາກ "1 form ຕໍ່ 1 ແຖວ" ເປັນ "1 form ຫຸ້ມທັງຕາລາງ" + checkbox
//         ເພື່ອໃຫ້ admin ເລືອກຫຼາຍລາຍການແລ້ວກົດເບິກພ້ອມກັນໄດ້ໃນຄັ້ງດຽວ.
//         Modal ຍົກເລິກຖືກແຍກອອກໄປ echo ຫຼັງປິດ form ຫຼັກ ເພື່ອບໍ່ໃຫ້ເກີດ <form> ຊ້ອນ <form>
//         (nested form ຜິດກົດ HTML ແລະ browser ອາດປິດ form ນອກກ່ອນເວລາ)
// ອັບເດດ (ຮອບນີ້ 2): ແກ້ JOIN ກັບ stock / stock_province ໃຫ້ໃຊ້ Item_code ແທນ Items
//         ເພື່ອປ້ອງກັນຈຳນວນແຖວບວມ (row duplication) ເມື່ອຊື່ Items ຊ້ຳກັນລະຫວ່າງ
//         ເຄື່ອງຄົນລະລາຍການ (ຄົນລະ Item_code / Size / Use_For)
?>
<?php
// ຮັບຄ່າພາສາຈາກ URL parameter
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'zh';

/* ==========================================================================
   Filter ຕາມແຂວງ (Province) - ເຊັ່ນ Sanakham, Luangphabang, ...
   ========================================================================== */
function fetchPendingProvinces($conn)
{
	$out = [];
	$sql = "SELECT DISTINCT Province FROM request
	        WHERE S_Status LIKE '1' AND Province IS NOT NULL AND Province <> ''
	        ORDER BY Province";
	if ($res = $conn->query($sql)) {
		while ($r = $res->fetch_row()) {
			$out[] = $r[0];
		}
	}
	return $out;
}

$provinceList = fetchPendingProvinces($conn);
$selectedProvince = isset($_GET['province']) ? trim($_GET['province']) : '';

// ອະນຸຍາດແຕ່ຄ່າທີ່ຢູ່ໃນລາຍຊື່ແຂວງຈິງເທົ່ານັ້ນ (ກັນຄ່າແປກປອມຈາກ URL)
if ($selectedProvince !== '' && !in_array($selectedProvince, $provinceList, true)) {
	$selectedProvince = '';
}

$allowedAdminIds = ['30', '2', '194', '793', '213', '214', '204'];
$showProvinceFilter = in_array((string)$_SESSION["iduser"], $allowedAdminIds, true);
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<link rel="shortcut icon" href="image/logoETL.jpg">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Bootstrap 5 -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

	<!-- ===== Font Awesome 6 ===== -->
    	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

	<!-- icons ເພີ່ມເຕີມ -->
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-solid-rounded/css/uicons-solid-rounded.css">
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-straight/css/uicons-regular-straight.css">

	<script src="js/pro.min.js" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="css/all.css">
	<link rel="stylesheet" href="css/all.min.css">

	<title>Stock</title>

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

		.cll {
			background-image: linear-gradient(to top, #4481eb 0%, #04befe 100%);
			--bs-table-bg: transparent;
			--bs-table-color: mediumblue;
		}

		.cll th {
			background-color: transparent !important;
			color: mediumblue !important;
		}

		/* ===================== Province Filter (Bootstrap 5) ===================== */
		.province-filter-card {
			border: 1px solid #e3e6ea;
			border-radius: 12px;
			box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
			transition: box-shadow .2s ease;
		}

		.province-filter-card:hover {
			box-shadow: 0 6px 20px rgba(0, 0, 0, 0.09);
		}

		.province-filter-card .form-select {
			border-color: #d0d5dd;
			background-color: #f9fafb;
		}

		.province-filter-card .form-select:hover {
			border-color: #4481eb;
		}

		.province-filter-card .form-select:focus {
			border-color: #4481eb;
			box-shadow: 0 0 0 0.2rem rgba(68, 129, 235, 0.15);
			background-color: #fff;
		}

		.province-filter-label {
			font-size: 14px;
			font-weight: 600;
			color: #344054;
			letter-spacing: .3px;
		}

		.province-filter-label i {
			color: #4481eb;
		}

		.province-active-badge {
			font-size: 12px;
			font-weight: 500;
			background-color: #eef4ff;
			color: #4481eb;
			padding: 6px 12px;
		}

		.province-active-badge a {
			color: #98a2b3;
			text-decoration: none;
		}

		.province-active-badge a:hover {
			color: #d92d20;
		}

		/* ===================== Bulk selection bar ===================== */
		.bulk-action-bar {
			position: sticky;
			top: 0;
			z-index: 5;
			max-width: 1300px;
			margin: 0 auto 10px auto;
			background: #fff;
			border: 1px solid #e3e6ea;
			border-radius: 12px;
			box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
			padding: 10px 16px;
		}

		.bulk-action-bar label {
			font-weight: 600;
			color: #344054;
		}

		.rowCheck {
			width: 18px;
			height: 18px;
		}

		.pending-count-badge {
			font-size: 13px;
			font-weight: 600;
			background-color: #e6f7ff;
			color: #0d8ecf;
			padding: 6px 12px;
		}
	</style>
</head>

<body>

	<?php if ($showProvinceFilter) { ?>
		<div class="container-fluid px-0 mt-3 mb-4">
			<form method="get" id="provinceFilterForm" class="province-filter-card p-3 bg-white">
				<input type="hidden" name="lang" id="filterLangInput" value="<?php echo htmlspecialchars($lang) ?>">
				<div class="d-flex flex-wrap align-items-center gap-3">
					<label for="provinceSelect" class="col-form-label province-filter-label mb-0 d-flex align-items-center gap-2">
						<i class="fa-solid fa-filter"></i>
						<span data-translate="filterByWarehouse">Filter ຕາມສາງ/ແຂວງ</span>
					</label>

					<div class="flex-grow-1" style="min-width:240px;">
						<select name="province" id="provinceSelect" class="form-select" onchange="submitProvinceFilter()">
							<option value="" data-translate="allProvinces">-- ທັງໝົດ (ທຸກແຂວງ) --</option>
							<?php foreach ($provinceList as $p): ?>
								<option value="<?php echo htmlspecialchars($p) ?>" <?php echo ($selectedProvince === $p) ? 'selected' : '' ?>>
									<?php echo htmlspecialchars($p) ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<?php if ($selectedProvince !== ''): ?>
						<span class="badge rounded-pill province-active-badge d-flex align-items-center gap-1">
							<i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($selectedProvince) ?>
							<a href="?lang=<?php echo htmlspecialchars($lang) ?>" id="clearProvinceFilter" class="ms-1" title="ລ້າງ filter">
								<i class="fa-solid fa-xmark"></i>
							</a>
						</span>
					<?php endif; ?>
				</div>
			</form>
		</div>
		<script>
			(function() {
				var urlParams = new URLSearchParams(window.location.search);
				var currentLang = urlParams.get('lang') || localStorage.getItem('site_lang') || 'zh';

				var langInput = document.getElementById('filterLangInput');
				if (langInput) langInput.value = currentLang;

				var clearLink = document.getElementById('clearProvinceFilter');
				if (clearLink) clearLink.href = '?lang=' + encodeURIComponent(currentLang);
			})();

			function submitProvinceFilter() {
				var langInput = document.getElementById('filterLangInput');
				if (langInput) {
					var urlParams = new URLSearchParams(window.location.search);
					langInput.value = urlParams.get('lang') || localStorage.getItem('site_lang') || 'zh';
				}
				document.getElementById('provinceFilterForm').submit();
			}
		</script>
	<?php } ?>

	<?php
	if ($_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "2" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793" or $_SESSION["iduser"] == "213" or $_SESSION["iduser"] == "214") {

		$sql = "SELECT
a.id,
a.Items,
a.OA_Out,
a.planing_number,
a.Province,
a.Item_code,
a.Use_For,
a.Section,
c.Stock_TMD,
a.Vendor,
a.Groupp,
a.picture,
a.Unit,
a.Type,
date(a.dateRe) as 'dateRe',
a.User_Re,
b.Unit 'Unitpro',
c.Unit 'Unitcenter'

FROM request a
LEFT JOIN stock_province b ON a.Item_code=b.Item_code and a.Province=b.Provinces
LEFT JOIN stock c ON a.Item_code=c.Item_code
WHERE a.S_Status LIKE '1'" . ($selectedProvince !== '' ? " AND a.Province = ?" : "") . "
ORDER BY a.dateRe asc";

		mysqli_set_charset(@$conn, "utf8");

		$stmt = $conn->prepare($sql);
		if ($stmt === false) {
			die("Error preparing query: " . $conn->error);
		}
		if ($selectedProvince !== '') {
			$stmt->bind_param("s", $selectedProvince);
		}
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows > 0) {
			$modalsHtml = ''; // buffer: cancel modals ຖືກເກັບໄວ້ ແລ້ວ echo ຫຼັງປິດ form ຫຼັກ
			$totalPending = $result->num_rows; // ຈຳນວນລາຍການທີ່ຄ້າງທັງໝົດ (ຕາມ filter ແຂວງທີ່ເລືອກຢູ່)
		?>
			<div align="center">

				<div class="bulk-action-bar d-flex flex-wrap align-items-center justify-content-between gap-2">
					<div class="d-flex align-items-center gap-2">
						<input type="checkbox" id="checkAll" class="rowCheck" onclick="toggleAll(this)">
						<label for="checkAll" data-translate="selectAll">ເລືອກທັງໝົດ</label>
						<span class="badge rounded-pill pending-count-badge d-flex align-items-center gap-1">
							<i class="fa-regular fa-clock"></i> ຄ້າງ <?php echo $totalPending ?> ລາຍການ
						</span>
					</div>
					<button form="bulkForm" type="submit" class="btn btn-success d-flex align-items-center gap-2" onclick="return confirmBulk()">
						<i class="fi fi-rr-hand-holding-box"></i>
						<span data-translate="withdrawSelected">ເບິກລາຍການທັງໝົດ</span>
					</button>
				</div>

				<form action="saveStock.php" id="bulkForm" name="bulkForm" method="post">
					<table class="table circle table-bordered">
						<thead>
							<tr class="cll" align="center" style="font-size: 15px;color:mediumblue">
								<th width="3%"></th>
								<th width="10%" data-translate="warehouse">ສາງ</th>
								<th width="10%" data-translate="equipment">ອຸປະກອນ</th>
								<th width="8%" data-translate="requestQty">ຈຳນວນທີ່ຂໍ້ມາ</th>
								<th width="12%" data-translate="stockQty">ຈຳນວນໃນສາງ Stock</th>
								<th width="15%" data-translate="centerWarehouse">ສາງອຸປະກອນພະແນກສາງສຳນັກງານໃຫຍ່</th>
								<th width="12%" data-translate="officeWarehouse">ສາງເຄື່ອງໃຊ້ຫ້ອງການສຳນັກງານໃຫຍ່</th>
								<th width="5%" data-translate="withdrawQty">ຈຳນວນເບິກ</th>
								<th width="15%"><i class="fa-light fa-warehouse fa-lg"></i></th>
							</tr>
						</thead>
						<tbody>
							<?php
							while ($row = $result->fetch_assoc()) {
								$id = $row['id'];
							?>
								<tr style="font-size: 13px;">
									<td align="center">
										<input type="checkbox" name="selected[]" value="<?php echo $id ?>" class="rowCheck">
									</td>
									<td width="18%">
										<i class="fa-regular fa-location-dot"></i> <?php echo $row['Province'] ?><br>
										<i class="fa-light fa-memo-circle-info fa-lg"></i> <strong data-translate="prNumber">PR ເລກທີ:</strong> <?php echo $row['OA_Out'] ?><br>
										<i class="fa-solid fa-diagram-project fa-lg"></i> <strong data-translate="planNumber">ແຜນເລກທີ່:</strong> <?php echo $row['planing_number'] ?><br>
										<i class="fa-regular fa-user-tie-hair fa-lg"></i> <?php echo $row['User_Re'] ?><br>
										<i class="fa-duotone fa-calendar-days"></i> <?php echo $row['dateRe'] ?>
									</td>
									<td width="30%">
										<i class="fa-light fa-forklift fa-lg"></i> <?php echo $row['Items'] ?><br>
										<i class="fa-solid fa-globe fa-lg"></i> <strong data-translate="vendor">ຂໍ້ມູນຮ້ານທີ່ຈັດຊື້:</strong> <?php echo $row['Vendor'] ?><br>
										<i class="fa-light fa-network-wired fa-lg"></i> <strong data-translate="section">ພາກສ່ວນ:</strong> <?php echo $row['Section'] ?>
									</td>
									<td width="5%" class="text-danger" align="center"><strong><?php echo $row['Unit'] ?><br><?php echo $row['Type'] ?></strong></td>
									<td width="8%" align="center"><strong><?php echo $row['Unitpro'] ?><br><?php echo $row['Type'] ?></strong></td>
									<td width="9%" class="text-primary" align="center"><strong><?php echo $row['Unitcenter'] ?><br><?php echo $row['Type'] ?></strong></td>
									<td width="8%" class="text-primary" align="center"><strong><?php echo $row['Stock_TMD'] ?><br><?php echo $row['Type'] ?></strong></td>

									<!-- ຄ່າຕໍ່ແຖວ ຖືກເກັບເປັນ array key=id ເພື່ອໃຫ້ saveStock.php ຮູ້ວ່າແມ່ນຄ່າຂອງລາຍການໃດ -->
									<input type="hidden" name="Province[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Province']) ?>" />
									<input type="hidden" name="Section[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Section']) ?>" />
									<input type="hidden" name="Vendor[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Vendor']) ?>" />
									<input type="hidden" name="Item_code[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Item_code']) ?>" />
									<input type="hidden" name="Use_For[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Use_For']) ?>" />
									<input type="hidden" name="Type[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Type']) ?>" />
									<input type="hidden" name="Groupp[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Groupp']) ?>" />
									<input type="hidden" name="picture[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['picture']) ?>" />
									<input type="hidden" name="Unitcenter[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Unitcenter']) ?>" />
									<input type="hidden" name="Stock_TMD[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Stock_TMD']) ?>" />
									<input type="hidden" name="Unit[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Unit']) ?>" />
									<input type="hidden" name="Items[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Items']) ?>" />
									<input type="hidden" name="OAtoPro[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['OA_Out']) ?>" />

									<td width="5%" align="center">
										<input type="text" name="give[<?php echo $id ?>]" class="form-control give-input" style="color:blue; width:80px" placeholder="0" />
										<br><?php echo $row['Type'] ?>
									</td>
									<td width="15%" align="center">
										<div class="d-flex flex-column align-items-center gap-2">
											<select class="form-select form-select-sm" style="max-width: 170px;" name="Stockcut[<?php echo $id ?>]">
												<option value="Unit" data-translate="warehouse_qty">ສາງອຸປະກອນພະແນກສາງສຳນັກງານໃຫຍ່</option>
												<option value="Stock_TMD" data-translate="office_supply_qty">ສາງເຄື່ອງໃຊ້ຫ້ອງການສຳນັກງານໃຫຍ່</option>
											</select>
											<div class="d-flex gap-2">
												<!-- ເບິກສະເພາະລາຍການນີ້ (1 ລາຍການ) ໂດຍບໍ່ຕ້ອງ check box ກ່ອນ:
												     submitSingle() ຈະເລືອກສະເພາະ id ນີ້ ແລ້ວ submit form ຫຼັກໃຫ້ເອງ -->
												<button type="button" id="Submit" class="btn btn-success btn-sm d-flex align-items-center justify-content-center gap-1" style="font-size: 13px;width: 90px;white-space:nowrap;" onclick="return submitSingle(<?php echo $id ?>, 'bulkForm')"><i class="fi fi-rr-hand-holding-box"></i><span data-translate="withdraw">ເບິກ</span></button>
												<a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#myModal<?php echo $id ?>">
													<button type="button" class="btn btn-danger btn-sm d-flex align-items-center justify-content-center gap-1" style="font-size: 13px;width: 90px;white-space:nowrap;"><i class="fa-solid fa-ban"></i><span data-translate="cancel">ຍົກເລິກ</span></button>
												</a>
											</div>
										</div>
									</td>
								</tr>
							<?php
								// ---- Modal ຍົກເລິກ ຖືກເກັບໄວ້ໃນ buffer ແລ້ວ echo ຫຼັງ </form> ຫຼັກ ----
								// (ບໍ່ອະນຸຍາດໃຫ້ <form> ຢູ່ໃນ <form> ອື່ນຕາມມາດຕະຖານ HTML,
								//  ຖ້າ modal ຢູ່ໃນນີ້ຈະເຮັດໃຫ້ browser ປິດ bulkForm ກ່ອນເວລາ)
								ob_start();
							?>
								<div class="modal fade" id="myModal<?php echo $id ?>" tabindex="-1" role="dialog">
									<div class="modal-dialog modal-lg">
										<div class="modal-content">
											<div class="modal-header">
												<h4 class="modal-title text-primary"><span data-translate="cancel">ການຍົກເລິກ</span></h4>
												<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
											</div>
											<div class="modal-body">
												<div id="collapseOne<?php echo $id ?>" class="collapse show">
													<div class="card-body">
														<form id="form1232-<?php echo $id ?>" name="form1232" method="post" action="DataReAdmin.php">
															<input type="hidden" name="id" value="<?php echo $id ?>" />
															<label><strong><u>
																		<h3 data-translate="cancelReason">ສາເຫດການຍົກເລິກ</h3>
																	</u> <strong style="color: blue"><?php echo $row['Items'] ?></strong></strong></label>
															<textarea style="height: 100px;" type="text" name="Issue" id="Issue" class="form-control" required></textarea><br>
															<button type="submit" name="buttonCancel" class="btn btn-warning text-danger"><i class="fa-solid fa-ban"></i> <strong data-translate="cancelRequest"> ຍົກເລິກການຂໍອຸປະກອນ</strong></button>
														</form>
													</div>
												</div>
											</div>
											<div class="modal-footer">
												<button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Close</button>
											</div>
										</div>
									</div>
								</div>
							<?php
								$modalsHtml .= ob_get_clean();
							} // end while
							?>
						</tbody>
					</table>

					<div class="bulk-action-bar d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2">
						<div class="d-flex align-items-center gap-2">
							<input type="checkbox" class="rowCheck" onclick="toggleAll(this)">
							<label data-translate="selectAll">ເລືອກທັງໝົດ</label>
							<span class="badge rounded-pill pending-count-badge d-flex align-items-center gap-1">
								<i class="fa-regular fa-clock"></i> ຄ້າງ <?php echo $totalPending ?> ລາຍການ
							</span>
						</div>
						<button type="submit" class="btn btn-success d-flex align-items-center gap-2" onclick="return confirmBulk()">
							<i class="fi fi-rr-hand-holding-box"></i>
							<span data-translate="withdrawSelected">ເບິກລາຍການທັງໝົດ</span>
						</button>
					</div>
				</form>
			</div>
			<?php echo $modalsHtml; ?>
	<?php
		} else {
			echo "<center><font color='#00BFFF'><font size='24'>ບໍ່ມີລາຍການເບິກອຸປະກອນ</center>";
		}
	}
	?>

	<?php
	// ສ່ວນຂອງ iduser == "204"
	if ($_SESSION["iduser"] == "204") {
		$sql = "SELECT
	a.id,
	a.Items,
	a.OA_Out,
	a.planing_number,
	a.Province,
	a.Item_code,
	a.Use_For,
	a.Section,
	c.Stock_TMD,
	a.Vendor,
	a.Groupp,
	a.picture,
	a.Unit,
	a.Type,
	date(a.dateRe) as 'dateRe',
	a.User_Re,
	b.Unit 'Unitpro',
	c.Unit 'Unitcenter'
	
	FROM request a
	LEFT JOIN stock_province b ON a.Item_code=b.Item_code and a.Province=b.Provinces
	LEFT JOIN stock c ON a.Item_code=c.Item_code
	WHERE a.S_Status LIKE '1' and a.Section = 'CANTEEN'" . ($selectedProvince !== '' ? " AND a.Province = ?" : "") . "
	ORDER BY a.dateRe asc";

		mysqli_set_charset(@$conn, "utf8");

		$stmt = $conn->prepare($sql);
		if ($stmt === false) {
			die("Error preparing query: " . $conn->error);
		}
		if ($selectedProvince !== '') {
			$stmt->bind_param("s", $selectedProvince);
		}
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows > 0) {
			$modalsHtml204 = '';
			$totalPending = $result->num_rows; // ຈຳນວນລາຍການທີ່ຄ້າງທັງໝົດ (ຕາມ filter ແຂວງທີ່ເລືອກຢູ່)
		?>
			<div align="center">

				<div class="bulk-action-bar d-flex flex-wrap align-items-center justify-content-between gap-2">
					<div class="d-flex align-items-center gap-2">
						<input type="checkbox" id="checkAll204" class="rowCheck" onclick="toggleAll(this, 'rowCheck204')">
						<label for="checkAll204">ເລືອກທັງໝົດ</label>
						<span class="badge rounded-pill pending-count-badge d-flex align-items-center gap-1">
							<i class="fa-regular fa-clock"></i> ຄ້າງ <?php echo $totalPending ?> ລາຍການ
						</span>
					</div>
					<button form="bulkForm204" type="submit" class="btn btn-success d-flex align-items-center gap-2" onclick="return confirmBulk('rowCheck204')">
						<i class="fa-solid fa-box-open"></i>
						<span>ເບິກລາຍການທີ່ເລືອກ</span>
					</button>
				</div>

				<form action="saveStock.php" id="bulkForm204" name="bulkForm204" method="post">
					<table class="table circle table-bordered">
						<thead>
							<tr class="cll" align="center" style="font-size: 15px;color:mediumblue">
								<th width="3%"></th>
								<th width="15%" data-translate="warehouse">ສາງ</th>
								<th width="27%" data-translate="equipment">ອຸປະກອນ</th>
								<th width="5%" data-translate="requestQty">ຈຳນວນທີ່ຂໍ້ມາ</th>
								<th width="8%" data-translate="stockQty">ຈຳນວນໃນສາງ Stock</th>
								<th width="9%" data-translate="centerWarehouse">ສາງອຸປະກອນພະແນກສາງສຳນັກງານໃຫຍ່</th>
								<th width="8%" data-translate="officeWarehouse">ສາງເຄື່ອງໃຊ້ຫ້ອງການສຳນັກງານໃຫຍ່</th>
								<th width="5%" data-translate="withdrawQty">ຈຳນວນເບິກ</th>
								<th width="15%"><i class="fa-light fa-warehouse fa-lg"></i></th>
							</tr>
						</thead>
						<tbody>
							<?php
							while ($row = $result->fetch_assoc()) {
								$id = $row['id'];
							?>
								<tr style="font-size: 13px;">
									<td align="center">
										<input type="checkbox" name="selected[]" value="<?php echo $id ?>" class="rowCheck204">
									</td>
									<td width="18%">
										<i class="fa-regular fa-location-dot"></i> <?php echo $row['Province'] ?><br>
										<i class="fa-light fa-memo-circle-info fa-lg"></i> <strong data-translate="prNumber">PR ເລກທີ:</strong> <?php echo $row['OA_Out'] ?><br>
										<i class="fa-solid fa-diagram-project fa-lg"></i> <strong data-translate="planNumber">ເລກທີ່ແຜນ:</strong> <?php echo $row['planing_number'] ?><br>
										<i class="fa-regular fa-user-tie-hair fa-lg"></i> <?php echo $row['User_Re'] ?><br>
										<i class="fa-duotone fa-calendar-days"></i> <?php echo $row['dateRe'] ?>
									</td>
									<td width="30%">
										<i class="fa-light fa-forklift fa-lg"></i> <?php echo $row['Items'] ?><br>
										<i class="fa-solid fa-globe fa-lg"></i> <strong data-translate="vendor">ຂໍ້ມູນຮ້ານທີ່ຈັດຊື້:</strong> <?php echo $row['Vendor'] ?><br>
										<i class="fa-light fa-network-wired fa-lg"></i> <strong data-translate="section">ພາກສ່ວນ:</strong> <?php echo $row['Section'] ?>
									</td>
									<td width="5%" class="text-danger" align="center"><strong><?php echo $row['Unit'] ?><br><?php echo $row['Type'] ?></strong></td>
									<td width="8%" align="center"><strong><?php echo $row['Unitpro'] ?><br><?php echo $row['Type'] ?></strong></td>
									<td width="9%" class="text-primary" align="center"><strong><?php echo $row['Unitcenter'] ?><br><?php echo $row['Type'] ?></strong></td>
									<td width="8%" class="text-primary" align="center"><strong><?php echo $row['Stock_TMD'] ?><br><?php echo $row['Type'] ?></strong></td>

									<input type="hidden" name="Province[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Province']) ?>" />
									<input type="hidden" name="Section[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Section']) ?>" />
									<input type="hidden" name="Vendor[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Vendor']) ?>" />
									<input type="hidden" name="Item_code[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Item_code']) ?>" />
									<input type="hidden" name="Use_For[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Use_For']) ?>" />
									<input type="hidden" name="Type[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Type']) ?>" />
									<input type="hidden" name="Groupp[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Groupp']) ?>" />
									<input type="hidden" name="picture[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['picture']) ?>" />
									<input type="hidden" name="Unitcenter[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Unitcenter']) ?>" />
									<input type="hidden" name="Stock_TMD[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Stock_TMD']) ?>" />
									<input type="hidden" name="Unit[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Unit']) ?>" />
									<input type="hidden" name="Items[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['Items']) ?>" />
									<input type="hidden" name="OAtoPro[<?php echo $id ?>]" value="<?php echo htmlspecialchars($row['OA_Out']) ?>" />

									<td width="5%" align="center">
										<input type="text" name="give[<?php echo $id ?>]" class="form-control give-input" style="color:blue; width:80px" placeholder="0" />
										<br><?php echo $row['Type'] ?>
									</td>
									<td width="15%" align="center">
										<div class="d-flex flex-column align-items-center gap-2">
											<select class="form-select form-select-sm" style="max-width: 170px;" name="Stockcut[<?php echo $id ?>]">
												<option value="Unit" data-translate="warehouse_qty">ສາງອຸປະກອນພະແນກສາງສຳນັກງານໃຫຍ່</option>
												<option value="Stock_TMD" data-translate="office_supply_qty">ສາງເຄື່ອງໃຊ້ຫ້ອງການສຳນັກງານໃຫຍ່</option>
											</select>
											<div class="d-flex gap-2">
												<button type="button" id="Submit" class="btn btn-success btn-sm d-flex align-items-center justify-content-center gap-1" style="font-size: 13px;width: 90px;white-space:nowrap;" onclick="return submitSingle(<?php echo $id ?>, 'bulkForm204')"><i class="fa-solid fa-box-open"></i>ເບິກ</button>
												<a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#myModal<?php echo $id ?>">
													<button type="button" class="btn btn-danger btn-sm d-flex align-items-center justify-content-center gap-1" style="font-size: 13px;width: 90px;white-space:nowrap;"><i class="fa-solid fa-ban"></i>ຍົກເລິກ</button>
												</a>
											</div>
										</div>
									</td>
								</tr>
							<?php
								ob_start();
							?>
								<div class="modal fade" id="myModal<?php echo $id ?>" tabindex="-1" role="dialog">
									<div class="modal-dialog modal-lg">
										<div class="modal-content">
											<div class="modal-header">
												<h4 class="modal-title text-primary"><i class="fa-solid fa-hexagon-exclamation text-warning"></i> ການຍົກເລິກ</h4>
												<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
											</div>
											<div class="modal-body">
												<div id="collapseOne<?php echo $id ?>" class="collapse show">
													<div class="card-body">
														<form id="form1232-<?php echo $id ?>" name="form1232" method="post" action="DataReAdmin.php">
															<input type="hidden" name="id" value="<?php echo $id ?>" />
															<label><strong><u>
																		<h3>ສາເຫດການຍົກເລິກ</h3>
																	</u> <strong style="color: blue"><?php echo $row['Items'] ?></strong></strong></label>
															<textarea style="height: 100px;" type="text" name="Issue" id="Issue" class="form-control" required></textarea><br>
															<button type="submit" name="buttonCancel" class="btn btn-warning text-danger"><strong>ຍົກເລິກການຂໍອຸປະກອນ</strong></button>
														</form>
													</div>
												</div>
											</div>
											<div class="modal-footer">
												<button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
											</div>
										</div>
									</div>
								</div>
							<?php
								$modalsHtml204 .= ob_get_clean();
							} // end while
							?>
						</tbody>
					</table>

					<div class="bulk-action-bar d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2">
						<div class="d-flex align-items-center gap-2">
							<input type="checkbox" class="rowCheck" onclick="toggleAll(this, 'rowCheck204')">
							<label>ເລືອກທັງໝົດ</label>
							<span class="badge rounded-pill pending-count-badge d-flex align-items-center gap-1">
								<i class="fa-regular fa-clock"></i> ຄ້າງ <?php echo $totalPending ?> ລາຍການ
							</span>
						</div>
						<button type="submit" class="btn btn-success d-flex align-items-center gap-2" onclick="return confirmBulk('rowCheck204')">
							<i class="fa-solid fa-box-open"></i>
							<span>ເບິກລາຍການທັງໝົດ</span>
						</button>
					</div>
				</form>
			</div>
			<?php echo $modalsHtml204; ?>
	<?php
		} else {
			echo "<center><font color='#00BFFF'><font size='24'>ບໍ່ມີລາຍການເບິກອຸປະກອນ</center>";
		}
	}
	?>

	<?php
	// ປະມວນຜົນການຍົກເລິກ
	if (isset($_POST["buttonCancel"])) {
		$date_time = date('20y-m-d G:i');

		$id = $_POST['id'];
		$Issue = $_POST['Issue'];

		// ແກ້ໄຂ: ໃຊ້ prepared statement ແທນການຕໍ່ string ໂດຍກົງ (ປ້ອງກັນ SQL injection)
		$stmtCancel = $conn->prepare(
			"UPDATE request
			 SET Remark = ?, User_Confirm = ?, S_Status = '3', DateComfirm = ?
			 WHERE id = ?"
		);
		if ($stmtCancel !== false) {
			$stmtCancel->bind_param("sssi", $Issue, $_SESSION["user"], $date_time, $id);
			$stmtCancel->execute();
			$stmtCancel->close();
		}
	?>
		<script>
			window.location = "DataReAdmin.php";
		</script>
	<?php
	}
	?>

	<!-- ໂຫລດ JavaScript ສຳລັບແປພາສາ -->
	<script src="translate/lang.js"></script>

	<script>
		(function() {
		if (typeof translations === 'undefined' || typeof setLanguage !== 'function') {
			console.error(' lang.js not loaded!');
			return;
		}

		function applyLang() {
			var urlParams = new URLSearchParams(window.location.search);
			var lang = urlParams.get('lang') || localStorage.getItem('site_lang') || 'zh';
			setLanguage(lang);
		}

		window.addEventListener('message', function(e) {
			if (e.data.type === 'CHANGE_LANGUAGE') {
				setLanguage(e.data.lang);
			}
		});

		applyLang();
		setTimeout(applyLang, 300);
		setTimeout(applyLang, 1000);
		})();
	</script>

	<!-- ===================== ການເລືອກຫຼາຍລາຍການ + ເບິກພ້ອມກັນ ===================== -->
	<script>
		// ຄລິກ "ເລືອກທັງໝົດ" -> ໝາຍ checkbox ທຸກແຖວໃນຕາຕະລາງດຽວກັນ
		function toggleAll(masterCb, checkboxClass) {
			checkboxClass = checkboxClass || 'rowCheck';
			document.querySelectorAll('.' + checkboxClass).forEach(function(el) {
				if (el !== masterCb) el.checked = masterCb.checked;
			});
		}

		// ກວດຄວາມຖືກຕ້ອງກ່ອນ submit: ຕ້ອງເລືອກຢ່າງໜ້ອຍ 1 ລາຍການ
		// ແລະທຸກລາຍການທີ່ຖືກເລືອກຕ້ອງມີຈຳນວນເບິກ > 0
		function confirmBulk(checkboxClass) {
			checkboxClass = checkboxClass || 'rowCheck';
			var checked = document.querySelectorAll('.' + checkboxClass + ':checked');

			if (checked.length === 0) {
				alert('ກະລຸນາເລືອກລາຍການທີ່ຕ້ອງການເບິກຢ່າງໜ້ອຍ 1 ລາຍການ');
				return false;
			}

			var missing = [];
			checked.forEach(function(cb) {
				var id = cb.value;
				var giveInput = document.querySelector('input[name="give[' + id + ']"]');
				if (!giveInput || giveInput.value.trim() === '' || parseFloat(giveInput.value) <= 0) {
					missing.push(id);
					if (giveInput) giveInput.classList.add('is-invalid');
				} else if (giveInput) {
					giveInput.classList.remove('is-invalid');
				}
			});

			if (missing.length > 0) {
				alert('ກະລຸນາປ້ອນຈຳນວນທີ່ຈະເບິກ (ຫຼາຍກວ່າ 0) ໃຫ້ຄົບທຸກລາຍການທີ່ເລືອກ');
				return false;
			}

			return confirm('ຕ້ອງການເບິກ ' + checked.length + ' ລາຍການ ແທ້ບໍ່?');
		}

		// ເບິກສະເພາະ 1 ລາຍການ (ປຸ່ມ "ເບິກ" ຢູ່ໃນແຖວນັ້ນໆ):
		// ບໍ່ວ່າ checkbox ຈະຖືກເລືອກໄວ້ຫຼືບໍ່ກໍຕາມ, ຟັງຊັນນີ້ຈະ
		//  1) ກວດຈຳນວນທີ່ຈະເບິກຂອງລາຍການນີ້
		//  2) ຍົກເລິກການເລືອກລາຍການອື່ນທັງໝົດໃນ form ດຽວກັນ, ແລ້ວເລືອກສະເພາະລາຍການນີ້
		//  3) submit form (ຝັ່ງ server ຈະປະມວນຜົນສະເພາະ id ທີ່ຢູ່ໃນ selected[] ເທົ່ານັ້ນ)
		function submitSingle(id, formId) {
			var giveInput = document.querySelector('#' + formId + ' input[name="give[' + id + ']"]');
			if (!giveInput || giveInput.value.trim() === '' || parseFloat(giveInput.value) <= 0) {
				alert('ກະລຸນາປ້ອນຈຳນວນທີ່ຈະເບິກໃຫ້ຖືກຕ້ອງ');
				if (giveInput) giveInput.classList.add('is-invalid');
				return false;
			}
			giveInput.classList.remove('is-invalid');

			if (!confirm('ຕ້ອງການເບິກລາຍການ ' + id + ' ແທ້ບໍ່?')) {
				return false;
			}

			document.querySelectorAll('#' + formId + ' input[name="selected[]"]').forEach(function(cb) {
				cb.checked = (String(cb.value) === String(id));
			});

			document.getElementById(formId).submit();
			return false;
		}
	</script>
</body>

</html>