<section class="homepage-contact content-width page-content">
    <?php
    // WP_Query arguments
    $args = array(
        'post_type' => 'page',
        'pagename'  => 'contact'
    );

    // The Query
    $contact_query = new WP_Query($args);

    // The Loop
    if ($contact_query->have_posts()) {
        while ($contact_query->have_posts()) {
            $contact_query->the_post();
            // Display content of the "contact" page
            the_content();
        }
    } else {
        // No posts found
        echo '<p>No contact page found.</p>';
    }

    // Restore original Post Data
    wp_reset_postdata();
    ?>
</section>
