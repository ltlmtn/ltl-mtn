<?php get_header(); ?>

<!-- Index Template -->
    
<?php 
    if( is_home() ) {
        get_template_part('snippets/homepage');
    } else {
        get_template_part('snippets/page');
    }
?>

<?php get_footer(); ?>