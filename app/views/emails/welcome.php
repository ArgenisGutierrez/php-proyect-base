<?php
// ==================================================
// BIENVENIDA (welcome.php)
// ==================================================
$content = '
<h2>¡Bienvenido a nuestra plataforma!</h2>
<p>Hola <strong>' . htmlspecialchars($user_name ?? 'Usuario') . '</strong>,</p>
<p>¡Felicitaciones! Tu cuenta ha sido verificada exitosamente y ya puedes disfrutar de todos nuestros servicios.</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="' . htmlspecialchars($dashboard_link ?? '#') . '" class="btn">
        Ir a mi cuenta
    </a>
</div>

<div class="info-box">
    <p><strong>Primeros pasos:</strong></p>
    <p>• Completa tu perfil</p>
    <p>• Explora nuestras funcionalidades</p>
    <p>• Configura tus preferencias</p>
</div>

<p>Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos. Nuestro equipo de soporte está aquí para ayudarte.</p>

<p>¡Esperamos que tengas una excelente experiencia!</p>
';

require __DIR__ . '/base.php';
