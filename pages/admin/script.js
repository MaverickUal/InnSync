const API          = '../../api/dashboard.php';
const ACCOUNTS_API = '../../api/accounts.php';

let allRooms = [];

$(document).ready(function () {
    updateClock();
    setInterval(updateClock, 1000);
    loadStats();
    loadRoomStatus();
    loadRecentBookings();

    $('#roomFilter').on('click', 'button', function () {
        $('#roomFilter button').removeClass('active btn-brown btn-outline-success btn-outline-danger btn-outline-warning btn-outline-secondary');
        const f = $(this).data('filter');
        if      (f === 'available')   $(this).addClass('active btn-outline-success');
        else if (f === 'occupied')    $(this).addClass('active btn-outline-danger');
        else if (f === 'maintenance') $(this).addClass('active btn-outline-warning');
        else                          $(this).addClass('active btn-brown');
        renderRoomGrid(allRooms, f);
    });
});

// ── CLOCK ──────────────────────────────────────────────────────────────────────
function updateClock() {
    const now = new Date();
    $('#dashClock').text(now.toLocaleString('en-PH', {
        weekday:'short', year:'numeric', month:'short',
        day:'numeric', hour:'2-digit', minute:'2-digit'
    }));
}

// ── STATS ──────────────────────────────────────────────────────────────────────
function loadStats() {
    $.get(API, { action: 'stats' }, function (raw) {
        const resp = typeof raw === 'string' ? JSON.parse(raw) : raw;
        if (resp.status !== 'success') return;
        const d = resp.data;

        $('#statUsers').text(d.users.total);
        $('#statApprovedUsers').text(d.users.approved ?? (d.users.total - (d.users.pending || 0)));
        $('#statAvailable').text(d.rooms.available);
        $('#statOccupied').text(d.occupied_rooms);
        $('#statBookings').text(d.bookings.total);
        $('#statRevenue').text('₱' + parseFloat(d.revenue.total_collected || 0)
            .toLocaleString('en-PH', { minimumFractionDigits: 0 }));

        const total = parseInt(d.rooms.total_rooms) || 1;
        const pct   = Math.round((parseInt(d.occupied_rooms) / total) * 100);
        $('#occupancyFill').css('width', pct + '%');
        $('#occupancyLabel').text(pct + '% occupied (' + d.occupied_rooms + ' of ' + total + ' rooms)');
        $('#occ-avail').text(d.rooms.available);
        $('#occ-occ').text(d.occupied_rooms);
        $('#occ-maint').text(d.rooms.maintenance);
        $('#occ-unavail').text(d.rooms.unavailable);

        const fmt = n => '₱' + parseFloat(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 0 });
        $('#payDP').text(fmt(d.revenue.total_downpayments));
        $('#payFull').text(fmt(d.revenue.total_remaining));
        $('#payRefunded').text(fmt(d.revenue.total_refunded));
        $('#payTotal').text(fmt(d.revenue.total_collected));

        if (d.pending_refunds > 0) {
            $('#refundAlertText').text(d.pending_refunds + ' pending refund request(s) need attention.');
            $('#refundAlert').removeClass('d-none');
        }
    }, 'text');
}

// ── ROOM STATUS GRID ───────────────────────────────────────────────────────────
function loadRoomStatus() {
    $.get(API, { action: 'room_status' }, function (raw) {
        const resp = typeof raw === 'string' ? JSON.parse(raw) : raw;
        if (resp.status !== 'success') return;
        allRooms = resp.data;
        renderRoomGrid(allRooms, 'all');
    }, 'text');
}

function renderRoomGrid(rooms, filter) {
    const filtered = filter === 'all' ? rooms : rooms.filter(r => {
        if (filter === 'occupied')    return r.booking_id !== null || r.room_status === 'occupied';
        if (filter === 'available')   return r.room_status === 'available' && r.booking_id === null;
        if (filter === 'maintenance') return r.room_status === 'maintenance';
        return false;
    });

    if (!filtered.length) {
        $('#roomGrid').html('<div class="text-muted text-center py-4 w-100">No rooms match this filter.</div>');
        return;
    }

    let html = '';
    filtered.forEach(r => {
        const isOccupied = r.booking_id !== null || r.room_status === 'occupied';
        let statusClass = '', badge = '';

        if (isOccupied) {
            statusClass = 'is-occupied';
            badge = '<span class="room-badge occupied"><i class="bi bi-circle-fill" style="font-size:8px"></i> Occupied</span>';
        } else if (r.room_status === 'maintenance') {
            statusClass = 'is-maintenance';
            badge = '<span class="room-badge maintenance"><i class="bi bi-circle-fill" style="font-size:8px"></i> Maintenance</span>';
        } else if (r.room_status === 'unavailable') {
            statusClass = 'is-unavailable';
            badge = '<span class="room-badge unavailable"><i class="bi bi-circle-fill" style="font-size:8px"></i> Unavailable</span>';
        } else {
            badge = '<span class="room-badge available"><i class="bi bi-circle-fill" style="font-size:8px"></i> Available</span>';
        }

        let guestInfo = '';
        if (isOccupied) {
            const ci = r.check_in  ? r.check_in.substring(0, 16)  : '';
            const co = r.check_out ? r.check_out.substring(0, 16) : '';
            guestInfo = `<div class="mt-1" style="font-size:0.8rem;">
                            <i class="bi bi-person-fill me-1 text-danger"></i>${r.guest_name || 'Guest'}
                         </div>
                         <div class="text-muted" style="font-size:0.7rem;">${ci} → ${co}</div>`;
        }

        html += `<div class="room-card ${statusClass}">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div>
                    <div class="room-number">${r.room_name}</div>
                    <div class="room-type">${r.type_name || 'Room'} &bull; ₱${parseFloat(r.price).toLocaleString()}/night</div>
                </div>
                ${badge}
            </div>
            ${guestInfo}
        </div>`;
    });
    $('#roomGrid').html(html);
}

// ── RECENT BOOKINGS ────────────────────────────────────────────────────────────
function loadRecentBookings() {
    $.get(API, { action: 'recent_bookings', limit: 8 }, function (raw) {
        const resp = typeof raw === 'string' ? JSON.parse(raw) : raw;
        if (resp.status !== 'success') {
            $('#recentBookings').html('<tr><td colspan="7" class="text-center py-4 text-danger">Failed to load.</td></tr>');
            return;
        }
        if (!resp.data.length) {
            $('#recentBookings').html('<tr><td colspan="7" class="text-center py-4 text-muted">No bookings yet.</td></tr>');
            return;
        }

        const pillClass = { pending:'pill-pending', confirmed:'pill-confirmed', cancelled:'pill-cancelled', completed:'pill-completed' };
        const payLabel  = { downpayment_only:'Partial', full_payment:'Full' };

        let html = '';
        resp.data.forEach(b => {
            const pill = pillClass[b.status] || 'pill-pending';
            const ci   = b.check_in  ? b.check_in.substring(0, 16)  : '—';
            const co   = b.check_out ? b.check_out.substring(0, 16) : '—';
            const pay  = b.payment_type
                ? `<span class="status-pill" style="background:#e0e7ff;color:#3730a3;">${payLabel[b.payment_type] || '—'}</span>`
                : '<span class="text-muted">—</span>';
            html += `<tr>
                <td class="text-muted">#${b.booking_id}</td>
                <td class="fw-semibold">${b.fullname || '—'}</td>
                <td>${b.room_name || '—'}</td>
                <td>${ci}</td>
                <td>${co}</td>
                <td><span class="status-pill ${pill}">${b.status}</span></td>
                <td>${pay}</td>
            </tr>`;
        });
        $('#recentBookings').html(html);
    }, 'text');
}
