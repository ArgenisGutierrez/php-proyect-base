<?php

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use function FastRoute\cachedDispatcher;

// 1. Función de sanitización (fuera de cualquier contexto de objeto)
function sanitizeRouteParams(array $params): array
{
  $sanitized = [];
  foreach ($params as $key => $value) {
    if ($key === 'id') {
      $sanitized[$key] = filter_var(
        $value,
        FILTER_VALIDATE_INT,
        [
          'options' => ['min_range' => 1]
        ]
      );
    } else {
      $sanitized[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    if ($sanitized[$key] === false || $sanitized[$key] === null) {
      throw new \InvalidArgumentException("Parámetro inválido: $key");
    }
  }
  return $sanitized;
}

// Iniciar compresión de salida
if (
  !headers_sent() && extension_loaded('zlib') && isset($_SERVER['HTTP_ACCEPT_ENCODING'])
  && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false
) {
  ob_start('ob_gzhandler');
} else {
  ob_start();
}

// Manejar solicitudes OPTIONS para CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  $allowedOrigins = ['https://tudominio.com'];
  $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

  if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
  }

  header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Authorization");
  header("Access-Control-Max-Age: 86400");
  http_response_code(200);
  exit;
}

// Obtener método HTTP y URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Eliminar query string
if (($pos = strpos($uri, '?')) !== false) {
  $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Configurar dispatcher con cache
$routeDefinitionCallback = function (RouteCollector $r) {
  $r->addGroup(
    '/api/v1',
    function (RouteCollector $r) {
      // $r->addRoute('POST', '/login', 'App\Controllers\ApiAuthController@login');
    }
  );

  // $r->addRoute('GET', '/notas', 'App\Controllers\NotasWebController@index');
};

$cacheFile = __DIR__ . '/../storage/cache/routes.cache';
$dispatcher = (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production' && file_exists($cacheFile))
  ? cachedDispatcher($routeDefinitionCallback, ['cacheFile' => $cacheFile])
  : simpleDispatcher($routeDefinitionCallback);

// Middleware para rutas API
if (strpos($uri, '/api') === 0) {
  // Configuración segura de CORS
  $allowedOrigins = ['http://base.local'];
  $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

  if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
  }

  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Credentials: true");
  header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
  header("X-Content-Type-Options: nosniff");
  header("X-Frame-Options: DENY");

  // Manejo de errores
  set_exception_handler(
    function ($e) {
      http_response_code(500);
      header('Content-Type: application/json');

      $response = ['status' => 'error', 'message' => 'Internal Server Error'];

      if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] !== 'production') {
        $response['error'] = $e->getMessage();
        $response['trace'] = $e->getTraceAsString();
      }

      echo json_encode($response);
      exit;
    }
  );
}

// Despachar la ruta
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// Manejar el resultado
switch ($routeInfo[0]) {
  case Dispatcher::NOT_FOUND:
    http_response_code(404);
    include __DIR__ . '/../app/views/404.php';
    break;

  case Dispatcher::METHOD_NOT_ALLOWED:
    header('Allow: ' . implode(', ', $routeInfo[1]));
    http_response_code(405);
    echo "Método no permitido";
    break;

  case Dispatcher::FOUND:
    $handler = $routeInfo[1];
    $vars = sanitizeRouteParams($routeInfo[2]); // Llamada corregida sin $this

    [$controllerClass, $method] = explode('@', $handler);

    // Validar controlador
    if (!class_exists($controllerClass)) {
      throw new \RuntimeException("Controlador $controllerClass no encontrado");
    }

    // Verificar herencia del controlador base
    if (!is_subclass_of($controllerClass, 'App\Core\Controller')) {
      throw new \RuntimeException("Controlador $controllerClass no es válido");
    }

    $controller = new $controllerClass();

    if (!method_exists($controller, $method)) {
      throw new \RuntimeException("Método $method no existe en $controllerClass");
    }

    // Llamar al método
    call_user_func_array([$controller, $method], $vars);
    break;
}

// Finalizar buffer
ob_end_flush();
