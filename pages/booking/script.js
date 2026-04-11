const BOOKING_API = "../../api/booking.php";
const ROOMS_API   = "../../api/rooms.php";
const PAYMENT_API = "../../api/payment.php";

const CHECKIN_HOUR  = 14; // 2:00 PM
const CHECKOUT_HOUR = 12; // 12:00 PM

let roomData    = [];
let currentRoom = null;

$(document).ready(function () {
    loadRooms();
    setMinDate();
    $("#selRoom").on("change", showRoomDetails);
    $("#txtCheckIn, #txtCheckOut").on("change", calcCost);
});

// ── HELPERS ────────────────────────────────────────────────────────────────────
function toDatetime(dateStr, hour) {
    if (!dateStr) return null;
    return dateStr + " " + String(hour).padStart(2, "0") + ":00:00";
}

function formatDisplay(datetimeStr) {
    if (!datetimeStr) return "";
    const d = new Date(datetimeStr.replace(" ", "T"));
    return d.toLocaleDateString("en-PH", { month:"short", day:"numeric", year:"numeric" })
         + " " + d.toLocaleTimeString("en-PH", { hour:"2-digit", minute:"2-digit", hour12:true });
}

// ── MIN DATE ───────────────────────────────────────────────────────────────────
function setMinDate() {
    const today = new Date().toISOString().split("T")[0];
    $("#txtCheckIn, #txtCheckOut").attr("min", today);

    $("#txtCheckIn").on("change", function () {
        const val = $(this).val();
        if (val && val < today) {
            $(this).val("");
            showDateModal("Check-in date must not be in the past.");
            return;
        }
        $("#txtCheckOut").attr("min", val);
        $("#txtCheckOut").val("");
        $("#costBreakdown").addClass("d-none");
        clearAlert();
    });

    $("#txtCheckOut").on("change", function () {
        const val = $(this).val();
        if (val && val < today) {
            $(this).val("");
            showDateModal("Check-out date must not be in the past.");
            return;
        }
        clearAlert();
        calcCost();
    });
}

// ── LOAD ROOMS ─────────────────────────────────────────────────────────────────
function loadRooms() {
    $.get(ROOMS_API, { action: "get" }, function (response) {

        let resp;
        try {
            resp = typeof response === "string" ? JSON.parse(response) : response;
        } catch (e) {
            console.error("JSON PARSE ERROR:", response);
            return;
        }

        console.log("API RESPONSE:", resp); 

        if (resp.status === "success") {
            roomData = resp.data;

            // Reset dropdown (important)
            $("#selRoom").html('<option value="">-- Choose a room --</option>');

            resp.data.forEach(r => {
                console.log("ROOM:", r); 

             
                const status = (r.status || "")
                    .toString()
                    .toLowerCase()
                    .trim();

              
                const roomOccupied =
                    parseInt(r.booking_occupied || 0) > 0 ||
                    parseInt(r.manual_occupied || 0) > 0;

                
                if (status === "available" && !roomOccupied) {
                    $("#selRoom").append(
                        `<option value="${r.room_id}">
                            ${r.room_name} — ₱${parseFloat(r.price).toLocaleString()}/night
                        </option>`
                    );
                }
            });

            
            if (typeof PRESELECT_ROOM !== "undefined" && PRESELECT_ROOM) {
                $("#selRoom").val(PRESELECT_ROOM).trigger("change");
            }

        } else {
            console.error("API STATUS ERROR:", resp);
        }

    }, "text").fail(function (err) {
        console.error("REQUEST FAILED:", err);
    });
}
// ── ROOM DETAILS — shows promo badge if room has one ──────────────────────────
function showRoomDetails() {
    const room_id = $("#selRoom").val();
    currentRoom = roomData.find(r => r.room_id == room_id);

    if (currentRoom) {
        $("#rdType").text(currentRoom.type_name || "N/A");
        $("#rdPrice").text("₱" + parseFloat(currentRoom.price).toLocaleString());
        $("#rdCapacity").text(currentRoom.capacity + " guests");
        $("#rdStatus").html(`<span class="badge" style="background:#985b36">${currentRoom.status}</span>`);

        // Promo badge — only show if the room has an assigned promo
        if (currentRoom.promo_id && currentRoom.promo_name) {
            const disc = parseFloat(currentRoom.promo_discount || 0);
            const discStr = disc > 0 ? ` — ${disc}% off` : "";
            $("#rdPromo").text(currentRoom.promo_name + discStr);
            $("#rdPromoWrap").removeClass("d-none");
            // Store reservation_type_id in the hidden field
            $("#selReservationType").val(currentRoom.promo_id);
        } else {
            $("#rdPromoWrap").addClass("d-none");
            $("#selReservationType").val("");
        }

        $("#roomDetails").removeClass("d-none");
        calcCost();
    } else {
        $("#roomDetails, #costBreakdown").addClass("d-none");
        $("#selReservationType").val("");
    }
}

// ── COST CALC — applies room's promo discount ──────────────────────────────────
function calcCost() {
    if (!currentRoom) return;
    const checkInDate  = $("#txtCheckIn").val();
    const checkOutDate = $("#txtCheckOut").val();
    if (!checkInDate || !checkOutDate) return;

    const ci     = new Date(checkInDate);
    const co     = new Date(checkOutDate);
    const nights = Math.round((co - ci) / 86400000);

    if (nights <= 0) {
        $("#costBreakdown").addClass("d-none");
        if (checkInDate && checkOutDate)
            showAlert("Check-out date must be after check-in date.", "warning");
        return;
    }

    const baseTotal     = nights * parseFloat(currentRoom.price);
    const discountPct   = parseFloat(currentRoom.promo_discount || 0);
    const discountAmt   = baseTotal * (discountPct / 100);
    const total         = baseTotal - discountAmt;
    const downpay       = total * 0.5;
    const remaining     = total - downpay;
    const durLabel      = nights + " night" + (nights !== 1 ? "s" : "");

    $("#cbRate").text("₱" + parseFloat(currentRoom.price).toLocaleString() + "/night");
    $("#cbNights").text(durLabel);
    $("#cbCheckIn").text(formatDisplay(toDatetime(checkInDate,  CHECKIN_HOUR)));
    $("#cbCheckOut").text(formatDisplay(toDatetime(checkOutDate, CHECKOUT_HOUR)));
    $("#cbSubtotal").text("₱" + baseTotal.toLocaleString("en-PH", { minimumFractionDigits: 2 }));

    if (discountPct > 0) {
        $("#cbDiscountRow").removeClass("d-none");
        $("#cbDiscountLabel").text(`${currentRoom.promo_name} (${discountPct}% off)`);
        $("#cbDiscountAmt").text("− ₱" + discountAmt.toLocaleString("en-PH", { minimumFractionDigits: 2 }));
    } else {
        $("#cbDiscountRow").addClass("d-none");
    }

    $("#cbTotal").text("₱" + total.toLocaleString("en-PH", { minimumFractionDigits: 2 }));
    $("#cbDownpayment").text("₱" + downpay.toLocaleString("en-PH", { minimumFractionDigits: 2 }));
    $("#cbRemaining").text("₱" + remaining.toLocaleString("en-PH", { minimumFractionDigits: 2 }));
    $("#costBreakdown").removeClass("d-none");
}

// ── SUBMIT BOOKING ─────────────────────────────────────────────────────────────
function store() {
    const room_id             = $("#selRoom").val();
    const reservation_type_id = $("#selReservationType").val() || null;
    const checkInDate         = $("#txtCheckIn").val();
    const checkOutDate        = $("#txtCheckOut").val();
    const method              = $("input[name='dpMethod']:checked").val();

    if (!room_id || !checkInDate || !checkOutDate) {
        showAlert("Please fill in all fields.", "warning"); return;
    }
    if (!method) {
        showAlert("Please select a downpayment method.", "warning"); return;
    }

    const ciDate  = new Date(checkInDate);
    const coDate  = new Date(checkOutDate);
    const nights  = Math.round((coDate - ciDate) / 86400000);

    if (nights <= 0) {
        showAlert("Check-out date must be after check-in date.", "warning"); return;
    }

    const check_in      = toDatetime(checkInDate,  CHECKIN_HOUR);
    const check_out     = toDatetime(checkOutDate, CHECKOUT_HOUR);
    const baseTotal     = nights * parseFloat(currentRoom.price);
    const discountPct   = parseFloat(currentRoom.promo_discount || 0);
    const total         = baseTotal - (baseTotal * discountPct / 100);
    const downpay       = total * 0.5;

    $("#btnBook").prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-2"></span>Checking availability...');

    $.get(BOOKING_API, { action: "check_availability", room_id, check_in, check_out }, function (raw) {
        const avail = typeof raw === "string" ? JSON.parse(raw) : raw;
        if (avail.status !== "available") {
            showAlert('<i class="bi bi-exclamation-triangle-fill me-2"></i>' + avail.message, "danger");
            $("#btnBook").prop("disabled", false).html('<i class="bi bi-calendar-check me-2"></i>Confirm Booking');
            return;
        }
        proceedBooking(room_id, reservation_type_id, check_in, check_out, method, total, downpay);
    }, "text");
}

function proceedBooking(room_id, reservation_type_id, check_in, check_out, method, total, downpay) {
    const bookPayload = { room_id, reservation_type_id, check_in, check_out, downpayment_amount: downpay };

    $("#btnBook").prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

    $.ajax({
        url: BOOKING_API, type: "POST",
        data: "action=store&payload=" + JSON.stringify(bookPayload),
        success: function (response) {
            const resp = JSON.parse(response);
            if (resp.status === "success") {
                const payPayload = {
                    booking_id:         resp.booking_id,
                    total_amount:       total,
                    downpayment_amount: downpay,
                    downpayment_method: method
                };
                $.ajax({
                    url: PAYMENT_API, type: "POST",
                    data: "action=store&payload=" + JSON.stringify(payPayload),
                    success: function (pr) {
                        const pr2 = JSON.parse(pr);
                        if (pr2.status === "success") {
                            showAlert("Booking confirmed! Redirecting to receipt...", "success");
                            setTimeout(() => {
                                window.location.href = "../payment?payment_id=" + pr2.payment_id + "&ref=" + pr2.reference;
                            }, 1200);
                        } else {
                            showAlert("Booking created but payment failed: " + pr2.message, "danger");
                            $("#btnBook").prop("disabled", false).html('<i class="bi bi-calendar-check me-2"></i>Confirm Booking');
                        }
                    }
                });
            } else {
                showAlert(resp.message, "danger");
                $("#btnBook").prop("disabled", false).html('<i class="bi bi-calendar-check me-2"></i>Confirm Booking');
            }
        }
    });
}

// ── UTILS ──────────────────────────────────────────────────────────────────────
function showAlert(msg, type) {
    $("#alertBox").html(`<div class="alert alert-${type} py-2 small">${msg}</div>`);
}

function clearAlert() {
    if ($("#alertBox .alert-danger, #alertBox .alert-warning").length) $("#alertBox").html("");
}

function showDateModal(msg) {
    $("#datePastModalMsg").text(msg);
    new bootstrap.Modal(document.getElementById("datePastModal")).show();
}
