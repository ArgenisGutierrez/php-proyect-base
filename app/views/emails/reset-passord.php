<?php
// ==================================================
// RECUPERACIÓN DE CONTRASEÑA (reset-password.php)
// ==================================================
$content = '
<h2>Solicitud de cambio de contraseña</h2>
<p>Hola <strong>' . htmlspecialchars($user_name ?? 'Usuario') . '</strong>,</p>
<p>Recibimos una solicitud para restablecer la contraseña de tu cuenta. Si fuiste tú quien la solicitó, haz clic en el botón de abajo para crear una nueva contraseña.</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="' . htmlspecialchars($reset_link) . '" class="btn">
        Cambiar mi contraseña
    </a>
</div>

<div class="info-box">
    <p><strong>Datos de la solicitud:</strong></p>
    <p>• Hora: ' . date('d/m/Y H:i:s') . '</p>
    <p>• IP: ' . htmlspecialchars($user_ip ?? 'No disponible') . '</p>
</div>

<div class="warning-box">
    <p><strong>¡Importante!</strong> Este enlace expirará en ' . ($expiry_hours ?? 1) . ' hora(s) por tu seguridad.</p>
</div>

<p>Si no solicitaste cambiar tu contraseña, ignora este correo y tu contraseña permanecerá sin cambios.</p>
<p>Para mayor seguridad, te recomendamos cambiar tu contraseña regularmente y usar una contraseña única y segura.</p>
';

require __DIR__ . '/base.php';
