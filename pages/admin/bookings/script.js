const API = "../../../api/booking.php";
let allBookings  = [];
let currentFilter = "all";

$(document).ready(function () {
    loadBookings();

    $(".filter-tab").on("click", function () {
        $(".filter-tab").removeClass("active");
        $(this).addClass("active");
        currentFilter = $(this).data("filter");
        applyFilters();
    });

    $("#searchInput").on("input", applyFilters);
});

function fmtDatetime(v) {
    if (!v) return "—";
    return String(v).substring(0, 16).replace("T", " ");
}

function loadBookings() {
    $("#bookingsList").html(`<tr><td colspan="10" class="text-center py-4 text-muted">
        <div class="spinner-border spinner-border-sm me-2"></div>Loading...</td></tr>`);

    $.get(API, { action: "get" }, function (response) {
        const resp = typeof response === "string" ? JSON.parse(response) : response;
        if (resp.status === "success") {
            allBookings = resp.data;
            applyFilters();
        } else {
            $("#bookingsList").html(`<tr><td colspan="10" class="text-center py-4 text-danger">Failed to load bookings.</td></tr>`);
        }
    }, "text");
}

function applyFilters() {
    const search = $("#searchInput").val().toLowerCase().trim();
    let filtered = currentFilter === "all" ? allBookings : allBookings.filter(b => b.status === currentFilter);
    if (search) filtered = filtered.filter(b =>
        (b.fullname   || "").toLowerCase().includes(search) ||
        (b.room_name  || "").toLowerCase().includes(search)
    );
    renderBookings(filtered);
}

function renderBookings(bookings) {
    $("#bookingCount").text(bookings.length);

    if (!bookings.length) {
        $("#bookingsList").html(`<tr><td colspan="10" class="text-center py-4 text-muted">No bookings found.</td></tr>`);
        return;
    }

    const pillClass = { pending:"pill-pending", confirmed:"pill-confirmed", cancelled:"pill-cancelled", completed:"pill-completed" };
    const payLabel  = { downpayment_only:"Partial", full_payment:"Full" };

    let html = "";
    bookings.forEach(b => {
        const pill = pillClass[b.status] || "pill-pending";

        // Payment summary
        let payDisplay = "—";
        if (b.payment_id) {
            const label = payLabel[b.payment_type] || "—";
            const remPill = b.remaining_status === "confirmed"
                ? `<span class="status-pill pill-confirmed">Paid</span>`
                : `<span class="status-pill pill-pending">Balance Pending</span>`;
            payDisplay = `<span class="badge" style="background:#4d4335;color:#fff">${label}</span> ${remPill}`;
        } else {
            payDisplay = `<span class="status-pill" style="background:#f3f4f6;color:#6b7280;">Unpaid</span>`;
        }

        // Total amount
        const total = b.total_amount
            ? "₱" + parseFloat(b.total_amount).toLocaleString("en-PH", { minimumFractionDigits: 2 })
            : "—";

        // Actions
        let actions = "";
        if (b.status === "pending") {
            actions = `<button class="btn btn-xs btn-success me-1" style="padding:2px 8px;font-size:0.75rem;"
                onclick="updateStatus(${b.booking_id},'confirmed')" title="Confirm"><i class="bi bi-check-lg"></i></button>
                <button class="btn btn-xs btn-danger" style="padding:2px 8px;font-size:0.75rem;"
                onclick="updateStatus(${b.booking_id},'cancelled')" title="Cancel"><i class="bi bi-x-lg"></i></button>`;
        } else if (b.status === "confirmed") {
            actions = `<button class="btn btn-sm btn-outline-secondary"
                onclick="updateStatus(${b.booking_id},'completed')">
                <i class="bi bi-check2-all me-1"></i>Complete</button>`;
        }

        html += `<tr>
            <td class="text-muted">#${b.booking_id}</td>
            <td class="fw-semibold">${b.fullname || "—"}</td>
            <td>${b.room_name || "—"}</td>
            <td class="text-muted small">${b.reservation_type || "—"}</td>
            <td>${fmtDatetime(b.check_in)}</td>
            <td>${fmtDatetime(b.check_out)}</td>
            <td><span class="status-pill ${pill}">${b.status}</span></td>
            <td>${payDisplay}</td>
            <td class="fw-semibold">${total}</td>
            <td>${actions || "—"}</td>
        </tr>`;
    });

    $("#bookingsList").html(html);
}

function updateStatus(id, status) {
    if (!confirm(`Set booking #${id} to "${status}"?`)) return;
    $.post(API, { action: "update", id, payload: JSON.stringify({ status }) }, function (response) {
        const resp = typeof response === "string" ? JSON.parse(response) : response;
        if (resp.status === "success") {
            loadBookings();
        } else {
            alert("Error: " + resp.message);
        }
    }, "text");
}
