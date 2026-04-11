const API = "../../../api/payment.php";
let allPayments = [];

$(document).ready(function () {
    loadPayments();
    $("#searchInput").on("input", applyFilters);
    $("#filterStatus").on("change", applyFilters);
});

function fmtMoney(v) {
    const n = parseFloat(v);
    return isNaN(n) ? "—" : "₱" + n.toLocaleString("en-PH", { minimumFractionDigits: 2 });
}

function fmtDatetime(v) {
    if (!v) return "—";
    return String(v).substring(0, 16).replace("T", " ");
}

function loadPayments() {
    $("#paymentsList").html(`<tr><td colspan="12" class="text-center py-4 text-muted">
        <div class="spinner-border spinner-border-sm me-2"></div>Loading...</td></tr>`);

    $.get(API, { action: "get" }, function (response) {
        const resp = typeof response === "string" ? JSON.parse(response) : response;
        if (resp.status === "success") {
            allPayments = resp.data;
            applyFilters();
        } else {
            $("#paymentsList").html(`<tr><td colspan="12" class="text-center py-4 text-danger">Failed to load payments.</td></tr>`);
        }
    }, "text");
}

function applyFilters() {
    const search = $("#searchInput").val().toLowerCase().trim();
    const status = $("#filterStatus").val();

    let filtered = allPayments;
    if (status) filtered = filtered.filter(p => p.booking_status === status);
    if (search) filtered = filtered.filter(p =>
        (p.fullname  || "").toLowerCase().includes(search) ||
        (p.room_name || "").toLowerCase().includes(search)
    );
    renderPayments(filtered);
}

function renderPayments(data) {
    $("#payCount").text(data.length);

    if (!data.length) {
        $("#paymentsList").html(`<tr><td colspan="12" class="text-center py-4 text-muted">No payments found.</td></tr>`);
        return;
    }

    const bookingPill = { pending:"pill-pending", confirmed:"pill-confirmed", cancelled:"pill-cancelled", completed:"pill-completed" };
    const dpLabel     = { downpayment_only:"Partial", full_payment:"Full" };

    let html = "";
    data.forEach(p => {
        const bPill = bookingPill[p.booking_status] || "pill-pending";

        // Downpayment badge
        const dpBadge = p.downpayment_status === "refunded"
            ? `<span class="status-pill pill-refunded">Refunded</span>`
            : `<span class="status-pill pill-confirmed">Confirmed</span>`;

        // Remaining balance badge
        const remBadge = p.remaining_status === "confirmed"
            ? `<span class="status-pill pill-confirmed">Paid</span>`
            : p.remaining_status === "refunded"
                ? `<span class="status-pill pill-refunded">Refunded</span>`
                : `<span class="status-pill pill-pending">Pending</span>`;

        // Refund info
        const refundCell = (p.cancellation_status === "refunded")
            ? `<span class="text-danger fw-semibold">${fmtMoney(p.refund_amount)}</span><br><span class="badge pill-refunded status-pill">Refunded</span>`
            : (p.cancellation_status === "pending")
                ? `<span class="text-warning small">Pending</span>`
                : `<span class="text-muted">—</span>`;

        // Actions
        let actions = "";
        if (p.booking_status === "cancelled" && p.cancellation_status === "pending") {
            actions = `<button class="btn btn-sm btn-outline-danger" onclick="promptRefund(${p.payment_id}, ${p.refund_amount || 0})">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Refund</button>`;
        } else if (p.booking_status !== "cancelled" && p.downpayment_status === "confirmed" && p.remaining_status === "pending") {
            actions = `<button class="btn btn-sm btn-outline-warning" onclick="promptRefund(${p.payment_id}, ${p.downpayment_amount})">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Refund DP</button>`;
        } else {
            actions = `<span class="text-muted small">—</span>`;
        }

        html += `<tr>
            <td class="text-muted fw-semibold">#${p.payment_id}</td>
            <td class="fw-semibold">${p.fullname || "—"}</td>
            <td>${p.room_name || "—"}</td>
            <td>${fmtDatetime(p.check_in)}</td>
            <td>${fmtDatetime(p.check_out)}</td>
            <td><span class="status-pill ${bPill}">${p.booking_status || "—"}</span></td>
            <td class="fw-semibold">${fmtMoney(p.total_amount)}</td>
            <td>${fmtMoney(p.downpayment_amount)} ${dpBadge}</td>
            <td>${fmtMoney(p.remaining_balance)} ${remBadge}</td>
            <td><span class="badge" style="background:#4d4335;color:#fff;">${dpLabel[p.payment_type] || "—"}</span></td>
            <td>${refundCell}</td>
            <td>${actions}</td>
        </tr>`;
    });

    $("#paymentsList").html(html);
}

function promptRefund(paymentId, amount) {
    const fmt = "₱" + parseFloat(amount || 0).toLocaleString("en-PH", { minimumFractionDigits: 2 });
    $("#refundModalBody").html(`Process refund of <strong>${fmt}</strong> for payment <strong>#${paymentId}</strong>? This cannot be undone.`);
    $("#refundConfirmBtn").off("click").on("click", function () {
        doRefund(paymentId);
    });
    new bootstrap.Modal(document.getElementById("refundModal")).show();
}

function doRefund(paymentId) {
    $.post(API, { action: "refund", id: paymentId }, function (response) {
        const resp = typeof response === "string" ? JSON.parse(response) : response;
        bootstrap.Modal.getInstance(document.getElementById("refundModal")).hide();
        if (resp.status === "success") {
            loadPayments();
        } else {
            alert("Refund failed: " + resp.message);
        }
    }, "text");
}
