<?php

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use InvalidArgumentException;

class Mailer
{
  private $mailer;
  private $templatePath;
  private $config;

  public function __construct()
  {
    $this->mailer = new PHPMailer(true);
    $this->config = $this->loadConfig();
    $this->templatePath = __DIR__ . '/../views/emails/';

    $this->configure();
  }

  private function loadConfig()
  {
    return [
      'host' => $_ENV['MAIL_HOST'] ?? 'smtp.example.com',
      'username' => $_ENV['MAIL_USERNAME'] ?? 'user@example.com',
      'password' => $_ENV['MAIL_PASSWORD'] ?? 'secret',
      'port' => $_ENV['MAIL_PORT'] ?? 587,
      'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
      'from_email' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@example.com',
      'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Sistema de Notificaciones',
      'debug' => $_ENV['MAIL_DEBUG'] ?? 0
    ];
  }

  private function configure()
  {
    // Configuración del servidor
    $this->mailer->isSMTP();
    $this->mailer->Host = $this->config['host'];
    $this->mailer->SMTPAuth = true;
    $this->mailer->Username = $this->config['username'];
    $this->mailer->Password = $this->config['password'];
    $this->mailer->SMTPSecure = $this->config['encryption'];
    $this->mailer->Port = $this->config['port'];

    // Configuración del remitente
    $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);

    // Configuración de depuración
    if ($this->config['debug']) {
      $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
    }

    // Configuración general
    $this->mailer->isHTML(true);
    $this->mailer->CharSet = 'UTF-8';
    $this->mailer->Encoding = 'quoted-printable';
  }

  public function to($email, $name = '')
  {
    if (!$this->validateEmail($email)) {
      throw new InvalidArgumentException("Dirección de correo inválida: $email");
    }

    $this->mailer->addAddress($email, $name);
    return $this;
  }

  public function cc($email, $name = '')
  {
    if (!$this->validateEmail($email)) {
      throw new InvalidArgumentException("Dirección CC inválida: $email");
    }

    $this->mailer->addCC($email, $name);
    return $this;
  }

  public function bcc($email, $name = '')
  {
    if (!$this->validateEmail($email)) {
      throw new InvalidArgumentException("Dirección BCC inválida: $email");
    }

    $this->mailer->addBCC($email, $name);
    return $this;
  }

  public function subject($subject)
  {
    $this->mailer->Subject = $subject;
    return $this;
  }

  public function body($content)
  {
    $this->mailer->Body = $content;
    return $this;
  }

  public function template($templateName, $data = [])
  {
    $templatePath = $this->templatePath . $templateName . '.php';

    if (!file_exists($templatePath)) {
      throw new \Exception("Template no encontrado: $templatePath");
    }

    $content = $this->renderTemplate($templatePath, $data);
    $this->mailer->Body = $content;
    return $this;
  }

  private function renderTemplate($templatePath, $data = [])
  {
    ob_start();

    // Extraer variables del array de datos
    foreach ($data as $key => $value) {
      if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
        $$key = $value;
      }
    }

    include $templatePath;
    return ob_get_clean();
  }

  public function attach($filePath, $name = '')
  {
    if (!file_exists($filePath)) {
      throw new \Exception("Archivo adjunto no encontrado: $filePath");
    }

    $this->mailer->addAttachment($filePath, $name);
    return $this;
  }

  public function send($maxRetries = 3)
  {
    try {
      $recipient = !empty($this->mailer->getToAddresses())
        ? $this->mailer->getToAddresses()[0][0]
        : 'unknown';

      $attempts = 0;
      do {
        try {
          $this->mailer->send();
          return true;
        } catch (Exception $e) {
          $attempts++;
          error_log("Error enviando email a $recipient: " . $this->mailer->ErrorInfo);
          if ($attempts < $maxRetries) {
            error_log("Intentando enviar el email nuevamente ($attempts/$maxRetries)...");
            sleep(1);
          }
        }
      } while ($attempts < $maxRetries);

      error_log("Falló el envío del email después de $maxRetries intentos");
      return false;
    } finally {
      $this->mailer->clearAddresses();
      $this->mailer->clearCCs();
      $this->mailer->clearBCCs();
      $this->mailer->clearAttachments();
      $this->mailer->clearReplyTos();
    }
  }

  private function validateEmail($email)
  {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return false;
    }
    // Verificar que no tenga caracteres problemáticos
    if (preg_match('/[<>"]/', $email)) {
      return false;
    }
    return true;
  }
}
