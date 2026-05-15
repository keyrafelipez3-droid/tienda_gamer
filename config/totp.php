<?php
require_once __DIR__ . '/../vendor/autoload.php';

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider;

function getTFA()
{
    return new TwoFactorAuth(new QRServerProvider(), 'GamerZone Bolivia');
}

function generarSecretTOTP()
{
    return getTFA()->createSecret();
}

function verificarCodigoTOTP($secret, $codigo)
{
    return getTFA()->verifyCode($secret, $codigo);
}

function getQRCodeUrl($correo, $secret)
{
    $otpauth = getTFA()->getQRText($correo, $secret);
    return 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . rawurlencode($otpauth);
}