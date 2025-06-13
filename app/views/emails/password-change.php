
<?php
// ==================================================
// CONTRASEÑA CAMBIADA (password-changed.php)
// ==================================================
$content = '
<h2>Contraseña actualizada exitosamente</h2>
<p>Hola <strong>' . htmlspecialchars($user_name ?? 'Usuario') . '</strong>,</p>
<p>Te confirmamos que tu contraseña ha sido cambiada exitosamente.</p>

<div class="info-box">
    <p><strong>Detalles del cambio:</strong></p>
    <p>• Fecha: ' . date('d/m/Y H:i:s') . '</p>
    <p>• Dirección IP: ' . htmlspecialchars($user_ip ?? 'No disponible') . '</p>
    <p>• Navegador: ' . htmlspecialchars($user_agent ?? 'No disponible') . '</p>
</div>

<p>Ya puedes iniciar sesión con tu nueva contraseña.</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="' . htmlspecialchars($login_link ?? '#') . '" class="btn">
        Iniciar sesión
    </a>
</div>

<div class="warning-box">
    <p><strong>¿No fuiste tú?</strong> Si no cambiaste tu contraseña, contacta inmediatamente con nuestro equipo de soporte.</p>
</div>
';

require __DIR__ . '/base.php';
?>
