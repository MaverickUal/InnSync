const API = "../../api/register.php";

function store() {
    let fullname = $("#txtFullname").val().trim();
    let email = $("#txtEmail").val().trim();
    let password = $("#txtPassword").val();
    let confirm = $("#txtConfirmPassword").val();

    if (!fullname || !email || !password || !confirm) {
        showAlert("Please fill in all fields.", "warning");
        return;
    }

    if (password !== confirm) {
        showAlert("Passwords do not match.", "danger");
        return;
    }

    if (password.length < 6) {
        showAlert("Password must be at least 6 characters.", "warning");
        return;
    }

    let payload = { fullname, email, password };

    $("#btnRegister").prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-2"></span>Registering...');

    $.ajax({
        url: API,
        type: "POST",
        data: "action=store&payload=" + JSON.stringify(payload),
        success: function (response) {
            let resp = JSON.parse(response);
            if (resp.status == "success") {
                showAlert(resp.message + " Redirecting to login...", "success");
                $("#txtFullname, #txtEmail, #txtPassword, #txtConfirmPassword").val("");
                setTimeout(() => window.location.href = "../../", 2000);
            } else {
                showAlert(resp.message, "danger");
                $("#btnRegister").prop("disabled", false).html("Create Account");
            }
        },
        error: function () {
            showAlert("Server error. Please try again.", "danger");
            $("#btnRegister").prop("disabled", false).html("Create Account");
        }
    });
}

function showAlert(msg, type) {
    $("#alertBox").html(`<div class="alert alert-${type} py-2">${msg}</div>`);
}
