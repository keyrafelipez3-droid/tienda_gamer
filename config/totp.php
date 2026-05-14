<?php
require_once __DIR__ . '/../vendor/autoload.php';

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\EndroidQrCodeProvider;

function getTFA()
{
    return new TwoFactorAuth(new EndroidQrCodeProvider(), 'GamerZone Bolivia');
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
    return getTFA()->getQRCodeImageAsDataUri($correo, $secret);
}