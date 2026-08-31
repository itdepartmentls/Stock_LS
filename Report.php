<?php
// Report.php

require_once __DIR__ . '/includes/conn.php';
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
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<link rel="shortcut icon" href="image/favicons.png">

	<!-- ===== Bootstrap 5 CSS ===== -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

	<!-- ===== Font Awesome 6 ===== -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

	<!-- ===== Select2 CSS ===== -->
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

	<!-- ===== jQuery (ເຕັມ ບໍ່ໃຊ້ slim) ===== -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

	<!-- ===== Bootstrap 5 JS Bundle ===== -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

	<!-- ===== Select2 JS ===== -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

	<!-- ===== ໄຟລ໌ຂອງທ່ານ ===== -->
	<link rel="stylesheet" href="js/pro.min.js">
	<link rel="stylesheet" href="css/all.css">
	<link rel="stylesheet" href="css/all.min.css">

	<!-- icons ເພີ່ມເຕີມ -->
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-solid-rounded/css/uicons-solid-rounded.css">
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-straight/css/uicons-regular-straight.css">

	<title>ການລາຍງານ</title>

	<!-- Core theme CSS -->
	<link href="css/styles.css" rel="stylesheet" />

	<style type="text/css">
		/* ===== ພື້ນຖານ / RESET ===== */
		* {
			box-sizing: border-box;
		}

		html,
		body {
			overflow-x: hidden;
			max-width: 100%;
		}

		/* ===== ສີຫຼັກເປັນສີຂຽວ ===== */
		:root {
			--bs-primary: #198754;
			--bs-primary-rgb: 25, 135, 84;
			--bs-success: #198754;
			--sidebar-width: 280px;
		}

		body,
		td,
		th {
			font-family: "Phetsarath OT";
		}

		.right {
			float: right;
		}

		.circle {
			border-radius: 14px;
			overflow: hidden;
		}

		/* ===== Layout Wrapper ===== */
		#wrapper {
			position: relative;
			min-height: 100vh;
			width: 100%;
		}

		/* ===== Sidebar ສີຂຽວ ===== */
		#sidebar-wrapper {
			background: linear-gradient(180deg, #0C2C55 0%, #0C2C55 100%);
			color: #fff;
			box-shadow: 2px 0 10px rgba(0, 0, 0, 0.15);
			border-right: none !important;
			min-height: 100vh;
			width: var(--sidebar-width);
			flex-shrink: 0;
			transition: margin-left 0.3s ease, transform 0.3s ease;
		}

		#sidebar-wrapper .sidebar-heading {
			background: rgba(0, 0, 0, 0.15) !important;
			border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
			color: #fff !important;
			font-weight: 600;
			padding: 1rem 1.25rem;
			display: flex;
			align-items: center;
			gap: 10px;
		}

		#sidebar-wrapper .list-group-item {
			background: transparent !important;
			color: #fff !important;
			border: none;
			border-radius: 0;
			padding: 0.75rem 1.25rem;
			transition: all 0.25s ease;
			font-weight: 500;
			display: flex;
			align-items: center;
			gap: 10px;
			flex-wrap: wrap;
		}

		#sidebar-wrapper .list-group-item:hover {
			background: rgba(255, 255, 255, 0.15) !important;
			transform: translateX(6px);
			color: #fff !important;
		}

		#sidebar-wrapper .list-group-item .badge {
			background-color: #ffc107;
			color: #000;
			margin-left: auto;
		}

		#sidebar-wrapper .list-group-item.active {
			background: rgba(255, 255, 255, 0.2) !important;
			border-left: 4px solid #ffc107;
		}

		#sidebar-wrapper .list-group-item i,
		#sidebar-wrapper .list-group-item .fi,
		#sidebar-wrapper .list-group-item .bi {
			font-size: 18px;
			width: 24px;
			text-align: center;
			flex-shrink: 0;
		}

		/* ===== Navbar ສີຂຽວ ===== */
		.navbar-green {
			background: linear-gradient(135deg, #0C2C55 0%, #0C2C55 100%) !important;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
			border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
			flex-wrap: wrap;
		}

		.navbar-green .nav-link,
		.navbar-green .navbar-brand,
		.navbar-green .navbar-text {
			color: #fff !important;
		}

		.navbar-green .nav-link:hover {
			color: rgba(255, 255, 255, 0.8) !important;
		}

		.navbar-green .btn-success {
			background-color: rgba(255, 255, 255, 0.2) !important;
			border-color: rgba(255, 255, 255, 0.3) !important;
			color: #fff !important;
		}

		.navbar-green .btn-success:hover {
			background-color: rgba(255, 255, 255, 0.3) !important;
		}

		/* ===== Logo ໃຫຍ່ຂຶ້ນ ===== */
		#sidebar-wrapper .sidebar-heading img {
			width: 100px !important;
			height: auto;
			border-radius: 14px;
			background: rgba(255, 255, 255, 0.1);
			padding: 6px;
			transition: all 0.3s ease;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
		}

		#sidebar-wrapper .sidebar-heading img:hover {
			transform: scale(1.05);
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
		}

		/* ===== ປຸ່ມ ===== */
		.btn-success {
			background-color: #198754 !important;
			border-color: #198754 !important;
		}

		.btn-success:hover {
			background-color: #146c43 !important;
			border-color: #146c43 !important;
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
		}

		.btn-primary {
			background-color: #0C2C55 !important;
			border-color: #0C2C55 !important;
		}

		.btn-primary:hover {
			background-color: #0A1F3F !important;
			border-color: #0A1F3F !important;
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
		}

		.btn-secondary {
			background-color: #6c757d;
			border-color: #6c757d;
		}

		.btn-secondary:hover {
			background-color: #5a6268;
			border-color: #545b62;
			transform: translateY(-2px);
		}

		/* ===== Tabs ສີຂຽວ ===== */
		.tab-custom {
			display: flex;
			flex-wrap: wrap;
			background: #f8f9fa;
			border-radius: 14px 14px 0 0;
			overflow: hidden;
			border-bottom: 3px solid #325a8f;
			margin-bottom: 20px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
		}

		.tab-custom .tablinks {
			flex: 1;
			min-width: 140px;
			padding: 14px 20px;
			background: transparent;
			border: none;
			outline: none;
			cursor: pointer;
			transition: all 0.3s ease;
			font-size: 15px;
			font-family: "Phetsarath OT";
			font-weight: 600;
			color: #555;
			position: relative;
			text-align: center;
		}

		.tab-custom .tablinks:hover {
			background: rgba(25, 135, 84, 0.08);
			color: #325a8f;
		}

		.tab-custom .tablinks.active {
			background: linear-gradient(135deg, #0C2C55, #325a8f);
			color: #fff;
			box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
		}

		.tab-custom .tablinks.active::after {
			content: '';
			position: absolute;
			bottom: -3px;
			left: 0;
			right: 0;
			height: 3px;
			background: #ffc107;
		}

		.tab-custom .tablinks i {
			margin-right: 8px;
		}

		/* ===== Tab Content ===== */
		.tabcontent-custom {
			display: none;
			padding: 20px 0;
			animation: fadeIn 0.4s ease;
		}

		.tabcontent-custom.active {
			display: block;
		}

		@keyframes fadeIn {
			from {
				opacity: 0;
				transform: translateY(10px);
			}

			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		/* ===== Form ===== */
		.form-container {
			background: #f8f9fa;
			padding: 20px 25px;
			border-radius: 14px;
			box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
			margin-bottom: 20px;
		}

		.form-container label {
			font-weight: 600;
			color: #0C2C55;
			margin-right: 15px;
			margin-bottom: 10px;
		}

		.form-container .form-control {
			border-radius: 8px;
			border: 1.5px solid #e0e0e0;
			transition: all 0.3s ease;
			display: inline-block;
			width: auto;
			min-width: 150px;
		}

		.form-container .form-control:focus {
			border-color: #325a8f;
			box-shadow: 0 0 0 0.2rem rgba(50, 90, 143, 0.25);
		}

		.form-container .form-group-inline {
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			gap: 10px;
		}

		.form-container .form-group-inline label {
			margin-bottom: 0;
		}

		.form-container select.form-control {
			min-width: 150px;
		}

		.form-container input.form-control {
			min-width: 130px;
		}

		/* ===== Dropdown ===== */
		.animate-dropdown {
			border-radius: 12px;
			border: none;
			padding: 0.5rem 0;
			min-width: 200px;
			animation: fadeInDown 0.3s ease;
			box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
		}

		@keyframes fadeInDown {
			from {
				opacity: 0;
				transform: translateY(-12px);
			}

			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		.lang-option {
			display: flex;
			align-items: center;
			padding: 0.6rem 1.25rem;
			transition: all 0.2s ease;
		}

		.lang-option:hover {
			background: #f0fdf4;
			transform: translateX(4px);
		}

		.lang-option.active {
			background: #198754;
			color: #fff !important;
		}

		.lang-option.active .lang-name {
			color: #fff !important;
		}

		.lang-option.active .check-icon {
			color: #fff;
		}

		.lang-option .flag-icon {
			font-size: 1.4rem;
			margin-right: 12px;
		}

		.lang-option .lang-name {
			flex: 1;
			font-weight: 500;
		}

		.lang-option .check-icon {
			color: transparent;
			font-size: 1rem;
		}

		.lang-option.active .check-icon {
			color: #fff;
		}

		/* ===== User Dropdown ===== */
		.user-dropdown .dropdown-item {
			padding: 0.6rem 1.25rem;
			transition: background 0.2s;
		}

		.user-dropdown .dropdown-item:hover {
			background: #f0fdf4;
		}

		.user-dropdown .dropdown-item.text-danger:hover {
			background: #fde8e8;
		}

		/* ===== Logout button ===== */
		.logout-btn {
			color: #fff !important;
			font-weight: 600;
			transition: color 0.3s;
		}

		.logout-btn:hover {
			color: #ffc107 !important;
		}

		/* ===== iframe ===== */
		iframe {
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
			border: 1px solid #e9ecef;
			transition: box-shadow 0.3s;
			border-radius: 14px;
			background: #fff;
			width: 100%;
			max-width: 100%;
			display: block;
		}

		iframe:hover {
			box-shadow: 0 6px 30px rgba(0, 0, 0, 0.10);
		}

		/* ===== Select2 ===== */
		.select2-container .select2-selection--single {
			border-radius: 8px !important;
			border-color: #e0e0e0 !important;
			height: 38px !important;
		}

		.select2-container--default .select2-selection--single .select2-selection__rendered {
			line-height: 38px !important;
		}

		/* ===== Page Content ===== */
		#page-content-wrapper {
			flex: 1;
			min-width: 0;
			width: 100%;
			background: #f8f9fa;
		}

		/* ===== Toggle Button ===== */
		#sidebarToggle {
			background-color: #0C2C55 !important;
			border-color: #596e89 !important;
			color: #fff !important;
			white-space: nowrap;
		}

		#sidebarToggle:hover {
			background-color: rgba(255, 255, 255, 0.25) !important;
		}

		/* ===== Scrollbar ===== */
		::-webkit-scrollbar {
			width: 8px;
			height: 8px;
		}

		::-webkit-scrollbar-track {
			background: #f1f1f1;
			border-radius: 10px;
		}

		::-webkit-scrollbar-thumb {
			background: linear-gradient(135deg, #198754, #146c43);
			border-radius: 10px;
		}

		::-webkit-scrollbar-thumb:hover {
			background: #146c43;
		}

		/* ============================================================
		   RESPONSIVE — ຮອງຮັບທຸກຫນ້າຈໍ
		   Desktop (>=992px): sidebar ຄົງທີ່ຢູ່ຂ້າງໆເນື້ອໃນ
		   Tablet/Mobile (<992px): sidebar ກາຍເປັນ off-canvas drawer
		   ============================================================ */

		#sidebarBackdrop {
			display: none;
			position: fixed;
			inset: 0;
			background: rgba(0, 0, 0, 0.45);
			z-index: 1040;
		}

		#sidebarBackdrop.show {
			display: block;
		}

		@media (max-width: 991.98px) {
			#wrapper {
				display: block;
			}

			#sidebar-wrapper {
				position: fixed;
				top: 0;
				left: 0;
				height: 100vh;
				min-height: 100vh;
				transform: translateX(-100%);
				z-index: 1045;
				overflow-y: auto;
			}

			#wrapper.sidebar-open #sidebar-wrapper {
				transform: translateX(0);
			}

			#page-content-wrapper {
				width: 100%;
				margin-left: 0;
			}

			#sidebarToggle .toggle-label {
				display: none;
			}

			/* ===== ຟອມຄົ້ນຫາ: ວາງແຖວລະໜຶ່ງຊ່ອງ, ເຕັມຄວາມກວ້າງ ===== */
			.form-container .form-group-inline {
				flex-direction: column;
				align-items: stretch;
			}

			.form-container .form-group-inline label {
				width: 100%;
			}

			.form-container .form-control {
				width: 100% !important;
				min-width: unset;
			}

			.form-container label {
				margin-right: 0;
			}
		}

		@media (min-width: 992px) {
			#wrapper {
				display: flex;
			}

			#sidebarBackdrop {
				display: none !important;
			}

			#wrapper.sidebar-hidden-desktop #sidebar-wrapper {
				margin-left: calc(var(--sidebar-width) * -1);
			}
		}

		@media (max-width: 768px) {
			.tab-custom .tablinks {
				flex: 1 1 100%;
				font-size: 14px;
				padding: 10px 12px;
			}

			.right {
				float: none;
				display: inline-block;
			}

			.container-fluid.px-4 {
				padding-left: 0.75rem !important;
				padding-right: 0.75rem !important;
			}

			.form-container {
				padding: 16px 14px;
			}

			iframe {
				min-height: 60vh;
			}
		}

		@media (max-width: 576px) {
			#sidebar-wrapper {
				width: 85%;
				max-width: 300px;
			}

			.btn-success#sidebarToggle {
				padding: 0.4rem 0.6rem;
				font-size: 0.85rem;
			}

			.tab-custom .tablinks {
				font-size: 13px;
				padding: 10px 8px;
			}

			iframe {
				min-height: 55vh;
				border-radius: 10px;
			}
		}

		/* ============================================================
		   SUMMARY DASHBOARD ("ສະຫຼຸບການລາຍງານ") — ໜ້າສະຫຼຸບ 4 ຖັນ
		   ============================================================ */
		.summary-wrap {
			background: #fff;
			border: 1px solid #dfe3e8;
			border-radius: 16px;
			overflow: hidden;
			box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
		}

		.summary-provinces {
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
			padding: 16px;
			background: #f8f9fa;
			border-bottom: 1px solid #e9ecef;
		}

		.summary-provinces .prov-btn {
			border: none;
			border-radius: 10px;
			padding: 10px 22px;
			font-weight: 600;
			font-family: "Phetsarath OT";
			background: #e9ecef;
			color: #325a8f;
			transition: all 0.2s ease;
		}

		.summary-provinces .prov-btn:hover {
			background: #dfe6ee;
		}

		.summary-provinces .prov-btn.active {
			background: linear-gradient(135deg, #0C2C55, #325a8f);
			color: #fff;
			box-shadow: 0 4px 12px rgba(12, 44, 85, 0.25);
		}

		.summary-columns {
			display: flex;
			flex-wrap: wrap;
		}

		.summary-col {
			flex: 1 1 260px;
			min-width: 260px;
			border-right: 1px solid #e9ecef;
			display: flex;
			flex-direction: column;
		}

		.summary-col:last-child {
			border-right: none;
		}

		.summary-col-header {
			background: #dde3ea;
			color: #0C2C55;
			text-align: center;
			font-weight: 700;
			padding: 12px 10px;
			letter-spacing: 0.5px;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			flex-wrap: wrap;
		}

		.summary-col-header .col-count-badge {
			background: linear-gradient(135deg, #0C2C55, #325a8f);
			color: #fff;
			font-size: 12px;
			font-weight: 700;
			letter-spacing: 0;
			padding: 2px 10px;
			border-radius: 999px;
			min-width: 26px;
			display: inline-block;
		}

		/* ===== Badge "ຈຳນວນອຸປະກອນ" (amount, SUM ຈາກ column ຈິງ) — ແຍກສີອອກຈາກ badge ຈຳນວນຄັ້ງ ===== */
		.summary-col-header .col-amount-badge {
			background: linear-gradient(135deg, #198754, #146c43);
			color: #fff;
			font-size: 12px;
			font-weight: 700;
			letter-spacing: 0;
			padding: 2px 10px;
			border-radius: 999px;
			min-width: 26px;
			display: inline-block;
		}

		.summary-col-header .badge-label {
			font-size: 10px;
			font-weight: 500;
			opacity: 0.85;
			margin-right: 2px;
		}

		.summary-col-title {
			text-align: center;
			font-weight: 600;
			color: #325a8f;
			padding: 12px 10px 6px;
			font-size: 14px;
		}

		.summary-col-search {
			padding: 0 12px 10px;
		}

		.summary-col-search input {
			width: 100%;
			border: 1px solid #dfe3e8;
			border-radius: 20px;
			padding: 6px 14px;
			font-size: 13px;
			font-family: "Phetsarath OT";
		}

		.summary-col-search input:focus {
			outline: none;
			border-color: #325a8f;
			box-shadow: 0 0 0 0.15rem rgba(50, 90, 143, 0.2);
		}

		.summary-col-listhead {
			display: flex;
			align-items: center;
			gap: 8px;
			padding: 6px 14px;
			font-size: 12px;
			font-weight: 700;
			color: #0C2C55;
			border-top: 1px solid #e9ecef;
			border-bottom: 1px solid #e9ecef;
			background: #f4f6f8;
		}

		.summary-col-listhead .h-img { width: 30px; }
		.summary-col-listhead .h-pr { width: 64px; }
		.summary-col-listhead .h-item { flex: 1; }
		.summary-col-listhead .h-date { width: 72px; text-align: center; }
		.summary-col-listhead .h-qty { width: 50px; text-align: right; }
		.summary-col-listhead .h-amount { width: 60px; text-align: right; }

		/* ===== ປຸ່ມສະຫຼັບແຜນ (planing_number) — ແຍກ ແຜນ1/ແຜນ2 ອອກຈາກກັນຢ່າງຈະແຈ້ງ ===== */
		.summary-plan-tabs {
			display: flex;
			gap: 8px;
			padding: 0 12px 10px;
		}

		.summary-plan-tabs .plan-tab {
			flex: 1;
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 1px;
			border: 1.5px solid #e0e0e0;
			background: #fff;
			border-radius: 10px;
			padding: 6px 8px;
			cursor: pointer;
			transition: all 0.2s ease;
			font-family: "Phetsarath OT";
		}

		.summary-plan-tabs .plan-tab .tab-label {
			font-size: 11px;
			font-weight: 700;
			color: #6c757d;
		}

		.summary-plan-tabs .plan-tab .tab-qty {
			font-size: 15px;
			font-weight: 800;
			color: #212529;
		}

		.summary-plan-tabs .plan-tab .tab-amount {
			font-size: 10px;
			font-weight: 600;
			color: #198754;
		}

		.summary-plan-tabs .plan-tab.active .tab-amount {
			color: #eafff3;
		}

		.summary-plan-tabs .plan-tab:hover {
			border-color: #325a8f;
			transform: translateY(-1px);
		}

		.summary-plan-tabs .plan-tab.active.plan-1 {
			background: linear-gradient(135deg, #198754, #146c43);
			border-color: #146c43;
			box-shadow: 0 3px 10px rgba(25, 135, 84, 0.25);
		}

		.summary-plan-tabs .plan-tab.active.plan-2 {
			background: linear-gradient(135deg, #d97706, #b45309);
			border-color: #b45309;
			box-shadow: 0 3px 10px rgba(217, 119, 6, 0.25);
		}

		.summary-plan-tabs .plan-tab.active.plan-3 {
			background: linear-gradient(135deg, #325a8f, #1f3d63);
			border-color: #1f3d63;
			box-shadow: 0 3px 10px rgba(50, 90, 143, 0.25);
		}

		.summary-plan-tabs .plan-tab.active .tab-label,
		.summary-plan-tabs .plan-tab.active .tab-qty {
			color: #fff;
		}

		.summary-plan-tabs .plan-tab:disabled {
			opacity: 0.4;
			cursor: not-allowed;
			transform: none;
		}

		.summary-col-list {
			max-height: 440px;
			overflow-y: auto;
			background: #eef1f4;
			flex: 1;
		}

		.summary-row {
			display: flex;
			align-items: center;
			gap: 8px;
			padding: 8px 14px;
			border-bottom: 1px solid #e0e4e8;
			font-size: 13px;
			background: #fff;
			margin: 6px 8px 0;
			border-radius: 8px;
			border: 1px solid #e6e9ec;
			transition: box-shadow 0.2s ease, transform 0.2s ease;
		}

		.summary-row:hover {
			box-shadow: 0 3px 10px rgba(12, 44, 85, 0.08);
			transform: translateY(-1px);
		}

		.summary-row img,
		.summary-row .r-img-fallback {
			width: 30px;
			height: 30px;
			border-radius: 6px;
			object-fit: cover;
			background: #f1f3f5;
			flex-shrink: 0;
		}

		.summary-row .r-img-fallback {
			display: flex;
			align-items: center;
			justify-content: center;
			color: #adb5bd;
			font-size: 14px;
		}

		.summary-row .r-pr {
			width: 64px;
			color: #325a8f;
			font-size: 12px;
			word-break: break-all;
		}

		.summary-row .r-item {
			flex: 1;
			color: #212529;
		}

		.summary-row .r-date {
			width: 72px;
			text-align: center;
			color: #6c757d;
			font-size: 11px;
			white-space: nowrap;
		}

		.summary-row .r-qty {
			width: 50px;
			text-align: right;
			font-weight: 700;
			color: #325a8f;
		}

		.summary-row .r-amount {
			width: 60px;
			text-align: right;
			font-weight: 700;
			color: #198754;
		}

		.summary-col-list .summary-empty,
		.summary-col-list .summary-loading {
			padding: 24px 10px;
			text-align: center;
			color: #adb5bd;
			font-size: 13px;
		}

		.summary-col-list .summary-empty i,
		.summary-col-list .summary-loading i {
			display: block;
			font-size: 22px;
			margin-bottom: 6px;
			color: #ced4da;
		}

		/* ===== Skeleton shimmer ຂະນະໂຫຼດຂໍ້ມູນ ===== */
		.summary-skeleton-row {
			display: flex;
			align-items: center;
			gap: 8px;
			padding: 8px 14px;
			margin: 6px 8px 0;
			border-radius: 8px;
			background: #fff;
			border: 1px solid #e6e9ec;
		}

		.summary-skeleton-row .sk-block {
			background: linear-gradient(90deg, #eef1f4 25%, #e2e6ea 37%, #eef1f4 63%);
			background-size: 400% 100%;
			animation: skeletonShimmer 1.4s ease infinite;
			border-radius: 6px;
		}

		.summary-skeleton-row .sk-img { width: 30px; height: 30px; flex-shrink: 0; }
		.summary-skeleton-row .sk-pr { width: 40px; height: 12px; }
		.summary-skeleton-row .sk-item { flex: 1; height: 12px; }
		.summary-skeleton-row .sk-date { width: 50px; height: 12px; }
		.summary-skeleton-row .sk-qty { width: 24px; height: 12px; }

		@keyframes skeletonShimmer {
			0% { background-position: 100% 50%; }
			100% { background-position: 0 50%; }
		}

		@media (max-width: 991.98px) {
			.summary-col {
				border-right: none;
				border-bottom: 1px solid #e9ecef;
			}
		}
	</style>

	<script type="text/javascript">
		$(document).ready(function() {
			if (typeof $.fn.select2 !== 'undefined') {
				$('.cust_id').select2({
					placeholder: "ເລືອກລາຍການ",
					allowClear: true,
					width: '100%'
				});
			}
		});
	</script>

	<?php
	// PHP 8: ໃຊ້ Null Coalescing Operator
	$proo = $_SESSION["Namepro"] ?? '';
	$userId = $_SESSION["iduser"] ?? '';

	// ກຳນົດລາຍການຜູ້ໃຊ້ທີ່ມີສິດ Admin
	$adminUsers = ['404', '30', '2', '194', '793', '213', '214'];
	$isAdmin = in_array($userId, $adminUsers);

	// ໃຊ້ Prepared Statement ປ້ອງກັນ SQL Injection
	if (!$isAdmin) {
		$sql = "SELECT COUNT(a.S_Status) as 'cccount' 
                FROM request a 
                WHERE a.S_Status IN ('1','2','3') AND a.Province = ?";
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
</head>

<body>
	<!-- Backdrop ສຳລັບ mobile/tablet -->
	<div id="sidebarBackdrop"></div>

	<div class="d-flex" id="wrapper">
		<!-- ===== Sidebar ສີຂຽວ ===== -->
		<div class="border-end" id="sidebar-wrapper">
			<?php include __DIR__ . '/includes/sidebar.php'; ?>
		</div>

		<!-- ===== Page content wrapper ===== -->
		<div id="page-content-wrapper">

			<!-- ===== Navbar ສີຂຽວ + Logout ===== -->
			<nav class="navbar navbar-expand-lg navbar-green border-bottom">
				<div class="container-fluid">
					<button class="btn btn-success" id="sidebarToggle" type="button">
						<i class="fa fa-eye-slash" aria-hidden="true"></i>
						<span class="toggle-label">&nbsp;Hide Menu</span>
					</button>

					<!-- ແທນທີ່ navbar-toggler-icon ດ້ວຍ Font Awesome -->
					<button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
						aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
						<!-- <i class="fas fa-bars text-white"></i> -->
						<!-- ຫຼື ໃຊ້ fa-solid -->
						<i class="fa-solid fa-bars text-white"></i>
					</button>

					<div class="collapse navbar-collapse" id="navbarSupportedContent">
						<ul class="navbar-nav ms-auto mt-2 mt-lg-0">

							<!-- ===== Dropdown ພາສາ ===== -->
							<li class="nav-item dropdown">
								<a class="nav-link text-white dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
									<i class="bi bi-translate fs-4"></i>
									<span class="ms-1 d-none d-lg-inline" id="currentLangText">ລາວ</span>
								</a>
								<ul class="dropdown-menu dropdown-menu-end shadow-sm animate-dropdown" aria-labelledby="languageDropdown">
									<li>
										<a class="dropdown-item lang-option active" href="#" data-lang="la" onclick="switchLanguage('la'); return false;">
											<span class="flag-icon">🇱🇦</span>
											<span class="lang-name">ພາສາລາວ</span>
											<i class="bi bi-check-circle-fill check-icon"></i>
										</a>
									</li>
									<li>
										<hr class="dropdown-divider">
									</li>
									<li>
										<a class="dropdown-item lang-option" href="#" data-lang="zh" onclick="switchLanguage('zh'); return false;">
											<span class="flag-icon">🇨🇳</span>
											<span class="lang-name">中文</span>
											<i class="bi bi-check-circle-fill check-icon"></i>
										</a>
									</li>
								</ul>
							</li>

							<!-- ===== Dropdown ຜູ້ໃຊ້ ===== -->
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

			<!-- ===== Page content ===== -->
			<div class="container-fluid px-4 py-3">

				<!-- ===== Tabs ===== -->
				<div class="tab-custom">
					<button class="tablinks active" onclick="openCity(event, 'Userstock')" id="defaultOpen">
						<i class="fa-duotone fa-arrows-rotate"></i>
						<strong data-translate="use_of_equipment">ການນຳໃຊ້ ອຸປະກອນ</strong>
					</button>
					<button class="tablinks" onclick="openCity(event, 'HubEqument')">
						<i class="fa-duotone fa-cubes-stacked"></i>
						<strong data-translate="receive_equipment">ການຮັບ ອຸປະກອນເຂົ້າສາງ</strong>
					</button>
					<button class="tablinks" onclick="openCity(event, 'BerkStock')">
						<i class="fa-duotone fa-cart-shopping"></i>
						<strong data-translate="withdraw_equipment">ເບິກ ອຸປະກອນ</strong>
					</button>
					<button class="tablinks" onclick="openCity(event, 'follow')">
						<i class="fa-duotone fa-location-crosshairs"></i>
						<strong data-translate="track_equipment">ຕິດຕາມ ອຸປະກອນ</strong>
					</button>
					<button class="tablinks" onclick="openCity(event, 'reportTotal')">
						<i class="fa-duotone fa-chart-pie"></i>
						<strong data-translate="report_summary">ສະຫຼຸບການລາຍງານ</strong>
					</button>
				</div>

				<!-- ===== Tab 1: ການນຳໃຊ້ອຸປະກອນ ===== -->
				<div id="Userstock" class="tabcontent-custom active">
					<div class="form-container">
						<form id="form1" name="form1" method="post" action="ReportUseStock.php" target="ReportUseStock">
							<div class="form-group-inline">
								<?php if (in_array($_SESSION["iduser"] ?? '', ['404', '30', '2', '194', '793', '213', '214'])): ?>
									<label>
										<strong><i class="fa-thin fa-list-dropdown"></i> <strong data-translate="warehouse">ສາງ</strong></strong>
										<select name="Pro" class="form-control">
											<option value="" data-translate="all_warehouses">ທັງໝົດສາງ</option>
											<?php
											$result = mysqli_query($conn, "SELECT DISTINCT usestock.Provinces from usestock;");
											while ($row = mysqli_fetch_assoc($result)) {
												echo '<option value="' . htmlspecialchars($row['Provinces']) . '">' . htmlspecialchars($row['Provinces']) . '</option>';
											}
											?>
										</select>
									</label>
								<?php endif; ?>

								<label>
									<strong data-translate="equipment_list">ລາຍການອຸປະກອນ</strong>
									<select class="cust_id related-post form-control" name="Equment" style="color:#198754; min-width:250px;">
										<option value="" data-translate="all">ທັງໝົດ</option>
										<?php
										$namePro = $_SESSION["Namepro"] ?? '';
										if (in_array($_SESSION["iduser"] ?? '', ['404', '30', '194', '2', '793', '213', '214'])) {
											$result = mysqli_query($conn, "SELECT DISTINCT Items from usestock;");
										} else {
											$result = mysqli_query($conn, "SELECT DISTINCT Items from usestock where usestock.Provinces like '$namePro';");
										}
										while ($row = mysqli_fetch_assoc($result)) {
											echo '<option value="' . htmlspecialchars($row['Items']) . '">' . htmlspecialchars($row['Items']) . '</option>';
										}
										?>
									</select>
								</label>

								<label>
									<strong><i class="fa-duotone fa-calendar-days"></i> <strong data-translate="from_date">ແຕ່ວັນທີ່</strong></strong>
									<input type="date" name="start" id="start" class="form-control" style="min-width:130px;" />
								</label>
								<label>
									<strong><i class="fa-duotone fa-calendar-days"></i> <strong data-translate="to_date">ຫາວັນທີ່</strong></strong>
									<input type="date" name="end" id="end" class="form-control" style="min-width:130px;" />
								</label>

								<label>
									<button type="submit" name="button" class="btn btn-primary" onclick="form1.action='ReportUseStock.php'; return true;">
										<i class="fa fa-search" aria-hidden="true"></i> ຄົ້ນຫາ
									</button>
								</label>
								<label>
									<button type="submit" name="buttonpro" id="buttonpro" onclick="form1.action='ReportUseStock.php';" class="btn btn-success">
										<i class="fa-sharp fa-solid fa-file-excel"></i>
									</button>
								</label>
								<label>
									<button type="submit" name="chartUser" id="chartUser" onclick="form1.action='ReportUseStock.php';" class="btn btn-secondary">
										<i class="fa-duotone fa-chart-mixed"></i>
									</button>
								</label>
							</div>
						</form>
					</div>

					<div align="center">
						<iframe class="circle" src="ReportUseStock.php" name="ReportUseStock" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
						</iframe>
					</div>
				</div>

				<!-- ===== Tab 2: ການຮັບອຸປະກອນເຂົ້າສາງ ===== -->
				<div id="HubEqument" class="tabcontent-custom">
					<div class="form-container">
						<form id="form2" name="form2" method="post" action="ReportHubStockData.php" target="ReportHubStockData">
							<div class="form-group-inline">
								<?php if (in_array($_SESSION["iduser"] ?? '', ['404', '30', '194', '793', '2', '213', '214'])): ?>
									<label>
										<strong><i class="fa-thin fa-list-dropdown"></i>ສາງ</strong>
										<select name="Pro" class="form-control">
											<option value="all">ທັງໝົດສາງ</option>
											<option value="Center">ສູນກາງ</option>
											<?php
											$result = mysqli_query($conn, "SELECT DISTINCT stock_provinceinput.Provinces from stock_provinceinput;");
											while ($row = mysqli_fetch_assoc($result)) {
												echo '<option value="' . htmlspecialchars($row['Provinces']) . '">' . htmlspecialchars($row['Provinces']) . '</option>';
											}
											?>
										</select>
									</label>
								<?php else: ?>
									<input type="hidden" name="Pro" value="<?= htmlspecialchars($_SESSION["Namepro"] ?? '') ?>" />
								<?php endif; ?>

								<label>
									<strong>ລາຍການຮັບອຸປະກອນ</strong>
									<select class="cust_id related-post form-control" name="Equment1" style="color:#198754; min-width:250px;">
										<option value="">ທັງໝົດ</option>
										<?php
										$namePro = $_SESSION["Namepro"] ?? '';
										if (in_array($_SESSION["iduser"] ?? '', ['404', '30', '194', '2', '793', '199', '208', '213', '214'])) {
											$result = mysqli_query($conn, "SELECT DISTINCT Items from stock;");
										} else {
											$result = mysqli_query($conn, "SELECT DISTINCT Items from stock_provinceinput where stock_provinceinput.Provinces like '$namePro';");
										}
										while ($row = mysqli_fetch_assoc($result)) {
											echo '<option value="' . htmlspecialchars($row['Items']) . '">' . htmlspecialchars($row['Items']) . '</option>';
										}
										?>
									</select>
								</label>

								<label>
									<strong><i class="fa-duotone fa-calendar-days"></i> ແຕ່ວັນທີ່</strong>
									<input type="date" name="start" id="start" class="form-control" />
								</label>
								<label>
									<strong><i class="fa-duotone fa-calendar-days"></i> ຫາວັນທີ່</strong>
									<input type="date" name="end" id="end" class="form-control" />
								</label>

								<label>
									<button type="submit" name="buttonhub" class="btn btn-primary" onclick="form2.action='ReportHubStockData.php'; return true;">
										<i class="fa fa-search" aria-hidden="true"></i> ຄົ້ນຫາ
									</button>
								</label>
								<label>
									<button type="submit" name="buttonproHUb" id="buttonproHUb" onclick="form2.action='ReportHubStockData.php';" class="btn btn-success">
										<i class="fa-sharp fa-solid fa-file-excel"></i>
									</button>
								</label>
								<label>
									<button type="submit" name="chartUserhub" id="chartUserhub" onclick="form2.action='ReportHubStockData.php';" class="btn btn-secondary">
										<i class="fa-duotone fa-chart-mixed"></i>
									</button>
								</label>
							</div>
						</form>
					</div>

					<div align="center">
						<iframe class="circle" src="ReportHubStockData.php" name="ReportHubStockData" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
						</iframe>
					</div>
				</div>

				<!-- ===== Tab 3: ເບິກອຸປະກອນ ===== -->
				<div id="BerkStock" class="tabcontent-custom">
					<div class="form-container">
						<form id="form33" name="form33" method="post" action="ReporberkData.php" target="ReporberkData">
							<div class="form-group-inline">
								<?php if (in_array($_SESSION["iduser"] ?? '', ['404', '30', '194', '793', '2', '213', '214'])): ?>
									<label>
										<strong><i class="fa-thin fa-list-dropdown"></i>ສາງ</strong>
										<select name="Pro" class="form-control">
											<option value="all">ທັງໝົດແຂວງ</option>
											<?php
											$result = mysqli_query($conn, "SELECT DISTINCT request.Province from request;");
											while ($row = mysqli_fetch_assoc($result)) {
												echo '<option value="' . htmlspecialchars($row['Province']) . '">' . htmlspecialchars($row['Province']) . '</option>';
											}
											?>
										</select>
									</label>
								<?php else: ?>
									<input type="hidden" name="Pro" value="<?= htmlspecialchars($_SESSION["Namepro"] ?? '') ?>" />
								<?php endif; ?>

								<label>
									<strong>ລາຍການອຸປະກອນເບີກ</strong>
									<select class="cust_id related-post form-control" name="Equment1" style="color:#198754; min-width:250px;">
										<option value="">ທັງໝົດ</option>
										<?php
										$namePro = $_SESSION["Namepro"] ?? '';
										if (in_array($_SESSION["iduser"] ?? '', ['404', '30', '194', '2', '793', '199', '208', '213', '214'])) {
											$result = mysqli_query($conn, "SELECT DISTINCT Items from request;");
										} else {
											$result = mysqli_query($conn, "SELECT DISTINCT Items from request where request.Province like '$namePro';");
										}
										while ($row = mysqli_fetch_assoc($result)) {
											echo '<option value="' . htmlspecialchars($row['Items']) . '">' . htmlspecialchars($row['Items']) . '</option>';
										}
										?>
									</select>
								</label>

								<label>
									<strong>ປະເພດວັນທີ່</strong>
									<select name="Choi" class="form-control">
										<option value="dateRe">ວັນທີ່ຂໍເບິກ</option>
										<option value="DateComfirm">ວັນທີ່ເບິກ</option>
									</select>
								</label>

								<label>
									<strong>ສະຖານະ</strong>
									<select name="ChoiRemark" class="form-control">
										<option value="">.....</option>
										<option value="2">ຍັງບໍ່ໄດ້ຮັບ</option>
										<option value="3">ໄດ້ຖືກຍົກເລີກ</option>
									</select>
								</label>

								<label>
									<strong><i class="fa-duotone fa-calendar-days"></i> ແຕ່ວັນທີ່</strong>
									<input type="date" name="start" id="start" class="form-control" />
								</label>
								<label>
									<strong><i class="fa-duotone fa-calendar-days"></i> ຫາວັນທີ່</strong>
									<input type="date" name="end" id="end" class="form-control" />
								</label>

								<label>
									<button type="submit" name="buttonberk" class="btn btn-primary" onclick="form33.action='ReporberkData.php'; return true;">
										<i class="fa fa-search" aria-hidden="true"></i> ຄົ້ນຫາ
									</button>
								</label>
								<label>
									<button type="submit" name="BerkStock" id="BerkStock" onclick="form33.action='ReporberkData.php'; return true;" class="btn btn-success">
										<i class="fa-sharp fa-solid fa-file-excel"></i>
									</button>
								</label>
							</div>
						</form>
					</div>

					<div align="center">
						<iframe src="ReporberkData.php" name="ReporberkData" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
						</iframe>
					</div>
				</div>

				<!-- ===== Tab 4: ຕິດຕາມອຸປະກອນ ===== -->
				<div id="follow" class="tabcontent-custom">
					<div class="form-container">
						<form id="form34" name="form34" method="post" action="reportFollow.php" target="reportFollow">
							<div class="form-group-inline">
								<?php if (in_array($_SESSION["iduser"] ?? '', ['404', '30', '194', '195', '793', '2', '213', '214'])): ?>
									<label>
										<strong><i class="fa-thin fa-list-dropdown"></i>ສາງ</strong>
										<select name="Pro" class="form-control">
											<option value="all">ທັງໝົດສາງ</option>
											<?php
											$result = mysqli_query($conn, "SELECT DISTINCT request.Province from request;");
											while ($row = mysqli_fetch_assoc($result)) {
												echo '<option value="' . htmlspecialchars($row['Province']) . '">' . htmlspecialchars($row['Province']) . '</option>';
											}
											?>
										</select>
									</label>
								<?php else: ?>
									<input type="hidden" name="Pro" value="<?= htmlspecialchars($_SESSION["Namepro"] ?? '') ?>" />
								<?php endif; ?>

								<label>
									<strong>ສະຖານະ</strong>
									<select name="Choi" class="form-control" required>
										<option value="">---ກະລຸນາເລືອກ---</option>
										<option value="ສ້ອມແປງ">ສ້ອມແປງ</option>
										<option value="ຍົກຍ້າຍ">ຍົກຍ້າຍ</option>
										<option value="ຖອນ">ຖອນ</option>
									</select>
								</label>

								<label>
									<strong>ສະຖານະ(<span style="color: red;">ສະເພາະຢືມເຄື່ອງ</span>)</strong>
									<select name="stt" class="form-control">
										<option value="all">---ທຸກສະຖານະ---</option>
										<option value="ໃຊ້ງານໄດ້">ໃຊ້ງານໄດ້</option>
										<option value="ຕາຍ">ຕາຍ</option>
									</select>
								</label>

								<label>
									<strong><i class="fa-duotone fa-calendar-days"></i> ແຕ່ວັນທີ່</strong>
									<input type="date" name="start" id="start" required class="form-control" />
								</label>
								<label>
									<strong><i class="fa-duotone fa-calendar-days"></i> ຫາວັນທີ່</strong>
									<input type="date" name="end" id="end" required class="form-control" />
								</label>

								<label>
									<button type="submit" name="buttonFollow" class="btn btn-primary" onclick="form34.action='reportFollow.php'; return true;">
										<i class="fa fa-search" aria-hidden="true"></i> ຄົ້ນຫາ
									</button>
								</label>
								<label>
									<button type="submit" name="FollowStock" id="FollowStock" onclick="form34.action='reportFollow.php'; return true;" class="btn btn-success">
										<i class="fa-sharp fa-solid fa-file-excel"></i>
									</button>
								</label>
							</div>
						</form>
					</div>

					<div align="center">
						<iframe src="reportFollow.php" name="reportFollow" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
						</iframe>
					</div>
				</div>

				<!-- ===== Tab 5: ສະຫຼຸບການລາຍງານ (Dashboard 4 ຖັນ) ===== -->
				<div id="reportTotal" class="tabcontent-custom">
					<div class="summary-wrap">

						<!-- ປຸ່ມເລືອກແຂວງ/ສາງ (ດຶງ dynamic ຈາກ request.Province) -->
						<div class="summary-provinces" id="summaryProvinces">
							<?php
							$provResult = mysqli_query($conn, "SELECT DISTINCT Province FROM request WHERE Province IS NOT NULL AND Province <> '' ORDER BY Province ASC;");
							$firstProv = true;
							while ($provRow = mysqli_fetch_assoc($provResult)) {
								$provName = $provRow['Province'];
								$activeClass = $firstProv ? ' active' : '';
								echo '<button type="button" class="prov-btn' . $activeClass . '" data-province="' . htmlspecialchars($provName) . '" onclick="loadSummary(this)">' . htmlspecialchars($provName) . '</button>';
								$firstProv = false;
							}
							?>
						</div>

						<!-- 4 ຖັນ: ຂໍເບິກ / ສັ່ງເຄື່ອງ / ຮັບເຄື່ອງ / ນຳໃຊ້ -->
						<div class="summary-columns">
							<?php
							$summaryColumns = [
								['key' => 'request', 'title' => 'ຂໍເບິກ',    'sub' => 'ຈຳນວນເບິກ (ຕາມ Item)'],
								['key' => 'order',   'title' => 'ສົ່ງເຄື່ອງ', 'sub' => 'ຈຳນວນສົ່ງ (ຕາມ Item)'],
								['key' => 'receive', 'title' => 'ຮັບເຄື່ອງ', 'sub' => 'ຈຳນວນຮັບເຄື່ອງ (ຕາມ Item)'],
								['key' => 'use',      'title' => 'ນຳໃຊ້',    'sub' => 'ຈຳນວນນຳໃຊ້ (ຕາມ Item)'],
							];
							foreach ($summaryColumns as $col):
							?>
								<div class="summary-col">
									<div class="summary-col-header">
										<span><?= htmlspecialchars($col['title']) ?></span>
										<span class="badge-label">ຄັ້ງ</span>
										<span class="col-count-badge" id="summaryCount_<?= $col['key'] ?>">–</span>
										<span class="badge-label">ອຸປະກອນ</span>
										<span class="col-amount-badge" id="summaryAmount_<?= $col['key'] ?>">–</span>
									</div>
									<div class="summary-col-title"><?= htmlspecialchars($col['sub']) ?></div>
									<div class="summary-col-search">
										<input type="text" placeholder="ຄົ້ນຫາ Item ຫຼື PR..." data-cat="<?= $col['key'] ?>" oninput="filterSummary(this)">
									</div>
									<div class="summary-plan-tabs" id="summaryPlanTabs_<?= $col['key'] ?>">
										<button type="button" class="plan-tab plan-1 active" data-cat="<?= $col['key'] ?>" data-plan="1" onclick="switchPlan(this)">
											<span class="tab-label">ແຜນ1</span>
											<span class="tab-qty">–</span>
											<span class="tab-amount">–</span>
										</button>
										<button type="button" class="plan-tab plan-2" data-cat="<?= $col['key'] ?>" data-plan="2" onclick="switchPlan(this)">
											<span class="tab-label">ແຜນ2</span>
											<span class="tab-qty">–</span>
											<span class="tab-amount">–</span>
										</button>
										<button type="button" class="plan-tab plan-3" data-cat="<?= $col['key'] ?>" data-plan="3" onclick="switchPlan(this)">
											<span class="tab-label">ແຜນ3</span>
											<span class="tab-qty">–</span>
											<span class="tab-amount">–</span>
										</button>
									</div>
									<div class="summary-col-listhead">
										<span class="h-img">ຮູບພາບ</span>
										<span class="h-pr">PR</span>
										<span class="h-item">Item</span>
										<span class="h-date">ວັນທີ່</span>
										<span class="h-qty">ຄັ້ງ</span>
										<span class="h-amount">ອຸປະກອນ</span>
									</div>
									<div class="summary-col-list" id="summaryList_<?= $col['key'] ?>">
										<div class="summary-loading">ກຳລັງໂຫຼດ...</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

				</div>

			</div>
		</div>
	</div>

	<script>
		function openCity(evt, cityName) {
			var i, tabcontent, tablinks;
			tabcontent = document.getElementsByClassName("tabcontent-custom");
			for (i = 0; i < tabcontent.length; i++) {
				tabcontent[i].classList.remove("active");
				tabcontent[i].style.display = "none";
			}
			tablinks = document.getElementsByClassName("tablinks");
			for (i = 0; i < tablinks.length; i++) {
				tablinks[i].className = tablinks[i].className.replace(" active", "");
			}
			document.getElementById(cityName).style.display = "block";
			document.getElementById(cityName).classList.add("active");
			evt.currentTarget.className += " active";
		}

		document.getElementById("defaultOpen").click();

		function resizeIframe(obj) {
			try {
				var h = obj.contentWindow.document.documentElement.scrollHeight;
				if (h && h > 0) {
					obj.style.height = h + 'px';
				}
			} catch (e) {}
		}

		function switchLanguage(lang) {
			if (typeof setLanguage === 'function') {
				setLanguage(lang);
			}
			document.querySelectorAll('.lang-option').forEach(function(el) {
				el.classList.remove('active');
			});
			document.querySelector('[data-lang="' + lang + '"]')?.classList.add('active');
			var langText = lang === 'la' ? 'ລາວ' : '中文';
			document.getElementById('currentLangText').textContent = langText;
			localStorage.setItem('site_lang', lang);
		}

		document.addEventListener('DOMContentLoaded', function() {
			var currentLang = localStorage.getItem('site_lang') || 'la';
			switchLanguage(currentLang);

			if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
				$('.cust_id').select2({
					placeholder: "ເລືອກລາຍການ",
					allowClear: true,
					width: '100%'
				});
			}
		});

		// ===== Toggle Sidebar (ຮອງຮັບທັງ desktop ແລະ mobile/tablet) =====
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

		// ປິດ sidebar ເມື່ອກົດທີ່ backdrop (mobile/tablet)
		sidebarBackdrop?.addEventListener('click', closeSidebar);

		// ປິດ sidebar ອັດຕະໂນມັດເມື່ອກົດເມນູ ຢູ່ mobile/tablet
		document.querySelectorAll('#sidebar-wrapper .list-group-item').forEach(function(item) {
			item.addEventListener('click', function() {
				if (!isDesktop()) {
					closeSidebar();
				}
			});
		});

		// ຖ້າປັບຂະໜາດຈໍຈາກ mobile -> desktop ໃຫ້ລ້າງ state ຂອງ mobile drawer
		window.addEventListener('resize', function() {
			if (isDesktop()) {
				closeSidebar();
			} else {
				wrapperEl.classList.remove('sidebar-hidden-desktop');
			}
		});

		// ============================================================
		// SUMMARY DASHBOARD ("ສະຫຼຸບການລາຍງານ")
		// ============================================================
		var summaryData = { request: [], order: [], receive: [], use: [] };
		var summaryCategories = ['request', 'order', 'receive', 'use'];
		// ແຜນທີ່ກຳລັງເລືອກຢູ່ໃນແຕ່ລະຖັນ (default = ແຜນ1)
		var summaryActivePlan = { request: '1', order: '1', receive: '1', use: '1' };

		// ແຜນ (planing_number) ຄາດຫວັງຄ່າ 'ແຜນ1' / 'ແຜນ2' / 'ແຜນ3' ຈາກ backend.
		function planKey(planLabel) {
			if (!planLabel) return '1';
			if (planLabel.indexOf('3') !== -1) return '3';
			if (planLabel.indexOf('2') !== -1) return '2';
			return '1';
		}

		function escapeHtml(str) {
			return String(str == null ? '' : str)
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;');
		}

		function summaryRowHtml(row) {
			var imgTag = row.image
				? '<img src="' + row.image + '" alt="" onerror="this.replaceWith(Object.assign(document.createElement(\'div\'),{className:\'r-img-fallback\',innerHTML:\'<i class=\\\'fa-solid fa-box\\\'></i>\'}))">'
				: '<div class="r-img-fallback"><i class="fa-solid fa-box"></i></div>';
			return '' +
				'<div class="summary-row">' +
					imgTag +
					'<span class="r-pr">' + escapeHtml(row.pr || '-') + '</span>' +
					'<span class="r-item">' + escapeHtml(row.item) + '</span>' +
					'<span class="r-date">' + escapeHtml(row.date || '-') + '</span>' +
					'<span class="r-qty" title="ຈຳນວນຄັ້ງ">' + row.qty + '</span>' +
					'<span class="r-amount" title="ຈຳນວນອຸປະກອນ">' + (row.amount != null ? row.amount : 0) + '</span>' +
				'</div>';
		}

		function skeletonHtml(n) {
			var one = '<div class="summary-skeleton-row">' +
				'<div class="sk-block sk-img"></div>' +
				'<div class="sk-block sk-pr"></div>' +
				'<div class="sk-block sk-item"></div>' +
				'<div class="sk-block sk-date"></div>' +
				'<div class="sk-block sk-qty"></div>' +
			'</div>';
			return new Array(n || 5).fill(one).join('');
		}

		function sumQty(rows) {
			return (rows || []).reduce(function(sum, r) { return sum + (parseInt(r.qty, 10) || 0); }, 0);
		}

		// ຈຳນວນອຸປະກອນຕົວຈິງລວມ (SUM ຈາກ column ຈິງ ຄື request.Unit / tacking.QTY / stock_provinceinput.Unit / usestock.Unit)
		function sumAmount(rows) {
			return (rows || []).reduce(function(sum, r) { return sum + (parseFloat(r.amount) || 0); }, 0);
		}

		function groupByPlan(rows) {
			var groups = { '1': [], '2': [], '3': [] };
			(rows || []).forEach(function(r) {
				groups[planKey(r.plan)].push(r);
			});
			return groups;
		}

		function currentSearchQuery(cat) {
			var input = document.querySelector('.summary-col-search input[data-cat="' + cat + '"]');
			return input ? input.value.trim().toLowerCase() : '';
		}

		function searchFilteredRows(cat) {
			var q = currentSearchQuery(cat);
			var rows = summaryData[cat] || [];
			if (!q) return rows;
			return rows.filter(function(r) {
				var itemMatch = (r.item || '').toLowerCase().indexOf(q) !== -1;
				var prMatch = (r.pr || '').toLowerCase().indexOf(q) !== -1;
				return itemMatch || prMatch;
			});
		}

		// ວາດ tab ແຜນ1/ແຜນ2/ແຜນ3 ພ້ອມຈຳນວນຄັ້ງ + ຈຳນວນອຸປະກອນ, ແລະ ຄືນ groups ທີ່ໃຊ້ຄິດໄລ່ແລ້ວ
		function renderPlanTabs(cat, groups) {
			var wrap = document.getElementById('summaryPlanTabs_' + cat);
			if (!wrap) return;

			['1', '2', '3'].forEach(function(pk) {
				var btn = wrap.querySelector('.plan-tab[data-plan="' + pk + '"]');
				if (!btn) return;
				var rows = groups[pk] || [];
				btn.querySelector('.tab-qty').textContent = rows.length ? sumQty(rows) : '0';
				var amountEl = btn.querySelector('.tab-amount');
				if (amountEl) amountEl.textContent = rows.length ? (sumAmount(rows) + ' ອັນ') : '';
				btn.disabled = rows.length === 0;
				btn.classList.toggle('active', summaryActivePlan[cat] === pk);
			});
		}

		function renderSummaryColumn(cat) {
			var el = document.getElementById('summaryList_' + cat);
			if (!el) return;

			var filteredRows = searchFilteredRows(cat);
			var groups = groupByPlan(filteredRows);

			// ອັບເດດ badge ລວມຢູ່ຫົວຖັນ: "ຄັ້ງ" (COUNT) ແລະ "ອຸປະກອນ" (SUM ຈາກ column ຈິງ),
			// ຄິດຈາກທຸກແຜນລວມກັນ ຕາມການຄົ້ນຫາປັດຈຸບັນ
			var totalBadge = document.getElementById('summaryCount_' + cat);
			if (totalBadge) totalBadge.textContent = sumQty(filteredRows);
			var amountBadge = document.getElementById('summaryAmount_' + cat);
			if (amountBadge) amountBadge.textContent = sumAmount(filteredRows);

			renderPlanTabs(cat, groups);

			// ຖ້າແຜນທີ່ເລືອກຢູ່ບໍ່ມີຂໍ້ມູນ ແຕ່ແຜນອື່ນມີ, ໃຫ້ສະຫຼັບໄປແຜນທຳອິດທີ່ມີຂໍ້ມູນອັດຕະໂນມັດ
			var activePlan = summaryActivePlan[cat];
			if (!groups[activePlan] || groups[activePlan].length === 0) {
				var fallbackPlan = ['1', '2', '3'].find(function(pk) {
					return groups[pk] && groups[pk].length > 0;
				});
				if (fallbackPlan) {
					summaryActivePlan[cat] = fallbackPlan;
					activePlan = fallbackPlan;
					renderPlanTabs(cat, groups);
				}
			}

			var rows = groups[activePlan] || [];
			if (rows.length === 0) {
				el.innerHTML = '<div class="summary-empty"><i class="fa-solid fa-inbox"></i>ບໍ່ມີຂໍ້ມູນ</div>';
				return;
			}

			el.innerHTML = rows.map(summaryRowHtml).join('');
		}

		function switchPlan(btn) {
			var cat = btn.getAttribute('data-cat');
			var plan = btn.getAttribute('data-plan');
			if (btn.disabled) return;
			summaryActivePlan[cat] = plan;
			renderSummaryColumn(cat);
		}

		function filterSummary(input) {
			var cat = input.getAttribute('data-cat');
			renderSummaryColumn(cat);
		}

		function loadSummary(btn) {
			var province = btn.getAttribute('data-province');

			document.querySelectorAll('#summaryProvinces .prov-btn').forEach(function(b) {
				b.classList.remove('active');
			});
			btn.classList.add('active');

			summaryCategories.forEach(function(cat) {
				var el = document.getElementById('summaryList_' + cat);
				if (el) el.innerHTML = skeletonHtml(5);
				var badge = document.getElementById('summaryCount_' + cat);
				if (badge) badge.textContent = '…';
				var amountBadge = document.getElementById('summaryAmount_' + cat);
				if (amountBadge) amountBadge.textContent = '…';
				summaryActivePlan[cat] = '1';
			});

			fetch('ReporDataTotalSummary.php?Pro=' + encodeURIComponent(province))
				.then(function(res) { return res.json(); })
				.then(function(data) {
					summaryCategories.forEach(function(cat) {
						summaryData[cat] = data[cat] || [];
						renderSummaryColumn(cat);
					});
				})
				.catch(function() {
					summaryCategories.forEach(function(cat) {
						var el = document.getElementById('summaryList_' + cat);
						if (el) el.innerHTML = '<div class="summary-empty"><i class="fa-solid fa-triangle-exclamation"></i>ບໍ່ສາມາດໂຫຼດຂໍ້ມູນໄດ້</div>';
						var badge = document.getElementById('summaryCount_' + cat);
						if (badge) badge.textContent = '!';
						var amountBadge = document.getElementById('summaryAmount_' + cat);
						if (amountBadge) amountBadge.textContent = '!';
					});
				});
		}

		document.addEventListener('DOMContentLoaded', function() {
			var firstProvBtn = document.querySelector('#summaryProvinces .prov-btn');
			if (firstProvBtn) {
				loadSummary(firstProvBtn); 
			}
		});
	</script>

	<!-- Core theme JS -->
	<script src="js/scripts.js"></script>

	<!-- Translate -->
	<script src="translate/lang.js"></script>
</body>

</html>