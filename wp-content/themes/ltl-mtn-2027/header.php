<!DOCTYPE html>

<head>

<?php get_template_part('snippets/header_meta') ?>
<?php
    $theme = get_stylesheet_directory_uri();
    $home_url = get_home_url();
?>

<?php /* This should always be included just before the </head> tag. */ wp_head(); ?>
</head>

<?php
  if( is_user_logged_in() ) {
    $loggedin = 'logged-in';
  } else {
    $loggedin = 'not-logged-in';
  }
  $slogan = get_theme_mod('slogan');
  if (!$slogan) {
      $slogan = 'This is human business.';
  }
?>

<body id="body" <?php body_class( 'ltlmtn ' . $loggedin ); ?>>

<header class="page-header">
  <div class="header-areas content-width">
    <div class="header-area-1">
        <a class="logo" href="<?php echo $home_url; ?>">
            <img src="<?php echo $theme; ?>/assets/images/logo-on-black.svg" alt="LTL Mountain Logo">
        </a>
        <div class="slogan">
            <span><?php echo $slogan; ?></span>
        </div>
    </div>
    <div class="header-area-2">
        <div class="menu-toggle">
            <div class="menu-open"><ion-icon name="menu"></ion-icon></div>
            <div class="menu-close"><ion-icon name="close"></ion-icon></div>
        </div>
    </div>
    <?php
        if (has_nav_menu('main_navigation')) {
            wp_nav_menu(array(
                'theme_location' => 'main_navigation',
                'container' => 'nav',
                'container_class' => 'main-navigation',
                'menu_class' => 'nav-menu',
            ));
        }
    ?>
  </div>
</header>


<!-- END OF HEADER.PHP -->
