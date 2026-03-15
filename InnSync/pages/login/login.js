$(document).ready(function(){

$("#loginForm").submit(function(e){

e.preventDefault();

$.ajax({

url: "api/login.php",
type: "POST",

data:{
email: $("#email").val(),
password: $("#password").val()
},

success:function(response){

$("#loginMsg").html(response);

}

});

});

});