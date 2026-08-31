<?php
// =========================================================
// scan_receive.php
// ໜ້າສະແກນ QR ຫຼັງ login: ໃຫ້ scan (ຫຼືພິມເລກທີ່ເອກະສານເອງ) ເພື່ອດຶງ
// ລາຍການສິນຄ້າ "ຍັງບໍ່ທັນຮັບ" ຂອງ shipment ນັ້ນອອກມາ ແລ້ວແກ້ໄຂ/ອັບໂຫລດ
// ຮູບພາບແຕ່ລະລາຍການໄດ້ທັນທີ (ກ່ອນຈະໄປກົດ "ຮັບເຄື່ອງ" ຢູ່ receive_tacking.php)
//
// ໃຊ້ AJAX endpoints:
//   - get_pending_items.php   ດຶງລາຍການ
//   - upload_image.php        ອັບໂຫລດຮູບ (ໃຊ້ຮ່ວມກັບ receive_tacking.php ຢູ່ແລ້ວ)
//   - update_item_photo.php   ບັນທຶກ path ຮູບໃໝ່ໃສ່ລາຍການ
// =========================================================

@session_start();

$sessUser    = $_SESSION["user"]    ?? "";
$sessNamepro = $_SESSION["Namepro"] ?? "";
$sessIduser  = $_SESSION["iduser"]  ?? "";

if ($sessUser === "" or $sessNamepro === "" or $sessIduser === "") {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}

$myFactory = trim($_SESSION["factory"] ?? '');

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
    <title>ສະແກນ QR - ແກ້ໄຂຮູບພາບສິນຄ້າ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Noto Sans Lao', sans-serif;
            background-color: #f0f2f5;
        }

        #qr-reader {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
            border-radius: 12px;
            overflow: hidden;
        }

        #qr-reader video {
            border-radius: 12px;
        }

        .item-card {
            transition: box-shadow .15s ease;
        }

        .item-card:hover {
            box-shadow: 0 4px 14px rgba(0, 0, 0, .08);
        }

        .thumb-wrap {
            width: 90px;
            height: 90px;
            flex-shrink: 0;
        }

        @media screen and (max-width: 640px) {
            .thumb-wrap {
                width: 64px;
                height: 64px;
            }
        }
    </style>
</head>

<body class="p-4 md:p-8">

    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-emerald-900"><i class="fa-solid fa-qrcode"></i> ສະແກນ QR ແກ້ໄຂຮູບພາບ</h1>
                <p class="text-sm text-gray-500">ໂຮງງານ: <span class="font-bold text-emerald-800"><?php echo h($myFactory ?: '-'); ?></span> | ຜູ້ໃຊ້: <?php echo h($sessUser); ?></p>
            </div>
            <a href="inbox_tacking.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-md"><i class="fa-solid fa-arrow-left"></i> ກັບຄືນ</a>
        </div>

        <!-- ກ່ອງ scanner -->
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <div class="flex items-center justify-between mb-3">
                <p class="font-bold text-gray-700 text-sm"><i class="fa-solid fa-camera"></i> ສະແກນ QR ດ້ວຍກ້ອງ</p>
                <button id="toggleScannerBtn" onclick="toggleScanner()" class="bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold px-3 py-1.5 rounded-lg">
                    <i class="fa-solid fa-video"></i> ເປີດກ້ອງ
                </button>
            </div>
            <div id="qr-reader" class="hidden"></div>
            <p id="scanHint" class="text-xs text-gray-400 text-center mt-2">ກົດ "ເປີດກ້ອງ" ແລ້ວອະນຸຍາດໃຊ້ກ້ອງໃນມືຖື/ຄອມ ເພື່ອສະແກນ QR ຂອງເອກະສານ</p>
        </div>

        <!-- ພິມເລກທີ່ເອກະສານເອງ (ສຳຮອງ) -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <p class="font-bold text-gray-700 text-sm mb-2"><i class="fa-solid fa-keyboard"></i> ຫຼື ພິມ/ແປະເລກທີ່ເອກະສານເອງ</p>
            <div class="flex gap-2">
                <input type="text" id="manualDocNo" placeholder="ເຊັ່ນ: GR-2026-0001" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm" autocomplete="off">
                <button onclick="lookupManual()" class="bg-blue-700 hover:bg-blue-800 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-md whitespace-nowrap">
                    <i class="fa-solid fa-magnifying-glass"></i> ຄົ້ນຫາ
                </button>
            </div>
        </div>

        <!-- ຜົນການຄົ້ນຫາ -->
        <div id="resultBox"></div>
    </div>

    <script>
        let html5QrCode = null;
        let scannerRunning = false;
        const CAMERA_ELEMENT_ID = "qr-reader";

        function showAlert(icon, title, text) {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                confirmButtonColor: '#047857',
                confirmButtonText: 'ຕົກລົງ'
            });
        }

        // ---------------------------------------------------------
        // ດຶງເລກທີ່ເອກະສານອອກຈາກຂໍ້ຄວາມທີ່ scan ໄດ້
        // ຮອງຮັບ 2 ແບບ: (1) ຂໍ້ຄວາມທຳມະດາຄືເລກທີ່ເອກະສານ
        //               (2) ລິ້ງ URL ທີ່ມີ ?doc_no=... ຢູ່ໃນນັ້ນ
        // ---------------------------------------------------------
        function extractDocNo(text) {
            text = (text || '').trim();
            try {
                const u = new URL(text);
                const param = u.searchParams.get('doc_no');
                if (param) return param;
            } catch (e) {
                // ບໍ່ແມ່ນ URL, ໃຊ້ຄ່າຕົ້ນສະບັບ
            }
            return text;
        }

        function toggleScanner() {
            if (scannerRunning) {
                stopScanner();
            } else {
                startScanner();
            }
        }

        function startScanner() {
            document.getElementById('qr-reader').classList.remove('hidden');
            document.getElementById('scanHint').textContent = 'ກຳລັງເປີດກ້ອງ... ຈໍ່ QR ໃສ່ກ່ອງ';
            html5QrCode = new Html5Qrcode(CAMERA_ELEMENT_ID);
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            };
            Html5Qrcode.getCameras().then(cameras => {
                if (!cameras || !cameras.length) {
                    showAlert('error', 'ບໍ່ພົບກ້ອງ', 'ອຸປະກອນນີ້ບໍ່ມີກ້ອງ ຫຼື ບໍ່ໄດ້ອະນຸຍາດການໃຊ້ງານ');
                    document.getElementById('qr-reader').classList.add('hidden');
                    return;
                }
                // ພະຍາຍາມໃຊ້ກ້ອງຫຼັງກ່ອນ (environment) ຖ້າມີ
                const cameraId = cameras.find(c => /back|rear|environment/i.test(c.label))?.id || cameras[cameras.length - 1].id;
                html5QrCode.start(
                    cameraId,
                    config,
                    onScanSuccess
                ).then(() => {
                    scannerRunning = true;
                    document.getElementById('toggleScannerBtn').innerHTML = '<i class="fa-solid fa-video-slash"></i> ປິດກ້ອງ';
                }).catch(err => {
                    console.error(err);
                    showAlert('error', 'ເປີດກ້ອງບໍ່ໄດ້', 'ກະລຸນາອະນຸຍາດການໃຊ້ກ້ອງ ຫຼື ໃຊ້ຊ່ອງພິມເລກທີ່ເອກະສານແທນ');
                    document.getElementById('qr-reader').classList.add('hidden');
                });
            }).catch(err => {
                console.error(err);
                showAlert('error', 'ເປີດກ້ອງບໍ່ໄດ້', 'ກະລຸນາອະນຸຍາດການໃຊ້ກ້ອງ ຫຼື ໃຊ້ຊ່ອງພິມເລກທີ່ເອກະສານແທນ');
                document.getElementById('qr-reader').classList.add('hidden');
            });
        }

        function stopScanner() {
            if (html5QrCode && scannerRunning) {
                html5QrCode.stop().then(() => {
                    html5QrCode.clear();
                    scannerRunning = false;
                    document.getElementById('qr-reader').classList.add('hidden');
                    document.getElementById('toggleScannerBtn').innerHTML = '<i class="fa-solid fa-video"></i> ເປີດກ້ອງ';
                    document.getElementById('scanHint').textContent = 'ກົດ "ເປີດກ້ອງ" ແລ້ວອະນຸຍາດໃຊ້ກ້ອງໃນມືຖື/ຄອມ ເພື່ອສະແກນ QR ຂອງເອກະສານ';
                }).catch(() => {});
            }
        }

        function onScanSuccess(decodedText) {
            const docNo = extractDocNo(decodedText);
            stopScanner();
            document.getElementById('manualDocNo').value = docNo;
            loadItems(docNo);
        }

        function lookupManual() {
            const docNo = document.getElementById('manualDocNo').value.trim();
            if (!docNo) {
                showAlert('warning', 'ກະລຸນາປ້ອນເລກທີ່ເອກະສານ', '');
                return;
            }
            loadItems(docNo);
        }

        // ---------------------------------------------------------
        // ດຶງລາຍການ "ຍັງບໍ່ທັນຮັບ" ຂອງ doc_no ນີ້ ແລ້ວສະແດງຜົນ
        // ---------------------------------------------------------
        async function loadItems(docNo) {
            const box = document.getElementById('resultBox');
            box.innerHTML = '<div class="bg-white rounded-lg shadow p-6 text-center text-gray-400"><i class="fa-solid fa-spinner fa-spin"></i> ກຳລັງຄົ້ນຫາ...</div>';

            try {
                const res = await fetch('get_pending_items.php?doc_no=' + encodeURIComponent(docNo));
                const result = await res.json();

                if (result.status !== 'success') {
                    box.innerHTML = '<div class="bg-white rounded-lg shadow p-6 text-center text-red-500">' + escapeHtml(result.message || 'ບໍ່ພົບຂໍ້ມູນ') + '</div>';
                    return;
                }

                renderItems(result);
            } catch (err) {
                console.error(err);
                box.innerHTML = '<div class="bg-white rounded-lg shadow p-6 text-center text-red-500">ເຊື່ອມຕໍ່ບໍ່ໄດ້: ' + escapeHtml(err.message) + '</div>';
            }
        }

        function escapeHtml(s) {
            const d = document.createElement('div');
            d.textContent = s == null ? '' : String(s);
            return d.innerHTML;
        }

        function renderItems(result) {
            const box = document.getElementById('resultBox');
            let html = '';
            html += '<div class="bg-white rounded-lg shadow p-4 mb-3">';
            html += '  <div class="flex flex-wrap justify-between items-center gap-2">';
            html += '    <p class="font-bold text-emerald-900">ເອກະສານເລກທີ: <span class="bg-gray-100 px-2 py-0.5 rounded">' + escapeHtml(result.doc_no) + '</span></p>';
            html += '    <span class="text-xs px-3 py-1.5 rounded-lg border border-orange-200 bg-orange-50 text-orange-800 font-bold"><i class="fi fi-sr-clock-five"></i> ຍັງບໍ່ທັນຮັບ (' + result.items.length + ' ລາຍການ)</span>';
            html += '  </div>';
            html += '  <p class="text-xs text-gray-500 mt-1">ປາຍທາງ: ' + escapeHtml(result.factory_to) + ' &middot; ວັນທີ: ' + escapeHtml(result.doc_date) + '</p>';
            html += '</div>';

            html += '<div class="space-y-3">';
            result.items.forEach(function(it) {
                html += renderItemCard(it);
            });
            html += '</div>';

            html += '<div class="mt-4 text-center">';
            html += '  <a href="receive_tacking.php?doc_no=' + encodeURIComponent(result.doc_no) + '" class="inline-block bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-5 py-2.5 rounded-lg text-sm shadow-md">';
            html += '    <i class="fa-solid fa-clipboard-check"></i> ໄປໜ້າຮັບເຄື່ອງເອກະສານນີ້';
            html += '  </a>';
            html += '</div>';

            box.innerHTML = html;
        }

        function renderItemCard(it) {
            const photoHtml = it.photo
                ? '<img src="' + it.photo + '" class="w-full h-full object-cover" id="photo-' + it.id + '">'
                : '<div class="w-full h-full flex items-center justify-center text-gray-300 text-2xl" id="photo-' + it.id + '"><i class="fa-solid fa-image"></i></div>';

            let html = '';
            html += '<div class="item-card bg-white rounded-lg shadow p-3 flex gap-3 items-center" id="card-' + it.id + '">';
            html += '  <div class="thumb-wrap bg-gray-50 border rounded-lg overflow-hidden flex items-center justify-center">' + photoHtml + '</div>';
            html += '  <div class="flex-1 min-w-0">';
            html += '    <p class="font-bold text-gray-800 text-sm truncate">' + escapeHtml(it.item) + '</p>';
            html += '    <p class="text-xs text-gray-500">Barcode: ' + escapeHtml(it.barcode) + '</p>';
            html += '    <p class="text-xs text-gray-500">ຂະໜາດ: ' + escapeHtml(it.size) + ' &middot; ຈຳນວນ: ' + escapeHtml(it.qty) + ' ' + escapeHtml(it.unit) + ' &middot; ນ້ຳໜັກ: ' + escapeHtml(it.weight) + '</p>';
            html += '  </div>';
            html += '  <div class="shrink-0">';
            html += '    <input type="file" accept="image/*" capture="environment" class="hidden" id="file-' + it.id + '" onchange="uploadItemPhoto(' + it.id + ', this)">';
            html += '    <button onclick="document.getElementById(\'file-' + it.id + '\').click()" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-3 py-2 rounded-lg whitespace-nowrap">';
            html += '      <i class="fa-solid fa-camera-retro"></i> ແກ້ໄຂຮູບ';
            html += '    </button>';
            html += '  </div>';
            html += '</div>';
            return html;
        }

        // ---------------------------------------------------------
        // ອັບໂຫລດຮູບໃໝ່ (ໃຊ້ upload_image.php ຮ່ວມກັບ receive_tacking.php)
        // ແລ້ວບັນທຶກ path ໃສ່ລາຍການນີ້ຜ່ານ update_item_photo.php
        // ---------------------------------------------------------
        function uploadItemPhoto(itemId, input) {
            if (!input.files || !input.files[0]) return;

            const formData = new FormData();
            formData.append('image', input.files[0]);

            Swal.fire({
                title: 'ກຳລັງອັບໂຫລດຮູບ...',
                text: 'ກະລຸນາລໍຖ້າ',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('upload_image.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include'
                })
                .then(res => {
                    if (!res.ok) throw new Error('HTTP error! status: ' + res.status);
                    return res.json();
                })
                .then(result => {
                    if (result.status !== 'success') {
                        Swal.close();
                        showAlert('error', 'ອັບໂຫລດຮູບບໍ່ສຳເລັດ', result.message || '');
                        return;
                    }
                    // ບັນທຶກ path ໃສ່ລາຍການນີ້
                    return fetch('update_item_photo.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            credentials: 'include',
                            body: 'id=' + encodeURIComponent(itemId) + '&photo_path=' + encodeURIComponent(result.path)
                        })
                        .then(res2 => {
                            if (!res2.ok) throw new Error('HTTP error! status: ' + res2.status);
                            return res2.json();
                        })
                        .then(result2 => {
                            Swal.close();
                            if (result2.status === 'success') {
                                const target = document.getElementById('photo-' + itemId);
                                if (target && target.tagName === 'IMG') {
                                    target.src = result.path + '?t=' + Date.now();
                                } else if (target) {
                                    const img = document.createElement('img');
                                    img.className = 'w-full h-full object-cover';
                                    img.id = 'photo-' + itemId;
                                    img.src = result.path + '?t=' + Date.now();
                                    target.replaceWith(img);
                                }
                                showAlert('success', 'ບັນທຶກຮູບແລ້ວ', 'ອັບເດດຮູບພາບສິນຄ້າສຳເລັດ');
                            } else {
                                showAlert('error', 'ບັນທຶກບໍ່ສຳເລັດ', result2.message || '');
                            }
                        });
                })
                .catch(err => {
                    Swal.close();
                    console.error(err);
                    showAlert('error', 'ເກີດຂໍ້ຜິດພາດ', err.message);
                });
        }
    </script>
</body>

</html>