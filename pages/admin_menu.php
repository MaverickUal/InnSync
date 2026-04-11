<?php
$currentPage = basename(dirname($_SERVER['PHP_SELF']));
$user = $_SESSION['user'];
$initial = strtoupper(substr($user['fullname'], 0, 1));
?>
<nav class="admin-navbar">
    <a href="/pages/admin" class="brand">Inn<span>Sync</span> <small class="opacity-50 fw-normal fs-6">Admin</small></a>
    <ul class="nav-links">
        <li><a href="/pages/admin" class="<?= $currentPage=='admin'?'active':'' ?>">Dashboard</a></li>
        <li><a href="/pages/admin/users" class="<?= $currentPage=='users'?'active':'' ?>">Users</a></li>
        <li><a href="/pages/admin/rooms" class="<?= $currentPage=='rooms'?'active':'' ?>">Rooms</a></li>
        <li><a href="/pages/admin/room_types" class="<?= $currentPage=='room_types'?'active':'' ?>">Room Types</a></li>
        <li><a href="/pages/admin/reservation_types" class="<?= $currentPage=='reservation_types'?'active':'' ?>">Res. Types</a></li>
        <li><a href="/pages/admin/bookings" class="<?= $currentPage=='bookings'?'active':'' ?>">Bookings</a></li>
        <li><a href="/pages/admin/payments" class="<?= $currentPage=='payments'?'active':'' ?>">Payments</a></li>
        <li><a href="/pages/admin/refund_rules" class="<?= $currentPage=='refund_rules'?'active':'' ?>">Refunds</a></li>
        <li><a href="/pages/admin/reports" class="<?= $currentPage=='reports'?'active':'' ?>">Reports</a></li>
        <li><a href="/pages/admin/transaction_reports" class="<?= $currentPage=='transaction_reports'?'active':'' ?>">Transactions</a></li>
    </ul>
    <div class="nav-right">
        <div class="dropdown">
            <button class="btn p-0 border-0 dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" style="background:none">
                <div class="user-badge">
                    <div class="avatar"><?= $initial ?></div>
                    <span class="d-none d-lg-inline"><?= htmlspecialchars(explode(' ', $user['fullname'])[0]) ?></span>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                <li><div class="px-3 py-2 small text-muted">Admin Account</div></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li><a class="dropdown-item text-danger" href="/api/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
