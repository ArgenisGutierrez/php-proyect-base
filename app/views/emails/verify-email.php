<?php
// ==================================================
// VERIFICACIÓN DE EMAIL (verify-email.php)
// ==================================================
$content = '
<h2>¡Bienvenido! Verifica tu email</h2>
<p>Hola <strong>' . htmlspecialchars($user_name ?? 'Usuario') . '</strong>,</p>
<p>Gracias por registrarte en nuestra plataforma. Para completar tu registro y comenzar a usar tu cuenta, necesitamos verificar tu dirección de correo electrónico.</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="' . htmlspecialchars($verification_link) . '" class="btn">
        Verificar mi email
    </a>
</div>

<div class="info-box">
    <p><strong>¿Prefieres usar un código?</strong> También puedes ingresar este código de verificación:</p>
</div>

<div class="code-box">
    <div class="verification-code">' . htmlspecialchars($verification_code ?? '000000') . '</div>
</div>

<div class="warning-box">
    <p><strong>Importante:</strong> Este enlace expirará en ' . ($expiry_hours ?? 24) . ' horas por seguridad.</p>
</div>

<p>Si no te registraste en nuestra plataforma, puedes ignorar este correo de forma segura.</p>
';

require __DIR__ . '/base.php';
