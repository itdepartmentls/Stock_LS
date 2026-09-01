<?php
@session_start();
if ($_SESSION["user"] == "" or $_SESSION["Namepro"] == "") {
	echo "<script>window.location = 'index.php';</script>";
	exit;
}

require_once __DIR__ . '/../includes/conn.php';

// PHP 8: ໃຊ້ Null Coalescing Operator
$proo = $_SESSION["Namepro"] ?? '';
$userId = $_SESSION["iduser"] ?? '';

// ກຳນົດລາຍການຜູ້ໃຊ້ທີ່ມີສິດ Admin
$adminUsers = ['404', '30', '194', '793'];
$isAdmin = in_array($userId, $adminUsers);

// ໃຊ້ Prepared Statement ປ້ອງກັນ SQL Injection
if (!$isAdmin) {
	$sql = "SELECT COUNT(a.S_Status) as 'cccount' 
            FROM request a 
            WHERE a.S_Status = '1' AND a.Province = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $proo);
} else {
	$sql = "SELECT COUNT(a.S_Status) as 'cccount' 
            FROM request a 
            WHERE a.S_Status = '1'";
	$stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$count = $row['cccount'] ?? 0;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<link rel="shortcut icon" href="image/favicons.png">

	<!-- ===== Bootstrap 5 CSS ===== -->
	<link href="css/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
	<link href="css/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

	<!-- ===== Font Awesome 6 ===== -->
	<link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">

	<!-- ===== Select2 CSS ===== -->
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

	<!-- ===== jQuery (ເຕັມ ບໍ່ໃຊ້ slim) ===== -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

	<!-- ===== Bootstrap 5 JS Bundle ===== -->
	<script src="js/vendor/bootstrap/bootstrap.bundle.min.js"></script>

	<!-- ===== Select2 JS ===== -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

	<!-- ===== ໄຟລ໌ຂອງທ່ານ ===== -->
	<link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">

	<!-- icons ເພີ່ມເຕີມ -->
	<link rel="stylesheet" href="css/vendor/uicons/uicons-solid-rounded.css">
	<link rel="stylesheet" href="css/vendor/uicons/uicons-regular-rounded.css">
	<link rel="stylesheet" href="css/vendor/uicons/uicons-regular-straight.css">

	<title>ຕິດຕາມອຸປະກອນ</title>

	<!-- Core theme CSS -->
	<link href="css/app.css" rel="stylesheet" />

	<link rel="stylesheet" href="css/pages/Createfollow.css">

	<script type="text/javascript">
		$(document).ready(function() {
			if (typeof $.fn.select2 !== 'undefined') {
				// ຄົ້ນຫາໄດ້ທັງ ຊື່ພາສາລາວ, ຊື່ພາສາຈີນ, ຂະຫນາດ, ແລະ Model
				function itemMatcher(params, data) {
					if ($.trim(params.term) === '') {
						return data;
					}
					if (typeof data.text === 'undefined' || !data.element) {
						return null;
					}
					var searchable = data.element.getAttribute('data-search') || data.text;
					if (searchable.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
						return data;
					}
					return null;
				}

				// ໄຮລ໌ໄລ໌ ຄຳທີ່ຄົ້ນຫາຢູ່ໃນຂໍ້ຄວາມ
				function highlightMatch(text, term) {
					var $div = $('<div></div>').text(text);
					var escaped = $div.html();
					if (!term) return escaped;
					var escapedTerm = $('<div></div>').text(term).html()
						.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
					var re = new RegExp('(' + escapedTerm + ')', 'ig');
					return escaped.replace(re, '<mark>$1</mark>');
				}

				// ສະແດງລາຍລະອຽດ (ລາວ / ຈີນ / ຂະຫນາດ / Model) ໃນລາຍການຄົ້ນຫາ
				function formatItem(item, term) {
					if (!item.element || !item.id) {
						return item.text;
					}
					var el = item.element;

					var lao      = el.getAttribute('data-lao') || item.text || '';
					var chinese  = el.getAttribute('data-chinese') || '';
					var size     = el.getAttribute('data-size') || '';
					var model    = el.getAttribute('data-model') || '';

					var $wrapper = $('<div></div>');
					$('<div></div>').addClass('fw-bold')
						.html(highlightMatch(lao, term))
						.appendTo($wrapper);

					var parts = [];
					if (chinese) parts.push(highlightMatch(chinese, term));
					if (size) parts.push('ຂະຫນາດ: ' + highlightMatch(size, term));
					if (model) parts.push('Model: ' + highlightMatch(model, term));

					if (parts.length) {
						$('<small></small>').addClass('text-muted d-block')
							.html(parts.join(' &nbsp;|&nbsp; '))
							.appendTo($wrapper);
					}

					return $wrapper;
				}

				$('.cust_id').select2({
					placeholder: "ເລືອກລາຍການ",
					allowClear: true,
					width: '100%',
					matcher: itemMatcher,
					templateResult: function(data) {
						var $field = $('.select2-container--open .select2-search__field');
						var term = $field.length ? $field.val() : '';
						return formatItem(data, term);
					},
					escapeMarkup: function(markup) {
						return markup; // formatItem ໄດ້ escape ໃຫ້ແລ້ວ
					}
				});
			}

			// ===== ສະແດງຕາຕະລາງຂໍ້ມູນອຸປະກອນ ເມື່ອເລືອກລາຍການ =====
			function showItemDetails(selectEl) {
				var table = document.getElementById('itemDetails1');
				var addressField = document.getElementById('address1'); // field ໃຊ້ຢູ່ສະຖານທີ່
				if (!table) return;

				var selectedOption = selectEl.options[selectEl.selectedIndex];

				if (!selectedOption || !selectedOption.value) {
					table.style.display = 'none';
					if (addressField) addressField.value = ''; // ລ້າງຄ່າ ຖ້າຍົກເລີກການເລືອກ
					return;
				}

				var lao      = selectedOption.getAttribute('data-lao') || '-';
				var chinese  = selectedOption.getAttribute('data-chinese') || '-';
				var size     = selectedOption.getAttribute('data-size') || '-';
				var model    = selectedOption.getAttribute('data-model') || '-';
				var qty      = selectedOption.getAttribute('data-qty') || '-';
				var picture  = selectedOption.getAttribute('data-picture') || '-';
				var groupp   = selectedOption.getAttribute('data-groupp') || '';

				table.querySelector('.item-lao').textContent      = lao;
				table.querySelector('.item-chinese').textContent  = chinese;
				table.querySelector('.item-size').textContent     = size;
				table.querySelector('.item-model').textContent    = model;
				table.querySelector('.item-qty').textContent      = qty;
				table.querySelector('.item-picture').innerHTML    = picture !== '-' ? '<img src="' + picture + '" style="width:64px;height:64px;">' : '-';

				// ✅ ໃສ່ຄ່າ Groupp ລົງ field ໃຊ້ຢູ່ສະຖານທີ່ ອັດຕະໂນມັດ
				if (addressField) {
					addressField.value = groupp;
				}

				table.style.display = '';
			}

			// ຮອງຮັບທັງ select ທຳມະດາ ແລະ select2
			if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
				$(document).on('select2:select select2:clear change', '.cust_id', function() {
					showItemDetails(this);
				});
			} else {
				document.querySelectorAll('.cust_id').forEach(function(sel) {
					sel.addEventListener('change', function() {
						showItemDetails(this);
					});
				});
			}
		});
	</script>
</head>

<body>
	<div id="sidebarBackdrop"></div>

	<div class="d-flex" id="wrapper">
		<div class="border-end" id="sidebar-wrapper">
			<div class="sidebar-heading border-bottom">
				<img src="image/Logo.png" class="img-fluid" alt="ETL">
				<span>ລະບົບສາງ LS</span>
			</div>
			<div class="list-group list-group-flush">
				<a class="list-group-item list-group-item-action p-3" href="Dasborad.php">
					<i class="fi fi-rr-hand-holding-box"></i>
					<strong>ຂໍເບິກອຸປະກອນ</strong>
					<span class="badge rounded-pill"><?php echo htmlspecialchars((string) $count); ?></span>
				</a>
				<?php if (!in_array($_SESSION["iduser"] ?? '', ['30', '194', '204', '213', '214', '215'])): ?>
					<a class="list-group-item list-group-item-action p-3" href="UseStock.php">
						<i class="fa fa-tasks" aria-hidden="true"></i>
						<strong>ນຳໃຊ້ອຸປະກອນ</strong>
					</a>
				<?php endif; ?>
				<a class="list-group-item list-group-item-action p-3 active" href="follow.php">
					<i class="fi fi-sr-location-alt"></i>
					<strong>ຕິດຕາມອຸປະກອນ</strong>
				</a>
				<a class="list-group-item list-group-item-action p-3" href="stock.php">
					<i class="fa-solid fa-cubes-stacked"></i>
					<strong>ສາງອຸປະກອນ</strong>
				</a>
				<a class="list-group-item list-group-item-action p-3" href="Report.php">
					<i class="fa fa-bar-chart" aria-hidden="true"></i>
					<strong>Report</strong>
				</a>
				<br>
			</div>
		</div>

		<div id="page-content-wrapper">

			<nav class="navbar navbar-expand-lg navbar-green border-bottom">
				<div class="container-fluid">
					<button class="btn btn-success" id="sidebarToggle" type="button">
						<i class="fa fa-eye-slash" aria-hidden="true"></i>
						<span class="toggle-label">&nbsp;Hide Menu</span>
					</button>

					<button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
						aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
						<i class="fa-solid fa-bars text-white"></i>
					</button>

					<div class="collapse navbar-collapse" id="navbarSupportedContent">
						<ul class="navbar-nav ms-auto mt-2 mt-lg-0">
							<li class="nav-item dropdown user-dropdown">
								<a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
									<i class="fa fa-user-circle"></i>&nbsp; <?= htmlspecialchars($_SESSION["user"] ?? '') ?>
								</a>
								<ul class="dropdown-menu dropdown-menu-end shadow-sm animate-dropdown" aria-labelledby="userDropdown">
									<li><span class="dropdown-item-text"><i class="fa fa-id-badge"></i> <?= htmlspecialchars($_SESSION["user"] ?? '') ?></span></li>
									<li>
										<hr class="dropdown-divider">
									</li>
									<li>
										<a class="dropdown-item text-danger" href="logout.php">
											<i class="fas fa-sign-out-alt"></i>&nbsp; <strong>Logout</strong>
										</a>
									</li>
								</ul>
							</li>
						</ul>
					</div>
				</div>
			</nav>

			<div class="container-fluid px-4 py-3">

				<div class="mb-3">
					<a class="btn btn-secondary btn-sm" href="follow.php">
						<i class="fa fa-arrow-left" aria-hidden="true"></i>
						ກັບຄືນ
					</a>
				</div>

				<div class="form-card">
					<form action="Createfollow.php" name="form3" method="post">

						<h3><i class="fa-regular fa-memo-circle-info"></i> ປະກອບເອກະສານ</h3>

						<div class="mb-3">
							<label class="doc-type-choice">
								<input type="radio" value="ສ້ອມແປງ" name="document" required> <b>ສ້ອມແປງ</b>
							</label>
							<label class="doc-type-choice">
								<input type="radio" value="ຖອນ" name="document" required> <b>ຖອນ</b>
							</label>
							<label class="doc-type-choice">
								<input type="radio" value="ຍົກຍ້າຍ" name="document" required> <b>ຍົກຍ້າຍ</b>
							</label>
						</div>

						<div class="mb-3">
							<label class="form-label"><strong>ອຸປະກອນ</strong></label>
							<select class="cust_id related-post form-control" name="Equment" required>
								<option value="">ກະລຸນາເລືອກອຸປະກອນ</option>
								<?php
								$stockResult = $conn->query("SELECT Items, Item_name_Chinese, Size, Model, Total_Unit, picture, Groupp FROM stock ORDER BY Items ASC");
								while ($stockRow = $stockResult->fetch_assoc()) {
									$laoName    = $stockRow['Items'] ?? '';
									$chineseName = $stockRow['Item_name_Chinese'] ?? '';
									$size       = $stockRow['Size'] ?? '';
									$Model      = $stockRow['Model'] ?? '';
									$qty        = $stockRow['Total_Unit'] ?? '';
									$picture    = $stockRow['picture'] ?? '';
									$groupp     = $stockRow['Groupp'] ?? '';
									$searchText = $laoName . ' ' . $chineseName . ' ' . $size . ' ' . $Model;
									echo '<option value="' . htmlspecialchars($laoName) . '" '
										. 'data-lao="' . htmlspecialchars($laoName) . '" '
										. 'data-chinese="' . htmlspecialchars($chineseName) . '" '
										. 'data-size="' . htmlspecialchars($size) . '" '
										. 'data-model="' . htmlspecialchars($Model) . '" '
										. 'data-qty="' . htmlspecialchars($qty) . '" '
										. 'data-picture="' . htmlspecialchars($picture) . '" '
										. 'data-groupp="' . htmlspecialchars($groupp) . '" '
										. 'data-search="' . htmlspecialchars($searchText) . '">'
										. htmlspecialchars($laoName) . '</option>';
								}
								?>
							</select>

							<!-- ຕາຕະລາງຂໍ້ມູນອຸປະກອນ -->
							<table class="table table-sm item-details-table mt-3" id="itemDetails1" style="display:none;">
								<thead>
									<tr>
										<th><i class="fi fi-rr-flag"></i>ຊື່ພາສາລາວ</th>
										<th><i class="fi fi-rr-globe"></i>ຊື່ພາສາຈີນ</th>
										<th><i class="fa-solid fa-ruler-combined"></i>ຂະຫນາດ</th>
										<th><i class="fa-solid fa-tag"></i>Model</th>
										<th><i class="fa-solid fa-cubes"></i>ຈຳນວນ</th>
										<th><i class="fa-solid fa-image"></i>picture</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="item-lao">-</td>
										<td class="item-chinese">-</td>
										<td class="item-size">-</td>
										<td class="item-model">-</td>
										<td><span class="item-qty">-</span></td>
										<td class="item-picture">-</td>
									</tr>
								</tbody>
							</table>
						</div>

						<div class="row">
							<div class="col-sm-4 mb-3">
								<label class="form-label"><strong>PR ເລກທີ</strong></label>
								<input type="text" class="form-control" name="pr_number" placeholder="..." required>
							</div>

							<div class="col-sm-4 mb-3">
								<label class="form-label"><strong>ຊີລຽວນາມເບີ</strong></label>
								<input type="text" class="form-control" name="seriel" placeholder="..." required>
							</div>
							<div class="col-sm-4 mb-3">
								<label class="form-label"><strong>Tickets</strong></label>
								<input type="number" class="form-control" name="ticket" placeholder="...">
							</div>
							<div class="col-sm-4 mb-3">
								<label class="form-label"><strong>ໃຊ້ຢູ່ສະຖານທີ່</strong></label>
								<input type="text" class="form-control" name="address" id="address1" placeholder="..." required>
							</div>
							<div class="col-sm-8 mb-3">
							     <label class="form-label"><strong>ໝາຍເຫດ</strong></label>
							     <input type="text" class="form-control" name="remark" placeholder="..." required>
						    </div>
						</div>

						

						<button type="submit" class="btn btn-submit w-100" name="save">
							<i class="fa fa-check-circle"></i> Submit
						</button>
					</form>
				</div>

			</div>

			<?php
			if (isset($_POST["save"])) {
				date_default_timezone_set("Asia/Bangkok");
				$date_time = date('20y-m-d G:i:s');
				$userProvinceValue = $_SESSION["user"] . " " . $date_time;
				$ticket = ($_POST["ticket"] !== "") ? (int) $_POST["ticket"] : null;

				$strSQL = "INSERT INTO followequment
          (Name_Come, Date, address, NumBer_S, Tickets, S_Status, Cat_Province, UserProvince, Remark_Pro, OA_f)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

				$stmt = $conn->prepare($strSQL);
				$stmt->bind_param(
					"ssssisssss",
					$_POST["Equment"],
					$date_time,
					$_POST["address"],
					$_POST["seriel"],
					$ticket,
					$_POST["document"],
					$_SESSION["Namepro"],
					$userProvinceValue,
					$_POST["remark"],
					$_POST["pr_number"]
				);

				$objQuery = $stmt->execute();
				if (!$objQuery) {
					echo "Error Add [" . htmlspecialchars($stmt->error) . "]";
				} else {
			?>
					<script>
						window.location = "follow.php";
					</script>
			<?php
				}
				$stmt->close();
			}
			?>

		</div>
	</div>

	<!-- Core theme JS -->
	<script src="js/scripts.js"></script>

	<script>
		var wrapperEl = document.getElementById('wrapper');
		var sidebarBackdrop = document.getElementById('sidebarBackdrop');
		var sidebarToggleBtn = document.getElementById('sidebarToggle');

		function isDesktop() {
			return window.innerWidth >= 992;
		}

		function closeSidebar() {
			wrapperEl.classList.remove('sidebar-open');
			sidebarBackdrop.classList.remove('show');
		}

		sidebarToggleBtn?.addEventListener('click', function() {
			if (isDesktop()) {
				wrapperEl.classList.toggle('sidebar-hidden-desktop');
			} else {
				var isOpen = wrapperEl.classList.toggle('sidebar-open');
				sidebarBackdrop.classList.toggle('show', isOpen);
			}
		});

		sidebarBackdrop?.addEventListener('click', closeSidebar);

		document.querySelectorAll('#sidebar-wrapper .list-group-item').forEach(function(item) {
			item.addEventListener('click', function() {
				if (!isDesktop()) {
					closeSidebar();
				}
			});
		});

		window.addEventListener('resize', function() {
			if (isDesktop()) {
				closeSidebar();
			} else {
				wrapperEl.classList.remove('sidebar-hidden-desktop');
			}
		});
	</script>
</body>

</html>
