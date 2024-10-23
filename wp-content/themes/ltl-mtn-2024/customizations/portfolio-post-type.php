<?php
function create_portfolio_post_type() {
    $labels = array(
        'name' => __('Portfolio Items'),
        'singular_name' => __('Portfolio Items'),
        'add_new' => __('Add New'),
        'add_new_item' => __('Add New Portfolio Item'),
        'edit_item' => __('Edit Portfolio Item'),
        'new_item' => __('New Portfolio Item'),
        'view_item' => __('View Portfolio Item'),
        'search_items' => __('Search Portfolios Items'),
        'not_found' => __('No portfolio Items found'),
        'not_found_in_trash' => __('No portfolio items found in Trash'),
        'parent_item_colon' => '',
        'menu_name' => __('Portfolio Items')
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'portfolio'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'menu_icon' => 'dashicons-portfolio',
        'show_in_rest' => true
    );

    register_post_type('portfolio', $args);
}

add_action('init', 'create_portfolio_post_type');


function add_portfolio_metaboxes() {
    add_meta_box(
        'portfolio_options', // Metabox ID
        'Portfolio Item Options', // Title
        'portfolio_options_callback', // Callback function
        'portfolio', // Post type
        'normal', // Context
        'high' // Priority
    );
}

function portfolio_options_callback($post) {
    wp_nonce_field(basename(__FILE__), 'portfolio_nonce');
    $descriptive_title = get_post_meta($post->ID, '_portfolio_descriptive_title', true);
    $quote = get_post_meta($post->ID, '_portfolio_quote', true);
    $attribution = get_post_meta($post->ID, '_portfolio_attribution', true);
    $link = get_post_meta($post->ID, '_portfolio_link', true);
    $link_label = get_post_meta($post->ID, '_portfolio_link_label', true);
    ?>
    <p>
        <label for="portfolio_descriptive_title">Descriptive Title:</label>
        <input type="text" name="portfolio_descriptive_title" id="portfolio_descriptive_title" value="<?php echo esc_attr($descriptive_title); ?>" style="width:100%;" />
    </p>
    <p>
        <label for="portfolio_quote">Quote:</label>
        <textarea name="portfolio_quote" id="portfolio_quote" rows="4" style="width:100%;"><?php echo esc_textarea($quote); ?></textarea>
    </p>
    <p>
        <label for="portfolio_attribution">Attribution:</label>
        <input type="text" name="portfolio_attribution" id="portfolio_attribution" value="<?php echo esc_attr($attribution); ?>" style="width:100%;" />
    </p>
    <p>
        <label for="portfolio_link">Link:</label>
        <input type="text" name="portfolio_link" id="portfolio_link" value="<?php echo esc_url($link); ?>" style="width:100%;" />
    </p>
    <p>
        <label for="portfolio_link_label">Link Label:</label>
        <input type="text" name="portfolio_link_label" id="portfolio_link_label" value="<?php echo esc_attr($link_label); ?>" style="width:100%;" />
    </p>
    <?php
}

function save_portfolio_metaboxes($post_id) {
    if (!isset($_POST['portfolio_nonce']) || !wp_verify_nonce($_POST['portfolio_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    if ('portfolio' !== $_POST['post_type'] || !current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    $quote = isset($_POST['portfolio_quote']) ? sanitize_textarea_field($_POST['portfolio_quote']) : '';
    $attribution = isset($_POST['portfolio_attribution']) ? sanitize_text_field($_POST['portfolio_attribution']) : '';
    $descriptive_title = isset($_POST['portfolio_descriptive_title']) ? sanitize_text_field($_POST['portfolio_descriptive_title']) : '';
    $link = isset($_POST['portfolio_link']) ? esc_url($_POST['portfolio_link']) : '';
    $link_label = isset($_POST['portfolio_link_label']) ? sanitize_text_field($_POST['portfolio_link_label']) : '';

    update_post_meta($post_id, '_portfolio_quote', $quote);
    update_post_meta($post_id, '_portfolio_attribution', $attribution);
    update_post_meta($post_id, '_portfolio_descriptive_title', $descriptive_title);
    update_post_meta($post_id, '_portfolio_link', $link);
    update_post_meta($post_id, '_portfolio_link_label', $link_label);
}

add_action('add_meta_boxes', 'add_portfolio_metaboxes');
add_action('save_post', 'save_portfolio_metaboxes');