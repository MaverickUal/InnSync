$(document).ready(function(){

$("#registerForm").submit(function(e){

e.preventDefault();

$.ajax({

url: "api/register.php",
type: "POST",

data:{
name: $("#name").val(),
email: $("#email").val(),
password: $("#password").val()
},

success:function(response){

$("#registerMsg").html(response);

}

});

});

});