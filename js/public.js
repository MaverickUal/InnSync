// ===== InnSync Public Page JS =====

let authModal;

$(document).ready(function () {
    authModal = new bootstrap.Modal(document.getElementById("authModal"));
    loadPublicRooms();
    smoothScroll();
    navHighlight();
});

// ===== OPEN AUTH MODAL =====
function openAuth(tab = 'login') {
    switchTab(tab);
    authModal.show();
}

function switchTab(tab) {
    if (tab === 'login') {
        $("#loginForm").removeClass("d-none");
        $("#registerForm").addClass("d-none");
        $("#tabLogin").addClass("active");
        $("#tabRegister").removeClass("active");
    } else {
        $("#loginForm").addClass("d-none");
        $("#registerForm").removeClass("d-none");
        $("#tabLogin").removeClass("active");
        $("#tabRegister").addClass("active");
    }
    $("#authAlert").html("");
}

// ===== LOGIN =====
function doLogin() {
    let email    = $("#loginEmail").val().trim();
    let password = $("#loginPassword").val();
    if (!email || !password) { showAuthAlert("Please fill in all fields.", "warning"); return; }

    let payload = { email, password };
    $("#btnLogin").prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-2"></span>Signing in...');

    $.ajax({
        url: "api/login.php",
        type: "POST",
        data: "action=postOne&payload=" + JSON.stringify(payload),
        success: function (response) {
            let resp = typeof response === 'string' ? JSON.parse(response) : response;
            if (resp.status == "success") {
                showAuthAlert("Login successful! Redirecting...", "success");
                $("#loginEmail, #loginPassword").val("");
                setTimeout(() => {
                    window.location.href = resp.role == "admin" ? "pages/admin" : "pages/home";
                }, 800);
            } else if (resp.code === "blacklisted") {
                bootstrap.Modal.getInstance(document.getElementById("authModal"))?.hide();
                new bootstrap.Modal(document.getElementById("blacklistModal")).show();
                $("#btnLogin").prop("disabled", false).html('<i class="bi bi-box-arrow-in-right me-2"></i>Sign In');
            } else {
                showAuthAlert(resp.message, "danger");
                $("#btnLogin").prop("disabled", false).html('<i class="bi bi-box-arrow-in-right me-2"></i>Sign In');
            }
        },
        error: function () {
            showAuthAlert("Server error. Try again.", "danger");
            $("#btnLogin").prop("disabled", false).html('<i class="bi bi-box-arrow-in-right me-2"></i>Sign In');
        }
    });
}

// ===== REGISTER =====
function doRegister() {
    let fullname       = $("#regName").val().trim();
    let email          = $("#regEmail").val().trim();
    let contact_number = $("#regContact").val().trim();
    let password       = $("#regPassword").val();
    let confirm        = $("#regConfirm").val();

    if (!fullname || !email || !contact_number || !password || !confirm) {
        showAuthAlert("Please fill in all fields.", "warning"); return;
    }
    if (password !== confirm) { showAuthAlert("Passwords do not match.", "danger"); return; }
    if (password.length < 6)  { showAuthAlert("Password must be at least 6 characters.", "warning"); return; }
    if (!/^[\d\s\+\-\(\)]{10,15}$/.test(contact_number)) {
        showAuthAlert("Please enter a valid contact number.", "warning"); return;
    }

    let payload = { fullname, email, contact_number, password };
    $("#btnRegister").prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-2"></span>Registering...');

    $.ajax({
        url: "api/register.php",
        type: "POST",
        data: "action=store&payload=" + JSON.stringify(payload),
        success: function (response) {
            let resp = typeof response === 'string' ? JSON.parse(response) : response;
            if (resp.status == "success") {
                showAuthAlert("Account created! You can now sign in.", "success");
                $("#regName, #regEmail, #regContact, #regPassword, #regConfirm").val("");
                setTimeout(() => switchTab('login'), 1800);
            } else {
                showAuthAlert(resp.message, "danger");
            }
            $("#btnRegister").prop("disabled", false).html('<i class="bi bi-person-plus me-2"></i>Create Account');
        },
        error: function () {
            showAuthAlert("Server error. Try again.", "danger");
            $("#btnRegister").prop("disabled", false).html('<i class="bi bi-person-plus me-2"></i>Create Account');
        }
    });
}

// ===== LOAD PUBLIC ROOMS =====
function loadPublicRooms() {
    $.get("api/rooms.php", { action: "get" }, function (response) {
        let resp = typeof response === 'string' ? JSON.parse(response) : response;
        let html = "";
        if (resp.status == "success" && resp.data.length > 0) {
            resp.data.slice(0, 3).forEach(r => {
                let img = r.image_path
                    ? `<div class="img-wrapper"><img src="api/${r.image_path}" alt="${r.room_name}"></div>`
                    : `<div class="no-img"><i class="bi bi-image"></i></div>`;
                html += `
                <div class="col-md-4">
                    <div class="room-preview-card">
                        ${img}
                        <div class="p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="fw-bold mb-1" style="color:var(--brown-dark)">${r.room_name}</h6>
                                <span class="badge" style="background:var(--rust)">${r.type_name || 'Room'}</span>
                            </div>
                            <div class="d-flex gap-3 text-muted small my-2">
                                <span><i class="bi bi-people me-1"></i>${r.capacity} guests</span>
                                <span><i class="bi bi-moon me-1"></i>₱${parseFloat(r.price).toLocaleString()}/night</span>
                            </div>
                            <p class="small text-muted mb-3">${(r.description || '').substring(0, 80)}...</p>
                            <button class="btn btn-gold btn-sm w-100 fw-semibold" onclick="openAuth('register')">
                                <i class="bi bi-calendar-check me-1"></i>Book This Room
                            </button>
                        </div>
                    </div>
                </div>`;
            });
        } else {
            ["Standard Room", "Deluxe Room", "Executive Suite"].forEach(name => {
                html += `
                <div class="col-md-4">
                    <div class="room-preview-card">
                        <div class="no-img"><i class="bi bi-building"></i></div>
                        <div class="p-3">
                            <h6 class="fw-bold" style="color:var(--brown-dark)">${name}</h6>
                            <p class="small text-muted">Comfortable and elegantly designed for your perfect stay.</p>
                            <button class="btn btn-gold btn-sm w-100" onclick="openAuth('register')">Book Now</button>
                        </div>
                    </div>
                </div>`;
            });
        }
        $("#publicRoomsGrid").html(html);
    }, 'text');
}

// ===== CONTACT FORM =====
function submitContact() {
    let name    = $("#cName").val().trim();
    let email   = $("#cEmail").val().trim();
    let subject = $("#cSubject").val();
    let message = $("#cMessage").val().trim();

    if (!name || !email || !subject || !message) {
        $("#contactAlert").html(`<div class="alert alert-warning py-2 small">Please fill in all fields.</div>`);
        return;
    }

    $("#contactAlert").html(`<div class="alert alert-success py-2 small"><i class="bi bi-check-circle me-1"></i>Message sent! We'll get back to you within 24 hours.</div>`);
    $("#cName, #cEmail, #cMessage").val("");
    $("#cSubject").val("");
}

// ===== SMOOTH SCROLL =====
function smoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) target.scrollIntoView({ behavior: 'smooth' });
        });
    });
}

// ===== NAV HIGHLIGHT ON SCROLL =====
function navHighlight() {
    const sections = ['home', 'services', 'rooms', 'gallery', 'about', 'contact'];
    window.addEventListener('scroll', () => {
        let current = 'home';
        sections.forEach(id => {
            const el = document.getElementById(id);
            if (el && window.scrollY >= el.offsetTop - 100) current = id;
        });
        document.querySelectorAll('.pub-navbar .nav-links a').forEach(a => {
            a.classList.toggle('active', a.getAttribute('href') === '#' + current);
        });
    });
}

// ===== ENTER KEY LOGIN =====
$(document).on("keypress", "#loginPassword", function (e) {
    if (e.which == 13) doLogin();
});

function showAuthAlert(msg, type) {
    $("#authAlert").html(`<div class="alert alert-${type} py-2 small">${msg}</div>`);
}