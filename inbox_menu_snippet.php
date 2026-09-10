<?php
// =========================================================
// inbox_menu_snippet.php
// ຕົວຢ່າງ: ວິທີເອົາ badge ຈຳນວນ "ລໍຖ້າຮັບ" ໄປໃສ່ໃນເມນູ Inbox ຂອງ Dasborad.php
//
// ວິທີໃຊ້:
//  1. ຄັດລອກ 2 ແຖວ require_once ຂ້າງລຸ່ມນີ້ ໄປວາງໄວ້ຫົວ Dasborad.php
//     (ຫຼັງຈາກ session_start() ແລະ conn.php ຖືກ include ແລ້ວ)
//  2. ຄັດລອກ block <a href="inbox_tacking.php" ...> ຂ້າງລຸ່ມ ໄປແທນທີ່
//     ລິ້ງ/ໄອຄອນ Inbox ເກົ່າທີ່ມີຢູ່ໃນ Dasborad.php ຂອງທ່ານ
// =========================================================

require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/inbox_pending_count.php';

$pendingCount = getPendingTackingCount($conn, trim($_SESSION["factory"] ?? ''));
?>

<!-- ==========================================================
     ຕົວຢ່າງ Inbox Menu Item ພ້ອມ Badge ແຈ້ງເຕືອນ
     (ປັບ class / style ໃຫ້ເຂົ້າກັບ layout ຂອງ Dasborad.php ຈິງໄດ້)
     ========================================================== -->
<a href="inbox_tacking.php"
   style="
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #1e293b;
        color: #fff;
        text-decoration: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: bold;
        font-size: 14px;
   ">
    <i class="fa-solid fa-inbox"></i>
    <span>Inbox</span>

    <?php if ($pendingCount > 0) { ?>
        <span style="
            position: absolute;
            top: -8px;
            right: -10px;
            background: #f59e0b;
            color: #ffffff;
            font-weight: 700;
            font-size: 11px;
            min-width: 20px;
            height: 20px;
            line-height: 20px;
            border-radius: 10px;
            text-align: center;
            padding: 0 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.35);
        "><?php echo $pendingCount > 99 ? '99+' : $pendingCount; ?></span>
    <?php } ?>
</a>

<!-- ==========================================================
     ຕົວຢ່າງເພີ່ມເຕີມ: ຖ້າ Dasborad.php ໃຊ້ Tailwind (ຄືກັນກັບ inbox_tacking.php)
     ໃຫ້ໃຊ້ version ນີ້ແທນ (ໃຊ້ class ແທນ inline style)
     ========================================================== -->
<a href="inbox_tacking.php" class="relative inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-md">
    <i class="fa-solid fa-inbox"></i>
    <span>Inbox</span>

    <?php if ($pendingCount > 0) { ?>
        <span class="absolute -top-2 -right-2.5 bg-amber-500 text-white text-[11px] font-bold min-w-[20px] h-5 leading-5 rounded-full text-center px-1 shadow">
            <?php echo $pendingCount > 99 ? '99+' : $pendingCount; ?>
        </span>
    <?php } ?>
</a>