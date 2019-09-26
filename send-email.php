<?php
$nombre = $_POST['Nombre'];
$apellidos = $_POST['Apellidos'];
$telefono = $_POST['Telefono'];
$mail = $_POST['Email'];
$dia = $_POST['Dia'];
$hora = $_POST['Hora'];
$direccion = $_POST['Direcci-on-de-Recogida'];
$destino = $_POST['Destino'];
$Observaciones = $_POST['Observaciones'];

$header = 'From: ' . $mail . " \r\n";
$header .= "X-Mailer: PHP/" . phpversion() . " \r\n";
$header .= "Mime-Version: 1.0 \r\n";
$header .= "Content-Type: text/plain";

$mensaje = "Este mensaje fue enviado por " . $nombre . " \r\n";
$mensaje .= "Su e-mail es: " . $mail . " \r\n";
$mensaje .= "Mensaje: " . $_POST['mensaje'] . " \r\n";
$mensaje .= "Enviado el " . date('d/m/Y', time());

$para = 'reservas@taxisantcugat.cat';
$asunto = 'Reserva Online';

mail($para, $asunto, utf8_decode($mensaje), $header);

echo 'Reserva realizada correctamente, en breve se la confirmaremos via E-mail';
?>