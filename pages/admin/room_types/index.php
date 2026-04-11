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
    <title>InnSync | Room Types</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
</head>
<body>

<?php include "../../admin_menu.php"; ?>

<div class="page-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div><h4>Room Types</h4><p>Manage the categories available for rooms.</p></div>
        <button class="btn btn-primary" onclick="openModal()"><i class="bi bi-plus-lg me-1"></i> Add Room Type</button>
    </div>
    <div class="card table-card">
        <div class="card-header"><i class="bi bi-tags me-2 text-primary"></i>Room Types</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead><tr><th>#</th><th>Type Name</th><th>Description</th><th>Actions</th></tr></thead>
                <tbody id="typesList"><tr><td colspan="4" class="text-center py-4 text-muted">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="typeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">Add Room Type</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="typeId">
                <div class="mb-3"><label class="form-label">Type Name</label><input type="text" class="form-control" id="txtTypeName" placeholder="e.g. Deluxe"></div>
                <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" id="txtTypeDesc" rows="3"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveType()">Save</button>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>