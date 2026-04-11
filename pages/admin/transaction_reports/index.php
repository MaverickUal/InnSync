<?php
include "../../../api/config.php";
if (!isset($_SESSION['user'])) header("LOCATION: ../../../");
if ($_SESSION['user']['role'] != 'admin') header("LOCATION: ../../home");
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InnSync | Transaction Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
    <style>
        .table th { white-space: nowrap; font-size: .75rem; letter-spacing: .03em; }
        .table td { white-space: nowrap; font-size: .82rem; }
        .summary-pill {
            background: #f5f0ea;
            border: 1px solid #e0d0bc;
            border-radius: 10px;
            padding: .5rem 1.1rem;
            font-size: .85rem;
            color: #4d4335;
        }
        .summary-pill span { font-weight: 700; font-size: 1rem; color: #985b36; }
    </style>
</head>
<body>

<?php include "../../admin_menu.php"; ?>

<div class="page-wrapper">

    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div><h4>Transaction History</h4><p>Display transaction history and revenue summary.</p></div>
        <button class="btn btn-sm btn-outline-secondary" onclick="loadTransactions()">
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
        </button>
    </div>

    <!-- Summary Bar -->
    <div class="d-flex flex-wrap gap-3 mb-3 align-items-center" id="summaryBar">
        <div class="summary-pill">Transactions: <span id="sumCount">—</span></div>
        <div class="summary-pill">Gross Revenue: <span id="sumRevenue">—</span></div>
        <div class="summary-pill">Collected: <span id="sumCollected">—</span></div>
        <div class="summary-pill">Pending Balance: <span id="sumPending">—</span></div>
        <div class="summary-pill" style="border-color:#fca5a5;">Refunded: <span id="sumRefunded" style="color:#dc2626;">—</span></div>
        <div class="summary-pill" style="border-color:#bbf7d0;">Net Revenue: <span id="sumNet" style="color:#065f46;">—</span></div>
    </div>

    <div class="card table-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-receipt me-2" style="color:#985b36"></i>Transaction Reports</span>
        </div>
        <div class="card-body p-0" id="reportBody">
            <div class="text-center py-5 text-muted">
                <div class="spinner-border" style="color:#985b36"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>