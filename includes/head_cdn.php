<?php
/**
 * includes/head_cdn.php
 * -----------------------------------------------------------------
 * Shared <head> assets (CDN CSS/JS) used by pages that don't inline
 * their own vendor links. Include inside <head>:
 *
 *     <?php include __DIR__ . '/includes/head_cdn.php'; ?>
 *
 * Mirrors the vendor stack already inlined in stock.php / allstock.php.
 */
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="css/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
