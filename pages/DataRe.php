<?php
require_once __DIR__ . '/../includes/conn.php';
@session_start();
if ($_SESSION["user"] == "" or  $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
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
// ທີ່ຢູ່ level ດຽວກັນກັບ DataRe.php (ໂຟນເດີນັ້ນບໍ່ກ່ຽວຂ້ອງກັນ)
// =========================================================
$uploadsBaseDir = __DIR__ . '/../picture/uploads';
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
	<meta charset="utf-8" />
	<link rel="shortcut icon" href="image/logoETL.jpg">
	<link rel="stylesheet" href="css/vendor/bootstrap/bootstrap.min.css">
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
	<script src="js/vendor/bootstrap/bootstrap.bundle.min.js"></script>
	<link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">
	<link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">

	<!-- ເອົາ SweetAlert2 ອອກແລ້ວ -->
	<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->

	<title>Stock</title>
	<!-- Favicon-->

	<!-- Core theme CSS (includes Bootstrap)-->


	<link rel="stylesheet" href="css/pages/DataRe.css">

	<table class="table table-hover circle">
		<thead>
			<tr class="trr" style="font-size: 15px;color:blue">
				<th width="10%">ຮູບພາບ</th>
				<th width="40%">ອຸປະກອນ</th>
				<th width="20%">ເອກະສານ</th>
				<th width="10%">ຈຳນວນຂໍເບິກ</th>
				<th width="20%"></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$Province = $_SESSION["Namepro"];

			$sql = "SELECT*FROM request a
WHERE a.Province LIKE '$Province' and a.S_Status ='3' and (TIMESTAMPDIFF(day,a.DateComfirm,CURDATE())< 14 )
ORDER BY a.dateRe asc ";

			mysqli_set_charset(@$conn, "utf8");
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while ($row = $result->fetch_assoc()) {
					$id = $row['id'];
					if ($row['S_Status'] == '3') {
						$showcolor = '#EDDFB6';
					} elseif ($row['S_Status'] == '1' or $row['S_Status'] == '2') {
						$showcolor = '#FFFFFF';
					}
					$itemImageUrl = getItemImageUrl($row['Item_code'], $imageByItemCode, $uploadsBaseDir, $uploadsBaseUrl);
			?>
					<tr bgcolor="<?= $showcolor; ?>" style="font-size: 13px;">
						<td width="10%" align="center">
							<?php if (!empty($itemImageUrl)): ?>
								<a href="<?php echo htmlspecialchars($itemImageUrl); ?>" target="_blank">
									<img src="<?php echo htmlspecialchars($itemImageUrl); ?>" alt="<?php echo htmlspecialchars($row['Items']); ?>" style="width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid #ddd;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
									<span style="display:none;color:red;font-size:10px;">ໂຫລດຮູບບໍ່ໄດ້</span>
								</a>
							<?php else: ?>
								<span class="text-muted">-</span>
							<?php endif; ?>
						</td>
						<td width="40%">
							<strong><i class="fa-light fa-forklift fa-lg"></i> <?php echo $row['Items'] ?><br><i class="fa-solid fa-globe fa-lg"></i> ຂໍ້ມູນຮ້ານທີ່ຈັດຊື້: <?php echo $row['Vendor'] ?><br><i class="fa-light fa-network-wired fa-lg"></i> ພາກສ່ວນ: <?php echo $row['Section'] ?></strong></td>
						<td width="20%"><strong> <i class="fa-light fa-memo-circle-info fa-lg"></i> PR ເລກທີ:
								<?php if (!empty($row['OA_Out'])): ?>
									<a href="pr_detail.php?pr_no=<?php echo urlencode($row['OA_Out']); ?>" target="_blank" style="color: #d32f2f; text-decoration: underline; font-weight: bold;">
										<?php echo $row['OA_Out']; ?>
									</a>
								<?php else: ?>
									<?php echo $row['OA_Out']; ?>
								<?php endif; ?>
								<?php if (!empty($row['planing_number'])): ?>
									<br><i class="fa-light fa-file-lines fa-lg"></i> ແຜນເລກທີ: <?php echo htmlspecialchars($row['planing_number']); ?>
								<?php endif; ?>
								<br><i class="fa-regular fa-user-tie-hair fa-lg"></i> <?php echo $row['User_Re'] ?><br><i class="fa-duotone fa-calendar-days fa-lg"></i> <?php echo $row['dateRe'] ?></strong></td>
						<td width="10%"><strong><?php echo $row['Unit'] ?> <?php echo $row['Type'] ?></strong></td>
						<?php if ($row['S_Status'] == '3') { ?>
							<td width="20%" align="center"><strong><i class="fa-solid fa-user-gear" style="color: orangered"> <?php echo $row['User_Confirm'] ?>:</i> <?php echo $row['Remark'] ?> </strong></td>
						<?php } elseif ($row['S_Status'] == '1' or $row['S_Status'] == '2') { ?>
							<?php echo "<td  width='20%' ><center> <a style='color: red' href ='deleteRe.php?id=$id'onClick=\"javascript:return confirm('ຕ້ອງການລົບລາຍການ $row[Items]  ແທ້ບໍ່?');\" >"; ?><strong><i class='fa fa-trash-o fa-lg text-danger' aria-hidden='true'></i><br>ລົບລາຍການ</strong></center></a> <?php
																																																																													}
																																																																												} ?>
					</tr>
		</tbody>
	<?php
			}
	?>

	<?php
	$Province = $_SESSION["Namepro"];

	$sql = "SELECT*FROM request a
WHERE a.Province LIKE '$Province' and (a.S_Status in ('1','2'))
ORDER BY a.dateRe asc ";

	mysqli_set_charset(@$conn, "utf8");
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {
			$id = $row['id'];
			$S_Status = $row['S_Status'];
			$Items = $row['Items'];
			$Province = $row['Province'];
			$Item_code = $row['Item_code'];
			$Vendor = $row['Vendor'];
			$Use_For = $row['Use_For'];
			$Type = $row['Type'];
			$Unit = $row['Unit'];
			$Groupp = $row['Groupp'];
			$picture = $row['picture'];
			$Section = $row['Section'];
			$User_Confirm = $row['User_Confirm'];
			$dateRe = $row['dateRe'];
			$Give = $row['Give'];
			$OA_Out = $row['OA_Out'];

			if ($row['S_Status'] == '3') {
				$showcolor = '#EDDFB6';
			} elseif ($row['S_Status'] == '1') {
				$showcolor = '#FFFFFF';
			} elseif ($row['S_Status'] == '2') {
				$showcolor = '#DCF6CF';
			}

			$itemImageUrl = getItemImageUrl($Item_code, $imageByItemCode, $uploadsBaseDir, $uploadsBaseUrl);
	?>
			<tr bgcolor="<?= $showcolor; ?>" style="font-size: 13px;">
				<td width="10%" align="center">
					<?php if (!empty($itemImageUrl)): ?>
						<a href="<?php echo htmlspecialchars($itemImageUrl); ?>" target="_blank">
							<img src="<?php echo htmlspecialchars($itemImageUrl); ?>" alt="<?php echo htmlspecialchars($row['Items']); ?>" style="width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid #ddd;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
							<span style="display:none;color:red;font-size:10px;">ໂຫລດຮູບບໍ່ໄດ້</span>
						</a>
					<?php else: ?>
						<span class="text-muted">-</span>
					<?php endif; ?>
				</td>
				<td width="40%">
					<strong><i class="fa-light fa-forklift fa-lg"></i> <?php echo $row['Items'] ?><br><i class="fa-solid fa-globe fa-lg"></i> ຂໍ້ມູນຮ້ານທີ່ຈັດຊື້: <?php echo $row['Vendor'] ?><br><i class="fa-light fa-network-wired fa-lg"></i> ພາກສ່ວນ: <?php echo $row['Section'] ?></strong></td>
				<td width="20%"><strong> <i class="fa-light fa-memo-circle-info fa-lg"></i> PR ເລກທີ:
					<?php if (!empty($row['OA_Out'])): ?>
						<a href="pr_detail.php?pr_no=<?php echo urlencode($row['OA_Out']); ?>" target="_blank" style="color: #d32f2f; text-decoration: underline; font-weight: bold;">
						<?php echo $row['OA_Out']; ?>
						</a>
					<?php else: ?>
						<?php echo $row['OA_Out']; ?>
					<?php endif; ?>
					<?php if (!empty($row['planing_number'])): ?>
						<br><i class="fa-light fa-file-lines fa-lg"></i> ແຜນເລກທີ: <?php echo htmlspecialchars($row['planing_number']); ?>
					<?php endif; ?>
					<br><i class="fa-regular fa-user-tie-hair fa-lg"></i> <?php echo $row['User_Re'] ?><br><i class="fa-duotone fa-calendar-days fa-lg"></i> <?php echo $row['dateRe'] ?></strong></td>
				<td width="10%"><strong>ຂໍເບິກ: <?php echo $row['Unit'] ?> <?php echo $row['Type'] ?><br>ເບິກໄດ້: <?php echo $row['Give'] ?> <?php echo $row['Type'] ?></strong></td>

				<?php if ($row['S_Status'] == '3') { ?>
					<td width="20%" align="center"><strong><i class="fa-solid fa-user-gear" style="color: orangered"> <?php echo $row['User_Confirm'] ?>:</i> <?php echo $row['Remark'] ?> </strong></td>
				<?php } elseif ($row['S_Status'] == '1') { ?>
					<?php
					echo "<td  width='20%' ><center> <a style='color: red' href ='deleteRe.php?id=$id'onClick=\"javascript:return confirm('ຕ້ອງການລົບລາຍການ $row[Items]  ແທ້ບໍ່?');\" >";
					?>
					<strong><i class='fa fa-trash-o fa-lg text-danger' aria-hidden='true'></i><br>ລົບລາຍການ</strong></center></a>
				<?php
				} elseif ($row['S_Status'] == '2') {
					$formId = 'form3_' . $id;
				?>
					<?php echo "<td  width='20%'>" ?><center>
						<form action="receipStock.php" id="<?php echo $formId; ?>" name="<?php echo $formId; ?>" method="post">
							<input hidden="id" type="number" name="id" id="id" style="color:blue; width:100px " value="<?php echo $row['id'] ?>" />
							<input hidden="id" type="text" name="Province" id="Province" style="color:blue; width:100px " value="<?php echo $row['Province'] ?>" />
							<input hidden="id" type="text" name="Section" id="Section" style="color:blue; width:100px " value="<?php echo $row['Section'] ?>" />
							<input hidden="id" type="text" name="Vendor" id="Vendor" style="color:blue; width:100px " value="<?php echo $row['Vendor'] ?>" />
							<input hidden="id" type="text" name="Item_code" id="Item_code" style="color:blue; width:100px " value="<?php echo $row['Item_code'] ?>" />
							<input hidden="id" type="text" name="Use_For" id="Use_For" style="color:blue; width:100px " value="<?php echo $row['Use_For'] ?>" />
							<input hidden="id" type="text" name="Type" id="Type" style="color:blue; width:100px " value="<?php echo $row['Type'] ?>" />
							<input hidden="id" type="text" name="Groupp" id="Groupp" style="color:blue; width:100px " value="<?php echo $row['Groupp'] ?>" />
							<input hidden="id" type="text" name="picture" id="picture" style="color:blue; width:100px " value="<?php echo $row['picture'] ?>" />
							<input hidden="id" type="text" name="Section" id="Section" style="color:blue; width:100px " value="<?php echo $row['Section'] ?>" />
							<input hidden="id" type="text" name="Unit" id="Unit" style="color:blue; width:100px " value="<?php echo $row['Unit'] ?>" />
							<input hidden="id" type="text" name="Items" id="Items" style="color:blue; width:100px " value="<?php echo $row['Items'] ?>" />
							<input hidden="id" type="text" name="give" id="give" style="color:blue; width:100px " value="<?php echo $row['Give'] ?>" />
							<input hidden="id" type="text" name="S_Status" id="S_Status" style="color:blue; width:100px " value="<?php echo $row['S_Status'] ?>" />
							<input hidden="id" type="text" name="OA" id="OA" style="color:blue; width:100px " value="<?php echo $OA_Out  ?>" />
							<input hidden="id" type="text" name="planing_number" id="planing_number" style="color:blue; width:100px " value="<?php echo htmlspecialchars($row['planing_number'] ?? '') ?>" />

							<!-- ປ່ຽນກັບໄປໃຊ້ confirm() ທຳມະດາ -->
							<button style="font-size: 13px;width: 200px;" id="Submit" class="btn btn-success" type="submit" onclick="return confirm('ຕ້ອງການຮັບລາຍການນີ້ແທ້ບໍ່?');"><i class="fa-regular fa-hands-holding-diamond fa-lg"></i> ຮັບລາຍການ</button>
						</form>
					</center>
					</td>
		<?php
				}
			}
		}
		?>
		</tbody>
	</table>
	</body>

</html>
