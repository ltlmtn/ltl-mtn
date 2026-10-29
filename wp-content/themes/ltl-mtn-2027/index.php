<?php get_header(); ?>

<!-- Index Template -->
    
<?php
    $theme = get_stylesheet_directory_uri();
    $home_url = get_home_url();
?>

<main>
    <div class="page-content content-width">

        <?php the_content(); ?>

        <?php
            if( is_singular('portfolio') ) {
                $link = get_post_meta($post->ID, '_portfolio_link', true);
                $link_label = get_post_meta($post->ID, '_portfolio_link_label', true);
                if( $link ) {
                    echo '<div class="portfolio-item-link">';
                        echo '<a href="' . $link . '" class="button" target="_blank">' . $link_label . ' <ion-icon name="open-outline"></ion-icon></a>';
                    echo '</div>';
                }
            }
        ?>
    </div>
</main>

<?php get_footer(); ?>