<?php

namespace App\Core;

use Delight\Auth\Auth;

class AuthMiddleware
{
  public static function handle(Auth $auth)
  {
    if (!$auth->isLoggedIn()) {
      header('Location:' . BASE_URL . 'login');
      exit;
    }
  }
}
