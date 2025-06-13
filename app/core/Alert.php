<?php

namespace Lib;

/**
 * Clase para manejo de notificaciones estilo "flash" en aplicaciones web
 * 
 * Utiliza SweetAlert2 para mostrar mensajes emergentes almacenados en sesión.
 * 
 * @package Lib
 * 
 * @example
 * // Mostrar alerta de éxito
 * Alert::success('Éxito', 'Registro guardado correctamente');
 * 
 * // Mostrar alerta en la vista
 * Alert::display();
 * 
 * @requires SweetAlert2 Debe estar incluido en el frontend para funcionar
 */
class Alert
{
  /**
   * Almacena una alerta de éxito en sesión
   * 
   * @param  string $title   Título principal de la
   *                         alerta
   * @param  string $message Mensaje detallado (opcional)
   * @static
   */
  public static function success(string $title, string $message = ''): void
  {
    self::set('success', $title, $message);
  }

  /**
   * Almacena una alerta de error en sesión
   * 
   * @param  string $title   Título principal de la
   *                         alerta
   * @param  string $message Mensaje detallado (opcional)
   * @static
   */
  public static function error(string $title, string $message = ''): void
  {
    self::set('error', $title, $message);
  }

  /**
   * Almacena una alerta informativa en sesión
   * 
   * @param  string $title   Título principal de la
   *                         alerta
   * @param  string $message Mensaje detallado (opcional)
   * @static
   */
  public static function info(string $title, string $message = ''): void
  {
    self::set('info', $title, $message);
  }

  /**
   * Almacena una alerta de advertencia en sesión
   * 
   * @param  string $title   Título principal de la
   *                         alerta
   * @param  string $message Mensaje detallado (opcional)
   * @static
   */
  public static function warning(string $title, string $message = ''): void
  {
    self::set('warning', $title, $message);
  }

  /**
   * Método interno para almacenar alertas en sesión
   * 
   * @param  string $type    Tipo de alerta (success|error|info|warning)
   * @param  string $title   Título de la
   *                         alerta
   * @param  string $message Mensaje detallado
   * @static
   * @access private
   */
  private static function set(string $type, string $title, string $message): void
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start(
        [
          'cookie_samesite' => 'Lax',
          'use_strict_mode' => true
        ]
      );
    }

    $_SESSION['alert'] = [
      'type' => $type,
      'title' => $title,
      'message' => $message
    ];
  }

  /**
   * Muestra la alerta almacenada y limpia la sesión
   * 
   * Genera el código JavaScript necesario para mostrar la alerta
   * 
   * @static
   * @return void
   */
  public static function display(): void
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start(
        [
          'cookie_samesite' => 'Lax',
          'use_strict_mode' => true
        ]
      );
    }

    if (isset($_SESSION['alert'])) {
      $alert = $_SESSION['alert'];
      unset($_SESSION['alert']);

      echo "
      <script>
        Swal.fire({
        icon: '" . htmlspecialchars($alert['type'], ENT_QUOTES) . "',
        title: '" . htmlspecialchars($alert['title'], ENT_QUOTES) . "',
        text: '" . htmlspecialchars($alert['message'], ENT_QUOTES) . "'
        });
      </script>";
    }
  }
}
