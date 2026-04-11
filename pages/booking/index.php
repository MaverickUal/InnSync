<?php
include "../../api/config.php";


if (!isset($_SESSION['user'])) {
    header("Location: ../../");
    exit;
}

if ($_SESSION['user']['role'] === 'admin') {
    header("Location: ../admin");
    exit;
}

$user = $_SESSION['user'];
$room_id = $_GET['room_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InnSync | Book a Room</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
</head>
<body>

<?php include "../menu.php"; ?>

<div class="page-wrapper">
<div class="row justify-content-center">
<div class="col-lg-7">

  <div class="alert mb-4 rounded-3" style="background:#faf6ef;border:1px solid #bc974a">
    <div class="d-flex gap-2">
      <i class="bi bi-info-circle-fill text-rust mt-1 fs-5"></i>
      <div>
        <strong style="color:#4d4335">Booking Rules</strong>
        <ul class="mb-0 small text-muted mt-1">
          <li>Standard check-in: <strong>2:00 PM</strong> — check-out: <strong>12:00 PM</strong></li>
          <li>A <strong>2-hour cleaning buffer</strong> is applied after every check-out</li>
          <li>A <strong>50% downpayment</strong> is required to confirm your booking (no cash)</li>
          <li>Duration is calculated as: <em>(check-out − check-in) − 2 hrs cleaning</em></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="card table-card">
    <div class="card-header">
      <i class="bi bi-calendar-plus me-2 text-rust"></i>New Reservation
    </div>

    <div class="card-body p-4">
      <div id="alertBox"></div>

      <!-- ROOM SELECT -->
      <div class="mb-3">
        <label class="form-label fw-semibold">Select Room</label>
        <select class="form-select" id="selRoom">
            <option value="">-- Choose a room --</option>
        </select>

        <div id="roomDetails" class="mt-3 p-3 rounded-3 d-none" style="background:#f5f0ea">
          <div class="row g-2 small">
            <div class="col-6"><span class="text-muted d-block">Type</span><span id="rdType" class="fw-semibold"></span></div>
            <div class="col-6"><span class="text-muted d-block">Price/Night</span><span id="rdPrice" class="fw-semibold text-rust"></span></div>
            <div class="col-6"><span class="text-muted d-block">Capacity</span><span id="rdCapacity" class="fw-semibold"></span></div>
            <div class="col-6"><span class="text-muted d-block">Status</span><span id="rdStatus"></span></div>

            <div class="col-12 d-none" id="rdPromoWrap">
              <span class="text-muted d-block mb-1">Promo</span>
              <span id="rdPromo" class="badge" style="background:#985b36;font-size:.8rem;padding:4px 10px"></span>
            </div>
          </div>
        </div>
      </div>

      <input type="hidden" id="selReservationType" value="">

      <!-- DATES -->
      <div class="row g-3 mb-3">
        <div class="col-6">
          <label class="form-label fw-semibold">Check-in</label>
          <input type="date" class="form-control" id="txtCheckIn">
        </div>
        <div class="col-6">
          <label class="form-label fw-semibold">Check-out</label>
          <input type="date" class="form-control" id="txtCheckOut">
        </div>
      </div>

      <!-- COST -->
      <div id="costBreakdown" class="d-none mb-3 p-3 rounded-3" style="background:#faf6ef;border:1px solid #e8ddd0">
        <div class="fw-semibold mb-2">Cost Breakdown</div>
        <div class="d-flex justify-content-between small"><span>Total</span><span id="cbTotal"></span></div>
      </div>

      <!-- PAYMENT -->
      <div class="mb-4">
        <label class="form-label fw-semibold">Downpayment Method</label>
        <div class="row g-2">
          <?php foreach ([['gcash','GCash'],['bank_transfer','Bank'],['credit_card','Card']] as $m): ?>
          <div class="col-4">
            <label class="p-2 border rounded w-100 text-center" id="lbl_<?= $m[0] ?>">
              <input type="radio" name="dpMethod" value="<?= $m[0] ?>">
              <?= $m[1] ?>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- BUTTON -->
      <button type="button" onclick="store()" class="btn btn-gold w-100 fw-semibold" id="btnBook">
        Confirm Booking
      </button>

    </div>
  </div>

</div>
</div>
</div>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script>
const PRESELECT_ROOM = <?= json_encode($room_id) ?>;
</script>


<script src="./script.js"></script>

</body>
</html>