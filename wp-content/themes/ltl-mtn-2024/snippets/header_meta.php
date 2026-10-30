<meta http-equiv="Content-Type" content="text/html, charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

<?php
  $seo_title = get_post_meta( get_the_ID(), 'seo_options_title', true );
  $seo_description = get_post_meta( get_the_ID(), 'seo_options_description', true );
  $home_title = get_theme_mod('seo_title');
  $home_description = get_theme_mod('seo_description');
?>

<?php
if ( is_front_page() or ( is_home() ) ) {
  if( $home_title ) {
    echo '<meta property="og:title" content="' . $home_title . '" />';
    echo '<title>' . $home_title . ' | ' . get_bloginfo('description') . '</title>';
  } else {
    echo '<meta property="og:title" content="' . get_bloginfo('name') . ': ' . get_bloginfo('description') . '" />';
    echo '<title>' . get_bloginfo('name') . ' &bull; ' . get_bloginfo('description') . '</title>';
  }
} elseif ( is_category() ) {
    echo '<meta property="og:title" content="' . get_bloginfo('name') . ': ' . single_cat_title( '', false ) . '" />';
    echo '<title>' . get_bloginfo('name') . ' &bull; ' . single_cat_title( '', false ) . '</title>';
} elseif( is_tax('gallery_type') or is_archive('custom_gallery') ) {
  echo '<meta property="og:title" content="' . get_bloginfo('name') . ': Gallery" />';
  echo '<title>' . get_bloginfo('name') . ' &bull; Gallery</title>';
} else {
  if( $seo_title ) {
    echo '<meta property="og:title" content="' . get_bloginfo('name') . ': ' . $seo_title . '" />';
    echo '<title>' . get_bloginfo('name') . ' &bull; ' . $seo_title . '</title>';
  } else {
    echo '<meta property="og:title" content="' . get_bloginfo('name') . ': ' . get_the_title() . '" />';
    echo '<title>' . get_bloginfo('name') . ' &bull; ' . get_the_title() . '</title>';
  }
}
?>

<meta property="og:type" content="website"/>
<meta property="og:url" content="<?php echo 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ?>"/>

<?php
  if( $post ) {
    $featured_image = get_the_post_thumbnail_url( $post->ID, 'full' );
    $default_image = get_theme_mod('default_image');
    if ( $featured_image) {
      $seo_image = $featured_image;
    } else {
      $seo_image = $default_image;
    }
    echo '<meta property="og:image" content="'.$seo_image.'"/>';
  }
?>

<meta property="og:site_name" content="<?php bloginfo('name'); ?>"/>

<?php
  if( $post ) {
    if( is_front_page() or is_home() ) {
      if( $home_description ) {
        echo '<meta name="description" content="' . $home_description . '">';
        echo '<meta property="og:description" content="' . $home_description . '" />';
      } else {
        echo '<meta name="description" content="' . get_bloginfo('description') . '">';
        echo '<meta property="og:description" content="' .  get_bloginfo('description') . '" />';
      }
    } else {
      $summary = get_the_excerpt( $post->ID );
      if( $seo_description ) {
        echo '<meta name="description" content="' . $seo_description . '">';
        echo '<meta property="og:description" content="' .  $seo_description . '" />';
      }
      elseif ( $summary !='' ) {
        echo '<meta name="description" content="' . $summary . '">';
        echo '<meta property="og:description" content="' .  $summary . '" />';
      } else {
        echo '<meta name="description" content="' . get_bloginfo('description') . '">';
        echo '<meta property="og:description" content="' .  get_bloginfo('description') . '" />';
      }
    }
  }
?>