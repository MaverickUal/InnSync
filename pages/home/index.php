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
    <title>InnSync | Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/css/public.css">
</head>
<body>
<?php include "../menu.php"; ?>

<!-- ===== HERO ===== -->
<section class="hero-section">
  <div>
    <p class="text-uppercase small fw-semibold mb-2" style="letter-spacing:3px;color:#d8b78e">Welcome back, <?= htmlspecialchars(explode(' ',$user['fullname'])[0]) ?>!</p>
    <h1>Experience Luxury &<br><span>Comfort Redefined</span></h1>
    <p>Nestled in the heart of the city, InnSync Hotel offers world-class accommodations and unforgettable experiences for every guest.</p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
      <a href="../booking" class="btn btn-gold btn-lg px-4 fw-semibold"><i class="bi bi-calendar-check me-2"></i>Book Now</a>
      <a href="../rooms" class="btn btn-outline-light btn-lg px-4"><i class="bi bi-door-open me-2"></i>View Rooms</a>
    </div>
    <!-- Quick stats -->
    <div class="d-flex gap-4 justify-content-center mt-5 flex-wrap">
      <div class="text-center"><div class="fw-bold fs-4" style="color:#bc974a">50+</div><div class="small opacity-75">Rooms</div></div>
      <div class="text-center"><div class="fw-bold fs-4" style="color:#bc974a">5★</div><div class="small opacity-75">Rating</div></div>
      <div class="text-center"><div class="fw-bold fs-4" style="color:#bc974a">10+</div><div class="small opacity-75">Years</div></div>
      <div class="text-center"><div class="fw-bold fs-4" style="color:#bc974a">5k+</div><div class="small opacity-75">Happy Guests</div></div>
    </div>
  </div>
</section>

<!-- ===== BOOKING SUMMARY STRIP ===== -->
<div style="background:#e8ddd0">
  <div class="container py-4">
    <div class="row g-3 text-center" style="color:#1a1a1a">
      <div class="col-md-3"><div class="fw-bold fs-3" id="sBTotal">-</div><div class="small" style="color:#4d4335">Total Bookings</div></div>
      <div class="col-md-3"><div class="fw-bold fs-3" id="sBPending">-</div><div class="small" style="color:#4d4335">Pending</div></div>
      <div class="col-md-3"><div class="fw-bold fs-3" id="sBConfirmed">-</div><div class="small" style="color:#4d4335">Confirmed</div></div>
      <div class="col-md-3">
        <a href="../booking" class="btn btn-gold fw-semibold px-4 mt-1"><i class="bi bi-plus-lg me-2"></i>New Booking</a>
      </div>
    </div>
  </div>
</div>

<!-- ===== SERVICES ===== -->
<section class="pub-section bg-white">
  <div class="container">
    <div class="section-title">
      <h2>Our Services</h2>
      <div class="divider"></div>
      <p>We offer a wide range of premium services to make your stay as comfortable as possible.</p>
    </div>
    <div class="row g-4">
      <?php
      $services = [
        ["bi-cup-hot","Fine Dining","Experience exquisite cuisine crafted by world-class chefs.","../contact"],
        ["bi-water","Swimming Pool","Relax in our temperature-controlled infinity pool.","../contact"],
        ["bi-heart-pulse","Spa & Wellness","Rejuvenate your body and mind with our spa treatments.","../contact"],
        ["bi-wifi","Free WiFi","Complimentary high-speed internet throughout the property.","../contact"],
        ["bi-car-front","Airport Transfer","Seamless airport pickup and drop-off, available 24/7.","../contact"],
        ["bi-shield-check","24/7 Security","Your safety is our priority. Security on duty always.","../contact"],
      ];
      foreach ($services as $s): ?>
      <div class="col-md-4 col-sm-6">
        <div class="service-card">
          <div class="icon-wrap"><i class="bi <?= $s[0] ?>"></i></div>
          <h5><?= $s[1] ?></h5>
          <p><?= $s[2] ?></p>
          <a href="<?= $s[3] ?>" class="btn btn-outline-gold btn-sm mt-3">Learn More</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== ROOMS PREVIEW ===== -->
<section class="pub-section bg-cream">
  <div class="container">
    <div class="section-title">
      <h2>Available Rooms</h2>
      <div class="divider"></div>
      <p>Choose from our carefully designed rooms, each offering a unique blend of comfort and elegance.</p>
    </div>
    <div class="row g-4" id="homeRoomsGrid">
      <div class="col-12 text-center py-4"><div class="spinner-border" style="color:#985b36"></div></div>
    </div>
    <div class="text-center mt-4">
      <a href="../rooms" class="btn btn-gold px-5 fw-semibold"><i class="bi bi-grid me-2"></i>View All Rooms</a>
    </div>
  </div>
</section>

<!-- ===== GALLERY ===== -->
<section class="pub-section bg-white">
  <div class="container">
    <div class="section-title">
      <h2>Gallery</h2>
      <div class="divider"></div>
      <p>A glimpse into the beauty and elegance that awaits you at InnSync Hotel.</p>
    </div>
    <div class="gallery-grid">
      <?php
      $imgs = [
        ["https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800","Deluxe Room"],
        ["https://images.unsplash.com/photo-1582719508461-905c673771fd?w=600","Pool Area"],
        ["https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=600","Lobby"],
        ["https://images.unsplash.com/photo-1615460549969-36fa19521a4f?w=600","Restaurant"],
        ["https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=600","Spa"],
        ["https://images.unsplash.com/photo-1584132967334-10e028bd69f7?w=600","Suite"],
      ];
      foreach ($imgs as $img): ?>
      <div class="gallery-item">
        <img src="<?= $img[0] ?>" alt="<?= $img[1] ?>" loading="lazy">
        <div class="gallery-overlay">
          <div class="text-white text-center"><i class="bi bi-zoom-in fs-2 d-block mb-1"></i><small><?= $img[1] ?></small></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="pub-footer">
    <div class="container">
        <div class="row g-4 mb-3 align-items-start">
            <div class="col-md-6">
                <h5>InnSync Hotel</h5>
                <p class="small mt-2 mb-0">Your home away from home for me and you and i. Experience luxury, comfort, and world-class hospitality at InnSync.</p>
            </div>
            <div class="col-md-3">
                <h5 class="small text-uppercase mb-2" style="letter-spacing:1px">Contact</h5>
                <p class="small mb-1">123 Tabi-Tabi Ave, Malolos</p>
                <p class="small mb-1">+63 (2) 8123-4567</p>
                <p class="small mb-0">mavmybhoxzsh@innsync.com</p>
            </div>
            <div class="col-md-3">
                <h5 class="small text-uppercase mb-2" style="letter-spacing:1px">Hours</h5>
                <p class="small mb-1">Front Desk: Open 24/7</p>
                <p class="small mb-0">Reservations: 8AM – 10PM</p>
            </div>
        </div>
        <hr style="border-color:rgba(255,255,255,.1)">
        <p class="small text-center mb-0">&copy; <?= date('Y') ?> InnSync Hotel. All rights reserved.</p>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>
