<?php
/**
 * sale_scope.php
 * -------------------------------------------------------------
 * ນິຍາມຂອບເຂດຂໍ້ມູນ (Data Scope) + ເງື່ອນໄຂສະຖານະ ຮ່ວມກັນລະຫວ່າງ:
 *   - DataReAdmin_Sale.php  (ໜ້າລາຍການ)
 *   - sidebar.php           (badge ແຈ້ງເຕືອນ)
 *
 * ຕ້ອງການແກ້ scope ຫຼືສະຖານະທີ່ນັບ ໃຫ້ແກ້ຢູ່ໄຟລ໌ນີ້ບ່ອນດຽວ
 * ຈະສົ່ງຜົນໄປທັງ 2 ໜ້າອັດຕະໂນມັດ ຕົວເລກຈຶ່ງຕົງກັນສະເໝີ
 * -------------------------------------------------------------
 */

if (!function_exists('getSaleUserScope')) {
	function getSaleUserScope($iduser)
	{
		$userScope = [
			'215' => [
				'provinces'   => ['Sanakham'],
				'pr_prefixes' => ['SNK'],
			],
			'216' => [
				'provinces'   => ['Luangphabang', 'Vientiane'],
				'pr_prefixes' => ['MNN', 'lpb', 'LS-OFFICE'],
			],
		];

		$iduser = (string)$iduser;
		return isset($userScope[$iduser]) ? $userScope[$iduser] : ['provinces' => [], 'pr_prefixes' => []];
	}
}

if (!function_exists('getSaleListStatuses')) {
	/**
	 * ສະຖານະທີ່ຖືວ່າ "ຄ້າງ" ສຳລັບ user ຝ່າຍຈັດຊື້ (215/216)
	 * ໜ້າລາຍການ ແລະ badge ແຈ້ງເຕືອນ ຕ້ອງໃຊ້ຊຸດນີ້ຮ່ວມກັນ
	 */
	function getSaleListStatuses()
	{
		return ['1', '2', '4'];
	}
}

if (!function_exists('buildSaleStatusSql')) {
	function buildSaleStatusSql($alias = 'a')
	{
		$quoted = array_map(function ($s) {
			return "'" . $s . "'";
		}, getSaleListStatuses());

		return "(" . $alias . ".S_Status IN (" . implode(',', $quoted) . "))";
	}
}

if (!function_exists('buildSaleScopeSql')) {
	/**
	 * ສ້າງ SQL fragment + bind types/values ຈາກ scope array
	 * (ໃຊ້ alias "a." ສະເໝີ -> ຕາຕະລາງ request ຕ້ອງ alias ວ່າ "a")
	 * ຖ້າ scope ວ່າງເປົ່າ -> sql = '1 = 0' (ບໍ່ໃຫ້ເຫັນຫຍັງເລີຍ)
	 */
	function buildSaleScopeSql(array $scope)
	{
		$conds  = [];
		$types  = '';
		$values = [];

		if (!empty($scope['provinces'])) {
			$placeholders = implode(',', array_fill(0, count($scope['provinces']), '?'));
			$conds[] = "a.Province IN ($placeholders)";
			foreach ($scope['provinces'] as $p) {
				$types   .= 's';
				$values[] = $p;
			}
		}

		if (!empty($scope['pr_prefixes'])) {
			foreach ($scope['pr_prefixes'] as $prefix) {
				$conds[] = "LOWER(a.OA_Out) LIKE LOWER(?)";
				$types   .= 's';
				$values[] = $prefix . '%';
			}
		}

		if (empty($conds)) {
			return ['sql' => '1 = 0', 'types' => '', 'values' => []];
		}

		return ['sql' => '(' . implode(' OR ', $conds) . ')', 'types' => $types, 'values' => $values];
	}
}

if (!function_exists('countSaleScopeRequests')) {
	/**
	 * ນັບຈຳນວນລາຍການຄ້າງຂອງ user ຕາມ scope + ສະຖານະ ດຽວກັນກັບ DataReAdmin_Sale.php
	 * ໃຊ້ COUNT(*) ຈາກ request ໂດຍບໍ່ join ຫຍັງ ຈຶ່ງບໍ່ມີແຖວຊ້ຳ (ຄືກັນກັບ GROUP BY a.id ຢູ່ໜ້າລາຍການ)
	 */
	function countSaleScopeRequests($conn, $iduser)
	{
		if (!$conn) return 0;

		$scope    = getSaleUserScope((string)$iduser);
		$scopeSql = buildSaleScopeSql($scope);

		$sql = "SELECT COUNT(*) AS c FROM request a
		        WHERE " . buildSaleStatusSql('a') . " AND " . $scopeSql['sql'];

		$stmt = $conn->prepare($sql);
		if ($stmt === false) return 0;

		if ($scopeSql['types'] !== '') {
			$refs = [$scopeSql['types']];
			foreach ($scopeSql['values'] as $k => $v) {
				$refs[] = &$scopeSql['values'][$k];
			}
			call_user_func_array([$stmt, 'bind_param'], $refs);
		}

		$stmt->execute();
		$res = $stmt->get_result();
		$row = $res ? $res->fetch_assoc() : null;
		$stmt->close();

		return (int)($row['c'] ?? 0);
	}
}