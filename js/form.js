  function habilitar(value){  
	  var calle = document.getElementById('calle');
	  var cp = document.getElementById('cp');
	  var vuelo = document.getElementById('vueloTren');
		if (value == "Aeropuerto" || value == "Estacion de Sants" || value == "Aeropuerto Girona" || value == "Aeropuerto Reus"){
			calle.style.display = (calle.style.display == 'none') ? 'display': 'none';
			cp.style.display = (cp.style.display == 'none') ? 'display': 'none';
			vuelo.style.display = (vuelo.style.display == 'block') ? 'display': 'block';
		} else {
			calle.style.display = (calle.style.display == 'block') ? 'display': 'block';
			cp.style.display = (cp.style.display == 'block') ? 'display': 'block';
			vuelo.style.display = (vuelo.style.display == 'none') ? 'display': 'none';
		}
	}
 
  function recogida(value){
	  var recogida = document.getElementById('recogida');
	  var labelRecogida = document.getElementById('labelRecogida');
	  if (value == "Si"){
		  labelRecogida.style.display = (labelRecogida.style.display == 'block') ? 'display': 'block';
		  recogida.style.display = (recogida.style.display == 'block') ? 'display': 'flex';
	  } else {
		  recogida.style.display = (recogida.style.display == 'none') ? 'display': 'none';
		  labelRecogida.style.display = (labelRecogida.style.display == 'none') ? 'display': 'none';
	  }
  }
  
 