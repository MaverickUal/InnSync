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
    <title>InnSync | About Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
</head>
<body>
<?php include "../menu.php"; ?>
<div class="page-wrapper">
    <div class="text-center mb-5">
        <h2 class="fw-bold" style="color:#4d4335">About InnSync Hotel</h2>
        <div style="width:55px;height:3px;background:#bc974a;margin:.75rem auto 1rem;border-radius:2px;"></div>
        <p class="text-muted">Our story, our team, our values.</p>
    </div>
    <div class="row align-items-center g-5 mb-5">
        <div class="col-lg-6">
            <p style="color:#bc974a;font-size:.85rem;font-weight:600;letter-spacing:2px;text-transform:uppercase">Our Story</p>
            <h3 class="fw-bold" style="color:#4d4335">A Legacy of Hospitality & Excellence</h3>
            <hr style="border:none;height:2px;background:linear-gradient(to right,#bc974a,transparent);margin:1rem 0 1.5rem">
            <p class="text-muted">Founded over a decade ago, InnSync Hotel has been a beacon of luxury hospitality in the region. We believe that every guest deserves an experience that transcends the ordinary.</p>
            <p class="text-muted">Our dedicated team of professionals is committed to ensuring your stay is nothing short of exceptional. With thoughtfully designed spaces, world-class amenities, and personalized service, we create memories that last a lifetime.</p>
            <div class="row g-3 mt-2">
                <div class="col-6"><div class="p-3 rounded-3 text-center" style="background:#f5f0ea"><div class="fw-bold fs-3" style="color:#985b36">10+</div><div class="small text-muted">Years of Service</div></div></div>
                <div class="col-6"><div class="p-3 rounded-3 text-center" style="background:#f5f0ea"><div class="fw-bold fs-3" style="color:#985b36">5,000+</div><div class="small text-muted">Happy Guests</div></div></div>
            </div>
        </div>
        <div class="col-lg-6">
            <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=800" alt="Hotel" class="img-fluid rounded-4 shadow" style="max-height:400px;object-fit:cover;width:100%">
        </div>
    </div>
    <h4 class="fw-bold text-center mb-4" style="color:#4d4335">Meet Our Team</h4>
    <div class="row g-4">
        <div class="row g-4">
        <?php
        $team=[ ["MU","Mav Ual","Leader","Still in progress"],
				["SA","Sebastian Amoranto","Member","Still in progress"],
				["PS","PJ Subibe","Member","Still in progress."],
				["PV","Paul Valderema","Member","Still in progress"],
				["JR","Justine Rado","Member","Still in progress"]];
        foreach($team as $t): ?>
        <div class="col-md col-sm-4 col-6">
            <div class="team-card">
                <div class="avatar-circle"><?= $t[0] ?></div>
                <h6><?= $t[1] ?></h6>
                <div class="role mb-2"><?= $t[2] ?></div>
                <p class="small text-muted mb-0"><?= $t[3] ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
