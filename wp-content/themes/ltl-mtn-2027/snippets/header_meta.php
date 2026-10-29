<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<?php
/**
 * Header Meta & Open Graph Tags
 *
 * Provides complete header meta, Open Graph, and Twitter Card tags
 * for all page types with robust fallbacks to prevent errors and null values.
 *
 * @package ltl-mtn-2027
 */

// Site identity
$site_name        = get_bloginfo( 'name' );
$site_description = get_bloginfo( 'description' );

if ( empty( $site_name ) ) {
    $site_name = 'LTL Mountain';
}

// Customizer SEO settings
$home_seo_title       = get_theme_mod( 'seo_title' );
$home_seo_description = get_theme_mod( 'seo_description' );
$default_image        = get_theme_mod( 'default_image' );

// Contextual variables
$page_title       = '';
$meta_title       = '';
$og_title         = '';
$meta_description = '';
$canonical_url    = '';
$og_type          = 'website';
$seo_image        = '';

// 1. Determine Contextual Meta Data
if ( is_front_page() || is_home() ) {
    $og_type       = 'website';
    $canonical_url = home_url( '/' );

    // Check if static front page has per-page SEO overrides
    $front_page_id  = get_queried_object_id();
    $page_seo_title = $front_page_id ? get_post_meta( $front_page_id, 'seo_options_title', true ) : '';
    $page_seo_desc  = $front_page_id ? get_post_meta( $front_page_id, 'seo_options_description', true ) : '';

    if ( ! empty( $page_seo_title ) ) {
        $og_title   = $page_seo_title;
        $meta_title = $page_seo_title . ( ! empty( $site_name ) ? ' • ' . $site_name : '' );
    } elseif ( ! empty( $home_seo_title ) ) {
        $og_title   = $home_seo_title;
        $meta_title = $home_seo_title . ( ! empty( $site_description ) ? ' • ' . $site_description : '' );
    } else {
        $og_title   = ! empty( $site_description ) ? $site_name . ': ' . $site_description : $site_name;
        $meta_title = ! empty( $site_description ) ? $site_name . ' • ' . $site_description : $site_name;
    }

    if ( ! empty( $page_seo_desc ) ) {
        $meta_description = $page_seo_desc;
    } elseif ( ! empty( $home_seo_description ) ) {
        $meta_description = $home_seo_description;
    } else {
        $meta_description = $site_description;
    }

    if ( $front_page_id && has_post_thumbnail( $front_page_id ) ) {
        $seo_image = get_the_post_thumbnail_url( $front_page_id, 'full' );
    }

} elseif ( is_singular() ) {
    $post_id   = get_queried_object_id();
    $post_type = get_post_type( $post_id );
    $og_type   = ( 'post' === $post_type ) ? 'article' : 'website';

    $seo_title = get_post_meta( $post_id, 'seo_options_title', true );
    $raw_title = ! empty( $seo_title ) ? $seo_title : get_the_title( $post_id );

    if ( empty( $raw_title ) ) {
        $raw_title = __( 'Untitled', 'ltl-mtn' );
    }

    $page_title = $raw_title;
    $og_title   = ! empty( $site_name ) ? $site_name . ': ' . $page_title : $page_title;
    $meta_title = ! empty( $site_name ) ? $site_name . ' • ' . $page_title : $page_title;

    // Description: Custom field -> Excerpt -> Content -> Site fallback
    $seo_desc = get_post_meta( $post_id, 'seo_options_description', true );
    if ( ! empty( $seo_desc ) ) {
        $meta_description = $seo_desc;
    } elseif ( has_excerpt( $post_id ) ) {
        $meta_description = get_the_excerpt( $post_id );
    } else {
        $post_obj = get_post( $post_id );
        if ( $post_obj && ! empty( $post_obj->post_content ) ) {
            $meta_description = wp_trim_words( wp_strip_all_tags( $post_obj->post_content ), 35, '...' );
        }
    }

    $canonical_url = wp_get_canonical_url( $post_id );
    if ( empty( $canonical_url ) ) {
        $canonical_url = get_permalink( $post_id );
    }

    if ( has_post_thumbnail( $post_id ) ) {
        $seo_image = get_the_post_thumbnail_url( $post_id, 'full' );
    }

} elseif ( is_post_type_archive() ) {
    $post_type = get_query_var( 'post_type' );
    if ( is_array( $post_type ) ) {
        $post_type = reset( $post_type );
    }

    if ( 'portfolio' === $post_type ) {
        $page_title = __( 'Our Work', 'ltl-mtn' );
    } else {
        $page_title = post_type_archive_title( '', false );
    }

    $og_title   = ! empty( $site_name ) ? $site_name . ': ' . $page_title : $page_title;
    $meta_title = ! empty( $site_name ) ? $site_name . ' • ' . $page_title : $page_title;

    $archive_desc     = get_the_archive_description();
    $meta_description = ! empty( $archive_desc ) ? $archive_desc : $site_description;

    $canonical_url = get_post_type_archive_link( $post_type );

} elseif ( is_category() || is_tag() || is_tax() ) {
    $term       = get_queried_object();
    $page_title = single_term_title( '', false );

    $og_title   = ! empty( $site_name ) ? $site_name . ': ' . $page_title : $page_title;
    $meta_title = ! empty( $site_name ) ? $site_name . ' • ' . $page_title : $page_title;

    $term_desc        = ( $term && ! empty( $term->description ) ) ? $term->description : '';
    $meta_description = ! empty( $term_desc ) ? $term_desc : $site_description;

    if ( $term && ( ! function_exists( 'is_wp_error' ) || ! is_wp_error( $term ) ) ) {
        $term_link = get_term_link( $term );
        if ( ! function_exists( 'is_wp_error' ) || ! is_wp_error( $term_link ) ) {
            $canonical_url = $term_link;
        }
    }

} elseif ( is_author() ) {
    $author_id  = get_queried_object_id();
    $page_title = get_the_author_meta( 'display_name', $author_id );

    $og_title   = ! empty( $site_name ) ? $site_name . ': ' . $page_title : $page_title;
    $meta_title = ! empty( $site_name ) ? $site_name . ' • ' . $page_title : $page_title;

    $author_desc      = get_the_author_meta( 'description', $author_id );
    $meta_description = ! empty( $author_desc ) ? $author_desc : $site_description;

    $canonical_url = get_author_posts_url( $author_id );

} elseif ( is_search() ) {
    $page_title = sprintf( __( 'Search Results for &ldquo;%s&rdquo;', 'ltl-mtn' ), get_search_query() );
    $og_title   = ! empty( $site_name ) ? $site_name . ': ' . $page_title : $page_title;
    $meta_title = ! empty( $site_name ) ? $site_name . ' • ' . $page_title : $page_title;

    $meta_description = sprintf( __( 'Search results for &ldquo;%s&rdquo; on %s', 'ltl-mtn' ), get_search_query(), $site_name );
    $canonical_url    = get_search_link();

} elseif ( is_404() ) {
    $page_title       = __( 'Page Not Found', 'ltl-mtn' );
    $og_title         = ! empty( $site_name ) ? $site_name . ': ' . $page_title : $page_title;
    $meta_title       = ! empty( $site_name ) ? $site_name . ' • ' . $page_title : $page_title;
    $meta_description = __( 'The page you were looking for could not be found.', 'ltl-mtn' );
    $canonical_url    = home_url( '/' );

} else {
    $page_title       = wp_strip_all_tags( get_the_archive_title() );
    $og_title         = ! empty( $site_name ) ? $site_name . ': ' . $page_title : $page_title;
    $meta_title       = ! empty( $site_name ) ? $site_name . ' • ' . $page_title : $page_title;
    $meta_description = $site_description;
}

// 2. Fallbacks & Sanitization

// Title fallbacks
if ( empty( $og_title ) ) {
    $og_title = $site_name;
}
if ( empty( $meta_title ) ) {
    $meta_title = $site_name;
}

$og_title   = wp_strip_all_tags( $og_title );
$meta_title = wp_strip_all_tags( $meta_title );

// Description fallbacks & cleaning
if ( empty( $meta_description ) ) {
    $meta_description = ! empty( $site_description ) ? $site_description : $site_name;
}
$meta_description = wp_strip_all_tags( $meta_description );
$meta_description = preg_replace( '/\s+/', ' ', trim( $meta_description ) );

// Canonical URL fallback
if ( empty( $canonical_url ) || ( function_exists( 'is_wp_error' ) && is_wp_error( $canonical_url ) ) ) {
    $request_uri   = ! empty( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
    $canonical_url = home_url( $request_uri );
}

// Social image fallback chain
if ( empty( $seo_image ) ) {
    if ( ! empty( $default_image ) ) {
        $seo_image = $default_image;
    } else {
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        if ( $custom_logo_id ) {
            $logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
            if ( $logo_url ) {
                $seo_image = $logo_url;
            }
        }
        if ( empty( $seo_image ) && file_exists( get_template_directory() . '/assets/images/favicon-2.png' ) ) {
            $seo_image = get_template_directory_uri() . '/assets/images/favicon-2.png';
        }
    }
}
?>

<?php if ( ! current_theme_supports( 'title-tag' ) ) : ?>
<title><?php echo esc_html( $meta_title ); ?></title>
<?php endif; ?>

<meta name="description" content="<?php echo esc_attr( $meta_description ); ?>">
<link rel="canonical" href="<?php echo esc_url( $canonical_url ); ?>">

<!-- Open Graph / Facebook -->
<meta property="og:site_name" content="<?php echo esc_attr( $site_name ); ?>">
<meta property="og:type" content="<?php echo esc_attr( $og_type ); ?>">
<meta property="og:title" content="<?php echo esc_attr( $og_title ); ?>">
<meta property="og:description" content="<?php echo esc_attr( $meta_description ); ?>">
<meta property="og:url" content="<?php echo esc_url( $canonical_url ); ?>">
<meta property="og:locale" content="<?php echo esc_attr( str_replace( '-', '_', get_locale() ) ); ?>">
<?php if ( ! empty( $seo_image ) ) : ?>
<meta property="og:image" content="<?php echo esc_url( $seo_image ); ?>">
<?php endif; ?>

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo esc_attr( $og_title ); ?>">
<meta name="twitter:description" content="<?php echo esc_attr( $meta_description ); ?>">
<?php if ( ! empty( $seo_image ) ) : ?>
<meta name="twitter:image" content="<?php echo esc_url( $seo_image ); ?>">
<?php endif; ?>