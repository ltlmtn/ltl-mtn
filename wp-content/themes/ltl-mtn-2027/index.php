<?php get_header(); ?>

<!-- Index Template -->
    
<?php
    $theme = get_stylesheet_directory_uri();
    $home_url = get_home_url();
    $is_testimonials = is_page( 'testimonials' ) || is_post_type_archive( 'testimonial' );
?>

<main>
    <div class="page-content content-width">

        <?php if ( is_front_page() && is_active_sidebar( 'homepage_blurb' ) ) : ?>
            <div class="homepage-blurb">
                <?php dynamic_sidebar( 'homepage_blurb' ); ?>
            </div>
        <?php endif; ?>

        <?php if ( $is_testimonials ) : ?>
            <?php
            $testimonials_page = get_page_by_path( 'testimonials', OBJECT, 'page' );
            if ( $testimonials_page ) {
                echo apply_filters( 'the_content', $testimonials_page->post_content );
            } elseif ( is_page( 'testimonials' ) ) {
                the_content();
            }
            get_template_part( 'customizations/testimonial-archive' );
            ?>
        <?php else : ?>
            <?php the_content(); ?>
        <?php endif; ?>

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