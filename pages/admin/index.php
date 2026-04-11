<?php
include "../../api/config.php";
if (!isset($_SESSION['user'])) header("LOCATION: ../../");
if ($_SESSION['user']['role'] != 'admin') header("LOCATION: ../home");
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InnSync | Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
    <style>
        .stat-card { border-radius:12px;padding:1.1rem 1.2rem;color:#fff;position:relative;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.10);min-height:90px;display:flex;flex-direction:column;justify-content:space-between; }
        .stat-card .stat-value { font-size:1.7rem;font-weight:800;line-height:1; }
        .stat-card .stat-label { font-size:0.78rem;opacity:.88;margin-top:2px; }
        .stat-card .stat-icon  { font-size:2.2rem;opacity:.18;position:absolute;right:12px;bottom:6px; }
        .room-badge { display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:0.75rem;font-weight:600; }
        .room-badge.available   { background:#d1fae5;color:#065f46; }
        .room-badge.occupied    { background:#fee2e2;color:#7f1d1d; }
        .room-badge.maintenance { background:#fef3c7;color:#78350f; }
        .room-badge.unavailable { background:#e5e7eb;color:#374151; }
        .room-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px; }
        .room-card { border-radius:10px;border:1.5px solid #e5e7eb;padding:12px 14px;background:#fff;transition:box-shadow 0.15s; }
        .room-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.10); }
        .room-card.is-occupied    { border-color:#fca5a5;background:#fff5f5; }
        .room-card.is-maintenance { border-color:#fcd34d;background:#fffbeb; }
        .room-card.is-unavailable { border-color:#d1d5db;background:#f9fafb; }
        .room-card .room-number { font-weight:700;font-size:0.93rem;color:#4d4335; }
        .room-card .room-type   { font-size:0.73rem;color:#9ca3af; }
        .section-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:12px; }
        .section-header h6 { font-weight:700;color:#4d4335;margin:0; }
        .status-pill { padding:2px 10px;border-radius:20px;font-size:0.72rem;font-weight:600;display:inline-block; }
        .pill-pending   { background:#fef3c7;color:#92400e; }
        .pill-confirmed { background:#d1fae5;color:#065f46; }
        .pill-cancelled { background:#fee2e2;color:#7f1d1d; }
        .pill-completed { background:#dbeafe;color:#1e40af; }
        .occ-bar  { height:10px;border-radius:6px;background:#e5e7eb;overflow:hidden;margin-top:6px; }
        .occ-fill { height:100%;border-radius:6px;transition:width 0.6s ease; }
        .tab-filter .btn { font-size:0.8rem;border-radius:20px;padding:4px 14px; }
    </style>
</head>
<body>

<?php include "../admin_menu.php"; ?>

<div class="page-wrapper">

    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-0">Dashboard</h4><p class="mb-0">Overview of rooms, bookings, and revenue</p></div>
        <div class="text-muted small"><i class="bi bi-clock me-1"></i><span id="dashClock"></span></div>
    </div>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card stat-card" style="background:#1a6fc4;">
                <div><div class="stat-value" id="statUsers">-</div><div class="stat-label">Total Users</div></div>
                <i class="bi bi-people stat-icon"></i>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card stat-card" style="background:#f59e0b;">
                <div><div class="stat-value" id="statApprovedUsers">-</div><div class="stat-label">Active Users</div></div>
                <i class="bi bi-person-check stat-icon"></i>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card stat-card" style="background:#10b981;">
                <div><div class="stat-value" id="statAvailable">-</div><div class="stat-label">Available Rooms</div></div>
                <i class="bi bi-door-open stat-icon"></i>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card stat-card" style="background:#ef4444;">
                <div><div class="stat-value" id="statOccupied">-</div><div class="stat-label">Occupied Today</div></div>
                <i class="bi bi-person-fill stat-icon"></i>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card stat-card" style="background:#8b5cf6;">
                <div><div class="stat-value" id="statBookings">-</div><div class="stat-label">Total Bookings</div></div>
                <i class="bi bi-calendar-check stat-icon"></i>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card stat-card" style="background:#4d4335;">
                <div><div class="stat-value" id="statRevenue">-</div><div class="stat-label">Revenue Collected</div></div>
                <i class="bi bi-cash-stack stat-icon"></i>
            </div>
        </div>
    </div>

    <!-- OCCUPANCY BAR -->
    <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
        <div class="section-header">
            <h6><i class="bi bi-bar-chart-fill me-2 text-warning"></i>Room Occupancy</h6>
            <span class="text-muted small" id="occupancyLabel">Loading...</span>
        </div>
        <div class="occ-bar">
            <div class="occ-fill" id="occupancyFill" style="width:0%;background:#ef4444;"></div>
        </div>
        <div class="d-flex gap-3 mt-2 flex-wrap">
            <span class="room-badge available"><i class="bi bi-circle-fill" style="font-size:8px"></i> Available: <strong id="occ-avail">-</strong></span>
            <span class="room-badge occupied"><i class="bi bi-circle-fill" style="font-size:8px"></i> Occupied: <strong id="occ-occ">-</strong></span>
            <span class="room-badge maintenance"><i class="bi bi-circle-fill" style="font-size:8px"></i> Maintenance: <strong id="occ-maint">-</strong></span>
            <span class="room-badge unavailable"><i class="bi bi-circle-fill" style="font-size:8px"></i> Unavailable: <strong id="occ-unavail">-</strong></span>
        </div>
    </div>

    <!-- ROOM STATUS GRID -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-0 pt-3 pb-2">
            <div class="section-header mb-0">
                <h6 class="mb-0"><i class="bi bi-grid-fill me-2 text-primary"></i>All Room Status</h6>
                <div class="tab-filter d-flex gap-1 flex-wrap" id="roomFilter">
                    <button class="btn btn-sm btn-brown active" data-filter="all">All</button>
                    <button class="btn btn-sm btn-outline-success" data-filter="available">Available</button>
                    <button class="btn btn-sm btn-outline-danger" data-filter="occupied">Occupied</button>
                    <button class="btn btn-sm btn-outline-warning" data-filter="maintenance">Maintenance</button>
                </div>
            </div>
        </div>
        <div class="card-body pt-2">
            <div class="room-grid" id="roomGrid">
                <div class="text-muted text-center py-4 w-100">Loading rooms...</div>
            </div>
        </div>
    </div>

    <!-- PAYMENT OVERVIEW -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="fw-bold mb-0" style="color:#4d4335;"><i class="bi bi-credit-card-fill me-2 text-success"></i>Payment Overview</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-lg-3">
                            <div class="p-3 rounded-3" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                <div class="small text-muted mb-1">Downpayments Collected</div>
                                <div class="fw-bold text-success fs-5" id="payDP">₱0</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="p-3 rounded-3" style="background:#eff6ff;border:1px solid #bfdbfe;">
                                <div class="small text-muted mb-1">Remaining Payments</div>
                                <div class="fw-bold text-primary fs-5" id="payFull">₱0</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="p-3 rounded-3" style="background:#fff5f5;border:1px solid #fca5a5;">
                                <div class="small text-muted mb-1">Total Refunded</div>
                                <div class="fw-bold text-danger fs-5" id="payRefunded">₱0</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="p-3 rounded-3" style="background:#faf6ef;border:1px solid #d8b78e;">
                                <div class="small text-muted mb-1">Net Revenue</div>
                                <div class="fw-bold fs-5" style="color:#4d4335;" id="payTotal">₱0</div>
                            </div>
                        </div>
                    </div>
                    <div id="refundAlert" class="alert alert-warning d-flex align-items-center mt-3 py-2 px-3 small d-none" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span id="refundAlertText"></span>
                        <a href="bookings" class="ms-2 fw-semibold text-warning">Review →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RECENT BOOKINGS -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0" style="color:#4d4335;"><i class="bi bi-calendar-check-fill me-2 text-primary"></i>Recent Bookings</h6>
            <a href="bookings" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead>
                        <tr><th>#</th><th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Status</th><th>Payment</th></tr>
                    </thead>
                    <tbody id="recentBookings">
                        <tr><td colspan="7" class="text-center py-4 text-muted">Loading...</td></tr>
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
