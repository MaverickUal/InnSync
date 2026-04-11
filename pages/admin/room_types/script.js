const API = "../../../api/room_type.php";
let editMode = false;

$(document).ready(function(){ loadTypes(); });

function loadTypes() {
    $.get(API, "action=get", function(response) {
        let resp = JSON.parse(response);
        let html = "";
        if (resp.status == "success" && resp.data.length > 0) {
            resp.data.forEach(t => {
                html += `<tr>
                    <td>${t.type_id}</td>
                    <td>${t.type_name}</td>
                    <td>${t.description || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editType(${t.type_id},'${t.type_name}','${t.description||''}')"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="dropType(${t.type_id})"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`;
            });
        } else {
            html = `<tr><td colspan="4" class="text-center py-4 text-muted">No room types yet.</td></tr>`;
        }
        $("#typesList").html(html);
    });
}

function openModal() {
    editMode = false;
    $("#modalTitle").text("Add Room Type");
    $("#typeId, #txtTypeName, #txtTypeDesc").val("");
    new bootstrap.Modal(document.getElementById("typeModal")).show();
}

function editType(id, name, desc) {
    editMode = true;
    $("#modalTitle").text("Edit Room Type");
    $("#typeId").val(id);
    $("#txtTypeName").val(name);
    $("#txtTypeDesc").val(desc);
    new bootstrap.Modal(document.getElementById("typeModal")).show();
}

function saveType() {
    let payload = { type_name: $("#txtTypeName").val(), description: $("#txtTypeDesc").val() };
    let data = editMode
        ? `action=update&id=${$("#typeId").val()}&payload=${JSON.stringify(payload)}`
        : `action=store&payload=${JSON.stringify(payload)}`;
    $.post(API, data, function(response) {
        let resp = JSON.parse(response);
        alert(resp.message);
        if (resp.status == "success") { bootstrap.Modal.getInstance(document.getElementById("typeModal")).hide(); loadTypes(); }
    });
}

function dropType(id) {
    if (!confirm("Delete this room type?")) return;
    $.post(API, { action: "drop", id }, function(response) {
        let resp = JSON.parse(response);
        alert(resp.message);
        if (resp.status == "success") loadTypes();
    });
}
