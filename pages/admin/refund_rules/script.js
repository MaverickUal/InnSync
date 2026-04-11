const API = "../../../api/refund_rules.php";
let editMode = false;
let rulesCache = [];   // in-memory store — no extra fetch needed for edit

$(document).ready(function () { loadRules(); });

/* ── Toast helper ── */
function showToast(message, success = true) {
    const id = "toast_" + Date.now();
    const color = success ? "#198754" : "#dc3545";
    const icon  = success ? "bi-check-circle-fill" : "bi-x-circle-fill";
    const toast = $(`
        <div id="${id}" style="
            background:#fff;border:1px solid ${color};color:${color};
            padding:0.65rem 1rem;border-radius:8px;font-size:0.875rem;
            box-shadow:0 4px 14px rgba(0,0,0,.1);display:flex;align-items:center;gap:0.5rem;
            animation:fadeInDown .2s ease;min-width:220px;">
            <i class="bi ${icon}"></i><span>${message}</span>
        </div>`);
    $("#toastContainer").append(toast);
    setTimeout(() => { toast.fadeOut(300, () => toast.remove()); }, 3000);
}

/* ── Load & render ── */
function loadRules() {
    $.get(API, "action=get", function (response) {
        const resp = JSON.parse(response);
        rulesCache = (resp.status === "success") ? resp.data : [];
        let html = "";
        if (rulesCache.length > 0) {
            rulesCache.forEach(r => {
                html += `<tr>
                    <td>${r.rule_id}</td>
                    <td>${r.rule_name}</td>
                    <td>${r.days_before} days</td>
                    <td><span class="badge bg-success">${r.refund_percent}%</span></td>
                    <td>${r.description || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editRule(${r.rule_id})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="dropRule(${r.rule_id})"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`;
            });
        } else {
            html = `<tr><td colspan="6" class="text-center py-4 text-muted">No refund rules yet.</td></tr>`;
        }
        $("#rulesList").html(html);
    });
}

/* ── Open add modal ── */
function openModal() {
    editMode = false;
    $("#modalTitle").text("Add Refund Rule");
    $("#ruleId,#txtRuleName,#txtDaysBefore,#txtRefundPercent,#txtRuleDesc").val("");
    new bootstrap.Modal(document.getElementById("ruleModal")).show();
}

/* ── Open edit modal — uses cache, no extra fetch ── */
function editRule(id) {
    const rule = rulesCache.find(r => r.rule_id == id);
    if (!rule) { showToast("Rule not found.", false); return; }
    editMode = true;
    $("#modalTitle").text("Edit Refund Rule");
    $("#ruleId").val(rule.rule_id);
    $("#txtRuleName").val(rule.rule_name);
    $("#txtDaysBefore").val(rule.days_before);
    $("#txtRefundPercent").val(rule.refund_percent);
    $("#txtRuleDesc").val(rule.description);
    new bootstrap.Modal(document.getElementById("ruleModal")).show();
}

/* ── Save (add / update) ── */
function saveRule() {
    const payload = {
        rule_name:      $("#txtRuleName").val().trim(),
        days_before:    $("#txtDaysBefore").val(),
        refund_percent: $("#txtRefundPercent").val(),
        description:    $("#txtRuleDesc").val().trim()
    };

    if (!payload.rule_name) { showToast("Rule name is required.", false); return; }
    if (payload.days_before === "" || payload.refund_percent === "") {
        showToast("Days before and refund % are required.", false); return;
    }

    const data = editMode
        ? `action=update&id=${$("#ruleId").val()}&payload=${JSON.stringify(payload)}`
        : `action=store&payload=${JSON.stringify(payload)}`;

    $.post(API, data, function (response) {
        const resp = JSON.parse(response);
        showToast(resp.message, resp.status === "success");
        if (resp.status === "success") {
            bootstrap.Modal.getInstance(document.getElementById("ruleModal")).hide();
            loadRules();
        }
    });
}

/* ── Delete ── */
function dropRule(id) {
    if (!confirm("Delete this refund rule?")) return;
    $.post(API, { action: "drop", id }, function (response) {
        const resp = JSON.parse(response);
        showToast(resp.message, resp.status === "success");
        if (resp.status === "success") loadRules();
    });
}
