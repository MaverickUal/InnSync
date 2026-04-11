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
    <title>InnSync | Refund Rules</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
</head>
<body>

<?php include "../../admin_menu.php"; ?>

<div id="toastContainer" style="position:fixed;top:1.2rem;right:1.2rem;z-index:9999;display:flex;flex-direction:column;gap:0.5rem;"></div>

<div class="page-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div><h4>Refund Rules</h4><p>Manage cancellation and refund policies.</p></div>
        <button class="btn btn-primary" onclick="openModal()"><i class="bi bi-plus-lg me-1"></i> Add Rule</button>
    </div>
    <div class="card table-card">
        <div class="card-header"><i class="bi bi-arrow-counterclockwise me-2 text-primary"></i>Refund Rules</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead><tr><th>#</th><th>Rule Name</th><th>Days Before</th><th>Refund %</th><th>Description</th><th>Actions</th></tr></thead>
                <tbody id="rulesList"><tr><td colspan="6" class="text-center py-4 text-muted">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="ruleModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="modalTitle">Add Refund Rule</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="ruleId">
            <div class="mb-3"><label class="form-label">Rule Name</label><input type="text" class="form-control" id="txtRuleName" placeholder="e.g. Full Refund"></div>
            <div class="row g-2">
                <div class="col-6"><label class="form-label">Days Before Check-in</label><input type="number" class="form-control" id="txtDaysBefore" placeholder="7"></div>
                <div class="col-6"><label class="form-label">Refund Percent (%)</label><input type="number" class="form-control" id="txtRefundPercent" placeholder="100" min="0" max="100"></div>
            </div>
            <div class="mb-3 mt-3"><label class="form-label">Description</label><textarea class="form-control" id="txtRuleDesc" rows="2"></textarea></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="saveRule()">Save</button>
        </div>
    </div></div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>