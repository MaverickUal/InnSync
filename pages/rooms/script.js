const ROOMS_API = "../../api/rooms.php";
const TYPES_API = "../../api/room_type.php";

$(document).ready(function () {
    loadTypes();
    loadRooms();
});

function loadTypes() {
    $.get(TYPES_API, { action: 'get' }, function (response) {
        const resp = typeof response === 'string' ? JSON.parse(response) : response;
        if (resp.status === "success") {
            resp.data.forEach(t => {
                $("#filterType").append(`<option value="${t.type_id}">${t.type_name}</option>`);
            });
        }
    }, 'text');
}

function loadRooms() {
    const params = {
        action:    "get",
        type_id:   $("#filterType").val(),
        min_price: $("#filterMinPrice").val(),
        max_price: $("#filterMaxPrice").val(),
        capacity:  $("#filterCapacity").val()
    };

    $("#roomsGrid").html(`
        <div class="col-12 text-center py-5">
            <div class="spinner-border" style="color:#985b36"></div>
            <p class="mt-2 text-muted">Loading rooms...</p>
        </div>
    `);

    $.get(ROOMS_API, params, function (response) {
        const resp = typeof response === 'string' ? JSON.parse(response) : response;
        let html = "";

        if (resp.status === "success" && resp.data.length > 0) {
            $("#roomCount").text(resp.data.length);

            resp.data.forEach(r => {
                const discount = parseFloat(r.promo_discount) || 0;
                const promoTag = (r.promo_name && discount > 0)
                    ? `<div class="room-promo-tag"><i class="bi bi-tag-fill me-1"></i>${discount}% off &mdash; ${r.promo_name}</div>`
                    : '';

                const imgHtml = r.image_path
                    ? `<div class="room-img-wrap"><img src="../../api/${r.image_path}" alt="${r.room_name}">${promoTag}</div>`
                    : `<div class="room-img-wrap room-img-placeholder"><i class="bi bi-building"></i>${promoTag}</div>`;

                let statusBadge, bookBtn;

                // occupied = active booking today OR admin manually set status=occupied
                const isOccupied = parseInt(r.booking_occupied) > 0 || parseInt(r.manual_occupied) > 0;

                if (isOccupied) {
                    statusBadge = `<span class="badge bg-danger">Occupied</span>`;
                    bookBtn     = `<button class="btn btn-danger btn-sm w-100" disabled>
                                    <i class="bi bi-person-fill me-1"></i>Currently Occupied
                                   </button>`;
                } else if (r.status === "available") {
                    statusBadge = `<span class="badge" style="background:#985b36">Available</span>`;
                    bookBtn     = `<a href="../booking?room_id=${r.room_id}" class="btn btn-gold btn-sm w-100 fw-semibold">
                                    <i class="bi bi-calendar-check me-1"></i>Book Now
                                   </a>`;
                } else if (r.status === "maintenance") {
                    statusBadge = `<span class="badge bg-warning text-dark">Maintenance</span>`;
                    bookBtn     = `<button class="btn btn-warning btn-sm w-100" disabled>
                                    <i class="bi bi-tools me-1"></i>Under Maintenance
                                   </button>`;
                } else {
                    statusBadge = `<span class="badge bg-secondary">Unavailable</span>`;
                    bookBtn     = `<button class="btn btn-secondary btn-sm w-100" disabled>Not Available</button>`;
                }

                html += `
                <div class="col-lg-4 col-md-6">
                  <div class="room-card">
                    ${imgHtml}
                    <div class="p-3">
                      <div class="d-flex justify-content-between align-items-start mb-1">
                        <h6 class="fw-bold mb-0" style="color:#4d4335">${r.room_name}</h6>
                        ${statusBadge}
                      </div>
                      <div class="small text-muted mb-2">
                        <i class="bi bi-tag me-1 text-rust"></i>${r.type_name || 'N/A'}
                      </div>
                      <div class="d-flex gap-3 text-muted small mb-2">
                        <span><i class="bi bi-people me-1"></i>${r.capacity} guests</span>
                        <span><i class="bi bi-moon me-1"></i>&#8369;${parseFloat(r.price).toLocaleString()}/night</span>
                      </div>
                      <p class="small text-muted mb-2">
                        ${(r.description || '').substring(0, 90)}${(r.description || '').length > 90 ? '...' : ''}
                      </p>
                      ${bookBtn}
                    </div>
                  </div>
                </div>`;
            });

        } else {
            $("#roomCount").text(0);
            html = `<div class="col-12 text-center py-5 text-muted">
                        <i class="bi bi-door-closed fs-1 d-block mb-2 opacity-25"></i>
                        <p>No rooms found matching your criteria.</p>
                        <button class="btn btn-outline-gold btn-sm" onclick="clearFilters()">Clear Filters</button>
                    </div>`;
        }

        $("#roomsGrid").html(html);

    }, 'text').fail(function () {
        $("#roomCount").text(0);
        $("#roomsGrid").html(`<div class="col-12 text-center py-5 text-danger">
            <i class="bi bi-exclamation-circle fs-1 d-block mb-2"></i>
            <p>Could not load rooms. Please refresh the page.</p>
        </div>`);
    });
}

function clearFilters() {
    $("#filterType").val("");
    $("#filterMinPrice").val("");
    $("#filterMaxPrice").val("");
    $("#filterCapacity").val("");
    loadRooms();
}
