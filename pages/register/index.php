<?php
include "../../api/config.php";
if (isset($_SESSION['user'])) header("LOCATION: ../home");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InnSync | Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/public.css">
</head>
<body class="auth-body">
    <div class="auth-wrapper" style="max-width:460px">
        <div class="auth-card card shadow-lg">
            <div class="auth-header text-center">
                <i class="bi bi-building fs-1 text-primary"></i>
                <h3 class="fw-bold mt-2">InnSync</h3>
                <p class="text-muted mb-0">Create your account</p>
            </div>
            <div class="card-body p-4">
                <div id="alertBox"></div>
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="txtFullname" placeholder="Juan dela Cruz">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="txtEmail" placeholder="you@email.com">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="txtPassword" placeholder="••••••••">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" id="txtConfirmPassword" placeholder="••••••••">
                    </div>
                </div>
                <div class="alert alert-info py-2 small">
                    <i class="bi bi-info-circle me-1"></i>
                    After registering, wait for admin approval before you can log in.
                </div>
                <button type="button" onclick="store()" class="btn btn-primary w-100" id="btnRegister">
                    Create Account
                </button>
                <hr>
                <p class="text-center mb-0">Already have an account? <a href="../../">Sign In</a></p>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
