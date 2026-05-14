<?php
/**
 * CONFIGURACIÓN DE CORREO - GamerZone
 * El cliente debe cambiar estos datos con su propio correo Gmail
 * Para obtener la contraseña de aplicación:
 * 1. Ir a myaccount.google.com/apppasswords
 * 2. Crear contraseña para "GamerZone"
 * 3. Pegar la clave de 16 caracteres en MAIL_PASS
 */

define('MAIL_HOST',     'smtp.gmail.com');
define('MAIL_PORT',     587);
define('MAIL_USER',     'tucorreo@gmail.com');   // <-- Cambiar por el correo del cliente
define('MAIL_PASS',     'xxxx xxxx xxxx xxxx');   // <-- Contraseña de aplicación Gmail
define('MAIL_FROM',     'tucorreo@gmail.com');   // <-- Mismo correo
define('MAIL_NAME',     'GamerZone Bolivia');
define('MAIL_ENABLED',  false); // Cambiar a true cuando el cliente configure su correo