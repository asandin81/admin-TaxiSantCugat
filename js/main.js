$('document').ready(function() {
	var user = JSON.parse(obtenerParametros());
	console.log(user.nombre);
	$('#subtitulo').text('Hola '+user.nombre+' '+user.apellidos+' bienvenido a tu area personal.' );
	$('#menu').click({op: 0}, menu);
	$('#btn-reserva').click({op: 1}, menu);
	$('#menu-reserva').click({op: 1}, menu);
	$('#mis-datos').click({op: 2}, menu);
	$('#mis-direcciones').click({op: 3}, menu);
	$("#historial").click({op: 4}, menu);
	$('#salir').click(function(){
		user = null;
		window.location.assign("../index.html");
	});

	$('#nueva-reserva').hide();
	$('#form-mis-datos').hide();
	$('#form-mis-direcciones').hide();
	$('#table-historial').hide();




});



function obtenerParametros() {
	var paramstr = window.location.search.substr(1);
	var paramarr = paramstr.split ("&");
	var params = {};
	var tmparr = paramarr[0].split("=");
	params[tmparr[0]] = tmparr[1].replace(/%22/g,'"');


if (params['user']) {
   console.log('El valor del parámetro variable es: '+params['user']);
   return params["user"];
} else {
   console.log('No se envió el parámetro variable');
}
}

function menu(op){
	switch(op.data.op){
		case 0:			
			$('#nueva-reserva').hide();
			$('#form-mis-datos').hide();
			$('#form-mis-direcciones').hide();
			$('#table-historial').hide();
			$('#home').show();
			break;
		case 1:
			$('#nueva-reserva').show();
			$('#form-mis-datos').hide();
			$('#form-mis-direcciones').hide();
			$('#table-historial').hide();
			$('#home').hide();
			$('#nueva-reserva').load('nuevareserva.html');
		break;
		case 2:
			$('#nueva-reserva').hide();
			$('#form-mis-datos').show();
			$('#form-mis-direcciones').hide();
			$('#table-historial').hide();
			$('#home').hide();
		break;
		case 3:
			$('#nueva-reserva').hide();
			$('#form-mis-datos').hide();
			$('#form-mis-direcciones').show();
			$('#table-historial').hide();
			$('#home').hide();
		break;
		case 4:
			$('#nueva-reserva').hide();
			$('#form-mis-datos').hide();
			$('#form-mis-direcciones').hide();
			$('#table-historial').show();
			$('#home').hide();
		break;
		default:
		 	$('#nueva-reserva').hide();
			$('#form-mis-datos').hide();
			$('#form-mis-direcciones').hide();
			$('#table-historial').hide();
			$('#home').show();
		break;
	}
}
