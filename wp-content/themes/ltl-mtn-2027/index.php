<?php get_header(); ?>

<!-- Index Template -->
    
<?php
    $theme = get_stylesheet_directory_uri();
    $home_url = get_home_url();
    $is_testimonials = is_page( 'testimonials' ) || is_post_type_archive( 'testimonial' );
    $is_portfolio_archive = is_page( 'work' ) || is_post_type_archive( 'portfolio' );
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
        <?php elseif ( $is_portfolio_archive ) : ?>
            <?php
            $portfolio_page = get_page_by_path( 'work', OBJECT, 'page' );
            if ( $portfolio_page ) {
                echo apply_filters( 'the_content', $portfolio_page->post_content );
            } elseif ( is_page( 'work' ) ) {
                the_content();
            }
            get_template_part( 'customizations/portfolio-archive' );
            ?>
        <?php else : ?>
            <?php if ( is_singular('portfolio') ) : ?>
                <h1><a href="/portfolio/">Our Work:</a> <?php the_title(); ?></h1>
            <?php endif; ?>
            <?php the_content(); ?>
            <?php if( is_singular('portfolio') ) : ?>
                <?php
                    $quote = get_post_meta($post->ID, '_portfolio_quote', true);
                    $attribution = get_post_meta($post->ID, '_portfolio_attribution', true);
                    $link = get_post_meta($post->ID, '_portfolio_link', true);
                    $link_label = get_post_meta($post->ID, '_portfolio_link_label', true) ?? 'View Project';
                 ?>
                 <?php if ( $quote ) : ?>
                     <blockquote>
                         <?php echo esc_html( $quote ); ?>
                         <?php if ( $attribution ) : ?>
                             <cite><?php echo esc_html( $attribution ); ?></cite>
                         <?php endif; ?>
                     </blockquote>
                 <?php endif; ?>
                 <?php if ( $link && $link_label ) : ?>
                     <p><a class="wp-block-button__link" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $link_label ); ?></a></p>
                 <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>