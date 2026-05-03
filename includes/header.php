<?php
/* includes/header.php — en-tête HTML commun */
require_once __DIR__ . '/../config.php';
requireLogin();
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($pageTitle) ?> — <?= APP_NAME ?></title>
  <!-- bootstrap 5 CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <!-- chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <!-- css personnalisé -->
  <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<div class="wrapper d-flex">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <main class="main-content flex-grow-1 p-4">
