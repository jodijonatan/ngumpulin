<?php
// app/controllers/auth.php
require_once __DIR__ . '/../models/model.php';

class AuthController
{
  protected $model;
  public function __construct()
  {
    $this->model = new Model();
    if (!isset($_SESSION)) session_start();
  }

  // login returns user array or false
  public function login($username, $password)
  {
    $user = $this->model->fetchOne('SELECT id, username, password, name, role FROM users WHERE username = ?', 's', [$username]);
    if ($user && password_verify($password, $user['password'])) {
      unset($user['password']);
      $_SESSION['user'] = $user;
      return $user;
    }
    return false;
  }

  public function logout()
  {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
      );
    }
    session_destroy();
  }

  // register user (returns id or false)
  public function register($username, $password, $name, $role = 'student')
  {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    try {
      $stmt = $this->model->run('INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)', 'ssss', [$username, $hash, $name, $role]);
      return $this->model->lastInsertId();
    } catch (Exception $e) {
      return false;
    }
  }

  public function currentUser()
  {
    return $_SESSION['user'] ?? null;
  }

  public function isLoggedIn()
  {
    return !empty($_SESSION['user']);
  }
}
