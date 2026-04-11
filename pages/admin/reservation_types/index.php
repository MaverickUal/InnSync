<?php
include "../../../api/config.php";
if (!isset($_SESSION['user']))             header("LOCATION: ../../../");
if ($_SESSION['user']['role'] != 'admin')  header("LOCATION: ../../home");
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InnSync | Reservation Types</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
    <style>
        .discount-badge {
            display:inline-flex; align-items:center; gap:4px;
            background:#fff3e0; color:#985b36; border:1px solid #f0c080;
            border-radius:20px; padding:2px 10px; font-size:.78rem; font-weight:600;
        }
        .no-discount { color:#adb5bd; font-size:.85rem; }
    </style>
</head>
<body>

<?php include "../../admin_menu.php"; ?>

<div class="page-wrapper">

    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div><h4>Reservation Types & Promos</h4><p>Manage promo types and discounts applied to rooms.</p></div>
        <button class="btn btn-primary" onclick="openModal()">
            <i class="bi bi-plus-lg me-1"></i> Add Reservation Type
        </button>
    </div>

    <div class="card table-card">
        <div class="card-header"><i class="bi bi-card-list me-2 text-primary"></i>All Reservation Types</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type / Promo Name</th>
                        <th>Description</th>
                        <th>Discount</th>
                        <th>Rooms Using</th>
                        <th style="width:110px">Actions</th>
                    </tr>
                </thead>
                <tbody id="typesList">
                    <tr><td colspan="6" class="text-center py-4 text-muted">
                        <div class="spinner-border spinner-border-sm me-2 text-primary"></div>Loading...
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── Add / Edit Modal ──────────────────────────────────────────────────── -->
<div class="modal fade" id="typeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Reservation Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="typeId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Type / Promo Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="txtTypeName" placeholder="e.g. Early Bird, Weekend Package">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea class="form-control" id="txtTypeDesc" rows="2" placeholder="Brief description of this promo..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-percent me-1 text-rust"></i>Discount (%)
                        <span class="text-muted fw-normal small">— applied to total booking cost</span>
                    </label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="txtDiscount"
                               placeholder="0" min="0" max="100" step="0.01" value="0">
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="form-text">Enter 0 for no discount. Maximum is 100.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveType()">
                    <i class="bi bi-floppy me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── Delete Confirm Modal ──────────────────────────────────────────────── -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-1"></i>Delete Promo
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-1">
                <p class="small mb-0">
                    Delete <strong id="deleteTypeName"></strong>?
                    Rooms assigned this promo will have their promo cleared.
                </p>
                <input type="hidden" id="deleteTypeId">
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDrop()">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>