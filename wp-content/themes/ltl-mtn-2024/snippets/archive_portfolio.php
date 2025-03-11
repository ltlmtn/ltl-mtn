<?php
    $theme = get_stylesheet_directory_uri();
    $home_url = get_home_url();
    $slogan = get_bloginfo('description');
?>
<header class="page-header work">
    <div class="header-area-1 content-width">
        <a class="logo" href="<?php echo $home_url; ?>">
            <img src="<?php echo $theme; ?>/assets/images/logo-on-black.svg" alt="LTL Mountain Logo">
        </a>
        <div class="slogan">
            <span><?php echo $slogan; ?></span>
        </div>
    </div>
    <div class="header-area-2 content-width">
        <h1>Our Work</h1>
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
    <section class="homepage-portfolio archive-portfolio content-width">
    <?php
        $args = array(
            'post_type'      => 'portfolio',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            'meta_query'     => array(
                array(
                    'key'     => '_portfolio_feature_on_homepage',
                    'value'   => '1',
                    'compare' => '='
                )
        )
    );

    // The Query
    $query = new WP_Query($args);

    // The Loop
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $featured_image = get_the_post_thumbnail($post->ID, 'large');
            $feature_on_homepage = get_post_meta($post->ID, '_portfolio_feature_on_homepage', true);
            $descriptive_title = get_post_meta($post->ID, '_portfolio_descriptive_title', true);
            $quote = get_post_meta($post->ID, '_portfolio_quote', true);
            $attribution = get_post_meta($post->ID, '_portfolio_attribution', true);
            $link = get_post_meta($post->ID, '_portfolio_link', true);
            $link_label = get_post_meta($post->ID, '_portfolio_link_label', true);
            $summary = get_post_meta($post->ID, '_portfolio_summary', true);

            echo '<div class="portfolio-item">';
                if( $quote ) {
                    echo '<div class="quote">';
                        echo '&quot;' . $quote . '&quot;';
                        echo '<div class="attribution">';
                            echo $attribution;
                        echo '</div>';
                    echo '</div>';
                }
                echo '<div class="portfolio-image">';
                    echo $featured_image;
                echo '</div>';
                echo '<div class="portfolio-content">';
                    echo '<h2>' . $descriptive_title . '</h2>';
                    echo '<p>' . $summary . '</p>';
                    if( $link ) {
                        echo '<a href="' . $link . '" class="button" target="_blank">' . $link_label . '</a>';
                    }
                echo '</div>';
            echo '</div>';
        }
    } else {
        // No posts found
    }

    // Restore original Post Data
    wp_reset_postdata();
    ?>
    </section>
    <section class="portfolio-grid">
        <?php
            $args = array(
                'post_type'      => 'portfolio',
                'posts_per_page' => -1,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
                'meta_query'     => array(
                    array(
                        'key'     => '_portfolio_feature_on_homepage',
                        'value'   => '0',
                        'compare' => '='
                    )
                )
                    );

        $query = new WP_Query($args);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $post_id = get_the_id();
                $permalink = get_the_permalink($post_id);
                $featured_image = get_the_post_thumbnail_url($post_id, 'medium');
                $title = get_the_title($post_id);
                echo '<div class="portfolio-grid-item">';
                    echo '<a href="' . $permalink . '">';
                        echo '<div class="portfolio-grid-image">';
                            echo '<img src="'.$featured_image.'" />';
                        echo '</div>';
                        echo '<h2>'.$title.'</h2>';
                    echo '</a>';
                echo '</div>';
            }
        } else {
            // No posts found
        }
        wp_reset_postdata();
    ?>
    </section>
</main>