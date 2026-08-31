<?php
// =========================================================
// inbox_tacking.php 
// ໜ້າ Inbox ສະແດງລາຍການສົ່ງເຄື່ອງທີ່ສົ່ງມາຫາໂຮງງານຂອງ user ທີ່ login ຢູ່ (ລໍຖ້າຮັບ / ຮັບແລ້ວ)
// ກົດ "ຮັບເຄື່ອງ" / "ເບິ່ງ/ພິມ" ເພື່ອໄປໜ້າ receive_tacking.php (ເອກະສານເຕັມຮູບແບບ)
// ຂຽນແບບເຂົ້າກັນໄດ້ກັບ PHP ເກົ່າ (ບໍ່ໃຊ້ closure, [], ??, get_result())
// =========================================================

@session_start();

$sessUser    = $_SESSION["user"]    ?? "";
$sessNamepro = $_SESSION["Namepro"] ?? "";
$sessIduser  = $_SESSION["iduser"]  ?? "";

if ($sessUser === "" or $sessNamepro === "" or $sessIduser === "") {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}

// =========================================================
// ໂຮງງານຂອງ user ທີ່ login ຢູ່ ອ່ານໂດຍກົງຈາກ sod_users.factory (ຕັ້ງຄ່າຜ່ານ phpMyAdmin)
// =========================================================
$myFactory = trim($_SESSION["factory"] ?? '');

if (strcasecmp($myFactory, 'HQ') === 0) {
    // HQ ໃຊ້ report_tacking.php ສະຫຼຸບທຸກໂຮງງານແທນ, ບໍ່ໃຫ້ເຂົ້າ Inbox ຂອງໂຮງງານ
    echo "<script>window.location = 'report_tacking.php';</script>";
    exit;
}

require_once __DIR__ . '/includes/conn.php';

if ($myFactory === '') {
    // user ນີ້ຍັງບໍ່ໄດ້ຖືກກຳນົດໃຫ້ໂຮງງານໃດ -> ແຈ້ງເຕືອນ ແລະ ຢຸດ
?>
    <!DOCTYPE html>
    <html lang="lo">

    <head>
        <meta charset="UTF-8">
        <title>Inbox</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>

    <body class="p-8 bg-gray-100">
        <div class="max-w-lg mx-auto bg-white rounded-lg shadow p-6 text-center">
            <p class="text-red-600 font-bold mb-2">ບັນຊີ "<?php echo htmlspecialchars($_SESSION["user"], ENT_QUOTES, 'UTF-8'); ?>" ຍັງບໍ່ຖືກກຳນົດໃຫ້ໂຮງງານໃດ</p>
            <p class="text-sm text-gray-500">ກະລຸນາຕິດຕໍ່ admin ໃຫ້ຕັ້ງຄ່າ column "factory" ຂອງບັນຊີນີ້ໃນຕາຕະລາງ sod_users (ຄ່າຕ້ອງເປັນ HQ, Sanakham, ຫຼື MuangNan)</p>
            <a href="Dasborad.php" class="inline-block mt-4 bg-gray-600 text-white px-4 py-2 rounded"><i class="fa-solid fa-arrow-left"></i> ກັບຄືນໜ້າຫຼັກ</a>
        </div>
    </body>

    </html>
<?php
    exit;
}

// ---- ດຶງລາຍການທີ່ຍັງບໍ່ໄດ້ຮັບ (ລໍຖ້າຮັບ) ສະເພາະຂອງໂຮງງານຕົນເອງ ----
$sqlPending = "SELECT Number_Bin, MIN(ID) as MinID, `From`, Factory_To, Carrier,
                      Name_Sender, Department_HQ, Number_Nam_Sender, Total_Cost, Doc_Date,
                      Name_Receiver, Department_Receiver, Number_Nam_Receiver, OA,
                      COUNT(*) as ItemCount
               FROM tacking
               WHERE Factory_To = ? AND (Sig_Receiver = '' OR Sig_Receiver IS NULL)
               GROUP BY Number_Bin
               ORDER BY MinID DESC";

$stmtPending = $conn->prepare($sqlPending);
if (!$stmtPending) {
    die("ຕຽມ query ບໍ່ສຳເລັດ (pending): " . $conn->error);
}
$stmtPending->bind_param("s", $myFactory);
$stmtPending->execute();
$stmtPending->bind_result(
    $p_NumberBin,
    $p_MinID,
    $p_From,
    $p_FactoryTo,
    $p_Carrier,
    $p_NameSender,
    $p_DepartmentHQ,
    $p_NumberNamSender,
    $p_TotalCost,
    $p_DocDate,
    $p_NameReceiver,
    $p_DepartmentReceiver,
    $p_NumberNamReceiver,
    $p_OA,
    $p_ItemCount
);

$pendingRows = array();
while ($stmtPending->fetch()) {
    $pendingRows[] = array(
        "Number_Bin" => $p_NumberBin,
        "MinID" => $p_MinID,
        "From" => $p_From,
        "Factory_To" => $p_FactoryTo,
        "Carrier" => $p_Carrier,
        "Name_Sender" => $p_NameSender,
        "Department_HQ" => $p_DepartmentHQ,
        "Number_Nam_Sender" => $p_NumberNamSender,
        "Total_Cost" => $p_TotalCost,
        "Doc_Date" => $p_DocDate,
        "ItemCount" => $p_ItemCount,
        "Name_Receiver" => $p_NameReceiver,
        "Department_Receiver" => $p_DepartmentReceiver,
        "Number_Nam_Receiver" => $p_NumberNamReceiver,
        "OA" => $p_OA
    );
}
$stmtPending->close();

// ---- ດຶງລາຍການທີ່ຮັບແລ້ວ (ທັງໝົດ) ສະເພາະຂອງໂຮງງານຕົນເອງ ----
$sqlDone = "SELECT Number_Bin, MIN(ID) as MinID, `From`, Name_Sender, Name_Receiver, Doc_Date, OA, COUNT(*) as ItemCount
            FROM tacking
            WHERE Factory_To = ? AND Sig_Receiver <> '' AND Sig_Receiver IS NOT NULL
            GROUP BY Number_Bin
            ORDER BY MinID DESC";

$stmtDone = $conn->prepare($sqlDone);
if (!$stmtDone) {
    die("ຕຽມ query ບໍ່ສຳເລັດ (done): " . $conn->error);
}
$stmtDone->bind_param("s", $myFactory);
$stmtDone->execute();
$stmtDone->bind_result($d_NumberBin, $d_MinID, $d_From, $d_NameSender, $d_NameReceiver, $d_DocDate, $d_OA, $d_ItemCount);

$doneRows = array();
while ($stmtDone->fetch()) {
    $doneRows[] = array(
        "Number_Bin" => $d_NumberBin,
        "From" => $d_From,
        "Name_Sender" => $d_NameSender,
        "Name_Receiver" => $d_NameReceiver,
        "Doc_Date" => $d_DocDate,
        "OA" => $d_OA,
        "ItemCount" => $d_ItemCount
    );
}
$stmtDone->close();
$conn->close();

function h($val)
{
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="lo">

<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="image/favicons.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox - ລາຍການລໍຖ້າຮັບເຄື່ອງ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Noto Sans Lao', sans-serif;
            background-color: #f0f2f5;
        }

        table {
            font-size: 13px;
        }

        /* Responsive Styles */
        @media screen and (max-width: 1024px) {
            table {
                font-size: 12px;
            }

            .max-w-6xl {
                max-width: 100%;
            }
        }

        @media screen and (max-width: 768px) {
            body {
                padding: 4px !important;
            }

            .p-4 {
                padding: 8px !important;
            }

            .mb-6 {
                margin-bottom: 8px !important;
            }

            .mb-8 {
                margin-bottom: 12px !important;
            }

            /* ປັບຫົວຂໍ້ */
            .flex.justify-between.items-center {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 8px !important;
            }

            .flex.justify-between.items-center div {
                text-align: center !important;
            }

            .flex.justify-between.items-center a {
                text-align: center !important;
            }

            .text-2xl {
                font-size: 1.25rem !important;
            }

            .text-lg {
                font-size: 1rem !important;
            }

            .text-sm {
                font-size: 10px !important;
            }

            /* ປັບຕາຕະລາງ */
            .overflow-x-auto {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
            }

            table {
                font-size: 9px !important;
                min-width: 600px;
            }

            table th,
            table td {
                padding: 4px 4px !important;
                font-size: 8px !important;
                white-space: nowrap;
            }

            /* ປັບປຸ່ມກົດ */
            .inline-block.px-3.py-1\.5 {
                padding: 3px 6px !important;
                font-size: 7px !important;
            }

            .bg-gray-600.px-4.py-2 {
                padding: 6px 12px !important;
                font-size: 11px !important;
            }

            /* ປັບຊ່ອງຫວ່າງ */
            .p-6 {
                padding: 12px !important;
            }

            .gap-4 {
                gap: 4px !important;
            }

            .rounded-lg {
                border-radius: 4px !important;
            }

            .shadow {
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            }
        }

        @media screen and (max-width: 480px) {
            table {
                font-size: 7px !important;
                min-width: 500px;
            }

            table th,
            table td {
                padding: 2px 2px !important;
                font-size: 6px !important;
            }

            .inline-block.px-3.py-1\.5 {
                padding: 2px 4px !important;
                font-size: 6px !important;
            }

            .text-2xl {
                font-size: 1rem !important;
            }

            .text-lg {
                font-size: 0.875rem !important;
            }

            .bg-gray-600.px-4.py-2 {
                padding: 4px 8px !important;
                font-size: 9px !important;
            }

            .rounded-lg {
                border-radius: 3px !important;
            }

            .p-6 {
                padding: 8px !important;
            }

            .mb-2 {
                margin-bottom: 4px !important;
            }

            .text-xs {
                font-size: 8px !important;
            }
        }

        /* ປັບຮູບແບບ scrollbar */
        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* ປັບຫົວຂໍ້ຕາຕະລາງ */
        .table-header-pending {
            background-color: #c2410c !important;
            color: white !important;
        }

        .table-header-done {
            background-color: #064e3b !important;
            color: white !important;
        }

        /* ປັບສີສະຖານະ */
        .status-pending {
            color: #c2410c;
        }

        .status-done {
            color: #064e3b;
        }

        /* ບ່ອນຄົ້ນຫາ */
        .no-result-row td {
            text-align: center;
            color: #9ca3af;
            padding: 16px !important;
        }
    </style>
</head>

<body class="p-4 md:p-8">

    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6 no-print">
            <div>
                <h1 class="text-2xl font-bold text-emerald-900"><i class="fa-solid fa-inbox"></i> ລາຍການລໍຖ້າຮັບເຄື່ອງ</h1>
                <p class="text-sm text-gray-500">ໂຮງງານ: <span class="font-bold text-emerald-800"><?php echo h($myFactory); ?></span> | ຜູ້ໃຊ້: <?php echo h($_SESSION["user"]); ?></p>
            </div>
            <a href="Dasborad.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-md"><i class="fa-solid fa-arrow-left"></i> ກັບຄືນ</a>
        </div>

        <!-- ບ່ອນຄົ້ນຫາ -->
        <div class="bg-white rounded-lg shadow p-3 mb-6 no-print">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="searchBox"
                    placeholder="ຄົ້ນຫາ: ເລກທີ, ເລກ PR, ຕົ້ນທາງ, ຂົນສົ່ງ, ຜູ້ຝາກ, ຜູ້ຮັບ ..."
                    class="w-full pl-9 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600">
                <button type="button" id="clearSearch"
                    class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <!-- ລາຍການລໍຖ້າຮັບ -->
        <h2 class="text-lg font-bold text-orange-800 mb-2"><i class="fa-solid fa-clock"></i> ລໍຖ້າຮັບ (<span id="pendingCount"><?php echo count($pendingRows); ?></span>)</h2>
        <?php if (count($pendingRows) === 0) { ?>
            <div class="bg-white rounded-lg shadow p-6 text-center text-gray-400 mb-8" id="pendingEmpty">ບໍ່ມີລາຍການລໍຖ້າຮັບ</div>
        <?php } else { ?>
            <div class="bg-white rounded-lg shadow overflow-x-auto mb-8">
                <table class="w-full border-collapse" id="pendingTable">
                    <thead class="bg-orange-700 text-white">
                        <tr>
                            <th class="p-2 border border-orange-600 text-left">ເລກທີ</th>
                            <th class="p-2 border border-orange-600 text-left">ວັນທີ</th>
                            <th class="p-2 border border-orange-600 text-left">ຕົ້ນທາງ</th>
                            <th class="p-2 border border-orange-600 text-left">ຂົນສົ່ງ</th>
                            <th class="p-2 border border-orange-600 text-left">ຜູ້ຝາກ</th>
                            <th class="p-2 border border-orange-600 text-left">ຜູ້ຮັບ</th>
                            <th class="p-2 border border-orange-600 text-left">ເລກ PR</th>
                            <th class="p-2 border border-orange-600 text-right">ຈຳນວນ</th>
                            <th class="p-2 border border-orange-600 text-center">ຈັດການ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingRows as $idx => $row) {
                            $bg = ($idx % 2 === 0) ? 'bg-white' : 'bg-orange-50/40';
                            $searchKey = strtolower($row['Number_Bin'] . ' ' . $row['From'] . ' ' . $row['Carrier'] . ' ' . $row['Name_Sender'] . ' ' . $row['Name_Receiver'] . ' ' . $row['OA']);
                        ?>
                            <tr class="<?php echo $bg; ?> searchable-row" data-search="<?php echo h($searchKey); ?>">
                                <td class="p-2 border border-gray-200 font-bold text-orange-800"><?php echo h($row['Number_Bin']); ?></td>
                                <td class="p-2 border border-gray-200"><?php echo h($row['Doc_Date']); ?></td>
                                <td class="p-2 border border-gray-200"><?php echo h($row['From']); ?></td>
                                <td class="p-2 border border-gray-200"><?php echo h($row['Carrier']); ?></td>
                                <td class="p-2 border border-gray-200"><?php echo h($row['Name_Sender']); ?></td>
                                <td class="p-2 border border-gray-200 text-gray-500 italic"><?php echo h($row['Name_Receiver']); ?></td>
                                <td class="p-2 border border-gray-200 font-semibold text-orange-700"><?php echo h($row['OA']); ?></td>
                                <td class="p-2 border border-gray-200 text-right"><?php echo h($row['ItemCount']); ?></td>
                                <td class="p-2 border border-gray-200 text-center">
                                    <a href="receive_tacking.php?doc_no=<?php echo urlencode($row['Number_Bin']); ?>"
                                        class="inline-block bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-3 py-1.5 rounded text-xs shadow">
                                        <i class="fa-solid fa-check"></i> ຮັບເຄື່ອງ
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="no-result-row hidden" id="pendingNoResult">
                            <td colspan="9">ບໍ່ພົບຂໍ້ມູນທີ່ຄົ້ນຫາ</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php } ?>

        <!-- ລາຍການຮັບແລ້ວ -->
        <h2 class="text-lg font-bold text-emerald-800 mb-2"><i class="fa-solid fa-check-circle"></i> ຮັບແລ້ວ (<span id="doneCount"><?php echo count($doneRows); ?></span>)</h2>
        <?php if (count($doneRows) === 0) { ?>
            <div class="bg-white rounded-lg shadow p-6 text-center text-gray-400" id="doneEmpty">ຍັງບໍ່ມີລາຍການທີ່ຮັບແລ້ວ</div>
        <?php } else { ?>
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="w-full border-collapse" id="doneTable">
                    <thead class="bg-emerald-900 text-white">
                        <tr>
                            <th class="p-2 border border-emerald-800 text-left">ເລກທີ</th>
                            <th class="p-2 border border-emerald-800 text-left">ວັນທີ</th>
                            <th class="p-2 border border-emerald-800 text-left">ຕົ້ນທາງ</th>
                            <th class="p-2 border border-emerald-800 text-left">ຜູ້ຝາກ</th>
                            <th class="p-2 border border-emerald-800 text-left">ຜູ້ຮັບ</th>
                            <th class="p-2 border border-emerald-800 text-left">ເລກ PR</th>
                            <th class="p-2 border border-emerald-800 text-right">ຈຳນວນ</th>
                            <th class="p-2 border border-emerald-800 text-center">ຈັດການ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doneRows as $idx => $row) {
                            $bg = ($idx % 2 === 0) ? 'bg-white' : 'bg-gray-50';
                            $searchKey = strtolower($row['Number_Bin'] . ' ' . $row['From'] . ' ' . $row['Name_Sender'] . ' ' . $row['Name_Receiver'] . ' ' . $row['OA']);
                        ?>
                            <tr class="<?php echo $bg; ?> searchable-row" data-search="<?php echo h($searchKey); ?>">
                                <td class="p-2 border border-gray-200 font-bold text-emerald-800"><?php echo h($row['Number_Bin']); ?></td>
                                <td class="p-2 border border-gray-200"><?php echo h($row['Doc_Date']); ?></td>
                                <td class="p-2 border border-gray-200"><?php echo h($row['From']); ?></td>
                                <td class="p-2 border border-gray-200"><?php echo h($row['Name_Sender']); ?></td>
                                <td class="p-2 border border-gray-200 text-emerald-700 font-semibold"><?php echo h($row['Name_Receiver']); ?></td>
                                <td class="p-2 border border-gray-200 font-semibold text-emerald-800"><?php echo h($row['OA']); ?></td>
                                <td class="p-2 border border-gray-200 text-right"><?php echo h($row['ItemCount']); ?></td>
                                <td class="p-2 border border-gray-200 text-center">
                                    <a href="receive_tacking.php?doc_no=<?php echo urlencode($row['Number_Bin']); ?>"
                                        class="inline-block bg-gray-600 hover:bg-gray-700 text-white font-bold px-3 py-1.5 rounded text-xs shadow">
                                        <i class="fa-solid fa-eye"></i> ເບິ່ງ/ພິມ
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="no-result-row hidden" id="doneNoResult">
                            <td colspan="8">ບໍ່ພົບຂໍ້ມູນທີ່ຄົ້ນຫາ</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php } ?>

        <p class="text-xs text-gray-400 mt-4 no-print">ອັບເດດລ່າສຸດ: <?php echo date('d/m/Y H:i'); ?></p>
    </div>

    <script>
        (function () {
            var searchBox = document.getElementById('searchBox');
            var clearBtn = document.getElementById('clearSearch');
            var pendingRows = document.querySelectorAll('#pendingTable tbody tr.searchable-row');
            var doneRows = document.querySelectorAll('#doneTable tbody tr.searchable-row');
            var pendingNoResult = document.getElementById('pendingNoResult');
            var doneNoResult = document.getElementById('doneNoResult');
            var pendingCountEl = document.getElementById('pendingCount');
            var doneCountEl = document.getElementById('doneCount');

            function filterRows(rows, noResultEl, countEl, keyword) {
                var visibleCount = 0;
                rows.forEach(function (row) {
                    var text = row.getAttribute('data-search') || '';
                    var match = text.indexOf(keyword) !== -1;
                    row.classList.toggle('hidden', !match);
                    if (match) visibleCount++;
                });
                if (noResultEl) {
                    noResultEl.classList.toggle('hidden', !(keyword.length > 0 && visibleCount === 0));
                }
                if (countEl) {
                    countEl.textContent = keyword.length > 0 ? visibleCount : rows.length;
                }
            }

            function runFilter() {
                var keyword = searchBox.value.trim().toLowerCase();
                clearBtn.classList.toggle('hidden', keyword.length === 0);
                if (pendingRows.length > 0) filterRows(pendingRows, pendingNoResult, pendingCountEl, keyword);
                if (doneRows.length > 0) filterRows(doneRows, doneNoResult, doneCountEl, keyword);
            }

            if (searchBox) {
                searchBox.addEventListener('input', runFilter);
            }
            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    searchBox.value = '';
                    runFilter();
                    searchBox.focus();
                });
            }
        })();
    </script>
</body>

</html>