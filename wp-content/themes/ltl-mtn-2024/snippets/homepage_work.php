<section class="homepage-portfolio content-width">
<?php
// WP_Query arguments
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
    echo '<p>No portfolio items found.</p>';
}

// Restore original Post Data
wp_reset_postdata();
?>

<div class="portfolio-more">
    <a href="/portfolio#archive" class="button black">More Work &rarr;</a>
</div>

</section>