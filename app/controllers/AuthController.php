<?php

namespace App\Controllers;

use App\Core\Conexion;
use App\Core\Controller;
use App\Core\AuthManager;
use App\Core\Mailer;
use Delight\Auth\InvalidEmailException;
use Delight\Auth\InvalidPasswordException;
use Delight\Auth\TooManyRequestsException;
use Delight\Auth\EmailNotVerifiedException;
use Delight\Auth\UserAlreadyExistsException;
use Lib\Alert;
use App\Core\Sanitizer;

class AuthController extends Controller
{
  private $auth;

  public function __construct()
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start(
        [
          'cookie_samesite' => 'Lax',
          'cookie_httponly' => true,
          'use_strict_mode' => true,
          'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
        ]
      );
    }
    $pdo = Conexion::getInstance()->getConnection();
    $this->auth = AuthManager::getInstance($pdo);
  }

  /*
    * Funcion para renderizar la vista de login
    */
  public function loginView()
  {
    $this->view('login');
  }

  /*
    * Funcion para renderizar la vista de registro
    */
  public function registerView()
  {
    $this->view('register');
  }

  /*
    * Funcion para renderizar la vista para solicitar un cambio de contraseña
    */
  public function changePaswordView()
  {
    $this->view('change-password');
  }

  /*
    * Función que maneja el login
    */
  public function login()
  {
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      Alert::error('Error', 'Método de petición no válido');
      $this->redirect('login');
      return;
    }

    // Sanitizar datos
    $sanitizedData = Sanitizer::sanitizeLoginData($_POST);
    if ($sanitizedData === false) {
      Alert::error('Datos', 'Datos de login inválidos');
      $this->redirect('login');
      return;
    }

    try {
      // Aplicar throttling antes del intento de login
      $this->auth->throttle(3, 60);

      // Intentar login
      $this->auth->login($sanitizedData['email'], $sanitizedData['password']);

      // Regenerar ID de sesión por seguridad
      session_regenerate_id(true);

      Alert::success('Bienvenido', 'Login exitoso');
      $this->redirect('dashboard');
    } catch (InvalidEmailException $e) {
      Alert::warning('Login', 'Credenciales incorrectas');
      $this->redirect('login');
    } catch (InvalidPasswordException $e) {
      Alert::warning('Login', 'Credenciales incorrectas');
      $this->redirect('login');
    } catch (EmailNotVerifiedException $e) {
      Alert::info('Verificación', 'Cuenta no verificada. Revisa tu correo electrónico');
      $this->redirect('login');
    } catch (TooManyRequestsException $e) {
      Alert::warning('Login', 'Demasiados intentos. Debes esperar 1 minuto para volver a intentar');
      $this->redirect('login');
    } catch (\Exception $e) {
      Alert::error('Error', 'Ocurrió un error inesperado');
      $this->redirect('login');
    }
  }

  /*
    * Función que maneja el registro en el sistema
    */
  public function register()
  {
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      Alert::error('Error', 'Método de petición no válido');
      $this->redirect('register');
      return;
    }

    // Sanitizar datos
    $sanitizedData = Sanitizer::sanitizeRegisterData($_POST);
    if ($sanitizedData === false) {
      Alert::error('Datos', 'Datos de registro inválidos o incompletos');
      $this->redirect('register');
      return;
    }

    // Verificar que las contraseñas coincidan
    if (
      !isset($_POST['password_confirmation'])
      || $_POST['password'] !== $_POST['password_confirmation']
    ) {
      Alert::warning('Password', 'Las contraseñas no coinciden');
      $this->redirect('register');
      return;
    }

    // Validar fuerza de contraseña
    if (!Sanitizer::validatePasswordStrength($sanitizedData['password'])) {
      Alert::warning('Password', 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número');
      $this->redirect('register');
      return;
    }

    try {
      // Registrar usuario
      $userId = $this->auth->register(
        $sanitizedData['email'],
        $sanitizedData['password'],
        function ($selector, $token) use ($sanitizedData){
          $url = BASE_URL . 'verify_email/' . \urlencode($selector) . '/' . \urlencode($token);
          // Aquí deberías enviar el email de verificación
          $mailer = new Mailer();
          $mailer->to($sanitizedData['email'])
            ->subject('Verificación de cuenta')
            ->template(
              'verify-email',
              [
                'user_name' => $sanitizedData['name'],
                'verification_link' => $url,
                'expiry_hours' => 24
              ]
            )->send();
        }
      );

      Alert::success('Registro exitoso', 'Verifique su correo electrónico para activar su cuenta');
      $this->redirect('login');
    } catch (InvalidEmailException $e) {
      Alert::error('Datos', 'Correo electrónico no válido');
      $this->redirect('register');
    } catch (InvalidPasswordException $e) {
      Alert::error('Datos', 'Contraseña no válida (mínimo 6 caracteres)');
      $this->redirect('register');
    } catch (UserAlreadyExistsException $e) {
      Alert::info('Usuario', 'Ya existe una cuenta con este correo electrónico');
      $this->redirect('register');
    } catch (TooManyRequestsException $e) {
      Alert::warning('Peticiones', 'Demasiados intentos. Espere un minuto antes de volver a intentar');
      $this->redirect('register');
    } catch (\Exception $e) {
      // Log del error para debugging
      error_log('Error en registro: ' . $e->getMessage());
      Alert::error('Error', 'Ocurrió un error durante el registro');
      $this->redirect('register');
    }
  }

  /*
    * Función de logout
    */
  public function logout()
  {
    try {
      // Destruir sesión de auth primero
      if ($this->auth->isLoggedIn()) {
        $this->auth->logOut();
      }

      // Limpiar datos de sesión
      $_SESSION = [];

      // Destruir cookie de sesión
      if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
          session_name(),
          '',
          [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite']
          ]
        );
      }

      // Destruir sesión
      session_destroy();

      Alert::success('Logout', 'Sesión cerrada correctamente');
      $this->redirect('login');
    } catch (\Exception $e) {
      error_log('Error en logout: ' . $e->getMessage());
      // Incluso si hay error, redirigir al login
      $this->redirect('login');
    }
  }

  /**
   * Verificar cuenta de usuario
   *
   * @param string $selector selector de verificación
   * @param string $token    token de
   *                         verificación
   */
  public function verify($selector, $token)
  {
    if (!isset($selector) || !isset($token)) {
      Alert::error('Error', 'Enlace de verificación inválido');
      $this->redirect('login');
      return;
    }

    try {
      $this->auth->confirmEmail($selector, $token);
      Alert::success('Verificación', 'Cuenta verificada correctamente. Ya puedes iniciar sesión');
      $this->redirect('login');
    } catch (\Exception $e) {
      Alert::error('Error', 'No se pudo verificar la cuenta. El enlace puede haber expirado');
      $this->redirect('login');
    }
  }

  public function forgetPassword()
  {
    // verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      Alert::error('Metodo', 'Metodo de petición no valido');
      $this->redirect('forget-password');
    }
    // verificar datos
    if (!isset($_POST['email']) || empty($_POST['email'])) {
      Alert::error('Datos', 'Email inválido');
      $this->redirect('forget-password');
    }
    //Comprobar validación de email y sanitizar
    $sanitizedEmail = Sanitizer::sanitizeEmail($_POST['email']);
    if ($sanitizedEmail === false) {
      Alert::error('Datos', 'Email inválido');
      $this->redirect('forget-password');
    }
    try {
      $this->auth->forgotPassword(
        $sanitizedEmail,
        function ($selector, $token) use ($sanitizedEmail){
          $url = BASE_URL . 'reset-password/' . urlencode($selector) . '/' . urlencode($token);
          $mailer = new Mailer();
          $mailer->to($sanitizedEmail)
            ->subject('Restablecimiento de contraseña')
            ->template(
              'reset-password',
              [
                'reset_link' => $url,
                'expiry_hours' => 1
              ]
            )->send();
        }
      );
      Alert::info('Email', 'Se ha enviado un correo para restablecer tu contraseña');
      $this->redirect('login');
    } catch (InvalidEmailException $e) {
      Alert::error('Datos', 'Email inválido');
      $this->redirect('forget-password');
    } catch (EmailNotVerifiedException $e) {
      Alert::error('Email', 'El email no ha sido verificado');
      $this->redirect('forget-password');
    } catch (ResetDisabledException $e) {
      Alert::info('Sistema', 'La restablecimiento de contraseña está desactivado');
      $this->redirect('forget-password');
    } catch (TooManyRequestsException $e) {
      Alert::warning('Peticiones', 'Ha excedido el límite de restablecimiento de contraseña');
      $this->redirect('forget-password');
    }
  }

  public function canResetPassword($selector, $token)
  {
    if (!isset($selector) || !isset($token)) {
      Alert::error('Error', 'Enlace de restablecimiento de contraseña inválido');
      $this->redirect('login');
    }
    try {
      $this->auth->canResetPasswordOrThrow($selector, $token);
      $this->view('reset-password', ['selector' => $selector, 'token' => $token]);
    } catch (InvalidSelectorTokenPairException $e) {
      Alert::warning('Token', 'Token inválido');
      $this->redirect('login');
    } catch (TokenExpiredException $e) {
      Alert::warning('Token', 'Token expirado');
      $this->redirect('login');
    } catch (ResetDisabledException $e) {
      Alert::info('Sistema', 'El restablecimiento de contraseña esta desactivado');
      $this->redirect('login');
    } catch (TooManyRequestsException $e) {
      Alert::warning('Peticiones', 'Se ha excedido el límite de restablecimiento de contraseña');
      $this->redirect('login');
    }
  }

  public function resetPassword()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      Alert::error('Metodo', 'Metodo de petición no valido');
      $this->redirect('login');
    }
    if (!isset($_POST['password']) || !isset($_POST['password_confirmation']) || !isset($_POST['selector']) || !isset($_POST['token'])) {
      Alert::error('Datos', 'Datos incompletos');
      $this->redirect('login');
    }
    if ($_POST['password'] !== $_POST['password_confirmation']) {
      Alert::warning('Password', 'Las contraseñas no coinciden');
      $this->redirect('login');
    }
    $sanitizedPassword = Sanitizer::sanitizePassword($_POST['password']);
    try {
      $this->auth->resetPassword($_POST['selector'], $_POST['token'], $sanitizedPassword);

      Alert::success('Password', 'Contraseña cambiada correctamente');
      $this->redirect('login');
    } catch (InvalidSelectorTokenPairException $e) {
      Alert::error('Token', 'Token inválido');
      $this->redirect('login');
    } catch (TokenExpiredException $e) {
      Alert::error('Token', 'El Token expiró');
      $this->redirect('login');
    } catch (ResetDisabledException $e) {
      Alert::info('Sistema', 'El restablecimiento de contraseña esta desactivado');
      $this->redirect('login');
    } catch (InvalidPasswordException $e) {
      Alert::error('Password', 'Password invalido');
      $this->redirect('login');
    } catch (TooManyRequestsException $e) {
      Alert::error('Peticiones', 'Se ha excedido el límite de restablecimiento de contraseña');
      $this->redirect('login');
    }
  }

  /**
   * Redirigir con validación de URL
   *
   * @param string $uri uri a la que se debe redirigir
   */
  private function redirect($uri)
  {
    // Validar que la URI sea segura
    $uri = ltrim($uri, '/');
    $url = BASE_URL . $uri;

    header('Location: ' . $url);
    exit;
  }
}
