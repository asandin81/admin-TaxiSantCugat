<?php
if(isset($_POST['email'])) {

// Debes editar las próximas dos líneas de código de acuerdo con tus preferencias
$email_to = "reservas@taxisantcugat.cat";
$email_subject = "angelsandin@gmail.com";

// Aquí se deberían validar los datos ingresados por el usuario
if(!isset($_POST['Nombre']) ||
!isset($_POST['Apellidos-2']) ||
!isset($_POST['Telefono']) ||
!isset($_POST['Email']) ||
!isset($_POST['Dia']) ||
!isset($_POST['Hora']) ||
!isset($_POST['Direcci-n-de-Recogida']) ||
!isset($_POST['Destino'])) {

echo "<b>Ocurrió un error y el formulario no ha sido enviado. </b><br />";
echo "Por favor, vuelva atrás y verifique la información ingresada<br />";
die();
}

$email_message = "Detalles del formulario de contacto:\n\n";
$email_message .= "Nombre: " . $_POST['Nombre'] . "\n";
$email_message .= "Apellido: " . $_POST['Apellidos-2'] . "\n";
$email_message .= "Telefono: " . $_POST['Telefono'] . "\n";
$email_message .= "Email: " . $_POST['Email'] . "\n";
$email_message .= "Dia: " . $_POST['Dia'] . "\n\n";
$email_message .= "Hora: " . $_POST['Hora'] . "\n\n";
$email_message .= "Direccion de Recogida: " . $_POST['Direcci-on-de-Recogida'] . "\n\n";
$email_message .= "Destino: " . $_POST['Destino'] . "\n\n";
$email_message .= "Observaciones: " . $_POST['Observaciones'] . "\n\n";



// Ahora se envía el e-mail usando la función mail() de PHP
$headers = 'From: '.$email_from."\r\n".
'Reply-To: '.$email_from."\r\n" .
'X-Mailer: PHP/' . phpversion();
@mail($email_to, $email_subject, $email_message, $headers);

echo "¡El formulario se ha enviado con éxito!";
}
?>