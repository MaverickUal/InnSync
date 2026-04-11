<?php
include "api/config.php";
if (isset($_SESSION['user'])) {
    header("LOCATION: " . ($_SESSION['user']['role'] == 'admin' ? "pages/admin" : "pages/home"));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InnSync Hotel | Your Home Away From Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   
    <link rel="stylesheet" href="css/public.css">
</head>
<body>

<!-- ===== PUBLIC NAVBAR ===== -->
<nav class="pub-navbar">
    <a href="#home" class="brand"><i class="bi bi-building"></i> Inn<span>Sync</span></a>
    <ul class="nav-links">
        <li><a href="#home" class="active">Home</a></li>
        <li><a href="#services">Services</a></li>
        <li><a href="#rooms">Rooms</a></li>
        <li><a href="#gallery">Gallery</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#contact">Contact</a></li>
    </ul>
    <div class="nav-actions">
        <button class="btn btn-outline-gold btn-sm" onclick="openAuth('login')">Sign In</button>
        <button class="btn btn-gold btn-sm" onclick="openAuth('register')">Register</button>
    </div>
</nav>

<!-- ===== HERO ===== -->
<section class="hero-section" id="home">
    <div>
        <p class="text-uppercase small fw-semibold mb-2" style="letter-spacing:3px;color:var(--gold-light)">Welcome to InnSync Hotel</p>
        <h1>Experience Luxury &<br><span>Comfort Redefined</span></h1>
        <p>Nestled in the heart of the city, InnSync Hotel offers world-class accommodations, exceptional service, and unforgettable experiences for every guest.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <button class="btn btn-gold btn-lg px-4 fw-semibold" onclick="openAuth('register')">
                <i class="bi bi-calendar-check me-2"></i>Book Now
            </button>
            <a href="#rooms" class="btn btn-outline-light btn-lg px-4">
                <i class="bi bi-door-open me-2"></i>View Rooms
            </a>
        </div>
        <div class="d-flex gap-4 justify-content-center mt-5 flex-wrap">
            <div class="text-center"><div class="fw-bold fs-4" style="color:var(--gold)">50+</div><div class="small opacity-75">Rooms</div></div>
            <div class="text-center"><div class="fw-bold fs-4" style="color:var(--gold)">5★</div><div class="small opacity-75">Rating</div></div>
            <div class="text-center"><div class="fw-bold fs-4" style="color:var(--gold)">10+</div><div class="small opacity-75">Years</div></div>
            <div class="text-center"><div class="fw-bold fs-4" style="color:var(--gold)">5k+</div><div class="small opacity-75">Happy Guests</div></div>
        </div>
    </div>
</section>

<!-- ===== SERVICES ===== -->
<section class="pub-section bg-white" id="services">
    <div class="container">
        <div class="section-title">
            <h2>Our Services</h2>
            <div class="divider"></div>
            <p>We offer a wide range of premium services to make your stay as comfortable and memorable as possible.</p>
        </div>
        <div class="row g-4">
            <?php
            $services = [
                ["bi-cup-hot","Fine Dining","Experience exquisite cuisine crafted by our world-class chefs using only the freshest local and international ingredients."],
                ["bi-water","Swimming Pool","Relax in our temperature-controlled infinity pool with stunning views of the city skyline."],
                ["bi-heart-pulse","Spa & Wellness","Rejuvenate your body and mind with our comprehensive range of spa treatments and wellness programs."],
                ["bi-wifi","Free WiFi","Stay connected with complimentary high-speed internet access throughout the entire property."],
                ["bi-car-front","Airport Transfer","Enjoy seamless airport pickup and drop-off services available 24/7 for your convenience."],
                ["bi-shield-check","24/7 Security","Your safety is our priority. Our professional security team is on duty around the clock."],
            ];
            foreach ($services as $s): ?>
            <div class="col-md-4 col-sm-6">
                <div class="service-card">
                    <div class="icon-wrap"><i class="bi <?= $s[0] ?>"></i></div>
                    <h5><?= $s[1] ?></h5>
                    <p><?= $s[2] ?></p>
                    <button class="btn btn-outline-gold btn-sm mt-3" onclick="openAuth('register')">Learn More</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== ROOMS PREVIEW ===== -->
<section class="pub-section bg-cream" id="rooms">
    <div class="container">
        <div class="section-title">
            <h2>Featured Rooms</h2>
            <div class="divider"></div>
            <p>Choose from our carefully designed rooms, each offering a unique blend of comfort and elegance.</p>
        </div>
        <div class="row g-4" id="publicRoomsGrid">
            <div class="col-12 text-center py-4"><div class="spinner-border text-brown"></div></div>
        </div>
        <div class="text-center mt-4">
            <button class="btn btn-gold px-5" onclick="openAuth('register')">
                <i class="bi bi-grid me-2"></i>View All Rooms & Book
            </button>
        </div>
    </div>
</section>

<!-- ===== GALLERY ===== -->
<section class="pub-section bg-white" id="gallery">
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
            <div class="gallery-item" onclick="openAuth('register')">
                <img src="<?= $img[0] ?>" alt="<?= $img[1] ?>" loading="lazy">
                <div class="gallery-overlay">
                    <div class="text-white text-center">
                        <i class="bi bi-zoom-in fs-2 d-block mb-1"></i>
                        <small><?= $img[1] ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== ABOUT US ===== -->
<section class="pub-section bg-cream" id="about">
    <div class="container">
        <div class="row align-items-center g-5 mb-5">
            <div class="col-lg-6">
                <p class="text-uppercase small fw-semibold" style="letter-spacing:2px;color:var(--gold)">About InnSync</p>
                <h2 class="fw-bold" style="color:var(--brown-dark)">A Legacy of Hospitality &amp; Excellence</h2>
                <hr class="divider-gold">
                <p class="text-muted">Founded over a decade ago, InnSync Hotel has been a beacon of luxury hospitality in the region. We believe that every guest deserves an experience that transcends the ordinary — from the moment you arrive to the moment you leave.</p>
                <p class="text-muted">Our dedicated team of professionals is committed to ensuring your stay is nothing short of exceptional. With thoughtfully designed spaces, world-class amenities, and personalized service, we create memories that last a lifetime.</p>
                <div class="row g-3 mt-2">
                    <div class="col-6"><div class="p-3 rounded-3 text-center" style="background:var(--white)"><div class="fw-bold fs-3" style="color:var(--rust)">10+</div><div class="small text-muted">Years of Service</div></div></div>
                    <div class="col-6"><div class="p-3 rounded-3 text-center" style="background:var(--white)"><div class="fw-bold fs-3" style="color:var(--rust)">5,000+</div><div class="small text-muted">Happy Guests</div></div></div>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=800" alt="Hotel" class="img-fluid rounded-4 shadow" style="max-height:420px;object-fit:cover;width:100%">
            </div>
        </div>
        <div class="section-title">
            <h2>Meet Our Team</h2>
            <div class="divider"></div>
            <p>The passionate people behind the InnSync experience.</p>
        </div>
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
</section>

<!-- ===== CONTACT ===== -->
<section class="pub-section bg-white" id="contact">
    <div class="container">
        <div class="section-title">
            <h2>Contact Us</h2>
            <div class="divider"></div>
            <p>We'd love to hear from you. Reach out for inquiries, feedback, or reservations.</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="contact-item">
                    <div class="ci-icon"><i class="bi bi-geo-alt"></i></div>
                    <div><div class="fw-semibold" style="color:var(--brown-dark)">Our Location</div><div class="text-muted small">123 Hospitality Avenue, City Center<br>Metro Manila, Philippines</div></div>
                </div>
                <div class="contact-item">
                    <div class="ci-icon"><i class="bi bi-telephone"></i></div>
                    <div><div class="fw-semibold" style="color:var(--brown-dark)">Phone Number</div><div class="text-muted small">+63 (2) 8123-4567<br>+63 917 123 4567</div></div>
                </div>
                <div class="contact-item">
                    <div class="ci-icon"><i class="bi bi-envelope"></i></div>
                    <div><div class="fw-semibold" style="color:var(--brown-dark)">Email Address</div><div class="text-muted small">info@innsync.com<br>reservations@innsync.com</div></div>
                </div>
                <div class="contact-item">
                    <div class="ci-icon"><i class="bi bi-clock"></i></div>
                    <div><div class="fw-semibold" style="color:var(--brown-dark)">Business Hours</div><div class="text-muted small">Front Desk: Open 24/7<br>Reservations: 8AM – 10PM daily</div></div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold mb-3" style="color:var(--brown-dark)">Send Us a Message</h5>
                    <div id="contactAlert"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Your Name</label>
                            <input type="text" class="form-control" id="cName" placeholder="Juan dela Cruz">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" class="form-control" id="cEmail" placeholder="you@email.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Subject</label>
                            <select class="form-select" id="cSubject">
                                <option value="">Select subject...</option>
                                <option>Room Inquiry</option>
                                <option>Reservation Question</option>
                                <option>Feedback</option>
                                <option>Complaint</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Message</label>
                            <textarea class="form-control" id="cMessage" rows="4" placeholder="Write your message here..."></textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-gold w-100 fw-semibold" onclick="submitContact()">
                                <i class="bi bi-send me-2"></i>Send Message
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="pub-footer">
    <div class="container">
        <div class="row g-4 mb-3 align-items-start">
            <div class="col-md-6">
                <h5><i class="bi bi-building me-2"></i>InnSync Hotel</h5>
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

<!-- ===== BLACKLIST MODAL ===== -->
<div class="modal fade" id="blacklistModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow-lg text-center">
            <div class="modal-body px-4 pt-4 pb-3">
                <div class="mb-3">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:50%;background:#fee2e2;">
                        <i class="bi bi-slash-circle-fill text-danger" style="font-size:1.6rem;"></i>
                    </span>
                </div>
                <h6 class="fw-bold mb-1" style="color:#7f1d1d;">Account Disabled</h6>
                <p class="text-muted small mb-3">Account disabled. Please contact support.</p>
                <a href="mailto:support@innsync.com" class="btn btn-sm btn-danger w-100 fw-semibold mb-2">
                    <i class="bi bi-envelope me-1"></i>Contact Support
                </a>
                <button class="btn btn-sm btn-outline-secondary w-100" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== AUTH MODAL ===== -->
<div class="modal fade auth-modal" id="authModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="modal-title fs-5"><i class="bi bi-building me-2"></i>Inn<span style="color:var(--gold)">Sync</span> Hotel</div>
                    <div class="d-flex gap-1 mt-2">
                        <button class="auth-tab-btn active" id="tabLogin" onclick="switchTab('login')">Sign In</button>
                        <button class="auth-tab-btn" id="tabRegister" onclick="switchTab('register')">Register</button>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="authAlert"></div>
                <!-- LOGIN -->
                <div id="loginForm">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="loginEmail" placeholder="you@email.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="loginPassword" placeholder="••••••••">
                        </div>
                    </div>
                    <button class="btn btn-brown w-100 fw-semibold" onclick="doLogin()" id="btnLogin">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                    <p class="text-center small text-muted mt-3 mb-0">Don't have an account? <a href="#" onclick="switchTab('register')" style="color:var(--rust)">Register here</a></p>
                </div>
                <!-- REGISTER -->
                <div id="registerForm" class="d-none">
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="regName" placeholder="Juan dela Cruz">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="regEmail" placeholder="you@email.com">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Contact Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="tel" class="form-control" id="regContact" placeholder="09XX XXX XXXX">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="regPassword" placeholder="••••••••">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="regConfirm" placeholder="••••••••">
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-gold w-100 fw-semibold mt-3" onclick="doRegister()" id="btnRegister">
                        <i class="bi bi-person-plus me-2"></i>Create Account
                    </button>
                    <p class="text-center small text-muted mt-3 mb-0">Already have an account? <a href="#" onclick="switchTab('login')" style="color:var(--rust)">Sign in here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/public.js"></script>
</body>
</html>