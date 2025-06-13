<?php
// ==================================================
// TEMPLATE BASE (base.php)
// ==================================================
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $subject ?? 'Notificación' ?></title>
  <style>
    /* Reset CSS */
    body,
    table,
    td,
    p,
    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }

    body {
      background-color: #f8f9fa;
      color: #333333;
      line-height: 1.6;
    }

    .email-container {
      max-width: 600px;
      margin: 0 auto;
      background-color: #ffffff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .email-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 30px 40px;
      text-align: center;
    }

    .email-header h1 {
      color: #ffffff;
      font-size: 24px;
      font-weight: 600;
      margin: 0;
    }

    .email-body {
      padding: 40px;
    }

    .email-body h2 {
      color: #2d3748;
      font-size: 20px;
      margin-bottom: 20px;
    }

    .email-body p {
      color: #4a5568;
      margin-bottom: 16px;
      font-size: 16px;
    }

    .btn {
      display: inline-block;
      padding: 14px 28px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #ffffff !important;
      text-decoration: none;
      border-radius: 6px;
      font-weight: 600;
      margin: 20px 0;
      transition: transform 0.2s;
    }

    .btn:hover {
      transform: translateY(-2px);
    }

    .code-box {
      background-color: #f7fafc;
      border: 2px dashed #cbd5e0;
      border-radius: 8px;
      padding: 20px;
      text-align: center;
      margin: 20px 0;
    }

    .verification-code {
      font-size: 32px;
      font-weight: 700;
      color: #2d3748;
      letter-spacing: 4px;
      font-family: 'Courier New', monospace;
    }

    .email-footer {
      background-color: #f8f9fa;
      padding: 30px 40px;
      text-align: center;
      border-top: 1px solid #e2e8f0;
    }

    .email-footer p {
      color: #718096;
      font-size: 14px;
      margin-bottom: 8px;
    }

    .warning-box {
      background-color: #fef5e7;
      border-left: 4px solid #f6ad55;
      padding: 16px;
      margin: 20px 0;
      border-radius: 0 6px 6px 0;
    }

    .warning-box p {
      color: #744210;
      margin: 0;
    }

    .info-box {
      background-color: #ebf8ff;
      border-left: 4px solid #4299e1;
      padding: 16px;
      margin: 20px 0;
      border-radius: 0 6px 6px 0;
    }

    .info-box p {
      color: #2a4365;
      margin: 0;
    }

    /* Responsive */
    @media only screen and (max-width: 600px) {
      .email-container {
        margin: 0;
        border-radius: 0;
      }

      .email-header,
      .email-body,
      .email-footer {
        padding: 20px;
      }

      .verification-code {
        font-size: 24px;
        letter-spacing: 2px;
      }
    }
  </style>
</head>

<body>
  <div style="padding: 40px 20px;">
    <div class="email-container">
      <div class="email-header">
        <h1><?php echo $company_name ?? 'Mi Aplicación' ?></h1>
      </div>

      <div class="email-body">
        <?php echo $content ?>
      </div>

      <div class="email-footer">
        <p>Este correo fue enviado desde <?php echo $company_name ?? 'Mi Aplicación' ?></p>
        <p>Si no solicitaste esta acción, puedes ignorar este mensaje</p>
        <p style="margin-top: 20px;">
          <small>© <?php echo date('Y') ?> <?php echo $company_name ?? 'Mi Aplicación' ?>. Todos los derechos reservados.</small>
        </p>
      </div>
    </div>
  </div>
</body>

</html>
