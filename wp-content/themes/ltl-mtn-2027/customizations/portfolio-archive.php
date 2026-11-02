<?php
$args = array(
    'post_type'      => 'portfolio',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'post_status'    => 'publish',
);

$portfolio_query = new WP_Query( $args );

if ( $portfolio_query->have_posts() ) :
    echo '<div class="portfolio-archive">';
    while ( $portfolio_query->have_posts() ) : $portfolio_query->the_post();
        $thumbnail = get_the_post_thumbnail_url( get_the_ID(), 'large' );
        $permalink = get_permalink();
        echo '<div class="portfolio-item">';
            echo '<a href="' . $permalink . '">';
                echo '<img src="' . $thumbnail . '" alt="' . get_the_title() . '">';
                echo '<h3>' . get_the_title() . '</h3>';
            echo '</a>';
        echo '</div>';
    endwhile;
    echo '</div>';
    wp_reset_postdata();
endif;