<?php
include "../../../api/config.php";
if (!isset($_SESSION['user']))             header("Location: /innsync/");
if ($_SESSION['user']['role'] !== 'admin') header("Location: /innsync/pages/home");
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InnSync | Room Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
    <style>
        .room-thumb { width:54px; height:42px; object-fit:cover; border-radius:6px; border:1px solid #dee2e6; }
        .room-thumb-placeholder {
            width:54px; height:42px; border-radius:6px; background:#f0ebe4;
            display:flex; align-items:center; justify-content:center;
            color:#adb5bd; font-size:1rem;
        }
        #imgPreview { max-height:160px; width:100%; object-fit:cover; border-radius:8px; border:1px solid #dee2e6; }
        .promo-badge {
            display:inline-flex; align-items:center; gap:4px;
            background:#fff3e0; color:#985b36; border:1px solid #f0c080;
            border-radius:20px; padding:2px 10px; font-size:.75rem; font-weight:600;
        }
    </style>
</head>
<body>

<?php include "../../admin_menu.php";
if (!isset($_SESSION['user']))             header("Location: ../../../");
if ($_SESSION['user']['role'] !== 'admin') header("Location: ../../home");
?>
<div class="page-wrapper">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold" style="color:#4d4335">
            <i class="bi bi-door-open me-2"></i>Room Management
        </h5>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="bi bi-plus-lg me-1"></i> Add Room
        </button>
    </div>

    <div class="card table-card">
        <div class="card-header">
            <i class="bi bi-door-open me-2 text-primary"></i>All Rooms
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width:66px">Image</th>
                            <th>Room Name</th>
                            <th>Type</th>
                            <th>Promo / Res. Type</th>
                            <th>Price/Night</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th style="width:110px">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="roomsList">
                        <tr><td colspan="8" class="text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm me-2 text-primary"></div>Loading...
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ── Add / Edit Modal ──────────────────────────────────────────────────── -->
<div class="modal fade" id="roomModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="roomId">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Room Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="txtRoomName" placeholder="e.g. Deluxe Suite 101">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Room Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="selRoomType">
                            <option value="">-- Select Type --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Price / Night (₱) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="txtPrice" placeholder="0.00" min="0" step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Capacity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="txtCapacity" placeholder="2" min="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" id="selStatus">
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="unavailable">Unavailable</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    <!-- ── Promo / Reservation Type ──────────────────────── -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-tag me-1 text-rust"></i>Promo / Reservation Type
                            <span class="text-muted fw-normal small">(optional)</span>
                        </label>
                        <select class="form-select" id="selPromo">
                            <option value="">-- No Promo --</option>
                        </select>
                        <div id="promoPreview" class="mt-2 d-none">
                            <span class="promo-badge">
                                <i class="bi bi-percent"></i>
                                <span id="promoPreviewText"></span>
                            </span>
                            <span class="text-muted small ms-2" id="promoPreviewDesc"></span>
                        </div>
                    </div>
                    <!-- ───────────────────────────────────────────────────── -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="txtDescription" rows="3" placeholder="Room details..."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Room Image</label>
                        <input type="file" class="form-control" id="fileImage" accept="image/*">
                        <div id="imgPreviewWrap" class="mt-2" style="display:none">
                            <img id="imgPreview" src="" alt="Preview">
                            <div class="mt-1 small text-muted" id="imgNote"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSave" onclick="saveRoom()">
                    <i class="bi bi-floppy me-1"></i> Save Room
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
                    <i class="bi bi-exclamation-triangle me-1"></i>Delete Room
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-1">
                <p class="small mb-0">
                    Are you sure you want to delete <strong id="deleteRoomName"></strong>?
                    This cannot be undone.
                </p>
                <input type="hidden" id="deleteRoomId">
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>
