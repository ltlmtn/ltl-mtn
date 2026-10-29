<?php

/**
 * @package WordPress
 * @subpackage asw_template
 */

// Enqueue Scripts and Styles
function asw_enqueue_scripts() {

	$styleSheetHash = rand(0, 99999999999);
	$theme = get_stylesheet_directory_uri();

    // Styles
    wp_enqueue_style('mytheme-style', get_stylesheet_uri( ), array(), $styleSheetHash);

	// JQuery
	wp_enqueue_script('jquery');

}
add_action('wp_enqueue_scripts', 'asw_enqueue_scripts');

// Thumbnails
add_theme_support('post-thumbnails');

//menus
add_action( 'init', 'register_my_menus' );
function register_my_menus() {
	register_nav_menus(
		array(
			'main_navigation' => __( 'Main Navigation' )
		)
	);
}

function asw_widgets_init() {

	register_sidebar( array(
		'name'          => 'Footer Content',
		'id'            => 'footer_content',
		'before_widget' => '',
		'after_widget'  => '',
		'before_title'  => '',
		'after_title'   => ''
	) );

}
add_action( 'widgets_init', 'asw_widgets_init' );

function mytheme_setup() {
    // Add support for Block Styles
	add_theme_support( 'wp-block-styles' );
	// Add Color Palettes
	add_theme_support( 'editor-color-palette', array(
		array(
			'name' => __( 'White' ),
			'slug' => 'white',
			'color' => '#FFF',
		),
		array(
			'name' => __( 'Black' ),
			'slug' => 'black',
			'color' => '#000',
		),
		array(
			'name' => __( 'Mustard' ),
			'slug' => 'mustard',
			'color' => '#DAB108',
		),
		array(
			'name' => __( 'Mustard' ),
			'slug' => 'mustard',
			'color' => '#DAB108',
		),
		array(
			'name' => __( 'Goldenrod' ),
			'slug' => 'goldenrod',
			'color' => '#EDD883',
		),
		array(
			'name' => __( 'Silver' ),
			'slug' => 'silver',
			'color' => '#EDEDED',
		),
	) );
	add_theme_support( 'disable-custom-colors' );
	add_theme_support('disable-custom-font-sizes');
	add_theme_support( 'responsive-embeds' );
}
add_action( 'after_setup_theme', 'mytheme_setup' );

add_post_type_support( 'page', 'excerpt' );


// Disable support for comments
function disable_comments() {
	// Remove support for comments and trackbacks from post types
	remove_post_type_support( 'post', 'comments' );
	remove_post_type_support( 'page', 'comments' );
	// Close comments on the front-end
	add_filter( 'comments_open', '__return_false' );
	// Hide existing comments
	add_filter( 'comments_array', '__return_empty_array' );
	// Remove comments form from the front-end
	add_action( 'wp_head', 'remove_comment_support', 1 );
  }
  add_action( 'admin_init', 'disable_comments' );
  
  function remove_comment_support() {
	// Remove comments from the post and page editor screens
	remove_meta_box( 'commentsdiv', 'post', 'normal' );
	remove_meta_box( 'commentstatusdiv', 'post', 'normal' );
	remove_meta_box( 'commentsdiv', 'page', 'normal' );
	remove_meta_box( 'commentstatusdiv', 'page', 'normal' );
  }


function custom_admin_stylesheet() {
	wp_enqueue_style( 'custom-admin-style', get_template_directory_uri() . '/admin.css' );
}
add_action( 'admin_enqueue_scripts', 'custom_admin_stylesheet' );

// Redirect 404 errors to homepage
function redirect_404_to_homepage() {
	if (is_404()) {
		wp_redirect(home_url());
		exit();
	}
}
add_action('template_redirect', 'redirect_404_to_homepage');


// Include Extras
get_template_part('customizations/page-seo');
get_template_part('customizations/customizations');
get_template_part('customizations/portfolio-post-type');

/**
 * Register Parallax Hero Custom Block
 */
function ltl_mtn_register_parallax_hero_block() {
	register_block_type( get_template_directory() . '/blocks/parallax-hero' );
}
add_action( 'init', 'ltl_mtn_register_parallax_hero_block' );

/**
 * Pass reference sample asset URLs to block editor
 */
function ltl_mtn_parallax_hero_editor_data() {
	wp_localize_script(
		'ltl-mtn-parallax-hero-editor-script',
		'ltlMtnParallaxHeroData',
		array(
			'referenceImage'    => get_template_directory_uri() . '/blocks/parallax-hero/reference/sample-hero.jpg',
			'referenceDepthMap' => get_template_directory_uri() . '/blocks/parallax-hero/reference/sample-hero.webp',
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'ltl_mtn_parallax_hero_editor_data' );
