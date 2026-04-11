<?php
include "../../api/config.php";
if (!isset($_SESSION['user'])) header("LOCATION: ../../");
if ($_SESSION['user']['role'] == 'admin') header("LOCATION: ../admin");
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InnSync | Booking History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
</head>
<body>

<?php include "../menu.php"; ?>

<div class="page-wrapper">
    <div class="card table-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-clock-history me-2" style="color:#985b36"></i>My Bookings</span>
            <a href="../rooms" class="btn btn-sm btn-gold">+ New Booking</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Room</th>
                            <th>Type</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="bookingsList">
                        <tr><td colspan="9" class="text-center py-4 text-muted">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-x-circle text-danger me-2"></i>Cancel Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <!-- Step 1: Reservation / Refund Rules -->
                <div id="cancelStep1">
                    <div class="alert alert-warning d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                        <div><strong>Please review the cancellation &amp; refund policy before proceeding.</strong></div>
                    </div>
                    <div id="refundRulesTable">
                        <p class="text-muted text-center py-2"><span class="spinner-border spinner-border-sm me-1"></span> Loading rules...</p>
                    </div>
                    <p class="text-muted small mt-2 mb-0">Refund eligibility is based on how many days remain before your check-in date.</p>
                </div>

                <!-- Step 2: Reason + Confirm -->
                <div id="cancelStep2" class="d-none">
                    <p class="text-muted mb-3">You are about to cancel this booking. Please provide a reason.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason for Cancellation</label>
                        <textarea class="form-control" id="cancelReason" rows="3" placeholder="e.g. Change of plans..."></textarea>
                    </div>
                    <div id="refundInfo" class="alert alert-info small d-none"></div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <!-- Step 1 footer -->
                <button type="button" class="btn btn-danger" id="btnProceedToCancel">I Understand, Proceed</button>
                <!-- Step 2 footer -->
                <button type="button" class="btn btn-danger d-none" id="btnConfirmCancel">Confirm Cancellation</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>
