const ROOMS_API  = '../../../api/rooms.php';
const TYPES_API  = '../../../api/room_type.php';
const PROMOS_API = '../../../api/reservation_type.php';

let promoData = []; // cache for promo list

$(document).ready(function () {
    loadTypes();
    loadPromos();
    loadRooms();

    $('#fileImage').on('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            $('#imgPreview').attr('src', e.target.result);
            $('#imgNote').text('New image selected — will replace existing on save.');
            $('#imgPreviewWrap').show();
        };
        reader.readAsDataURL(file);
    });

    // Show promo preview when selection changes
    $('#selPromo').on('change', updatePromoPreview);
});

function parseResp(raw) {
    try { return JSON.parse(raw); }
    catch { return { status: 'failed', message: 'Bad response: ' + String(raw).substring(0, 200) }; }
}

// ── Load room types into modal select ────────────────────────────────────────
function loadTypes() {
    $.get(TYPES_API, { action: 'get' }, function (raw) {
        const resp = parseResp(raw);
        $('#selRoomType').html('<option value="">-- Select Type --</option>');
        if (resp.status === 'success') {
            resp.data.forEach(t =>
                $('#selRoomType').append(`<option value="${t.type_id}">${t.type_name}</option>`)
            );
        }
    }, 'text');
}

// ── Load reservation types (promos) into modal select ────────────────────────
function loadPromos() {
    $.get(PROMOS_API, { action: 'get' }, function (raw) {
        const resp = parseResp(raw);
        promoData = [];
        $('#selPromo').html('<option value="">-- No Promo --</option>');
        if (resp.status === 'success') {
            resp.data.forEach(p => {
                promoData.push(p);
                const discLabel = parseFloat(p.discount_percent) > 0
                    ? ` (${parseFloat(p.discount_percent)}% off)` : '';
                $('#selPromo').append(
                    `<option value="${p.reservation_type_id}">${p.type_name}${discLabel}</option>`
                );
            });
        }
    }, 'text');
}

// ── Show discount preview below promo select ─────────────────────────────────
function updatePromoPreview() {
    const selId = $('#selPromo').val();
    if (!selId) {
        $('#promoPreview').addClass('d-none');
        return;
    }
    const promo = promoData.find(p => p.reservation_type_id == selId);
    if (!promo) { $('#promoPreview').addClass('d-none'); return; }

    const disc = parseFloat(promo.discount_percent);
    if (disc > 0) {
        $('#promoPreviewText').text(disc + '% discount applied to booking total');
    } else {
        $('#promoPreviewText').text('No discount on this promo');
    }
    $('#promoPreviewDesc').text(promo.description || '');
    $('#promoPreview').removeClass('d-none');
}

// ── Status badge ─────────────────────────────────────────────────────────────
function statusBadge(status, bookingOccupied) {
    if (parseInt(bookingOccupied) > 0 && status === 'available') {
        return `<span class="badge bg-danger">Occupied</span>
                <span class="badge bg-secondary ms-1" style="font-size:0.65rem">booking</span>`;
    }
    const map = {
        available:   'bg-success',
        occupied:    'bg-danger',
        unavailable: 'bg-secondary',
        maintenance: 'bg-warning text-dark'
    };
    const cls = map[status] || 'bg-secondary';
    const lbl = status.charAt(0).toUpperCase() + status.slice(1);
    return `<span class="badge ${cls}">${lbl}</span>`;
}

// ── Load rooms table ──────────────────────────────────────────────────────────
function loadRooms() {
    $('#roomsList').html(`
        <tr><td colspan="8" class="text-center py-4 text-muted">
            <div class="spinner-border spinner-border-sm me-2 text-primary"></div>Loading rooms...
        </td></tr>`);

    $.ajax({ url: ROOMS_API, type: 'GET', data: { action: 'get' }, dataType: 'text' })
    .done(function (raw) {
        const resp = parseResp(raw);
        let html = '';

        if (resp.status === 'success' && resp.data && resp.data.length > 0) {
            resp.data.forEach(r => {
                const thumb = r.image_path
                    ? `<img src="../../../api/${r.image_path}" class="room-thumb" alt="${r.room_name}">`
                    : `<div class="room-thumb-placeholder"><i class="bi bi-image"></i></div>`;

                const safeName = String(r.room_name).replace(/'/g, "\\'").replace(/"/g, '&quot;');

                const promoCell = r.promo_name
                    ? `<span class="promo-badge">
                           <i class="bi bi-tag-fill"></i>${r.promo_name}
                           ${parseFloat(r.promo_discount) > 0 ? `<span class="ms-1">${parseFloat(r.promo_discount)}% off</span>` : ''}
                       </span>`
                    : `<span class="text-muted small">—</span>`;

                html += `
                <tr>
                    <td>${thumb}</td>
                    <td class="fw-semibold">${r.room_name}</td>
                    <td>${r.type_name || '<span class="text-muted">—</span>'}</td>
                    <td>${promoCell}</td>
                    <td>₱${parseFloat(r.price).toLocaleString()}</td>
                    <td>${r.capacity} guest${r.capacity != 1 ? 's' : ''}</td>
                    <td>${statusBadge(r.status, r.booking_occupied)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1"
                            onclick="openEditModal(${r.room_id})" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger"
                            onclick="openDeleteModal(${r.room_id}, '${safeName}')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
        } else if (resp.status === 'success') {
            html = `<tr><td colspan="8" class="text-center py-5 text-muted">
                <i class="bi bi-door-closed fs-1 d-block mb-2 opacity-25"></i>
                No rooms yet. Click <strong>+ Add Room</strong> to get started.
            </td></tr>`;
        } else {
            html = `<tr><td colspan="8" class="text-center py-4 text-danger">
                <i class="bi bi-exclamation-triangle me-1"></i>${resp.message}
            </td></tr>`;
        }
        $('#roomsList').html(html);
    })
    .fail(function (xhr) {
        $('#roomsList').html(`
            <tr><td colspan="8" class="text-center py-4 text-danger">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Failed to reach API (HTTP ${xhr.status}).
            </td></tr>`);
    });
}

// ── Open Add Modal ────────────────────────────────────────────────────────────
function openAddModal() {
    $('#modalTitle').text('Add Room');
    $('#roomId, #txtRoomName, #txtPrice, #txtCapacity, #txtDescription').val('');
    $('#selRoomType').val('');
    $('#selStatus').val('available');
    $('#selPromo').val('');
    $('#promoPreview').addClass('d-none');
    $('#fileImage').val('');
    $('#imgPreviewWrap').hide();
    new bootstrap.Modal(document.getElementById('roomModal')).show();
}

// ── Open Edit Modal ───────────────────────────────────────────────────────────
function openEditModal(roomId) {
    $.ajax({ url: ROOMS_API, type: 'GET', data: { action: 'getOne', id: roomId }, dataType: 'text' })
    .done(function (raw) {
        const resp = parseResp(raw);
        if (resp.status !== 'success') { alert('Could not load room: ' + resp.message); return; }
        const r = resp.data;

        $('#modalTitle').text('Edit Room');
        $('#roomId').val(r.room_id);
        $('#txtRoomName').val(r.room_name);
        $('#selRoomType').val(r.type_id);
        $('#txtPrice').val(r.price);
        $('#txtCapacity').val(r.capacity);
        $('#selStatus').val(r.status);
        $('#txtDescription').val(r.description || '');
        $('#fileImage').val('');

        // Set promo
        $('#selPromo').val(r.reservation_type_id || '');
        updatePromoPreview();

        if (r.images && r.images.length > 0) {
            $('#imgPreview').attr('src', '../../../api/' + r.images[0].image_path);
            $('#imgNote').text('Current image — pick a new file to replace it.');
            $('#imgPreviewWrap').show();
        } else {
            $('#imgPreviewWrap').hide();
        }

        new bootstrap.Modal(document.getElementById('roomModal')).show();
    })
    .fail(() => alert('Failed to fetch room data.'));
}

// ── Save Room ─────────────────────────────────────────────────────────────────
function saveRoom() {
    const roomId   = $('#roomId').val();
    const roomName = $('#txtRoomName').val().trim();
    const typeId   = $('#selRoomType').val();
    const price    = $('#txtPrice').val();
    const capacity = $('#txtCapacity').val();
    const status   = $('#selStatus').val();
    const desc     = $('#txtDescription').val().trim();
    const promoId  = $('#selPromo').val() || null;

    if (!roomName)                            { alert('Room name is required.');     return; }
    if (!typeId)                              { alert('Please select a room type.'); return; }
    if (!price || parseFloat(price) < 0)     { alert('Enter a valid price.');       return; }
    if (!capacity || parseInt(capacity) < 1) { alert('Enter a valid capacity.');    return; }

    const payload = JSON.stringify({
        room_name: roomName, type_id: typeId,
        reservation_type_id: promoId,
        price, capacity, status, description: desc
    });

    const fd = new FormData();
    fd.append('action',  roomId ? 'update' : 'store');
    fd.append('payload', payload);
    if (roomId) fd.append('id', roomId);
    const file = $('#fileImage')[0].files[0];
    if (file) fd.append('image', file);

    const btn = $('#btnSave');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

    $.ajax({ url: ROOMS_API, type: 'POST', data: fd, contentType: false, processData: false, dataType: 'text' })
    .done(function (raw) {
        const resp = parseResp(raw);
        if (resp.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('roomModal')).hide();
            loadRooms();
            // Warn admin if image failed to save (room itself was saved successfully)
            if (resp.message && resp.message.includes('image save failed')) {
                setTimeout(() => alert('⚠️ Room saved but image upload failed:\n' + resp.message), 400);
            }
        } else {
            alert('Error: ' + resp.message);
        }
    })
    .fail(xhr => alert('Save failed: ' + xhr.responseText.substring(0, 300)))
    .always(() => btn.prop('disabled', false).html('<i class="bi bi-floppy me-1"></i> Save Room'));
}

// ── Delete ────────────────────────────────────────────────────────────────────
function openDeleteModal(roomId, roomName) {
    $('#deleteRoomId').val(roomId);
    $('#deleteRoomName').text(roomName);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function confirmDelete() {
    const id = $('#deleteRoomId').val();
    $.ajax({ url: ROOMS_API, type: 'POST', data: { action: 'drop', id }, dataType: 'text' })
    .done(function (raw) {
        const resp = parseResp(raw);
        if (resp.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            loadRooms();
        } else {
            alert('Error: ' + resp.message);
        }
    })
    .fail(xhr => alert('Delete failed: ' + xhr.responseText.substring(0, 200)));
}
