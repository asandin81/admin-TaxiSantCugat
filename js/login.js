$(document).ready(function() {
	var error = $('#mensajeError');
	// Login - Debuelve el token y el Json del usuario.
	$('#btnLogin').click(function() {
		$.ajax({
			url : 'http://localhost:8080/webTaxiSantCugat/api/users/login',
			method : 'POST',
			headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
			data : {
				email : $('#email').val(),
				password : $('#password').val()
			},
			statusCode: {
				200: function(response, status, xhr) {
				console.log(xhr.getResponseHeader('token'));
				var user = JSON.stringify(response);
				window.location.assign("desktop/main.html?"+"user="+user);
			},
				403: function(){
					error.text("* El password no es correcto.");
				},
				404: function(){
					error.text("*  El email, no existe.");
				}
			}
			
		});
	});

});

