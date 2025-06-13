<?php

namespace App\Core;

use PDO;
use PDOException;

class Conexion
{
  protected static $instance;
  protected $conexion;

  private function __construct()
  {
    $this->conectar();
  }

  private function __clone() {}
  public function __wakeup() {}

  public static function getInstance(): self
  {
    if (!isset(self::$instance)) {
      self::$instance = new static();
    }
    return self::$instance;
  }

  public function getConnection(): PDO
  {
    if (!$this->isConnected()) {
      $this->conectar();
    }
    return $this->conexion;
  }

  private function isConnected(): bool
  {
    try {
      return (bool) $this->conexion->query('SELECT 1');
    } catch (PDOException $e) {
      return false;
    }
  }

  private function conectar()
  {
    try {
      if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
        throw new \RuntimeException('Configuración de base de datos incompleta');
      }

      $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_NAME
      );

      $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => false
      ];

      $this->conexion = new PDO($dsn, DB_USER, DB_PASS, $opciones);
    } catch (PDOException $e) {
      error_log('Error de conexión PDO: ' . $e->getMessage());
      throw new \RuntimeException('Error al conectar con el servidor de base de datos.');
    }
  }
}
