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
    <title>InnSync | Payments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
    <style>
        .status-pill { padding:3px 10px; border-radius:20px; font-size:0.75rem; font-weight:600; display:inline-block; }
        .pill-pending   { background:#fef3c7;color:#92400e; }
        .pill-confirmed { background:#d1fae5;color:#065f46; }
        .pill-cancelled { background:#fee2e2;color:#7f1d1d; }
        .pill-completed { background:#dbeafe;color:#1e40af; }
        .pill-refunded  { background:#f3e8ff;color:#6b21a8; }
    </style>
</head>
<body>
<?php include "../../admin_menu.php"; ?>

<div class="page-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div><h4>Payments</h4><p>All payment records and refund management.</p></div>
        <div class="d-flex gap-2 align-items-center">
            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Search guest or room..." style="max-width:220px;">
            <select class="form-select form-select-sm" id="filterStatus" style="max-width:160px;">
                <option value="">All Bookings</option>
                <option value="confirmed">Confirmed</option>
                <option value="pending">Pending</option>
                <option value="cancelled">Cancelled</option>
                <option value="completed">Completed</option>
            </select>
            <span class="badge bg-secondary" id="payCount">0</span>
        </div>
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
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Booking</th>
                            <th>Total</th>
                            <th>Downpayment</th>
                            <th>Balance</th>
                            <th>Type</th>
                            <th>Refund</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="paymentsList">
                        <tr><td colspan="12" class="text-center py-4 text-muted">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Refund Confirm Modal -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header" style="background:#4d4335;color:white;border:none">
                <h6 class="modal-title mb-0">Process Refund</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body small" id="refundModalBody"></div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger fw-semibold" id="refundConfirmBtn">Process Refund</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>
