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

