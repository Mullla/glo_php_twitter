<?php
include_once 'config.php';

function get_url($page = '') {
  return HOST . "/$page";
}

function get_page_title($title = '') {
  return SITE_NAME . ($title ? " - $title" : '');
}

function db() {
  try {
    return new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [
      PDO::ATTR_EMULATE_PREPARES => false,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
  } catch (PDOException $e) {
    die($e->getMessage());
  }
}

function db_query($sql, $exec = false) {
  if (empty($sql)) return false;

  if ($exec) return db()->exec(($sql));

  return db()->query($sql);
}

function get_posts($user_id = 0, $sort = false) {
  $sorting = $sort ? 'ASC' : 'DESC';

  if ($user_id > 0) return db_query("SELECT posts.*, users.name, users.login, users.avatar FROM `posts` JOIN `users` ON users.id = posts.user_id WHERE posts.user_id = $user_id ORDER BY `posts`.`date` $sorting;")->fetchAll();

  return db_query("SELECT posts.*, users.name, users.login, users.avatar FROM `posts` JOIN `users` ON users.id = posts.user_id ORDER BY `posts`.`date` $sorting;")->fetchAll();
}

function get_user_info($login) {
  return db_query("SELECT * FROM `users` WHERE `login` = '$login';")->fetch();
}

function add_user($login, $password) {
  $login = trim($login);
  $name = ucfirst($login);
  $password_hashed = password_hash($password, PASSWORD_DEFAULT);

  return db_query("INSERT INTO `users` (`login`, `pass`, `name`) VALUES ('$login', '$password_hashed', '$name');", true);
}

function register_user($auth_data) {
  if (
    empty($auth_data)
    || !isset($auth_data['login'])
    || empty(trim($auth_data['login']))
    || !isset($auth_data['password'])
    || empty(trim($auth_data['password']))
    || !isset($auth_data['pass2'])
    || empty(trim($auth_data['pass2']))
  ) return false;

  $user = get_user_info($auth_data['login']);

  if (!empty($user)) {
    $_SESSION['error'] = 'Пользователь ' . $auth_data['login'] . ' уже существует';
    redirect('register.php');
  }

  if ($auth_data['password'] !== $auth_data['pass2']) {
    $_SESSION['error'] = 'Пароли не совпадают';
    redirect('register.php');
  }

  if (add_user($auth_data['login'], $auth_data['password'])) {
    redirect('index.php');
  }
}

function login($auth_data) {
  if (
    empty($auth_data)
    || !isset($auth_data['login'])
    || empty($auth_data['login'])
    || !isset($auth_data['password'])
    || empty($auth_data['password'])
  ) return false;

  $user = get_user_info($auth_data['login']);

  if (empty($user)) {
    $_SESSION['error'] = 'Пользователь не найден';
    redirect();
  }

  if (password_verify($auth_data['password'], $user['pass'])) {
    $_SESSION['user'] = $user;
    $_SESSION['error'] = '';
    redirect('user_posts.php?id=' . $user['id']);
  } else {
    $_SESSION['error'] = 'Пароль неверный';
    redirect();
  }
}

function get_error_message() {
  $error = '';

  if (isset($_SESSION['error']) && !empty($_SESSION['error'])) {
    $error = $_SESSION['error'];
    $_SESSION['error'] = '';
  }

  return $error;
}

function redirect($page = '') {
  header("Location: " . get_url($page));
  die;
}

function logged_in() {
  return isset($_SESSION['user']['id']) && !empty($_SESSION['user']['id']);
}

function add_post($text, $image) {
  $text = trim($text);
  $text = preg_replace('/\s+/', ' ', $text);

  if (mb_strlen($text) > 255) {
    $text = mb_substr($text, 0, 250) . '...';
  }

  if (count(explode(' ', $text)) > 50) {
    $text = implode(' ', array_slice(explode(' ', $text), 0, 50)) . '...';
  }

  $user_id = $_SESSION['user']['id'];
  $sql = "INSERT INTO `posts` (`user_id`, `text`, `image`) VALUES ('$user_id', '$text', '$image');";

  return db_query($sql, true);
}

function delete_post($id) {
  if ($id < 0 || !intval($id)) return false;

  $user_id = $_SESSION['user']['id'];
  $sql = "DELETE FROM `posts` WHERE `id` = $id AND `user_id` = $user_id;";

  return db_query($sql, true);
}

function get_likes_count($post_id) {
  if (empty($post_id)) return 0;

  return db_query("SELECT COUNT(*) FROM `likes` WHERE `post_id` = $post_id;")->fetchColumn();
}

function is_post_liked($post_id) {
  $user_id = $_SESSION['user']['id'];

  if (empty($post_id)) return false;

  return db_query("SELECT * FROM `likes` WHERE `post_id` = $post_id AND `user_id` = $user_id;")->rowCount() > 0;
}

function add_like($post_id) {
  $user_id = $_SESSION['user']['id'];

  if (empty($post_id)) return false;

  $sql = "INSERT INTO `likes` (`user_id`, `post_id`) VALUES ('$user_id', '$post_id');";
  return db_query($sql, true);
}

function delete_like($post_id) {
  if ($post_id < 0 || !intval($post_id)) return false;
  if (empty($post_id)) return false;

  $user_id = $_SESSION['user']['id'];
  $sql = "DELETE FROM `likes` WHERE `post_id` = $post_id AND `user_id` = $user_id;";

  return db_query($sql, true);
}

function get_liked_posts() {
  $user_id = $_SESSION['user']['id'];

  $sql = "SELECT posts.*, users.name, users.login, users.avatar FROM `likes` JOIN `posts` ON posts.id = likes.post_id JOIN `users` ON users.id = posts.user_id WHERE likes.user_id = $user_id;";

  return db_query($sql)->fetchAll();
}
