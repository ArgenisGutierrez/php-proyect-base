<?php

namespace App\Core;

class Controller
{
  /**
   *  Renderiza una vista
   */
  protected function view($view, $data = [])
  {
    $data = $this->sanitizeOutput($data);
    extract($data);
    $viewPath = __DIR__ . "/../views/{$view}.php";

    if (!file_exists($viewPath)) {
      header('Location: ' . BASE_URL . '404');
    }

    include_once $viewPath;
  }

  /**
   * Sanitiza todos los valores de salida para prevenir XSS
   */
  private function sanitizeOutput(array $data): array
  {
    array_walk_recursive(
      $data,
      function (&$value) {
        if (is_string($value)) {
          $value = $this->escapeOutput($value);
        }
      }
    );
    return $data;
  }

  /**
   * Escapa contenido HTML para prevenir XSS
   */
  private function escapeOutput(string $value): string
  {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
  }
}
