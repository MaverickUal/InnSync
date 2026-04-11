const API = "../../../api/transaction_reports.php";

$(document).ready(function () {
    loadTransactions();
});

function loadTransactions() {
    $("#reportBody").html(`<div class="text-center py-5"><div class="spinner-border" style="color:#985b36"></div></div>`);
    $.get(API, { action: "get" }, function (response) {
        const resp = typeof response === "string" ? JSON.parse(response) : response;
        if (resp.status === "success") {
            renderTransactions(resp.data);
        } else {
            $("#reportBody").html(`<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Failed: ${resp.message}</div>`);
        }
    }, "text").fail(function () {
        $("#reportBody").html(`<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Could not reach the server.</div>`);
    });
}

function fmtMoney(v) {
    const n = parseFloat(v);
    return isNaN(n) ? "—" : "₱" + n.toLocaleString("en-PH", { minimumFractionDigits: 2 });
}

function fmtMethod(v) {
    if (!v) return "—";
    return v.replace(/_/g, " ").replace(/\b\w/g, c => c.toUpperCase());
}

function fmtDatetime(v) {
    if (!v) return "—";
    return String(v).substring(0, 16).replace("T", " ");
}

function payTypeBadge(type) {
    if (type === "full_payment") return `<span class="badge bg-success">Full</span>`;
    return `<span class="badge bg-warning text-dark">Partial</span>`;
}

function bookingBadge(status) {
    const map = { pending:"bg-warning text-dark", confirmed:"bg-success", cancelled:"bg-danger", completed:"bg-secondary" };
    const cls = map[status] || "bg-secondary";
    const lbl = (status || "—").charAt(0).toUpperCase() + (status || "").slice(1);
    return `<span class="badge ${cls}">${lbl}</span>`;
}

function renderTransactions(data) {
    updateSummary(data);

    let html = `<div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" style="font-size:.8rem;">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Booking</th>
                    <th>Type</th>
                    <th>Total</th>
                    <th>Downpayment</th>
                    <th>DP Method</th>
                    <th>DP Ref</th>
                    <th>DP Date</th>
                    <th>DP Receipt</th>
                    <th>Balance</th>
                    <th>Bal. Method</th>
                    <th>Bal. Ref</th>
                    <th>Bal. Date</th>
                    <th>Full Receipt</th>
                    <th>Refund</th>
                    <th>Total Paid</th>
                </tr>
            </thead>
            <tbody>`;

    if (!data.length) {
        html += `<tr><td colspan="20" class="text-center py-5 text-muted">
            <i class="bi bi-receipt fs-2 d-block mb-2 opacity-25"></i>No transactions found.</td></tr>`;
    } else {
        data.forEach(t => {
            const isCancelled = t.booking_status === "cancelled";
            const dp       = parseFloat(t.downpayment_amount) || 0;
            const rem      = parseFloat(t.remaining_balance)  || 0;
            const refunded = parseFloat(t.refund_amount)      || 0;
            const totalPaid = isCancelled
                ? 0
                : (t.remaining_status === "confirmed" ? dp + rem : dp);

            const remBadge = t.remaining_status === "confirmed"
                ? `<span class="badge bg-success">Paid</span>`
                : t.remaining_status === "refunded"
                    ? `<span class="badge bg-purple" style="background:#7c3aed">Refunded</span>`
                    : `<span class="badge bg-warning text-dark">Pending</span>`;

            const refundCell = t.cancellation_status === "refunded"
                ? `<span class="text-danger fw-semibold">${fmtMoney(refunded)}</span>`
                : t.cancellation_status === "pending"
                    ? `<span class="text-warning small">Pending</span>`
                    : `<span class="text-muted">—</span>`;

            const rowStyle = isCancelled ? "opacity:0.6;" : "";

            html += `<tr style="${rowStyle}">
                <td class="fw-semibold">#${t.transaction_id}</td>
                <td>${t.fullname || "—"}</td>
                <td>${t.room_name || "—"}</td>
                <td>${fmtDatetime(t.check_in)}</td>
                <td>${fmtDatetime(t.check_out)}</td>
                <td>${bookingBadge(t.booking_status)}</td>
                <td>${payTypeBadge(t.payment_type)}</td>
                <td class="fw-semibold">${isCancelled ? `<s class="text-muted">${fmtMoney(t.total_amount)}</s>` : fmtMoney(t.total_amount)}</td>
                <td>${fmtMoney(t.downpayment_amount)}</td>
                <td>${fmtMethod(t.downpayment_method)}</td>
                <td class="font-monospace small">${t.downpayment_reference || "—"}</td>
                <td>${fmtDatetime(t.downpayment_date)}</td>
                <td class="font-monospace small text-muted">${t.dp_receipt_number || "—"}</td>
                <td>${fmtMoney(t.remaining_balance)} ${remBadge}</td>
                <td>${fmtMethod(t.remaining_method)}</td>
                <td class="font-monospace small">${t.remaining_reference || "—"}</td>
                <td>${fmtDatetime(t.remaining_date)}</td>
                <td class="font-monospace small text-muted">${t.full_receipt_number || "—"}</td>
                <td>${refundCell}</td>
                <td class="fw-bold" style="color:#4d4335">${isCancelled ? `<span class="text-muted">—</span>` : fmtMoney(totalPaid)}</td>
            </tr>`;
        });
    }

    html += `</tbody></table></div>`;
    $("#reportBody").html(html);
}

function updateSummary(data) {
    // Only count non-cancelled bookings in revenue/collected
    const active = data.filter(t => t.booking_status !== "cancelled");
    const count     = data.length;
    const revenue   = active.reduce((s, t) => s + (parseFloat(t.total_amount) || 0), 0);
    const collected = active.reduce((s, t) => {
        const dp  = parseFloat(t.downpayment_amount) || 0;
        const rem = t.remaining_status === "confirmed" ? (parseFloat(t.remaining_balance) || 0) : 0;
        return s + dp + rem;
    }, 0);
    const refunded = data.reduce((s, t) => {
        return s + (t.cancellation_status === "refunded" ? (parseFloat(t.refund_amount) || 0) : 0);
    }, 0);
    const pending = revenue - collected;
    const net     = collected - refunded;

    const fmt = n => "₱" + n.toLocaleString("en-PH", { minimumFractionDigits: 2 });
    $("#sumCount").text(count);
    $("#sumRevenue").text(fmt(revenue));
    $("#sumCollected").text(fmt(collected));
    $("#sumPending").text(fmt(pending < 0 ? 0 : pending));
    // Update additional pills if present
    if ($("#sumRefunded").length) $("#sumRefunded").text(fmt(refunded));
    if ($("#sumNet").length)      $("#sumNet").text(fmt(net < 0 ? 0 : net));
}
