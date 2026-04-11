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
    <title>InnSync | Browse Rooms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
</head>
<body>
<?php include "../menu.php"; ?>
<div class="page-wrapper">

  <div class="page-header mb-4">
    <h4>Browse Rooms</h4>
    <p>Find your perfect room — filter by type, price, or capacity.</p>
  </div>

  <!-- Filters -->
  <div class="card table-card mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-end">
        <div class="col-md-3 col-sm-6">
          <label class="form-label small fw-semibold mb-1">Room Type</label>
          <select class="form-select form-select-sm" id="filterType">
            <option value="">All Types</option>
          </select>
        </div>
        <div class="col-md-2 col-sm-6">
          <label class="form-label small fw-semibold mb-1">Min Price</label>
          <input type="number" class="form-control form-control-sm" id="filterMinPrice" placeholder="₱0">
        </div>
        <div class="col-md-2 col-sm-6">
          <label class="form-label small fw-semibold mb-1">Max Price</label>
          <input type="number" class="form-control form-control-sm" id="filterMaxPrice" placeholder="₱9999">
        </div>
        <div class="col-md-2 col-sm-6">
          <label class="form-label small fw-semibold mb-1">Min Capacity</label>
          <input type="number" class="form-control form-control-sm" id="filterCapacity" placeholder="1+">
        </div>
        <div class="col-md-3">
          <button class="btn btn-gold btn-sm w-100 fw-semibold" onclick="loadRooms()">
            <i class="bi bi-search me-1"></i>Search Rooms
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Room count -->
  <div class="mb-3">
    <span class="small text-muted"><span id="roomCount">0</span> room(s) found</span>
  </div>

  <!-- Rooms Grid -->
  <div class="row g-4" id="roomsGrid">
    <div class="col-12 text-center py-5">
      <div class="spinner-border" style="color:#985b36"></div>
      <p class="text-muted mt-2">Loading rooms...</p>
    </div>
  </div>

</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>
