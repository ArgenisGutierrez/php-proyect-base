<?php

namespace App\Core;

use Delight\Auth\Auth;
use PDO;

class AuthManager
{
  private static ?Auth $instance = null;

  public static function getInstance(PDO $pdo): Auth
  {
    if (self::$instance === null) {
      // Mejor detección de HTTPS (incluye proxies reversos)
      $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['SERVER_PORT'] ?? null) === 443
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

      // Cookies más seguras (SameSite Strict en POST)
      $sameSite = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' ? 'Strict' : 'Lax';

      self::$instance = new Auth(
        $pdo,
        null,
        null,
        true,
        [
          'secure' => $isSecure,
          'httponly' => true,
          'samesite' => $sameSite
        ]
      );
    }
    return self::$instance;
  }
}
