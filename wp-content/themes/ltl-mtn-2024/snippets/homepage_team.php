<section class="homepage-team content-width page-content">
    <?php
    // WP_Query arguments
    $args = array(
        'post_type' => 'page',
        'pagename'  => 'team'
    );

    // The Query
    $team_query = new WP_Query($args);

    // The Loop
    if ($team_query->have_posts()) {
        while ($team_query->have_posts()) {
            $team_query->the_post();
            // Display content of the "team" page
            the_content();
        }
    } else {
        // No posts found
        echo '<p>No team page found.</p>';
    }

    // Restore original Post Data
    wp_reset_postdata();
    ?>
</section>
