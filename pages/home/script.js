const BOOKING_API = "../../api/booking.php";
const ROOMS_API   = "../../api/rooms.php";

$(document).ready(function () {
    loadStats();
    loadRooms();
});

function loadStats() {
    $.get(BOOKING_API, { action: "getByUser" }, function (response) {
        let resp = typeof response === 'string' ? JSON.parse(response) : response;
        if (resp.status == "success") {
            let b = resp.data;
            $("#sBTotal").text(b.length);
            $("#sBPending").text(b.filter(x => x.status == "pending").length);
            $("#sBConfirmed").text(b.filter(x => x.status == "confirmed").length);
        }
    }, 'text');
}

function loadRooms() {
    $.get(ROOMS_API, { action: "get" }, function (response) {
        let resp = typeof response === 'string' ? JSON.parse(response) : response;
        let html = "";
        if (resp.status == "success" && resp.data.length > 0) {
            resp.data.slice(0, 3).forEach(r => {
                const img = r.image_path
                    ? `<img src="../../api/${r.image_path}" alt="${r.room_name}">`
                    : `<div style="height:185px;background:linear-gradient(135deg,#4d4335,#985b36);display:flex;align-items:center;justify-content:center;font-size:3rem;color:rgba(255,255,255,.2)"><i class="bi bi-building"></i></div>`;

                const isOccupied = parseInt(r.booking_occupied) > 0 || parseInt(r.manual_occupied) > 0;
                let statusBadge, bookBtn;
                if (isOccupied) {
                    statusBadge = `<span class="badge bg-danger" style="font-size:.72rem">Occupied</span>`;
                    bookBtn     = `<button class="btn btn-danger btn-sm w-100" disabled><i class="bi bi-person-fill me-1"></i>Currently Occupied</button>`;
                } else if (r.status === 'maintenance') {
                    statusBadge = `<span class="badge bg-warning text-dark" style="font-size:.72rem">Maintenance</span>`;
                    bookBtn     = `<button class="btn btn-warning btn-sm w-100" disabled><i class="bi bi-tools me-1"></i>Under Maintenance</button>`;
                } else if (r.status === 'unavailable' || r.status === 'occupied') {
                    statusBadge = `<span class="badge bg-secondary" style="font-size:.72rem">Unavailable</span>`;
                    bookBtn     = `<button class="btn btn-secondary btn-sm w-100" disabled>Not Available</button>`;
                } else {
                    statusBadge = `<span class="badge" style="background:#985b36;font-size:.72rem">${r.type_name || 'Room'}</span>`;
                    bookBtn     = `<a href="../booking?room_id=${r.room_id}" class="btn btn-gold btn-sm w-100 fw-semibold"><i class="bi bi-calendar-check me-1"></i>Book This Room</a>`;
                }

                html += `
                <div class="col-md-4">
                  <div class="room-card">
                    ${img}
                    <div class="p-3">
                      <div class="d-flex justify-content-between align-items-start mb-1">
                        <h6 class="fw-bold mb-0" style="color:#4d4335">${r.room_name}</h6>
                        ${statusBadge}
                      </div>
                      <div class="d-flex gap-3 text-muted small my-2">
                        <span><i class="bi bi-people me-1"></i>${r.capacity} guests</span>
                        <span><i class="bi bi-moon me-1"></i>₱${parseFloat(r.price).toLocaleString()}/night</span>
                      </div>
                      <p class="small text-muted mb-3">${(r.description || '').substring(0, 70)}...</p>
                      ${bookBtn}
                    </div>
                  </div>
                </div>`;
            });
        } else {
            html = `<div class="col-12 text-center text-muted py-4">No rooms available yet.</div>`;
        }
        $("#homeRoomsGrid").html(html);
    }, 'text').fail(function () {
        $("#homeRoomsGrid").html(`<div class="col-12 text-center text-muted py-4">Could not load rooms. Please refresh.</div>`);
    });
}