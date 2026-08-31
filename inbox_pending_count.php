<?php
// =========================================================
// inbox_pending_count.php
// Helper function: ນັບຈຳນວນລາຍການ "ລໍຖ້າຮັບ" (ຍັງບໍ່ຮັບເຄື່ອງ) ຂອງໂຮງງານ
// ຂອງ user ທີ່ login ຢູ່ - ໃຊ້ສຳລັບສະແດງ badge ແຈ້ງເຕືອນ
//
// ວິທີໃຊ້: ໃນ Dasborad.php (ຫຼືໜ້າໃດກໍ່ຕາມທີ່ຢາກສະແດງ badge)
//   require_once __DIR__ . '/conn.php';
//   require_once __DIR__ . '/inbox_pending_count.php';
//   $pendingCount = getPendingTackingCount($conn, trim($_SESSION["factory"] ?? ''));
// =========================================================

if (!function_exists('getPendingTackingCount')) {
    /**
     * ນັບຈຳນວນເອກະສານ (Number_Bin ທີ່ບໍ່ຊ້ຳກັນ) ທີ່ຍັງບໍ່ໄດ້ຮັບ
     * ສະເພາະຂອງໂຮງງານທີ່ລະບຸ (Factory_To = $myFactory)
     *
     * @param mysqli $conn
     * @param string $myFactory ຄ່າ factory ຂອງ user ທີ່ login ຢູ່ (ຈາກ $_SESSION["factory"])
     * @return int ຈຳນວນລາຍການທີ່ລໍຖ້າຮັບ (0 ຖ້າ HQ ຫຼື ບໍ່ມີໂຮງງານ)
     */
    function getPendingTackingCount($conn, $myFactory)
    {
        $myFactory = trim((string)$myFactory);

        // HQ ບໍ່ມີ Inbox ຂອງໂຮງງານຕົນເອງ (ໃຊ້ report_tacking.php ແທນ)
        if ($myFactory === '' || strcasecmp($myFactory, 'HQ') === 0) {
            return 0;
        }

        $sql = "SELECT COUNT(DISTINCT Number_Bin) as cnt
                FROM tacking
                WHERE Factory_To = ? AND (Sig_Receiver = '' OR Sig_Receiver IS NULL)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param("s", $myFactory);
        $stmt->execute();

        $cnt = 0;
        $stmt->bind_result($cnt);
        $stmt->fetch();
        $stmt->close();

        return (int)$cnt;
    }
}