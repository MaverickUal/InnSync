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
    <title>InnSync | Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
    <style>
        .report-tab { cursor:pointer; padding:8px 20px; border-radius:20px; font-size:0.85rem; font-weight:600; border:2px solid #d8b78e; color:#4d4335; transition:all 0.15s; }
        .report-tab.active { background:#4d4335; color:#fff; border-color:#4d4335; }
        .summary-box { border-radius:10px; padding:14px 16px; }
        .status-pill { padding:2px 10px; border-radius:20px; font-size:0.72rem; font-weight:600; display:inline-block; }
        .pill-pending   { background:#fef3c7;color:#92400e; }
        .pill-confirmed { background:#d1fae5;color:#065f46; }
        .pill-cancelled { background:#fee2e2;color:#7f1d1d; }
        .pill-completed { background:#dbeafe;color:#1e40af; }
    </style>
</head>
<body>
<?php include "../../admin_menu.php"; ?>

<div class="page-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-0">Reports</h4><p class="mb-0">Booking and revenue reports by period</p></div>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
    </div>

    <!-- Period selector -->
    <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex gap-2">
                <span class="report-tab active" data-type="daily">Daily</span>
                <span class="report-tab" data-type="weekly">Weekly</span>
                <span class="report-tab" data-type="monthly">Monthly</span>
            </div>
            <input type="date" class="form-control form-control-sm" id="reportDate" style="max-width:180px;" value="<?= date('Y-m-d') ?>">
            <button class="btn btn-brown btn-sm" onclick="loadReport()"><i class="bi bi-search me-1"></i>Generate</button>
            <span class="text-muted small ms-auto" id="reportPeriodLabel"></span>
        </div>
    </div>

    <!-- Summary cards -->
    <div class="row g-3 mb-4" id="summaryCards">
        <div class="col-6 col-md-2">
            <div class="summary-box" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                <div class="small text-muted">Total Bookings</div>
                <div class="fw-bold fs-4 text-success" id="sumTotal">-</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="summary-box" style="background:#eff6ff;border:1px solid #bfdbfe;">
                <div class="small text-muted">Confirmed</div>
                <div class="fw-bold fs-4 text-primary" id="sumConfirmed">-</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="summary-box" style="background:#fff5f5;border:1px solid #fca5a5;">
                <div class="small text-muted">Cancelled</div>
                <div class="fw-bold fs-4 text-danger" id="sumCancelled">-</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="summary-box" style="background:#fff5f5;border:1px solid #fca5a5;">
                <div class="small text-muted">Total Refunded</div>
                <div class="fw-bold fs-4 text-danger" id="sumRefunded">-</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="summary-box" style="background:#fef3c7;border:1px solid #fcd34d;">
                <div class="small text-muted">Gross Revenue</div>
                <div class="fw-bold fs-4 text-warning" id="sumGross">-</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="summary-box" style="background:#faf6ef;border:1px solid #d8b78e;">
                <div class="small text-muted">Net Revenue</div>
                <div class="fw-bold fs-4" style="color:#4d4335;" id="sumRevenue">-</div>
            </div>
        </div>
    </div>

    <!-- Report table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0" style="color:#4d4335;"><i class="bi bi-table me-2"></i>Booking Details</h6>
            <span class="badge bg-secondary" id="rowCount">0 records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Guest</th>
                            <th>Room</th>
                            <th>Type</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Status</th>
                            <th>Down (₱)</th>
                            <th>Remaining (₱)</th>
                            <th>Total (₱)</th>
                            <th>Refund (₱)</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody id="reportBody">
                        <tr><td colspan="12" class="text-center py-5 text-muted">Select a period and click Generate.</td></tr>
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
