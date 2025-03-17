<div class="anchor-menu-container">
    <input type="checkbox" id="anchor-menu-toggle" class="anchor-menu-toggle-checkbox">
    <label class="anchor-menu-toggle" for="anchor-menu-toggle">
        <div class="anchor-menu-open"><ion-icon name="menu"></ion-icon></div>
        <div class="anchor-menu-close"><ion-icon name="close"></ion-icon></div>
    </label>
    <nav class="anchor-menu">
        <?php wp_nav_menu( array( 
            'theme_location' => 'homepage_anchors',
            'container' => 'nav',
            'container_class' => 'main-navigation',
            'menu_class' => 'nav-menu',
        ) ); ?>
    </nav>
</div>