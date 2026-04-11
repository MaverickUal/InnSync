const API       = "../../../api/reservation_type.php";
const ROOMS_API = "../../../api/rooms.php";
let editMode    = false;

$(document).ready(function () {
    loadTypes();
});

// ── Load reservation types, then count room usage ─────────────────────────────
function loadTypes() {
    $("#typesList").html(`<tr><td colspan="6" class="text-center py-4 text-muted">
        <div class="spinner-border spinner-border-sm me-2 text-primary"></div>Loading...</td></tr>`);

    $.get(API, { action: "get" }, function (raw) {
        const types = JSON.parse(raw);

        if (types.status !== "success" || !types.data) {
            $("#typesList").html(`<tr><td colspan="6" class="text-center py-4 text-danger">
                Failed to load reservation types.</td></tr>`);
            return;
        }

        // Fetch rooms to count usage per promo
        $.get(ROOMS_API, { action: "get" }, function (roomRaw) {
            let roomCounts = {};
            try {
                const rooms = JSON.parse(roomRaw);
                if (rooms.status === "success") {
                    rooms.data.forEach(r => {
                        if (r.promo_id) {
                            roomCounts[r.promo_id] = (roomCounts[r.promo_id] || 0) + 1;
                        }
                    });
                }
            } catch(e) {}

            renderTable(types.data, roomCounts);
        }, "text").fail(function () {
            // Rooms fetch failed — render table without counts
            renderTable(types.data, {});
        });

    }, "text").fail(function () {
        $("#typesList").html(`<tr><td colspan="6" class="text-center py-4 text-danger">
            Failed to reach API.</td></tr>`);
    });
}

function renderTable(data, roomCounts) {
    if (!data.length) {
        $("#typesList").html(`<tr><td colspan="6" class="text-center py-5 text-muted">
            <i class="bi bi-tags fs-1 d-block mb-2 opacity-25"></i>
            No reservation types yet. Click <strong>+ Add Reservation Type</strong> to get started.
        </td></tr>`);
        return;
    }

    let html = "";
    data.forEach((t, i) => {
        const disc = parseFloat(t.discount_percent);
        const discCell = disc > 0
            ? `<span class="discount-badge"><i class="bi bi-percent"></i>${disc}% off</span>`
            : `<span class="no-discount">—</span>`;

        const usageCount = roomCounts[t.reservation_type_id] || 0;
        const usageCell  = usageCount > 0
            ? `<span class="badge bg-primary">${usageCount} room${usageCount !== 1 ? "s" : ""}</span>`
            : `<span class="text-muted small">None</span>`;

        const safeName = String(t.type_name).replace(/'/g, "\\'");
        const safeDesc = (t.description || "").replace(/'/g, "\\'");

        html += `<tr>
            <td>${i + 1}</td>
            <td class="fw-semibold">${t.type_name}</td>
            <td class="text-muted small">${t.description || "—"}</td>
            <td>${discCell}</td>
            <td>${usageCell}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1"
                    onclick="editType(${t.reservation_type_id},'${safeName}','${safeDesc}',${disc})"
                    title="Edit"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger"
                    onclick="openDeleteModal(${t.reservation_type_id},'${safeName}')"
                    title="Delete"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    });
    $("#typesList").html(html);
}

// ── Open Add Modal ────────────────────────────────────────────────────────────
function openModal() {
    editMode = false;
    $("#modalTitle").text("Add Reservation Type");
    $("#typeId, #txtTypeName, #txtTypeDesc").val("");
    $("#txtDiscount").val("0");
    new bootstrap.Modal(document.getElementById("typeModal")).show();
}

// ── Open Edit Modal ───────────────────────────────────────────────────────────
function editType(id, name, desc, discount) {
    editMode = true;
    $("#modalTitle").text("Edit Reservation Type");
    $("#typeId").val(id);
    $("#txtTypeName").val(name);
    $("#txtTypeDesc").val(desc);
    $("#txtDiscount").val(discount);
    new bootstrap.Modal(document.getElementById("typeModal")).show();
}

// ── Save ──────────────────────────────────────────────────────────────────────
function saveType() {
    const name     = $("#txtTypeName").val().trim();
    const desc     = $("#txtTypeDesc").val().trim();
    const discount = parseFloat($("#txtDiscount").val()) || 0;

    if (!name) { alert("Type name is required."); return; }
    if (discount < 0 || discount > 100) { alert("Discount must be between 0 and 100."); return; }

    const payload = JSON.stringify({ type_name: name, description: desc, discount_percent: discount });
    const id      = $("#typeId").val();
    const data    = editMode
        ? { action: "update", id, payload }
        : { action: "store",  payload };

    $.post(API, data, function (response) {
        const resp = JSON.parse(response);
        if (resp.status === "success") {
            bootstrap.Modal.getInstance(document.getElementById("typeModal")).hide();
            loadTypes();
        } else {
            alert("Error: " + resp.message);
        }
    });
}

// ── Delete confirm modal ──────────────────────────────────────────────────────
function openDeleteModal(id, name) {
    $("#deleteTypeId").val(id);
    $("#deleteTypeName").text(name);
    new bootstrap.Modal(document.getElementById("deleteModal")).show();
}

function confirmDrop() {
    const id = $("#deleteTypeId").val();
    $.post(API, { action: "drop", id }, function (response) {
        const resp = JSON.parse(response);
        if (resp.status === "success") {
            bootstrap.Modal.getInstance(document.getElementById("deleteModal")).hide();
            loadTypes();
        } else {
            alert("Error: " + resp.message);
        }
    });
}
