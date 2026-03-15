document.addEventListener("DOMContentLoaded", function () {

fetch("modals.html")
.then(response => response.text())
.then(data => {

document.getElementById("modalContainer").innerHTML = data;

});

});