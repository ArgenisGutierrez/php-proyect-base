<?php
// ==================================================
// CÓDIGO DE VERIFICACIÓN 2FA (two-factor.php)
// ==================================================
$content = '
<h2>Código de verificación</h2>
<p>Hola <strong>' . htmlspecialchars($user_name ?? 'Usuario') . '</strong>,</p>
<p>Para completar tu inicio de sesión, ingresa este código de verificación:</p>

<div class="code-box">
    <div class="verification-code">' . htmlspecialchars($verification_code ?? '000000') . '</div>
</div>

<div class="info-box">
    <p><strong>Detalles del acceso:</strong></p>
    <p>• Hora: ' . date('d/m/Y H:i:s') . '</p>
    <p>• IP: ' . htmlspecialchars($user_ip ?? 'No disponible') . '</p>
    <p>• Ubicación: ' . htmlspecialchars($location ?? 'No disponible') . '</p>
</div>

<div class="warning-box">
    <p><strong>Importante:</strong> Este código expira en ' . ($expiry_minutes ?? 10) . ' minutos.</p>
</div>

<p>Si no intentaste iniciar sesión, alguien más podría estar tratando de acceder a tu cuenta. Te recomendamos cambiar tu contraseña inmediatamente.</p>
';

require __DIR__ . '/base.php';
