<?php get_header(); ?>

<!-- Index Template -->
    
<?php if( is_home() ) {
    get_template_part('snippets/homepage');
} ?>

<?php get_footer(); ?>