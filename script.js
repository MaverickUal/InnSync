document.addEventListener("DOMContentLoaded", function () {

fetch("modals.html")
.then(response => response.text())
.then(data => {

document.getElementById("modalContainer").innerHTML = data;



const logoutBtn = document.getElementById("logoutBtn");
const loginNavBtn = document.getElementById("loginNavBtn");
const registerNavBtn = document.getElementById("registerNavBtn");




if(localStorage.getItem("loggedIn") === "true"){

if(logoutBtn) logoutBtn.style.display = "block";
if(loginNavBtn) loginNavBtn.style.display = "none";
if(registerNavBtn) registerNavBtn.style.display = "none";

}




if(logoutBtn){
logoutBtn.addEventListener("click", function(){

localStorage.removeItem("loggedIn");

alert("Logged out successfully");

window.location.href = "index.html";

});
}




const registerForm = document.getElementById("registerForm");

if (registerForm) {

const name = document.getElementById("fname");
const email = document.getElementById("email");
const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirmPassword");
const registerBtn = document.getElementById("registerBtn");


function validateName() {

const regex = /^[A-Za-z ]{3,}$/;

if (!regex.test(name.value)) {

document.getElementById("nameError").textContent =
"Name must be at least 3 letters and contain letters only.";

return false;
}

document.getElementById("nameError").textContent = "";
return true;
}


function validateEmail() {

const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

if (!regex.test(email.value)) {

document.getElementById("emailError").textContent =
"Enter a valid email address.";

return false;
}

document.getElementById("emailError").textContent = "";
return true;
}


function validatePassword() {

const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;

if (!regex.test(password.value)) {

document.getElementById("passwordError").textContent =
"Password must be 8 characters with upper, lower, and number.";

return false;
}

document.getElementById("passwordError").textContent = "";
return true;
}


function validateConfirmPassword() {

if (confirmPassword.value !== password.value) {

document.getElementById("confirmPasswordError").textContent =
"Passwords do not match.";

return false;
}

document.getElementById("confirmPasswordError").textContent = "";
return true;
}


function checkRegisterForm() {

if (
validateName() &&
validateEmail() &&
validatePassword() &&
validateConfirmPassword()
) {

registerBtn.disabled = false;

} else {

registerBtn.disabled = true;

}

}


name.addEventListener("blur", validateName);
email.addEventListener("blur", validateEmail);
password.addEventListener("blur", validatePassword);
confirmPassword.addEventListener("blur", validateConfirmPassword);

registerForm.addEventListener("input", checkRegisterForm);


registerForm.addEventListener("submit", function (e) {

if (
!validateName() ||
!validateEmail() ||
!validatePassword() ||
!validateConfirmPassword()
) {

e.preventDefault();
return;

}

document.getElementById("successMessage").textContent =
"Registration Successful!";

});

}




const loginForm = document.getElementById("loginForm");

if (loginForm) {

const email = document.getElementById("loginEmail");
const password = document.getElementById("loginPassword");
const loginBtn = document.getElementById("loginBtn");

const dummyEmail = "admin@innsync.com";
const dummyPassword = "123456";


function validateLogin() {

if (email.value !== "" && password.value !== "") {

loginBtn.disabled = false;

} else {

loginBtn.disabled = true;

}

}

loginForm.addEventListener("input", validateLogin);


loginForm.addEventListener("submit", function (e) {

e.preventDefault();

if(email.value === dummyEmail && password.value === dummyPassword){

localStorage.setItem("loggedIn", "true");

alert("Login Successful!");

window.location.href = "index.html";

}else{

document.getElementById("loginError").textContent =
"Incorrect email or password.";

}

});

}




const toggles = document.querySelectorAll(".toggle-password");

toggles.forEach(function (btn) {

btn.addEventListener("click", function () {

const input = this.previousElementSibling;

if (input.type === "password") {

input.type = "text";
this.textContent = "Hide";

} else {

input.type = "password";
this.textContent = "Show";

}

});

});


}); 

}); 