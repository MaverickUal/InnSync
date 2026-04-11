const API = "../../api/accounts.php";

function update() {
    const fullname = $("#txtFullname").val().trim();
    const email    = $("#txtEmail").val().trim();
    const contact  = $("#txtContact").val().trim();

    if (!fullname || !email) {
        showAlert("Please fill in your name and email.", "warning");
        return;
    }

    const payload = { fullname, email, contact_number: contact, role: "customer" };

    $.ajax({
        url: API,
        type: "POST",
        data: { action: "update", id: USER_ID, payload: JSON.stringify(payload) },
        dataType: "text",
        success: function (response) {
            const resp = typeof response === "string" ? JSON.parse(response) : response;
            showAlert(resp.message, resp.status === "success" ? "success" : "danger");
        },
        error: function () {
            showAlert("Could not save changes. Please try again.", "danger");
        }
    });
}

function showAlert(msg, type) {
    $("#alertBox").html(`<div class="alert alert-${type} py-2 small">${msg}</div>`);
}
