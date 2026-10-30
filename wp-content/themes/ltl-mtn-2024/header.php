<!DOCTYPE html>

<head>

<?php get_template_part('snippets/header_meta') ?>

<?php /* This should always be included just before the </head> tag. */ wp_head(); ?>
</head>

<?php
  if( is_user_logged_in() ) {
    $loggedin = 'logged-in';
  } else {
    $loggedin = 'not-logged-in';
  }
?>

<body id="body" class="ltlmtn <?php if(is_front_page()) { echo 'homepage '; } elseif(is_home()) { echo 'posts-index '; } else { echo get_post_type() . ' '; } ?> <?= $loggedin; ?>">


<!-- END OF HEADER.PHP -->
