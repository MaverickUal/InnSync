const PAYMENT_API = "../../api/payment.php";
let currentPayment = null;

$(document).ready(function () {
    // Highlight radio selection
    $(document).on("change", "input[name='remMethod']", function () {
        $("[id^='rlbl_']").css({ background: "", borderColor: "" });
        $("#rlbl_" + this.value).css({ background: "#faf6ef", borderColor: "#bc974a" });
    });

    if (PRELOAD_PAYMENT_ID) {
        loadPaymentDetails(PRELOAD_PAYMENT_ID);
        $("#selectPaymentCard").remove();
    }

    window.addEventListener("pageshow", function (e) {
        if (e.persisted && PRELOAD_PAYMENT_ID) {
            loadPaymentDetails(PRELOAD_PAYMENT_ID);
        }
    });
});

function loadPaymentDetails(payment_id) {
    $("#receiptPlaceholder").addClass("d-none");
    $("#receiptArea").removeClass("d-none");
    $("#paymentStatusBody").html(`<div class="text-center py-3"><div class="spinner-border" style="color:#985b36"></div></div>`);

    $.get(PAYMENT_API, { action: "getOne", id: payment_id }, function (response) {
        let resp = typeof response === 'string' ? JSON.parse(response) : response;
        if (resp.status == "success" && resp.data) {
            currentPayment = resp.data;
            renderPaymentStatus(resp.data);
            renderDownpaymentReceipt(resp.data);
            if (resp.data.payment_type == "full_payment") {
                renderFullReceipt(resp.data);
            } else {
                $("#fullReceipt").addClass("d-none");
            }
        }
    }, 'text');
}

function renderPaymentStatus(p) {
    let dpBadge  = `<span class="badge bg-success px-2 py-1">Confirmed</span>`;
    let remBadge = p.remaining_status == "confirmed"
        ? `<span class="badge bg-success px-2 py-1">Paid</span>`
        : `<span class="badge bg-warning text-dark px-2 py-1">Pending</span>`;

    let html = `
    <div class="mb-3 p-3 rounded-3" style="background:#f5f0ea">
        <div class="small text-muted mb-1">Booking Reference</div>
        <div class="fw-bold" style="color:#4d4335">${p.room_name}</div>
        <div class="small text-muted">${p.check_in ? String(p.check_in).substring(0,16) : '—'} → ${p.check_out ? String(p.check_out).substring(0,16) : '—'}</div>
    </div>
    <div class="receipt-row"><span class="label">Total Amount</span><span class="value">₱${parseFloat(p.total_amount).toLocaleString()}</span></div>
    <hr style="border-color:#e8ddd0">
    <div class="receipt-row mb-2">
        <span class="label">Downpayment (50%)</span>
        <div class="d-flex align-items-center gap-2">
            <span class="value">₱${parseFloat(p.downpayment_amount).toLocaleString()}</span>${dpBadge}
        </div>
    </div>
    <div class="receipt-row">
        <span class="label">Remaining Balance</span>
        <div class="d-flex align-items-center gap-2">
            <span class="value">₱${parseFloat(p.remaining_balance).toLocaleString()}</span>${remBadge}
        </div>
    </div>`;

    if (p.payment_type == "full_payment") {
        html += `<div class="alert alert-success mt-3 py-2 small text-center mb-0"><i class="bi bi-check-circle me-1"></i>Full payment complete!</div>`;
        $("#payRemainingCard").addClass("d-none");
    } else {
        html += `<div class="alert mt-3 py-2 small mb-0" style="background:#faf6ef;border:1px solid #d8b78e"><i class="bi bi-info-circle me-1" style="color:#985b36"></i>Remaining balance is due before check-in.</div>`;
        $("#payRemainingCard").removeClass("d-none");
    }

    $("#paymentStatusBody").html(html);
    window._currentPaymentId = p.payment_id;
}

function renderDownpaymentReceipt(p) {
    $("#rcDpNo").text("DP-" + p.payment_id);
    $("#rcDpGuest").text(p.fullname);
    $("#rcDpRoom").text(p.room_name);
    $("#rcDpCheckIn").text(p.check_in ? String(p.check_in).substring(0,16) : "—");
    $("#rcDpCheckOut").text(p.check_out ? String(p.check_out).substring(0,16) : "—");
    $("#rcDpMethod").text(p.downpayment_method ? p.downpayment_method.replace(/_/g," ").toUpperCase() : "-");
    $("#rcDpRef").text(p.downpayment_reference || "-");
    $("#rcDpDate").text(p.downpayment_date || "-");
    $("#rcDpTotal").text("₱" + parseFloat(p.total_amount).toLocaleString());
    $("#rcDpAmount").text("₱" + parseFloat(p.downpayment_amount).toLocaleString());
    $("#rcDpRemaining").text("₱" + parseFloat(p.remaining_balance).toLocaleString());

    let status = p.remaining_status == "confirmed"
        ? `<span class="badge bg-success">Remaining Balance: PAID</span>`
        : `<span class="badge bg-warning text-dark">Remaining Balance: PENDING</span>`;
    $("#rcDpStatus").html(status);
}

function renderFullReceipt(p) {
    $("#fullReceipt").removeClass("d-none");
    $("#rcFullNo").text("FULL-" + p.payment_id);
    $("#rcFullGuest").text(p.fullname);
    $("#rcFullRoom").text(p.room_name);
    $("#rcFullCheckIn").text(p.check_in ? String(p.check_in).substring(0,16) : "—");
    $("#rcFullCheckOut").text(p.check_out ? String(p.check_out).substring(0,16) : "—");
    $("#rcFullDpMethod").text(p.downpayment_method ? p.downpayment_method.replace(/_/g," ").toUpperCase() : "-");
    $("#rcFullDpRef").text(p.downpayment_reference || "-");
    $("#rcFullDpDate").text(p.downpayment_date || "-");
    $("#rcFullDp").text("₱" + parseFloat(p.downpayment_amount).toLocaleString());
    $("#rcFullRemMethod").text(p.remaining_method ? p.remaining_method.replace(/_/g," ").toUpperCase() : "-");
    $("#rcFullRemRef").text(p.remaining_reference || "-");
    $("#rcFullRemDate").text(p.remaining_date || "-");
    $("#rcFullRem").text("₱" + parseFloat(p.remaining_balance).toLocaleString());
    $("#rcFullTotal").text("₱" + parseFloat(p.total_amount).toLocaleString());
}

function payRemaining() {
    let method = $("input[name='remMethod']:checked").val();
    if (!method)        { showRemAlert("Please select a payment method.", "warning"); return; }
    if (!currentPayment){ showRemAlert("Payment details not loaded.", "warning"); return; }

    let payload = {
        payment_id:       currentPayment.payment_id,
        remaining_method: method
    };

    $("[onclick='payRemaining()']").prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

    $.ajax({
        url: PAYMENT_API,
        type: "POST",
        data: { action: "payRemaining", payload: JSON.stringify(payload) },
        dataType: 'text',
        success: function (response) {
            let resp = typeof response === 'string' ? JSON.parse(response) : response;
            if (resp.status == "success") {
                showRemAlert("Full payment complete! Refreshing...", "success");
                setTimeout(() => {
                    loadPaymentDetails(currentPayment.payment_id);
                }, 1000);
                $("[onclick='payRemaining()']").prop("disabled", false).html('<i class="bi bi-check-circle me-2"></i>Pay Remaining Balance');
            } else {
                showRemAlert(resp.message, "danger");
                $("[onclick='payRemaining()']").prop("disabled", false).html('<i class="bi bi-check-circle me-2"></i>Pay Remaining Balance');
            }
        }
    });
}

function showRemAlert(msg, type) {
    $("#remainingAlert").html(`<div class="alert alert-${type} py-2 small">${msg}</div>`);
}