<?php
include "../../api/config.php";
if (!isset($_SESSION['user'])) header("LOCATION: ../../");
if ($_SESSION['user']['role'] == 'admin') header("LOCATION: ../admin");
$user = $_SESSION['user'];
// Refresh user data from DB
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id=?");
$stmt->bind_param("i", $user['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$_SESSION['user'] = $user;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InnSync | My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
</head>
<body>
<?php include "../menu.php"; ?>
<div class="page-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card table-card p-4">
                <div class="text-center mb-4">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle fw-bold text-white"
                         style="width:80px;height:80px;font-size:2rem;background:linear-gradient(135deg,#4d4335,#985b36)">
                        <?= strtoupper(substr($user['fullname'], 0, 1)) ?>
                    </div>
                    <h5 class="fw-bold mb-0" style="color:#4d4335"><?= htmlspecialchars($user['fullname']) ?></h5>
                    <span class="badge mt-1" style="background:#985b36"><?= $user['role'] ?></span>
                </div>
                <div id="alertBox"></div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="txtFullname" value="<?= htmlspecialchars($user['fullname']) ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="txtEmail" value="<?= htmlspecialchars($user['email']) ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Contact Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                        <input type="tel" class="form-control" id="txtContact" value="<?= htmlspecialchars($user['contact_number'] ?? '') ?>">
                    </div>
                </div>
                <button type="button" onclick="update()" class="btn btn-gold w-100 fw-semibold">
                    <i class="bi bi-save me-2"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>const USER_ID = "<?= $user['user_id'] ?>";</script>
<script src="script.js"></script>
</body>
</html>
