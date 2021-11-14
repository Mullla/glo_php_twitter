<?php

include_once 'functions.php';

if (!logged_in()) redirect();

if ( isset($_POST['text']) && !empty(trim($_POST['text'])) && isset($_POST['image'])) {
  if(!add_post($_POST['text'], $_POST['image'])) {
    $_SESSION['error'] = 'Во время добавления поста что-то пошло не так';
  }
}

redirect('user_posts.php');
