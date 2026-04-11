<?php
include "../../../api/config.php";
if (!isset($_SESSION['user'])) header("LOCATION: ../../../");
if ($_SESSION['user']['role'] != 'admin') header("LOCATION: ../../home");
$user = $_SESSION['user'];

// Fetch admin logs directly
$stmt = $conn->prepare("SELECT al.*, u.fullname FROM admin_logs al LEFT JOIN users u ON al.admin_id = u.user_id ORDER BY al.created_at DESC LIMIT 200");
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InnSync | Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
</head>
<body>

<?php include "../../admin_menu.php"; ?>

<div class="page-wrapper">
    <div class="card table-card">
        <div class="card-header"><i class="bi bi-journal-text me-2 text-primary"></i>Activity Logs (Last 200)</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>Admin</th><th>Action</th><th>Description</th><th>Date/Time</th></tr></thead>
                    <tbody>
                        <?php if (count($logs) > 0): ?>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= $log['log_id'] ?></td>
                                <td><?= htmlspecialchars($log['fullname'] ?? 'System') ?></td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($log['action']) ?></span></td>
                                <td><?= htmlspecialchars($log['description']) ?></td>
                                <td><?= $log['created_at'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No logs yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
