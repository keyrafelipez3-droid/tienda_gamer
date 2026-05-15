<?php
// Cambia MAIL_ENABLED a true y rellena las credenciales SMTP para envío real.
// Con false el sistema funciona en modo demo (muestra el código en pantalla).
define('MAIL_ENABLED', false);
define('MAIL_HOST',    'smtp.gmail.com');
define('MAIL_USER',    'tu_correo@gmail.com');
define('MAIL_PASS',    'tu_app_password');
define('MAIL_PORT',    587);
define('MAIL_FROM',    'tu_correo@gmail.com');
define('MAIL_NAME',    'GamerZone Bolivia');
