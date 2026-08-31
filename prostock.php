<?php
require_once __DIR__ . '/conn.php';
@session_start();
if ($_SESSION["user"] == "") {
	echo "<script>window.location = 'index.php';</script>";
	exit;
}

// =========================================================
// ດຶງຮູບພາບຈາກ Item_code ແບບສົດໆທຸກຄັ້ງ (ບໍ່ໃຊ້ຄ່າ "picture" ທີ່ຄ້າງໃນ DB
// ເພາະຊື່ໄຟລ໌ຮູບຖືກສ້າງໃໝ່ແບບສຸ່ມ (uniqid) ທຸກຄັ້ງທີ່ອັບໂຫຼດ/ປ່ຽນຮູບຢູ່
// index.php/upload.php -> ຄ່າ "picture" ທີ່ບັນທຶກໄວ້ຄັ້ງດຽວຈະກາຍເປັນ path
// ເກົ່າທີ່ບໍ່ມີໄຟລ໌ຈິງແລ້ວ ເຮັດໃຫ້ຮູບບໍ່ຂຶ້ນ)
//
// ໝາຍເຫດ: index.php/upload.php ຕົວຈິງແລ້ວຢູ່ໃນໂຟນເດີ picture/ ບໍ່ແມ່ນ root,
// ດັ່ງນັ້ນ uploads/ ທີ່ຖືກໃຊ້ຈິງຈຶ່ງແມ່ນ "picture/uploads/" — ບໍ່ແມ່ນ "uploads/"
// ທີ່ຢູ່ level ດຽວກັນ (ຖ້າມີ, ບໍ່ກ່ຽວຂ້ອງກັນ)
// =========================================================
$uploadsBaseDir = __DIR__ . '/picture/uploads';
$uploadsBaseUrl = 'picture/uploads';

function loadImageFilemap($mapFile)
{
	if (file_exists($mapFile)) {
		$decoded = json_decode(file_get_contents($mapFile), true);
		return is_array($decoded) ? $decoded : array();
	}
	return array();
}

// map[safeFileName] = Item_code (display name) -> ສ້າງ index ຍ້ອນກັບ
// ເພື່ອຄົ້ນຫາໄວ (Item_code -> safeFileName)
$imageMap = loadImageFilemap($uploadsBaseDir . '/filemap.json');
$imageByItemCode = array();
foreach ($imageMap as $safeName => $itemCode) {
	// ຖ້າມີຫຼາຍໄຟລ໌ຕໍ່ 1 ລະຫັດ (ບໍ່ຄວນມີຫຼັງ cleanup, ແຕ່ກັນໄວ້ກ່ອນ) ໃຫ້ໃຊ້ອັນຫຼ້າສຸດ
	$path = $uploadsBaseDir . '/' . $safeName;
	if (!isset($imageByItemCode[$itemCode]) || (file_exists($path) && filemtime($path) > (isset($imageByItemCode[$itemCode]['mtime']) ? $imageByItemCode[$itemCode]['mtime'] : 0))) {
		$imageByItemCode[$itemCode] = array(
			'safeName' => $safeName,
			'mtime'    => file_exists($path) ? filemtime($path) : 0,
		);
	}
}

/**
 * ຄືນຄ່າ URL ຮູບພາບປັດຈຸບັນຂອງ Item_code ນີ້, ຫຼື null ຖ້າບໍ່ພົບ
 */
function getItemImageUrl($itemCode, $imageByItemCode, $uploadsBaseDir, $uploadsBaseUrl)
{
	$itemCode = trim((string) $itemCode);
	if ($itemCode === '' || !isset($imageByItemCode[$itemCode])) {
		return null;
	}
	$safeName = $imageByItemCode[$itemCode]['safeName'];
	$path = $uploadsBaseDir . '/' . $safeName;
	if (!file_exists($path)) {
		return null;
	}
	// ຕິດ ?v=timestamp ກັນ browser cache ຮູບເກົ່າ
	return $uploadsBaseUrl . '/' . rawurlencode($safeName) . '?v=' . filemtime($path);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

	<link rel="shortcut icon" href="image/logoETL.jpg">

	<meta charset="utf-8">
	<link rel="shortcut icon" href="image/favicons.png">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1/dist/jquery.slim.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
	<link rel="stylesheet" href="js/pro.min.js">
	<link rel="stylesheet" href="css/all.css">
	<link rel="stylesheet" href="css/all.min.css">

	<title>ສາງອຸປະກອນ</title>
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

		.buttonleft {
			float: left;
		}

		.cooloorr {
			background-image: linear-gradient(to top, #accbee 0%, #e7f0fd 100%);
		}

		.right {
			float: right;
		}

		.table thead th {
			position: sticky;
			top: 0;
			background-color: #e7f0fd;
			z-index: 10;
			color: #0d6efd !important;
		}

		.text-primary {
			color: #0d6efd !important;
		}

		.item-details {
			line-height: 1.45;
		}

		.item-details .item-title {
			font-size: 15px;
			margin-bottom: 2px;
		}

		.item-details .item-line {
			font-size: 13px;
		}

		.item-details i {
			width: 18px;
			color: #111;
		}
	</style>


</head>

<body>

	<!-- Sidebar-->

	<!-- Page content wrapper-->

	<!-- Top navigation-->

	<!-- Page content-->


	<div class="table-responsive">
		<table class="table table-bordered  ">
			<thead>
				<tr style="font-size: 15px;">
					<th class="text-primary" width="10%">Item Code</th>
					<th class="text-primary" width="20%">Items</th>
					<th class="text-primary" width="10%">ຂໍ້ມູນຮ້ານທີ່ຈັດຊື້</th>
					<th class="text-primary" width="20%">ຈຳນວນ</th>
					<th class="text-primary" width="20%">ຮູບອູປະກອນ</th>
				</tr>
			</thead>
	</div>

	<tbody>
		<?php
		$conns = $conn;

		$input             = $_POST["input"] ?? '';
		$prostock_province = $_POST['province'] ?? '';
		$Section           = $_POST["Section"] ?? '';
		$resutl            = $_POST["resutl"] ?? '';
		$SectionUseGroup   = $_POST["SectionUseGroup"] ?? '';
		$hasGroup          = ($SectionUseGroup !== '');

		/* ==========================================================================
		   ສ້າງ SQL ດ້ວຍ prepared statement (WHERE clause ແບບ dynamic ແຕ່ປອດໄພ)
		   - ຖ້າເລືອກ Stock_HQ ໃຫ້ອ່ານຈາກຕາຕະລາງ stock ໂດຍກົງ (ມີຄໍລຳຄົບຢູ່ແລ້ວ)
		   - ຖ້າບໍ່ແມ່ນ HQ ໃຫ້ LEFT JOIN stock_province ກັບ stock
		     ດ້ວຍເງື່ອນໄຂ stock.Items = stock_province.Items
		     ເພື່ອດຶງ Item_name_Chinese, Size, Model ມາສະແດງນຳ
		   - ທຸກຄ່າຈາກ $_POST ຖືກ bind ຜ່ານ ? placeholder
		   - ຄົ້ນຫາໄດ້ທັງ Items (ຊື່ອຸປະກອນ) ແລະ Item_code (ເລກລະຫັດ)
		   ========================================================================== */

		$isHQ = ($prostock_province === 'Stock_HQ');
		$table = $isHQ ? 'stock' : 'stock_province';

		// ແກ້ໄຂ (performance): ເລືອກສະເພາະຄໍລຳທີ່ໃຊ້ສະແດງຜົນ ແທນ SELECT * (ຫຼຸດ I/O)
		// ແລະປ່ຽນ JOIN key ຈາກ Items (ຊື່, VARCHAR) ມາເປັນ Item_code (unique key, ມີ index)
		// ເພື່ອໃຫ້ MySQL ໃຊ້ index lookup ແທນ full scan join
		$selectFields = $isHQ
			? "stock.Item_code, stock.Items, stock.id, stock.Unit, stock.Type, stock.Vendor, stock.Use_For, stock.Item_name_Chinese, stock.Size, stock.Model"
			: "stock_province.Item_code, stock_province.Items, stock_province.id, stock_province.Unit, stock_province.Type, stock_province.Vendor, stock_province.Use_For, stock.Item_name_Chinese, stock.Size, stock.Model";
		$fromClause = $isHQ
			? 'stock'
			: 'stock_province LEFT JOIN stock ON stock.Item_code = stock_province.Item_code';

		$where  = [];
		$params = [];
		$types  = '';

		if (!$isHQ) {
			$where[]  = "$table.Provinces = ?";
			$params[] = $prostock_province;
			$types   .= 's';
		}

		if ($Section !== '' && $Section !== 'ທຸກພາກສ່ວນ') {
			$where[]  = "$table.Section = ?";
			$params[] = $Section;
			$types   .= 's';
		}

		if ($input !== '') {
			$searchPattern = ($Section !== '' && $Section !== 'ທຸກພາກສ່ວນ') ? $input . '%' : '%' . $input . '%';
			// ແກ້ໄຂ: ຄົ້ນຫາໄດ້ທັງ Items (ຊື່ອຸປະກອນ) ແລະ Item_code (ເລກລະຫັດ)
			// ເມື່ອກ່ອນຄົ້ນຫາສະເພາະ Items ຢ່າງດຽວ ຈຶ່ງພິມເລກ Item_code ແລ້ວບໍ່ພົບຫຍັງ
			$where[]  = "($table.Items LIKE ? OR $table.Item_code LIKE ?)";
			$params[] = $searchPattern;
			$params[] = $searchPattern;
			$types   .= 'ss';
		}

		if ($hasGroup) {
			$where[]  = "$table.Groupp = ?";
			$params[] = $SectionUseGroup;
			$types   .= 's';
		}

		if ($resutl !== '' && $resutl !== 'all') {
			// ອະນຸຍາດແຕ່ຮູບແບບ operator+ຕົວເລກ ເຊັ່ນ '>5', '<=10', '=0' ເທົ່ານັ້ນ
			if (preg_match('/^(<=|>=|=|<|>)\s*(\d+(\.\d+)?)$/', trim($resutl), $m)) {
				$where[]  = "$table.Unit " . $m[1] . " ?";
				$params[] = $m[2];
				$types   .= 'd';
			}
			// ຖ້າ format ບໍ່ຖືກຕ້ອງ: ບໍ່ໃສ່ filter ນີ້ (ບໍ່ die, ບໍ່ inject)
		}

		$sql2 = "SELECT $selectFields FROM $fromClause";
		if (!empty($where)) {
			$sql2 .= " WHERE " . implode(' AND ', $where);
		}

		mysqli_set_charset($conns, "utf8");

		$result23 = null;
		$stmt2 = $conns->prepare($sql2);

		if ($stmt2 === false) {
			die("Error preparing query: " . $conns->error);
		}

		if (!empty($params)) {
			$bindArgs = [];
			$bindArgs[] = $types;
			foreach ($params as $k => $v) {
				$bindArgs[] = &$params[$k];
			}
			call_user_func_array([$stmt2, 'bind_param'], $bindArgs);
		}

		$stmt2->execute();
		$result23 = $stmt2->get_result();

		if ($result23 instanceof mysqli_result) {
			while ($row23 = $result23->fetch_assoc()) {
				$Itemms = $row23['Items'] ?? '';
				$id     = $row23['id'] ?? '';
				$unit   = $row23['Unit'] ?? 0;

				if ($unit <= 5) {
					$bgcolor = "#F0C0C1";
					$cl = "red";
				} else {
					$bgcolor = "#FFFFFF";
					$cl = "black";
				}

				$itemImageUrl = getItemImageUrl($row23['Item_code'] ?? '', $imageByItemCode, $uploadsBaseDir, $uploadsBaseUrl);
		?>
				<tr bgcolor="<?php echo $bgcolor ?>" style="font-size: 13px;">
					<td width="10%"><strong><?php echo htmlspecialchars($row23['Item_code'] ?? '') ?></strong></td>
					<td width="20%" class="item-details-cell">
						<div class="item-details">
							<div class="item-title">
								<i class="fa fa-link" aria-hidden="true"></i>
								<a style="color: blue" href='EditItemstock1.php?Itemms=<?php echo urlencode($Itemms) ?>&id=<?php echo urlencode($id) ?>'><strong><?php echo htmlspecialchars($row23['Items'] ?? '') ?></strong></a>
							</div>
							<?php if (!empty($row23['Item_name_Chinese'])): ?>
								<div class="item-line"><i class="fa fa-globe" aria-hidden="true"></i> <?php echo htmlspecialchars($row23['Item_name_Chinese']) ?></div>
							<?php endif; ?>
							<div class="item-line"><i class="fa fa-arrows-h" aria-hidden="true"></i> ຂະໜາດ: <?php echo htmlspecialchars($row23['Size'] ?? '-') ?: '-' ?></div>
							<div class="item-line"><i class="fa fa-tag" aria-hidden="true"></i> Model: <?php echo htmlspecialchars($row23['Model'] ?? '-') ?: '-' ?></div>
							<div class="item-line"><i class="fa fa-sitemap" aria-hidden="true"></i> ປະເພດ: <?php echo htmlspecialchars($row23['Use_For'] ?? '-') ?: '-' ?></div>
						</div>
					</td>
					<td width="10%"><strong><?php echo htmlspecialchars($row23['Vendor'] ?? '') ?></strong></td>
					<td width="20%" align="center" style="color: <?php echo $cl ?>"><strong><?php echo htmlspecialchars($row23['Unit'] ?? '') ?> <?php echo htmlspecialchars($row23['Type'] ?? '') ?></strong></td>
					<td width="20%">
						<!-- ດຶງຮູບພາບມາຈາກ folder picture/uploads -->
						<?php if (!empty($itemImageUrl)): ?>
							<a href="<?php echo htmlspecialchars($itemImageUrl) ?>" target="_blank">
								<img src="<?php echo htmlspecialchars($itemImageUrl) ?>" style="width:128px;height:100px;object-fit:cover;border-radius:10px;border:1px solid #ddd;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
								<span style="display:none;color:red;font-size:11px;">ໂຫລດຮູບບໍ່ໄດ້</span>
							</a>
						<?php else: ?>
							<span class="text-muted">-</span>
						<?php endif; ?>
					</td>
				</tr>
		<?php
			}
		} else {
			echo "<tr><td colspan='5'>No results found or an unexpected error occurred.</td></tr>";
		}
		?>
	</tbody>
	</table>

	<!-- Bootstrap core JS-->

	<!-- Core theme JS-->
	<script src="js/scripts.js"></script>
</body>

</html>