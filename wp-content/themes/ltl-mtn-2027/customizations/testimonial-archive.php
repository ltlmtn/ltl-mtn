<?php
/**
 * Testimonial Archive Loop
 *
 * Displays all testimonials in menu order in a grid.
 */

$args = array(
    'post_type'      => 'testimonial',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'post_status'    => 'publish',
);

$testimonials_query = new WP_Query( $args );

if ( $testimonials_query->have_posts() ) :
?>
    <section class="testimonial-grid">
        <?php
        while ( $testimonials_query->have_posts() ) :
            $testimonials_query->the_post();
            $attribution = get_post_meta( get_the_ID(), 'attribution', true );
        ?>
            <article id="testimonial-<?php the_ID(); ?>" <?php post_class( 'testimonial-item' ); ?>>
                <div class="testimonial-bubble">
                    <div class="testimonial-content">
                        <?php the_content(); ?>
                    </div>
                </div>
                <?php if ( ! empty( $attribution ) ) : ?>
                    <div class="testimonial-attribution">
                        <?php echo esc_html( $attribution ); ?>
                    </div>
                <?php endif; ?>
            </article>
        <?php endwhile; ?>
    </section>
    <?php wp_reset_postdata(); ?>
<?php else : ?>
    <p><?php _e( 'No testimonials found.' ); ?></p>
<?php endif; ?>

