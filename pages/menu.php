<?php
$currentPage = basename(dirname($_SERVER['PHP_SELF']));
$user = $_SESSION['user'];
$initial = strtoupper(substr($user['fullname'], 0, 1));
?>
<nav class="app-navbar">
    <a href="../home" class="brand">Inn<span>Sync</span></a>
    <ul class="nav-links">
        <li><a href="../home" class="<?= $currentPage=='home'?'active':'' ?>">Home</a></li>
        <li><a href="../rooms" class="<?= $currentPage=='rooms'?'active':'' ?>">Rooms</a></li>
        <li><a href="../booking" class="<?= $currentPage=='booking'?'active':'' ?>">Book</a></li>
        <li><a href="../history" class="<?= $currentPage=='history'?'active':'' ?>">History</a></li>
        <li><a href="../about" class="<?= $currentPage=='about'?'active':'' ?>">About</a></li>
        <li><a href="../contact" class="<?= $currentPage=='contact'?'active':'' ?>">Contact</a></li>
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
                <li><div class="px-3 py-2 small text-muted"><?= htmlspecialchars($user['email']) ?></div></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li><a class="dropdown-item" href="../account"><i class="bi bi-person me-2"></i>My Profile</a></li>
                <li><a class="dropdown-item text-danger" href="../../api/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</nav>