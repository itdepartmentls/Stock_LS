<?php

/**
 * includes/roles.php
 * -----------------------------------------------------------------
 * ຈຸດດຽວ (single source of truth) ທີ່ນິຍາມ "ໃຜມີສິດຫຍັງ" ໃນລະບົບ.
 *
 * ກ່ອນມີໄຟລ໌ນີ້ ລາຍຊື່ user ID ຖືກ hardcode ຊ້ຳກັນຢູ່ຫຼາຍໜ້າ
 * (sidebar.php, Dasborad.php, DataReAdmin.php, DataReAdmin_Sale.php,
 * stock.php, ...) ເຮັດໃຫ້ເວລາເພີ່ມ user ໃໝ່ ຕ້ອງໄລ່ແກ້ທຸກໄຟລ໌
 * ແລະ ລືມບາງບ່ອນໄດ້ງ່າຍ.
 *
 * ວິທີໃຊ້:
 *   require_once __DIR__ . '/roles.php';   // (path ອາດເປັນ '/../includes/roles.php' ຈາກ pages/)
 *   if (is_stock_admin($_SESSION['iduser'] ?? '')) { ... }
 *   if (is_purchaser($_SESSION['iduser'] ?? ''))  { ... }
 *
 * ເພີ່ມ / ຖອນ user:  ແກ້ array ຂ້າງລຸ່ມນີ້ບ່ອນດຽວ ຈົບ.
 *
 * ໝາຍເຫດ: ຄ່າ $_SESSION['iduser'] ເປັນ string ('215') ບາງເທື່ອກໍ່ int (215)
 *          ຂຶ້ນກັບບ່ອນ set. function ຂ້າງລຸ່ມ cast ເປັນ string ໃຫ້ກ່ອນສະເໝີ.
 * -----------------------------------------------------------------
 */

// ===========================================================
// admin ສາງ — ເຫັນຄຳຮ້ອງເບີກ (request) ຂອງ "ທຸກແຂວງ" ບໍ່ຈຳກັດແຂວງຕົນເອງ,
// ແລະ badge "ຂໍເບີກອຸປະກອນ" ໃນ sidebar ນັບແບບ S_Status = '1' ທັງໝົດ.
// (ເດີມແມ່ນລາຍຊື່ໃນ includes/sidebar.php)
// ===========================================================
const STOCK_ADMIN_IDS = ['404', '30', '2', '194', '793', '213', '214', '215'];

// ===========================================================
// ຝ່າຍຈັດຊື້ (ເງິນແກ້ວ ...) — ໃນໜ້າ "ຂໍເບີກອຸປະກອນ" (Dasborad.php)
// ໃຫ້ໂຫຼດ DataReAdmin_Sale.php (ໜ້າບັນທຶກຂໍ້ມູນການຈັດຊື້ຕົວຈິງ:
// Price / Currency / Purchase_Shop / Reference_Doc) ແທນ DataReAdmin.php ປົກກະຕິ.
// ===========================================================
const PURCHASER_IDS = ['215'];

/**
 * ຜູ້ໃຊ້ນີ້ເປັນ admin ສາງ ບໍ່?
 */
function is_stock_admin($userId): bool
{
    return in_array((string) $userId, STOCK_ADMIN_IDS, true);
}

/**
 * ຜູ້ໃຊ້ນີ້ເປັນຝ່າຍຈັດຊື້ ບໍ່? (ໃຊ້ໜ້າ DataReAdmin_Sale.php)
 */
function is_purchaser($userId): bool
{
    return in_array((string) $userId, PURCHASER_IDS, true);
}
