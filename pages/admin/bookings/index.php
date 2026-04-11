<?php
include "../../../api/config.php";
if (!isset($_SESSION['user'])) header("LOCATION: ../../../");
if ($_SESSION['user']['role'] != 'admin') header("LOCATION: ../../home");
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InnSync | Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../css/public.css">
    <style>
        .status-pill { padding:3px 10px; border-radius:20px; font-size:0.75rem; font-weight:600; display:inline-block; }
        .pill-pending   { background:#fef3c7;color:#92400e; }
        .pill-confirmed { background:#d1fae5;color:#065f46; }
        .pill-cancelled { background:#fee2e2;color:#7f1d1d; }
        .pill-completed { background:#dbeafe;color:#1e40af; }
        .filter-tab { cursor:pointer; padding:5px 14px; border-radius:20px; font-size:0.8rem; font-weight:600; border:1.5px solid #d8b78e; color:#4d4335; transition:all 0.15s; display:inline-block; }
        .filter-tab.active { background:#4d4335; color:#fff; border-color:#4d4335; }
    </style>
</head>
<body>
<?php include "../../admin_menu.php"; ?>

<div class="page-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div><h4>Bookings</h4><p>Manage all reservations.</p></div>
        <div class="d-flex gap-2 align-items-center">
            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Search guest or room..." style="max-width:220px;">
            <span class="badge bg-secondary" id="bookingCount">0</span>
        </div>
    </div>

    <!-- Filter tabs -->
    <div class="d-flex gap-2 mb-3 flex-wrap">
        <span class="filter-tab active" data-filter="all">All</span>
        <span class="filter-tab" data-filter="pending">Pending</span>
        <span class="filter-tab" data-filter="confirmed">Confirmed</span>
        <span class="filter-tab" data-filter="cancelled">Cancelled</span>
        <span class="filter-tab" data-filter="completed">Completed</span>
    </div>

    <div class="card table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 small align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Guest</th>
                            <th>Room</th>
                            <th>Type</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bookingsList">
                        <tr><td colspan="10" class="text-center py-4 text-muted">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>
