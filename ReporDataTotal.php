<?php
require_once __DIR__ . '/includes/conn.php';
@session_start();

if (($_SESSION["user"] ?? '') == '') {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}

date_default_timezone_set("Asia/Bangkok");

/**
 * ອະນຸຍາດແຕ່ຊື່ຄໍລໍາທີ່ຢູ່ໃນລາຍການນີ້ເທົ່ານັ້ນ ເພື່ອປ້ອງກັນ SQL Injection
 * ຜ່ານ column name ($Choi) ທີ່ຮັບມາຈາກ POST.
 */
function safe_date_column(string $col): string
{
    $allowed = ['dateRe', 'DateComfirm'];
    return in_array($col, $allowed, true) ? $col : 'dateRe';
}

/** ພິມສະຖານະຄໍາຮ້ອງ (S_Status) ເປັນ HTML */
function render_status(array $row): string
{
    switch ($row['S_Status']) {
        case '1':
            return 'ລໍຖ້າການເບິກ';
        case '4':
            return "<p style='color: green'>" . htmlspecialchars($row['Give']) . ' ' . htmlspecialchars($row['Type']) . '</p>';
        case '3':
            return "<p style='color: red'>ໄດ້ຖືກຍົກເລີກ</p>";
        case '2':
            return "<p style='color: red'>" . htmlspecialchars($row['Give']) . ' ' . htmlspecialchars($row['Type']) . '<br>ສາຂາຍັງບໍ່ໄດ້ຮັບ</p>';
        default:
            return '';
    }
}

/** ພິມ <head> ຮ່ວມກັນ (ໃຊ້ຊ້ຳກັນທັງສອງໂໝດ, ໜີການຄັດລອກ HTML) */
function render_head(bool $withExtraStyles = false): void
{
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="image/logoETL.jpg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .bs-example { margin: 20px; }
        body, td, th { font-family: "Phetsarath OT"; }
        .navbarr { position: fixed; }
        <?php if ($withExtraStyles): ?>
        .circle { border-radius: 14px; }
        .BG { background-image: linear-gradient(120deg, #89f7fe 0%, #66a6ff 100%); }
        <?php endif; ?>
    </style>
</head>
<body>
<?php
}

/* =========================================================
 *  ໂໝດ 1: ສົ່ງອອກ Excel ຕາຕະລາງເບີກອຸປະກອນ (BerkStock)
 * ========================================================= */
if (isset($_POST["BerkStock"])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=ເບີກ-ອຸປະກອນ.xls");

    render_head(false);

    $Pro         = $_POST["Pro"] ?? '';
    $start       = $_POST["start"] ?? '';
    $end         = $_POST["end"] ?? '';
    $Choi        = safe_date_column($_POST["Choi"] ?? '');
    $ChoiRemark  = $_POST["ChoiRemark"] ?? '';

    $startDT = $start . ' 00:00:00';
    $endDT   = $end . ' 23:59:59';

    // ສ້າງ SQL ດ້ວຍ prepared statement (whitelisted column name + bound values)
    $conditions = ["a.$Choi > ?", "a.$Choi < ?"];
    $params     = [$startDT, $endDT];
    $types      = 'ss';
    $orderBy    = "a.$Choi asc";

    if ($Pro !== 'all') {
        $conditions[] = "a.Province like ?";
        $params[]     = $Pro;
        $types       .= 's';
    } else {
        $orderBy = 'a.Province asc';
    }

    if ($ChoiRemark !== '') {
        $conditions[] = "a.S_Status = ?";
        $params[]     = $ChoiRemark;
        $types       .= 's';
    }

    $sql = "SELECT * FROM request a WHERE " . implode(' and ', $conditions) . " ORDER BY $orderBy";

    mysqli_set_charset($conn, "utf8");
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
?>
    <div align="center">
        <table class="table table-hover table-bordered table-sm" border="1">
            <thead>
                <tr class="text-body" bgcolor="#E8E8E8" align="center">
                    <th>ສາງ</th>
                    <th>ອຸປະກອນ</th>
                    <th>ວັນທີຂໍເບິກ</th>
                    <th>ວັນທີເບິກ</th>
                    <th>ຈຳນວນຂໍເບິກ</th>
                    <th>ຈຳນວນເບິກ</th>
                    <th>ຜູ້ເບີກເຄື່ອງ</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr style="font-size: 14px;">
                    <td width="10%" align="center"><?= htmlspecialchars($row['Province']) ?></td>
                    <td width="30%">
                        <strong>ອຸປະກອນ:</strong> <?= htmlspecialchars($row['Items']) ?><br>
                        <strong>ຂໍ້ມູນຮ້ານທີ່ຈັດຊື້:</strong> <?= htmlspecialchars($row['Vendor']) ?><br>
                        <strong>ພາກສ່ວນ:</strong> <?= htmlspecialchars($row['Section']) ?><br>
                        <strong>ໃຊ້ໃນກຸ່ມ:</strong> <?= htmlspecialchars($row['Groupp']) ?><br>
                        <b>ເລກທີ:</b> <?= htmlspecialchars($row['OA_Out']) ?>
                    </td>
                    <td width="10%" align="center"><?= htmlspecialchars($row['dateRe']) ?></td>
                    <td width="10%" align="center"><?= htmlspecialchars($row['DateComfirm']) ?></td>
                    <td width="10%" align="center" class="text-success"><strong><?= htmlspecialchars($row['Unit']) ?> <?= htmlspecialchars($row['Type']) ?></strong></td>
                    <td width="10%" align="center" class="text-primary"><strong><?= render_status($row) ?></strong></td>
                    <td width="20%" align="center"><?= htmlspecialchars($row['User_Confirm']) ?><br><strong>ໝາຍເຫດ:</strong> <?= htmlspecialchars($row['Remark']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php
    } else {
        echo "<center><font color='#ff0000'><h5>No Data</h5></font></center>";
    }
    $stmt->close();

/* =========================================================
 *  ໂໝດ 2: ຕາຕະລາງເບີກ + ໃຊ້ອຸປະກອນ (buttonberk)
 * ========================================================= */
} elseif (isset($_POST["buttonberk"])) {

    render_head(true);

    $Pro         = $_POST["Pro"] ?? '';
    $start       = $_POST["start"] ?? '';
    $end         = $_POST["end"] ?? '';
    $Choi        = safe_date_column($_POST["Choi"] ?? '');
    $ChoiRemark  = $_POST["ChoiRemark"] ?? '';
    $Equment1    = $_POST["Equment1"] ?? '';

    $startDT = $start . ' 00:00:00';
    $endDT   = $end . ' 23:59:59';

    mysqli_set_charset($conn, "utf8");

    if ($Equment1 !== '') {
        // ຮ່ວມກັບຕາຕະລາງ usestock ເມື່ອລະບຸອຸປະກອນສະເພາະ
        $sql = "SELECT a.*, u.*
                FROM request a
                INNER JOIN usestock u ON a.Items = u.Items
                WHERE a.$Choi > ? AND a.$Choi < ? AND a.Province LIKE ? AND a.Items = ?
                ORDER BY a.$Choi ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssss', $startDT, $endDT, $Pro, $Equment1);
    } else {
        $conditions = ["a.$Choi > ?", "a.$Choi < ?"];
        $params     = [$startDT, $endDT];
        $types      = 'ss';
        $orderBy    = "a.$Choi asc";

        if ($Pro !== 'all') {
            $conditions[] = "a.Province like ?";
            $params[]     = $Pro;
            $types       .= 's';
        } else {
            $orderBy = 'a.Province asc';
        }

        if ($ChoiRemark !== '') {
            $conditions[] = "a.S_Status = ?";
            $params[]     = $ChoiRemark;
            $types       .= 's';
        }

        $sql  = "SELECT * FROM request a WHERE " . implode(' and ', $conditions) . " ORDER BY $orderBy";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
?>
    <div align="center">
        <table class="table table-hover table-bordered table-sm">
            <thead>
                <tr align="center" class="text-dark BG">
                    <th>ສາງ</th>
                    <th>ອຸປະກອນ</th>
                    <th>ວັນທີຂໍເບິກ</th>
                    <th>ວັນທີເບິກ</th>
                    <th>ຈຳນວນຂໍເບິກ</th>
                    <th>ຈຳນວນຮັບ</th>
                    <th>ຜູ້ເບີກເຄື່ອງ</th>
                    <th>ຜູ້ຮັບແລະວັນທີຮັບເຄື່ອງ</th>
                    <th>ຈຳນວນທີ່ໃຊ້</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr style="font-size: 14px;">
                    <td width="10%" align="center"><?= htmlspecialchars($row['Province']) ?></td>
                    <td width="30%">
                        <strong>ອຸປະກອນ:</strong> <?= htmlspecialchars($row['Items']) ?><br>
                        <strong>ຂໍ້ມູນຮ້ານທີ່ຈັດຊື້:</strong> <?= htmlspecialchars($row['Vendor']) ?><br>
                        <strong>ພາກສ່ວນ:</strong> <?= htmlspecialchars($row['Section']) ?><br>
                        <strong>ໃຊ້ໃນກຸ່ມ:</strong> <?= htmlspecialchars($row['Groupp']) ?><br>
                        <b>ເລກທີ:</b> <?= htmlspecialchars($row['OA_Out']) ?>
                    </td>
                    <td width="5%" align="center"><?= htmlspecialchars($row['dateRe']) ?></td>
                    <td width="5%" align="center"><?= htmlspecialchars($row['DateComfirm']) ?></td>
                    <td width="10%" align="center" class="text-success"><strong><?= htmlspecialchars($row['Unit']) ?> <?= htmlspecialchars($row['Type']) ?></strong></td>
                    <td width="10%" align="center" class="text-primary"><strong><?= render_status($row) ?></strong></td>
                    <td width="10%" align="center"><?= htmlspecialchars($row['User_Confirm']) ?><br><?= htmlspecialchars($row['Remark']) ?></td>
                    <td width="10%" align="center"><?= htmlspecialchars($row['hub']) ?></td>
                    <td width="5%" align="center"><?= htmlspecialchars($row['Unit']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php
    } else {
        echo "<center><font color='#ff0000'><h5>No Data</h5></font></center>";
    }
    $stmt->close();
}
?>
</body>
</html>