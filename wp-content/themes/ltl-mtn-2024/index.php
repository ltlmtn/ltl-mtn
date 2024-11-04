<?php get_header(); ?>

<!-- Index Template -->
    
<?php 
    if( is_home() ) {

        get_template_part('snippets/homepage');

    } elseif( is_post_type_archive('portfolio') ) {

        get_template_part('snippets/archive_portfolio');

    } else {

        get_template_part('snippets/page');

    }
?>

<?php get_footer(); ?>