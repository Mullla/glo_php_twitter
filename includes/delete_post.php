<?php

include_once 'functions.php';

if (!logged_in()) redirect();

if ( isset($_GET['id']) && !empty($_GET['id']) ) {
  if( !delete_post($_GET['id']) ) {
    $_SESSION['error'] = 'Во время удаления поста что-то пошло не так';
  }
}

redirect('user_posts.php');
