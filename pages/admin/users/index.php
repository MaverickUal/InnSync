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
    <title>InnSync | User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
    <style>
        .status-pill { padding:2px 10px; border-radius:20px; font-size:0.72rem; font-weight:600; display:inline-block; }
        .pill-approved  { background:#d1fae5; color:#065f46; }
        .pill-blacklist { background:#fee2e2; color:#7f1d1d; }
        .filter-tab { cursor:pointer; padding:5px 14px; border-radius:20px; font-size:0.8rem; font-weight:600; border:1.5px solid #d8b78e; color:#4d4335; transition:all 0.15s; }
        .filter-tab.active { background:#4d4335; color:#fff; border-color:#4d4335; }
    </style>
</head>
<body>
<?php include "../../admin_menu.php"; ?>
<div class="page-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div><h4>User Management</h4><p>Manage user accounts, roles, and approval status.</p></div>
    </div>

    <!-- Filter tabs -->
    <div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
        <span class="filter-tab active" data-filter="all">All</span>
        <span class="filter-tab" data-filter="approved">Approved</span>
        <span class="filter-tab" data-filter="blacklist">Blacklist</span>
        <input type="text" class="form-control form-control-sm ms-auto" id="searchInput" placeholder="Search name or email..." style="max-width:220px;">
        <span class="badge bg-secondary" id="userCount">0</span>
    </div>

    <div class="card table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersList">
                        <tr><td colspan="8" class="text-center py-4 text-muted">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header" style="background:#4d4335;color:white;border:none">
            <h5 class="modal-title">Edit User</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editId">
            <div class="mb-3"><label class="form-label fw-semibold">Full Name</label><input type="text" class="form-control" id="editName"></div>
            <div class="mb-3"><label class="form-label fw-semibold">Email</label><input type="email" class="form-control" id="editEmail"></div>
            <div class="mb-3"><label class="form-label fw-semibold">Contact Number</label><input type="text" class="form-control" id="editContact"></div>
            <div class="mb-3"><label class="form-label fw-semibold">Role</label>
                <select class="form-select" id="editRole">
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="mb-3"><label class="form-label fw-semibold">Approval Status</label>
                <select class="form-select" id="editStatus">
                    <option value="approved">Approved</option>
                    <option value="blacklist">Blacklist</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-gold fw-semibold" onclick="saveEdit()">Save Changes</button>
        </div>
    </div></div>
</div>

<!-- Approve/Reject confirm modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-sm"><div class="modal-content">
        <div class="modal-header" style="background:#4d4335;color:white;border:none">
            <h6 class="modal-title mb-0" id="statusModalTitle">Confirm</h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body small" id="statusModalBody"></div>
        <div class="modal-footer py-2">
            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-sm btn-gold fw-semibold" id="statusConfirmBtn">Confirm</button>
        </div>
    </div></div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>
