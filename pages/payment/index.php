<?php
include "../../api/config.php";
if (!isset($_SESSION['user'])) header("LOCATION: ../../");
if ($_SESSION['user']['role'] == 'admin') header("LOCATION: ../admin");
$user = $_SESSION['user'];
$payment_id = $_GET['payment_id'] ?? '';
$ref        = $_GET['ref'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InnSync | Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
    <style>
    .receipt-box { background:#fff; border:2px solid #bc974a; border-radius:12px; padding:1.75rem; }
    .receipt-box .receipt-header { text-align:center; border-bottom:2px dashed #e8ddd0; padding-bottom:1.1rem; margin-bottom:1.1rem; }
    .receipt-row { display:flex; justify-content:space-between; margin-bottom:6px; font-size:.9rem; }
    .receipt-row .label { color:#7a6a5a; }
    .receipt-row .value { font-weight:600; }
    .receipt-total { border-top:2px dashed #e8ddd0; margin-top:.9rem; padding-top:.9rem; }
    </style>
</head>
<body>
<?php include "../menu.php"; ?>
<div class="page-wrapper">
<div class="row justify-content-center g-4">

  <!-- LEFT: Payment Status / Actions -->
  <div class="col-lg-5">
    <div class="card table-card mb-4" id="paymentStatusCard">
      <div class="card-header"><i class="bi bi-credit-card me-2 text-rust"></i>Payment Status</div>
      <div class="card-body p-4" id="paymentStatusBody">
        <div class="text-center py-4"><div class="spinner-border" style="color:#985b36"></div></div>
      </div>
    </div>

    <!-- Pay Remaining Balance Form (shown when remaining is pending) -->
    <div class="card table-card d-none" id="payRemainingCard">
      <div class="card-header"><i class="bi bi-cash-stack me-2 text-rust"></i>Pay Remaining Balance</div>
      <div class="card-body p-4">
        <div id="remainingAlert"></div>
        <p class="small text-muted mb-3">Choose your payment method for the remaining balance. <strong>Cash is allowed</strong> for remaining balance.</p>
        <div class="row g-2 mb-3">
          <?php foreach ([['cash','Cash','bi-cash'],['gcash','GCash','bi-phone'],['bank_transfer','Bank Transfer','bi-bank'],['credit_card','Credit Card','bi-credit-card-2-front']] as $m): ?>
          <div class="col-6">
            <label class="d-flex align-items-center gap-2 p-2 rounded-3 border" style="cursor:pointer" id="rlbl_<?= $m[0] ?>">
              <input type="radio" name="remMethod" value="<?= $m[0] ?>" class="form-check-input m-0">
              <i class="bi <?= $m[2] ?> text-rust"></i>
              <span class="small fw-semibold"><?= $m[1] ?></span>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
        <button class="btn btn-gold w-100 fw-semibold" onclick="payRemaining()">
          <i class="bi bi-check-circle me-2"></i>Pay Remaining Balance
        </button>
      </div>
    </div>

    <?php if (!$payment_id): ?>
    <?php endif; ?>
  </div>

  <!-- RIGHT: Receipt -->
  <div class="col-lg-5">
    <div id="receiptArea" class="d-none">

      <!-- Downpayment Receipt -->
      <div class="receipt-box mb-4" id="dpReceipt">
        <div class="receipt-header">
          <i class="bi bi-building fs-2" style="color:#985b36"></i>
          <h5 class="fw-bold mt-1 mb-0" style="color:#4d4335">InnSync Hotel</h5>
          <small class="text-muted">Downpayment Receipt</small>
        </div>
        <div class="receipt-row"><span class="label">Receipt No.</span><span class="value text-rust" id="rcDpNo"></span></div>
        <div class="receipt-row"><span class="label">Guest</span><span class="value" id="rcDpGuest"></span></div>
        <div class="receipt-row"><span class="label">Room</span><span class="value" id="rcDpRoom"></span></div>
        <div class="receipt-row"><span class="label">Check-in</span><span class="value" id="rcDpCheckIn"></span></div>
        <div class="receipt-row"><span class="label">Check-out</span><span class="value" id="rcDpCheckOut"></span></div>
        <div class="receipt-row"><span class="label">Method</span><span class="value" id="rcDpMethod"></span></div>
        <div class="receipt-row"><span class="label">Reference No.</span><span class="value" id="rcDpRef"></span></div>
        <div class="receipt-row"><span class="label">Date</span><span class="value" id="rcDpDate"></span></div>
        <div class="receipt-total">
          <div class="receipt-row"><span class="label">Total Amount</span><span class="value" id="rcDpTotal"></span></div>
          <div class="receipt-row"><span class="label fw-bold" style="color:#4d4335">Downpayment Paid</span><span class="value text-rust fs-5" id="rcDpAmount"></span></div>
          <div class="receipt-row"><span class="label">Remaining Balance</span><span class="value" id="rcDpRemaining"></span></div>
          <div class="mt-2"><span id="rcDpStatus"></span></div>
        </div>
      </div>

      <!-- Full Billing Summary (shown when fully paid) -->
      <div class="receipt-box d-none" id="fullReceipt">
        <div class="receipt-header">
          <i class="bi bi-check-circle-fill fs-2" style="color:#985b36"></i>
          <h5 class="fw-bold mt-1 mb-0" style="color:#4d4335">InnSync Hotel</h5>
          <small class="text-muted">Full Payment Billing Summary</small>
        </div>
        <div class="receipt-row"><span class="label">Full Receipt No.</span><span class="value text-rust" id="rcFullNo"></span></div>
        <div class="receipt-row"><span class="label">Guest</span><span class="value" id="rcFullGuest"></span></div>
        <div class="receipt-row"><span class="label">Room</span><span class="value" id="rcFullRoom"></span></div>
        <div class="receipt-row"><span class="label">Check-in</span><span class="value" id="rcFullCheckIn"></span></div>
        <div class="receipt-row"><span class="label">Check-out</span><span class="value" id="rcFullCheckOut"></span></div>
        <hr style="border-color:#e8ddd0">
        <div class="receipt-row"><span class="label">Downpayment Method</span><span class="value" id="rcFullDpMethod"></span></div>
        <div class="receipt-row"><span class="label">Downpayment Ref.</span><span class="value" id="rcFullDpRef"></span></div>
        <div class="receipt-row"><span class="label">Downpayment Date</span><span class="value" id="rcFullDpDate"></span></div>
        <div class="receipt-row"><span class="label">Downpayment Amount</span><span class="value" id="rcFullDp"></span></div>
        <hr style="border-color:#e8ddd0">
        <div class="receipt-row"><span class="label">Remaining Balance Method</span><span class="value" id="rcFullRemMethod"></span></div>
        <div class="receipt-row"><span class="label">Remaining Ref.</span><span class="value" id="rcFullRemRef"></span></div>
        <div class="receipt-row"><span class="label">Remaining Date</span><span class="value" id="rcFullRemDate"></span></div>
        <div class="receipt-row"><span class="label">Remaining Amount</span><span class="value" id="rcFullRem"></span></div>
        <div class="receipt-total">
          <div class="receipt-row"><span class="label fw-bold fs-5" style="color:#4d4335">Total Paid</span><span class="value text-rust fs-5" id="rcFullTotal"></span></div>
          <div class="text-center mt-2"><span class="badge bg-success px-3 py-2">✅ Full Payment Complete</span></div>
        </div>
      </div>

    </div>
    <div id="receiptPlaceholder" class="text-center py-5 text-muted">
      <i class="bi bi-receipt fs-1 d-block mb-2 opacity-25"></i>
      <p>Select a payment to view receipt</p>
    </div>
  </div>

</div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const PRELOAD_PAYMENT_ID = "<?= $payment_id ?>";
const PRELOAD_REF        = "<?= $ref ?>";
</script>
<script src="script.js"></script>
</body>
</html>
