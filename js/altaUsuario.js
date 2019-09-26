/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
 
 
const URL = 'http://taxisantcugat.cat:8080/';
  const form = document.forms['altaUsuario'];
  var boton = document.getElementById('botonEnviar')

  boton.addEventListener('click', cargar)  
  
  form.addEventListener('submit', e => {
  	boton.disabled = true
    e.preventDefault()
    fetch(scriptURL, { method: 'POST', body: new FormData(form)})
    	.then(response => respuesta())
     // .then(response => console.log('Success!', response))
      .catch(error => console.error('Error!', error.message))
     /* if(e.status == 200){
      	alert('Formulario enviado con exito')
      } else {
      	alert('Fallo al envier el formulation')
      }*/
  })
function cargar(){
	//var loading = document.getElementById('botonEnviar').disabled = true
	//loading.insertAdjacentHTML('afterbegin','<i class="fa fa-spinner fa-spin"></i>')
	
	var respuesta = document.getElementById('respuesta');
	var botones = document.getElementById('botones');
		
	botones.style.display = (botones.style.display == 'none') ? 'display': 'none';
				
	respuesta.insertAdjacentHTML('afterbegin','<p style="color:red"><b>Dando de alta un nuevo usuario. Espere....</b></p>')
	
}  

function respuesta(){
	var respuesta = document.getElementById('respuesta');
	var botones = document.getElementById('botones');
		
	botones.style.display = (botones.style.display == 'none') ? 'display': 'none';
				
	respuesta.insertAdjacentHTML('beforeend','<p style="color:red"><b>Usuario creado con exito, compruebe su Email, para verificar la cuenta.</b></p>')
    respuesta.insertAdjacentHTML('beforeend','<button id="btnLogin" class="botonEnviar" type="submit">LogIn</button>')
	
}


