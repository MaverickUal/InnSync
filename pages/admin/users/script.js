const API         = "../../../api/accounts.php";
const DASH_API    = "../../../api/dashboard.php";
let allUsers      = [];
let currentFilter = 'all';

$(document).ready(function () {
    loadUsers();

    // Filter tabs
    $('.filter-tab').on('click', function () {
        $('.filter-tab').removeClass('active');
        $(this).addClass('active');
        currentFilter = $(this).data('filter');
        applyFilters();
    });

    // Search
    $('#searchInput').on('input', applyFilters);
});

function loadUsers() {
    $.get(API, "action=get", function (response) {
        const resp = typeof response === 'string' ? JSON.parse(response) : response;
        if (resp.status === "success") {
            allUsers = resp.data;
            applyFilters();
        }
    });
}

function applyFilters() {
    const search = $('#searchInput').val().toLowerCase().trim();
    let filtered = allUsers;

    if (currentFilter !== 'all') {
        filtered = filtered.filter(u => u.status === currentFilter);
    }
    if (search) {
        filtered = filtered.filter(u =>
            u.fullname.toLowerCase().includes(search) ||
            u.email.toLowerCase().includes(search)
        );
    }

    renderUsers(filtered);
}

function renderUsers(users) {
    $("#userCount").text(users.length);
    if (!users.length) {
        $("#usersList").html(`<tr><td colspan="8" class="text-center py-4 text-muted">No users found.</td></tr>`);
        return;
    }

    let html = "";
    users.forEach(u => {
        const roleBadge = u.role === 'admin'
            ? `<span class="badge" style="background:#4d4335;color:#fff">Admin</span>`
            : `<span class="badge" style="background:#985b36;color:#fff">Customer</span>`;

        const statusPill = {
            approved:  '<span class="status-pill pill-approved">Approved</span>',
            blacklist: '<span class="status-pill pill-blacklist">Blacklist</span>'
        }[u.status] || `<span class="status-pill pill-blacklist">${u.status}</span>`;

        // Blacklist / Approve toggle buttons
        let approvalBtns = '';
        const name = u.fullname.replace(/'/g, "\\'");
        if (u.status !== 'approved') {
            approvalBtns += `
                <button class="btn btn-xs btn-success me-1" style="padding:1px 8px;font-size:0.72rem;" title="Approve" onclick="promptStatus(${u.user_id},'approved','${name}')">
                    <i class="bi bi-check-lg"></i>
                </button>`;
        }
        if (u.status !== 'blacklist') {
            approvalBtns += `
                <button class="btn btn-xs btn-danger me-1" style="padding:1px 8px;font-size:0.72rem;" title="Blacklist" onclick="promptStatus(${u.user_id},'blacklist','${name}')">
                    <i class="bi bi-slash-circle"></i>
                </button>`;
        }

        html += `<tr>
            <td>${u.user_id}</td>
            <td class="fw-semibold">${u.fullname}</td>
            <td>${u.email}</td>
            <td>${u.contact_number || '-'}</td>
            <td>${roleBadge}</td>
            <td>${statusPill}</td>
            <td>${u.created_at ? u.created_at.substring(0,10) : '—'}</td>
            <td>
                ${approvalBtns}
                <button class="btn btn-sm btn-outline-secondary me-1" title="Edit" onclick="openEdit(${u.user_id})"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="dropUser(${u.user_id})"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    });

    $("#usersList").html(html);
}

// ── APPROVE / BLACKLIST ────────────────────────────────────────────────────────
function promptStatus(userId, newStatus, name) {
    const verb = newStatus === 'approved' ? 'Approve' : 'Blacklist';
    $('#statusModalTitle').text(verb + ' User');
    $('#statusModalBody').text(verb + ' account for "' + name + '"?');
    $('#statusConfirmBtn').text(verb).off('click').on('click', function () {
        $.post(DASH_API, { action: 'approve_user', id: userId, status: newStatus }, function (raw) {
            const resp = typeof raw === 'string' ? JSON.parse(raw) : raw;
            bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
            if (resp.status === 'success') {
                const u = allUsers.find(x => x.user_id == userId);
                if (u) u.status = newStatus;
                applyFilters();
            } else {
                alert('Error: ' + resp.message);
            }
        }, 'text');
    });
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

// ── EDIT ────────────────────────────────────────────────────────────────
function openEdit(id) {
    const u = allUsers.find(x => x.user_id == id);
    if (!u) return;
    $("#editId").val(u.user_id);
    $("#editName").val(u.fullname);
    $("#editEmail").val(u.email);
    $("#editContact").val(u.contact_number || "");
    $("#editRole").val(u.role);
    $("#editStatus").val(u.status);
    new bootstrap.Modal(document.getElementById("editModal")).show();
}

function saveEdit() {
    const id = $("#editId").val();
    const payload = {
        fullname:       $("#editName").val(),
        email:          $("#editEmail").val(),
        contact_number: $("#editContact").val(),
        role:           $("#editRole").val()
    };

    // First update account info
    $.post(API, { action: "update", id, payload: JSON.stringify(payload) }, function (response) {
        const resp = typeof response === 'string' ? JSON.parse(response) : response;

        if (resp.status === "success") {
            // Then update status via dashboard API
            const newStatus = $("#editStatus").val();
            $.post(DASH_API, { action: 'approve_user', id, status: newStatus }, function () {
                bootstrap.Modal.getInstance(document.getElementById("editModal")).hide();
                loadUsers();
            }, 'text');
        } else {
            alert(resp.message);
        }
    });
}

// ── DELETE ──────────────────────────────────────────────────────────────
function dropUser(id) {
    if (!confirm("Permanently delete this user? This cannot be undone.")) return;
    $.post(API, { action: "drop", id }, function (response) {
        const resp = typeof response === 'string' ? JSON.parse(response) : response;
        if (resp.status === "success") {
            allUsers = allUsers.filter(u => u.user_id != id);
            applyFilters();
        } else {
            alert(resp.message);
        }
    });
}
