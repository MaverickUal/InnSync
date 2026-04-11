const BOOKING_API    = "../../api/booking.php";
const REFUND_API     = "../../api/refund_rules.php";
let cancelBookingId  = null;

function fmtDatetime(v) {
    if (!v) return "—";
    return String(v).substring(0, 16).replace("T", " ");
}

$.ajaxSetup({ cache: false });

$(document).ready(function () {
    loadBookings();
    loadRefundRules();
});

// Reload when tab becomes visible (e.g. after paying)
document.addEventListener("visibilitychange", function () {
    if (document.visibilityState === "visible") loadBookings();
});

// Reload on back/forward navigation
window.addEventListener("pageshow", function (e) {
    if (e.persisted) loadBookings();
});

// ─── Load Refund Rules (once on page load) ───────────────────────────────────
function loadRefundRules() {
    $.get(REFUND_API, { action: "get", _: Date.now() }, function (response) {
        let resp = typeof response === 'string' ? JSON.parse(response) : response;
        if (resp.status !== "success" || !resp.data.length) {
            $("#refundRulesTable").html('<p class="text-muted small">No refund rules configured.</p>');
            return;
        }

        let rows = resp.data.map(r => `
            <tr>
                <td><strong>${r.rule_name}</strong></td>
                <td>${r.days_before > 0 ? r.days_before + "+ days before check-in" : "Less than 7 days / any time"}</td>
                <td><span class="badge ${parseFloat(r.refund_percent) > 0 ? 'bg-success' : 'bg-danger'}">${parseFloat(r.refund_percent)}% refund</span></td>
                <td class="text-muted small">${r.description || ''}</td>
            </tr>`).join('');

        $("#refundRulesTable").html(`
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Policy</th>
                        <th>Cancellation Window</th>
                        <th>Refund</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>`);
    }, 'text').fail(function () {
        $("#refundRulesTable").html('<p class="text-muted small">Could not load refund rules.</p>');
    });
}

// ─── Load Bookings ────────────────────────────────────────────────────────────
function loadBookings() {
    $.get(BOOKING_API, { action: "getByUser", _: Date.now() }, function (response) {
        let resp = typeof response === 'string' ? JSON.parse(response) : response;
        let html = "";

        if (resp.status === "success" && resp.data.length > 0) {
            resp.data.forEach((b) => {
                let statusBadge = {
                    pending:   "bg-warning text-dark",
                    confirmed: "bg-success",
                    cancelled: "bg-danger",
                    completed: "bg-secondary"
                }[b.status] || "bg-secondary";

                // Payment / refund status display
                let payDisplay = '-';
                if (b.status === 'cancelled') {
                    if (b.cancellation_status === 'refunded') {
                        payDisplay = `<span class="badge bg-danger">Cancelled</span> <span class="badge" style="background:#7c3aed">Refunded ₱${parseFloat(b.refund_amount||0).toLocaleString()}</span>`;
                    } else if (b.cancellation_status === 'pending' && parseFloat(b.refund_amount) > 0) {
                        payDisplay = `<span class="badge bg-danger">Cancelled</span> <span class="badge bg-warning text-dark">Refund Pending ₱${parseFloat(b.refund_amount||0).toLocaleString()}</span>`;
                    } else {
                        payDisplay = `<span class="badge bg-danger">Cancelled</span> <span class="badge bg-secondary">No Refund</span>`;
                    }
                } else if (b.payment_id) {
                    if (b.payment_type === 'full_payment') {
                        payDisplay = `<span class="badge bg-success">Full Payment</span>`;
                    } else if (b.downpayment_status === 'confirmed' && b.remaining_status === 'pending') {
                        payDisplay = `<span class="badge bg-success">Downpayment Paid</span> <span class="badge bg-warning text-dark">Balance Pending</span>`;
                    } else if (b.downpayment_status === 'confirmed') {
                        payDisplay = `<span class="badge bg-success">Downpayment Paid</span>`;
                    }
                } else {
                    payDisplay = `<span class="badge bg-light text-muted border">Unpaid</span>`;
                }

                // Amount display
                let amountDisplay = b.total_amount
                    ? `<div>&#8369;${parseFloat(b.total_amount).toLocaleString()}</div>
                       <small class="text-muted">Down: &#8369;${parseFloat(b.downpayment_amount || 0).toLocaleString()}</small>`
                    : '-';

                // Action buttons
                let actionBtn = "";
                if (b.status === "pending" || b.status === "confirmed") {
                    if (b.payment_id && b.remaining_status === 'pending') {
                        actionBtn += `<a href="../payment?payment_id=${b.payment_id}" class="btn btn-sm btn-outline-success me-1">Pay Balance</a>`;
                    } else if (!b.payment_id) {
                        actionBtn += `<a href="../payment" class="btn btn-sm btn-outline-success me-1">Pay</a>`;
                    }
                    if (b.payment_id) {
                        actionBtn += `<a href="../payment?payment_id=${b.payment_id}" class="btn btn-sm btn-outline-secondary me-1">Receipt</a>`;
                    }
                    actionBtn += `<button class="btn btn-sm btn-outline-danger" onclick="openCancel(${b.booking_id})">Cancel</button>`;
                } else if (b.payment_id) {
                    actionBtn = `<a href="../payment?payment_id=${b.payment_id}" class="btn btn-sm btn-outline-secondary">Receipt</a>`;
                }

                html += `
                <tr>
                    <td>${b.booking_id}</td>
                    <td>${b.room_name}</td>
                    <td>${b.reservation_type || '-'}</td>
                    <td>${fmtDatetime(b.check_in)}</td>
                    <td>${fmtDatetime(b.check_out)}</td>
                    <td><span class="badge ${statusBadge}">${b.status}</span></td>
                    <td>${payDisplay}</td>
                    <td>${amountDisplay}</td>
                    <td>${actionBtn}</td>
                </tr>`;
            });
        } else {
            html = `<tr><td colspan="9" class="text-center py-4 text-muted">
                No bookings yet. <a href="../rooms">Browse rooms to book one!</a>
            </td></tr>`;
        }

        $("#bookingsList").html(html);
    }, 'text');
}

// ─── Open Cancel Modal ────────────────────────────────────────────────────────
function openCancel(booking_id) {
    cancelBookingId = booking_id;

    $("#cancelStep1").removeClass("d-none");
    $("#cancelStep2").addClass("d-none");
    $("#btnProceedToCancel").removeClass("d-none");
    $("#btnConfirmCancel").addClass("d-none").prop("disabled", false).html("Confirm Cancellation");
    $("#cancelReason").val("");
    $("#refundInfo").addClass("d-none").html("").removeClass("alert-secondary").addClass("alert-info");

    new bootstrap.Modal(document.getElementById("cancelModal")).show();
}

// ─── Step 1 → Step 2 ─────────────────────────────────────────────────────────
$("#btnProceedToCancel").on("click", function () {
    $("#cancelStep1").addClass("d-none");
    $("#cancelStep2").removeClass("d-none");
    $("#btnProceedToCancel").addClass("d-none");
    $("#btnConfirmCancel").removeClass("d-none");
});

// ─── Step 2: Perform Cancellation ────────────────────────────────────────────
$("#btnConfirmCancel").on("click", function () {
    if (!cancelBookingId) return;

    let reason = $("#cancelReason").val().trim() || "Cancelled by user";
    let $btn = $(this);

    $btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span> Cancelling...');

    $.ajax({
        url: BOOKING_API,
        type: "POST",
        data: {
            action: "cancel",
            id: cancelBookingId,
            payload: JSON.stringify({ reason })
        },
        dataType: 'text',
        success: function (response) {
            let resp = typeof response === 'string' ? JSON.parse(response) : response;
            if (resp.status === "success") {
                if (parseFloat(resp.refund_amount) > 0) {
                    $("#refundInfo")
                        .removeClass("d-none alert-secondary")
                        .addClass("alert-info")
                        .html(`<i class="bi bi-info-circle me-1"></i> A refund of <strong>&#8369;${parseFloat(resp.refund_amount).toLocaleString()}</strong> will be processed by the admin.`);
                } else {
                    $("#refundInfo")
                        .removeClass("d-none alert-info")
                        .addClass("alert-secondary")
                        .html(`<i class="bi bi-info-circle me-1"></i> No refund applicable based on current cancellation policy.`);
                }

                $btn.html("Cancelled ✓");

                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById("cancelModal")).hide();
                    $btn.prop("disabled", false).html("Confirm Cancellation");
                    loadBookings();
                }, 1500);

            } else {
                alert(resp.message || "Cancellation failed. Please try again.");
                $btn.prop("disabled", false).html("Confirm Cancellation");
            }
        },
        error: function () {
            alert("Network error. Please try again.");
            $btn.prop("disabled", false).html("Confirm Cancellation");
        }
    });
});