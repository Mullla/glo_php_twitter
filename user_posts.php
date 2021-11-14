<?php
include_once 'includes/functions.php';

$error = get_error_message();

if (isset($_GET['id']) && !empty($_GET['id'])) {
  $id = $_GET['id'];
} else if (logged_in()) {
  $id = $_SESSION['user']['id'];
} else if(empty($_GET['id'])) {
  redirect();
} else {
  $id = 0;
}

$posts = get_posts($id);
$title = 'Твиты' . (!empty($id) && !empty($posts) ? ' пользователя @' . $posts[0]['login'] : '');

include_once 'includes/header.php';

if (logged_in()) include_once 'includes/tweet_form.php';

include_once 'includes/posts.php';
include_once 'includes/footer.php';
