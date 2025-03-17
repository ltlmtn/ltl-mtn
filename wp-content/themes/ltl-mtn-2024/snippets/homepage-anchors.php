<nav class="anchor-menu">
    <div class="menu-toggle">
        <div class="menu-open"><ion-icon name="menu"></ion-icon></div>
        <div class="menu-close"><ion-icon name="close"></ion-icon></div>
    </div>
    <?php wp_nav_menu( array( 
        'theme_location' => 'homepage_anchors',
        'container' => 'nav',
        'container_class' => 'main-navigation',
        'menu_class' => 'nav-menu',
    ) ); ?>
</nav>