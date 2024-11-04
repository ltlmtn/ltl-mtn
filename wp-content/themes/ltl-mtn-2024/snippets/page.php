<?php
    $theme = get_stylesheet_directory_uri();
    $home_url = get_home_url();
    $slogan = get_bloginfo('description');
?>
<header class="page-header">
    <div class="header-area-1 content-width">
        <a class="logo" href="<?php echo $home_url; ?>">
            <img src="<?php echo $theme; ?>/assets/images/logo-on-black.svg" alt="LTL Mountain Logo">
        </a>
        <div class="slogan">
            <span><?php echo $slogan; ?></span>
        </div>
    </div>
    <div class="header-area-2 content-width">
        <h1><?php the_title(); ?></h1>
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
</header>
<main>
    <div class="page-content content-width">
        <?php the_content(); ?>
    </div>
</main>