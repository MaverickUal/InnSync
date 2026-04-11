<?php
include "../../api/config.php";
if (!isset($_SESSION['user'])) header("LOCATION: ../../");
if ($_SESSION['user']['role'] == 'admin') header("LOCATION: ../admin");
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InnSync | Contact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
</head>
<body>
<?php include "../menu.php"; ?>
<div class="page-wrapper">
    <div class="text-center mb-4">
        <h2 class="fw-bold" style="color:#4d4335">Contact Us</h2>
        <div style="width:55px;height:3px;background:#bc974a;margin:.75rem auto 1rem;border-radius:2px;"></div>
        <p class="text-muted">Reach out with any inquiries, feedback, or concerns.</p>
    </div>
    <div class="row g-4 justify-content-center">
        <div class="col-lg-4">
            <div class="contact-item"><div class="ci-icon"><i class="bi bi-geo-alt"></i></div><div><div class="fw-semibold" style="color:#4d4335">Location</div><div class="text-muted small">123 Tabi-Tabi Ave, Malolos<br>Malolos Bulacan, Philippines</div></div></div>
            <div class="contact-item"><div class="ci-icon"><i class="bi bi-telephone"></i></div><div><div class="fw-semibold" style="color:#4d4335">Phone</div><div class="text-muted small">+63 (2) 8123-4567<br>+63 917 123 4567</div></div></div>
            <div class="contact-item"><div class="ci-icon"><i class="bi bi-envelope"></i></div><div><div class="fw-semibold" style="color:#4d4335">Email</div><div class="text-muted small">mavmybhoxzsh@innsync.com<br>reservationsniPJ@innsync.com</div></div></div>
            <div class="contact-item"><div class="ci-icon"><i class="bi bi-clock"></i></div><div><div class="fw-semibold" style="color:#4d4335">Hours</div><div class="text-muted small">Front Desk: Open 24/7<br>Reservations: 8AM–10PM</div></div></div>
        </div>
        <div class="col-lg-6">
            <div class="card table-card p-4">
                <h5 class="fw-bold mb-3" style="color:#4d4335"><i class="bi bi-chat-dots me-2 text-rust"></i>Send a Message</h5>
                <div id="contactAlert"></div>
                <div class="row g-3">
                    <div class="col-12"><label class="form-label small fw-semibold">Your Name</label><input type="text" class="form-control" id="cName" value="<?= htmlspecialchars($user['fullname']) ?>"></div>
                    <div class="col-12"><label class="form-label small fw-semibold">Email</label><input type="email" class="form-control" id="cEmail" value="<?= htmlspecialchars($user['email']) ?>"></div>
                    <div class="col-12"><label class="form-label small fw-semibold">Subject</label><select class="form-select" id="cSubject"><option value="">Select subject...</option><option>Room Inquiry</option><option>Reservation Question</option><option>Feedback</option><option>Complaint</option><option>Other</option></select></div>
                    <div class="col-12"><label class="form-label small fw-semibold">Message</label><textarea class="form-control" id="cMessage" rows="5" placeholder="Write your message here..."></textarea></div>
                    <div class="col-12"><button class="btn btn-gold w-100 fw-semibold" onclick="sendMessage()"><i class="bi bi-send me-2"></i>Send Message</button></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function sendMessage() {
    let name=$("#cName").val().trim(), email=$("#cEmail").val().trim(), subject=$("#cSubject").val(), message=$("#cMessage").val().trim();
    if (!name||!email||!subject||!message) { $("#contactAlert").html('<div class="alert alert-warning py-2 small">Please fill in all fields.</div>'); return; }
    $("#contactAlert").html('<div class="alert alert-success py-2 small"><i class="bi bi-check-circle me-1"></i>Message sent! We\'ll get back to you within 24 hours.</div>');
    $("#cMessage").val(""); $("#cSubject").val("");
}
</script>
</body>
</html>
