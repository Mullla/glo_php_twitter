<?php
include_once 'includes/functions.php';

$posts = get_posts(0, true);
$title = 'Сначала старые твиты';

include_once 'includes/header.php';
include_once 'includes/posts.php';
include_once 'includes/footer.php';
