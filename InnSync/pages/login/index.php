<!DOCTYPE html>
<html>
<head>

<title>Login</title>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<link rel="stylesheet" href="login.css">

</head>

<body>

<div class="login-box">

<h2>Login</h2>

<form id="loginForm">

<input type="email" id="email" placeholder="Email" required>

<input type="password" id="password" placeholder="Password" required>

<button type="submit">Login</button>

<p id="loginMsg"></p>

</form>

<p>No account?</p>
<a href="register.php">Create an account</a>

</div>

<script src="js/login.js"></script>

</body>
</html>