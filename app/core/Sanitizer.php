<?php

/**
 * Clase para sanitizar datos de login
 */
class Sanitizer
{
  /**
   * Sanitiza un email
   * 
   * @param  string $email
   * @return string|false Retorna el email sanitizado o false si no es válido
   */
  public static function sanitizeEmail($email)
  {
    // Remover espacios en blanco al inicio y final
    $email = trim($email);

    // Convertir a minúsculas
    $email = strtolower($email);

    // Remover caracteres peligrosos
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return false;
    }

    // Verificar longitud máxima (RFC 5321 especifica 320 caracteres máximo)
    if (strlen($email) > 320) {
      return false;
    }

    // Verificar que no contenga caracteres maliciosos
    $maliciousChars = ['<', '>', '"', '\'', '&', '\0', '\n', '\r', '\t'];
    foreach ($maliciousChars as $char) {
      if (strpos($email, $char) !== false) {
        return false;
      }
    }

    return $email;
  }

  /**
   * Sanitiza una contraseña
   * 
   * @param  string $password
   * @return string|false Retorna la contraseña sanitizada o false si no es válida
   */
  public static function sanitizePassword($password)
  {
    // Verificar que la contraseña no esté vacía
    if (empty($password)) {
      return false;
    }

    // Verificar longitud mínima y máxima
    if (strlen($password) < 6 || strlen($password) > 255) {
      return false;
    }

    // Remover caracteres nulos y de control peligrosos
    $password = str_replace(['\0', '\n', '\r'], '', $password);

    // Verificar que no contenga solo espacios en blanco
    if (trim($password) === '') {
      return false;
    }

    return $password;
  }

  /**
   * Función principal para sanitizar datos de login
   * 
   * @param  array $loginData Array con 'email' y 'password'
   * @return array|false Retorna array con datos sanitizados o false si hay error
   */
  public static function sanitizeLoginData($loginData)
  {
    $result = [];

    // Verificar que existan los campos requeridos
    if (!isset($loginData['email']) || !isset($loginData['password'])) {
      return false;
    }

    // Sanitizar email
    $sanitizedEmail = self::sanitizeEmail($loginData['email']);
    if ($sanitizedEmail === false) {
      return false;
    }
    $result['email'] = $sanitizedEmail;

    // Sanitizar password
    $sanitizedPassword = self::sanitizePassword($loginData['password']);
    if ($sanitizedPassword === false) {
      return false;
    }
    $result['password'] = $sanitizedPassword;

    return $result;
  }
  /**
   * Sanitiza un nombre (nombre, apellido, etc.)
   * 
   * @param  string $name
   * @return string|false Retorna el nombre sanitizado o false si no es válido
   */
  public static function sanitizeName($name)
  {
    // Remover espacios en blanco al inicio y final
    $name = trim($name);

    // Verificar que no esté vacío
    if (empty($name)) {
      return false;
    }

    // Remover caracteres HTML y PHP tags
    $name = strip_tags($name);

    // Convertir entidades HTML
    $name = html_entity_decode($name, ENT_QUOTES, 'UTF-8');

    // Permitir solo letras, espacios, guiones y apostrofes (para nombres como O'Connor, María-José)
    $name = preg_replace('/[^a-zA-ZÀ-ÿñÑ\s\-\']/u', '', $name);

    // Remover espacios múltiples y reemplazar por uno solo
    $name = preg_replace('/\s+/', ' ', $name);

    // Remover guiones y apostrofes múltiples
    $name = preg_replace('/\-+/', '-', $name);
    $name = preg_replace('/\'+/', "'", $name);

    // Verificar longitud (entre 2 y 50 caracteres)
    if (strlen($name) < 2 || strlen($name) > 50) {
      return false;
    }

    // Verificar que no contenga solo caracteres especiales
    if (!preg_match('/[a-zA-ZÀ-ÿñÑ]/u', $name)) {
      return false;
    }

    // Capitalizar primera letra de cada palabra
    $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');

    // Limpiar espacios finales otra vez después de la capitalización
    $name = trim($name);

    return $name;
  }

  /**
   * Sanitiza datos de registro completos (email, password, nombre, apellido)
   * 
   * @param  array $userData Array con los datos del usuario
   * @return array|false Retorna array con datos sanitizados o false si hay error
   */
  public static function sanitizeRegisterData($userData)
  {
    $result = [];

    // Sanitizar email
    if (isset($userData['email'])) {
      $sanitizedEmail = self::sanitizeEmail($userData['email']);
      if ($sanitizedEmail === false) {
        return false;
      }
      $result['email'] = $sanitizedEmail;
    }

    // Sanitizar password
    if (isset($userData['password'])) {
      $sanitizedPassword = self::sanitizePassword($userData['password']);
      if ($sanitizedPassword === false) {
        return false;
      }
      $result['password'] = $sanitizedPassword;
    }

    // Sanitizar nombre
    if (isset($userData['nombre']) || isset($userData['first_name'])) {
      $nameField = isset($userData['nombre']) ? 'nombre' : 'first_name';
      $sanitizedName = self::sanitizeName($userData[$nameField]);
      if ($sanitizedName === false) {
        return false;
      }
      $result[$nameField] = $sanitizedName;
    }

    // Sanitizar apellido
    if (isset($userData['apellido']) || isset($userData['last_name'])) {
      $lastNameField = isset($userData['apellido']) ? 'apellido' : 'last_name';
      $sanitizedLastName = self::sanitizeName($userData[$lastNameField]);
      if ($sanitizedLastName === false) {
        return false;
      }
      $result[$lastNameField] = $sanitizedLastName;
    }

    // Sanitizar nombre completo (si viene en un solo campo)
    if (isset($userData['full_name'])) {
      $sanitizedFullName = self::sanitizeName($userData['full_name']);
      if ($sanitizedFullName === false) {
        return false;
      }
      $result['full_name'] = $sanitizedFullName;
    }

    return $result;
  }

  /**
   * Función para validar fuerza de contraseña (opcional)
   * 
   * @param  string $password
   * @return bool
   */
  public static function validatePasswordStrength($password)
  {
    // Al menos 8 caracteres, una mayúscula, una minúscula y un número
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
  }
}
