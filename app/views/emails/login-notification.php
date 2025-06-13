<?php
// ==================================================
// NOTIFICACIÓN DE ACCESO (login-notification.php)
// ==================================================
$content = '
<h2>Nuevo acceso a tu cuenta</h2>
<p>Hola <strong>' . htmlspecialchars($user_name ?? 'Usuario') . '</strong>,</p>
<p>Te notificamos que se ha iniciado sesión en tu cuenta desde un nuevo dispositivo o ubicación.</p>

<div class="info-box">
    <p><strong>Detalles del acceso:</strong></p>
    <p>• Fecha: ' . date('d/m/Y H:i:s') . '</p>
    <p>• IP: ' . htmlspecialchars($user_ip ?? 'No disponible') . '</p>
    <p>• Ubicación: ' . htmlspecialchars($location ?? 'No disponible') . '</p>
    <p>• Dispositivo: ' . htmlspecialchars($device ?? 'No disponible') . '</p>
    <p>• Navegador: ' . htmlspecialchars($browser ?? 'No disponible') . '</p>
</div>

<p>Si fuiste tú quien inició sesión, puedes ignorar este mensaje.</p>

<div class="warning-box">
    <p><strong>¿No fuiste tú?</strong> Si no reconoces este acceso, cambia tu contraseña inmediatamente y contacta con soporte.</p>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="' . htmlspecialchars($security_link ?? '#') . '" class="btn">
        Revisar seguridad
    </a>
</div>
';

require __DIR__ . '/base.php';
