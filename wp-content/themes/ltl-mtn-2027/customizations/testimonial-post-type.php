<?php

function create_testimonial_taxonomy() {
    $labels = array(
        'name'              => __( 'Testimonial Categories' ),
        'singular_name'     => __( 'Testimonial Category' ),
        'search_items'      => __( 'Search Testimonial Categories' ),
        'all_items'         => __( 'All Testimonial Categories' ),
        'parent_item'       => __( 'Parent Testimonial Category' ),
        'parent_item_colon' => __( 'Parent Testimonial Category:' ),
        'edit_item'         => __( 'Edit Testimonial Category' ),
        'update_item'       => __( 'Update Testimonial Category' ),
        'add_new_item'      => __( 'Add New Testimonial Category' ),
        'new_item_name'     => __( 'New Testimonial Category Name' ),
        'menu_name'         => __( 'Categories' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'testimonial-category' ),
        'show_in_rest'      => false,
    );

    register_taxonomy( 'testimonial_category', array( 'testimonial' ), $args );
}
add_action( 'init', 'create_testimonial_taxonomy', 0 );

function create_testimonial_post_type() {
    $labels = array(
        'name'               => __( 'Testimonials' ),
        'singular_name'      => __( 'Testimonial' ),
        'add_new'            => __( 'Add New' ),
        'add_new_item'       => __( 'Add New Testimonial' ),
        'edit_item'          => __( 'Edit Testimonial' ),
        'new_item'           => __( 'New Testimonial' ),
        'view_item'          => __( 'View Testimonial' ),
        'search_items'       => __( 'Search Testimonials' ),
        'not_found'          => __( 'No testimonials found' ),
        'not_found_in_trash' => __( 'No testimonials found in Trash' ),
        'parent_item_colon'  => '',
        'menu_name'          => __( 'Testimonials' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'testimonial' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor', 'excerpt', 'page-attributes' ),
        'menu_icon'          => 'dashicons-format-quote',
        'show_in_rest'       => false,
        'taxonomies'         => array( 'testimonial_category' ),
    );

    register_post_type( 'testimonial', $args );
}
add_action( 'init', 'create_testimonial_post_type' );

function add_testimonial_metaboxes() {
    add_meta_box(
        'testimonial_options',
        __( 'Testimonial Options' ),
        'testimonial_options_callback',
        'testimonial',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'add_testimonial_metaboxes' );

function testimonial_options_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'testimonial_nonce' );
    $attribution = get_post_meta( $post->ID, 'attribution', true );
    ?>
    <p>
        <label for="attribution"><strong><?php _e( 'Attribution:' ); ?></strong></label>
        <input type="text" name="attribution" id="attribution" value="<?php echo esc_attr( $attribution ); ?>" style="width:100%; margin-top:5px;" />
    </p>
    <?php
}

function save_testimonial_metaboxes( $post_id ) {
    if ( ! isset( $_POST['testimonial_nonce'] ) || ! wp_verify_nonce( $_POST['testimonial_nonce'], basename( __FILE__ ) ) ) {
        return $post_id;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }

    if ( ! isset( $_POST['post_type'] ) || 'testimonial' !== $_POST['post_type'] || ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }

    $attribution = isset( $_POST['attribution'] ) ? sanitize_text_field( $_POST['attribution'] ) : '';
    update_post_meta( $post_id, 'attribution', $attribution );
}
add_action( 'save_post', 'save_testimonial_metaboxes' );

/**
 * Testimonials Carousel Shortcode
 *
 * Usage: [testimonials_carousel category="carousel" autoplay="true" speed="5"]
 */
function testimonials_carousel_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'category' => 'carousel',
            'autoplay' => 'true',
            'speed'    => '5',
        ),
        $atts,
        'testimonials_carousel'
    );

    $category = sanitize_text_field( $atts['category'] );
    $autoplay = filter_var( $atts['autoplay'], FILTER_VALIDATE_BOOLEAN );
    $speed = floatval( $atts['speed'] );
    if ( $speed <= 0 ) {
        $speed = 5;
    }

    $args = array(
        'post_type'      => 'testimonial',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'post_status'    => 'publish',
    );

    if ( ! empty( $category ) ) {
        $term = get_term_by( 'slug', $category, 'testimonial_category' );
        if ( ! $term ) {
            $term = get_term_by( 'name', $category, 'testimonial_category' );
        }
        $term_slug = $term ? $term->slug : $category;

        $args['tax_query'] = array(
            array(
                'taxonomy' => 'testimonial_category',
                'field'    => 'slug',
                'terms'    => $term_slug,
            ),
        );
    }

    $query = new WP_Query( $args );

    if ( ! $query->have_posts() ) {
        return '';
    }

    static $carousel_instance = 0;
    $carousel_instance++;
    $carousel_id = 'testimonials-carousel-' . $carousel_instance;

    ob_start();
    ?>
    <div id="<?php echo esc_attr( $carousel_id ); ?>" 
         class="testimonials-carousel-wrapper" 
         data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>" 
         data-speed="<?php echo esc_attr( $speed ); ?>">
        
        <button type="button" class="carousel-arrow carousel-prev" aria-label="<?php esc_attr_e( 'Previous testimonials' ); ?>">
            <svg width="28" height="42" viewBox="0 0 28 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                <polygon points="28,0 0,21 28,42" fill="currentColor" />
            </svg>
        </button>

        <div class="testimonials-carousel-viewport">
            <div class="testimonials-carousel-track">
                <?php
                while ( $query->have_posts() ) :
                    $query->the_post();
                    $attribution = get_post_meta( get_the_ID(), 'attribution', true );
                    $excerpt = get_the_excerpt();
                ?>
                    <article class="carousel-slide testimonial-item">
                        <div class="testimonial-bubble">
                            <div class="testimonial-content">
                                <?php if ( ! empty( $excerpt ) ) : ?>
                                    <?php echo esc_html( $excerpt ); ?>
                                <?php else : ?>
                                    <?php the_content(); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ( ! empty( $attribution ) ) : ?>
                            <div class="testimonial-attribution">
                                <?php echo esc_html( $attribution ); ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endwhile; ?>
            </div>
        </div>

        <button type="button" class="carousel-arrow carousel-next" aria-label="<?php esc_attr_e( 'Next testimonials' ); ?>">
            <svg width="28" height="42" viewBox="0 0 28 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                <polygon points="0,0 28,21 0,42" fill="currentColor" />
            </svg>
        </button>
    </div>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode( 'testimonials_carousel', 'testimonials_carousel_shortcode' );


