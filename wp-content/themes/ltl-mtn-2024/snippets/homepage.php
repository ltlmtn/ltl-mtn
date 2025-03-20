<?php
    $theme = get_stylesheet_directory_uri();
    $home_url = get_home_url();
    $slogan = get_bloginfo('description');
    $video_file_id = get_theme_mod('video_file');
    if( $video_file_id ) {
        $video_file = wp_get_attachment_url($video_file_id);
    }
    $video_poster = get_theme_mod('video_poster');
    $video_highlight_color = get_theme_mod('video_highlight_color');
    if( $video_highlight_color ) {
        $video_section_style = 'style="background-color: '.$video_highlight_color.';"';
    } else {
        $video_section_style = 'style="background-color: var(--mustard);"';
    }
    $title_section_1 = get_theme_mod('title_section_1', 'This is human business.');
    $title_section_2 = get_theme_mod('title_section_2', 'We are human experts.');
    $title_section_3 = get_theme_mod('title_section_3', 'Let\'s make something happen.');
?>

<section class="homepage-section heading-section intro" id="top">
    <?php if( is_active_sidebar('intro') ) : ?>
        <div class="main">
            <div class="intro-container">
                <?php dynamic_sidebar('intro'); ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<div class="homepage-video">
    <?php if ($video_file) : ?>
        <video autoplay muted loop playsinline>
            <source src="<?= $video_file; ?>" type="video/mp4">
        </video>
    <?php else : ?>
        <img src="<?php echo esc_url($video_poster); ?>" alt="Video Poster">
    <?php endif; ?>
</div>

<section class="homepage-section work" <?= $video_section_style; ?>>
    <div class="homepage-logo">
        <img src="<?= $theme; ?>/assets/images/logo-on-white.svg" alt="Logo" />
    </div>
    <div class="section-heading" id="work">
        <div class="heading-areas">
            <div class="section-title">
                <span><h1><?php echo $title_section_1; ?></h1></span>
            </div>
            <?php get_template_part('snippets/homepage-anchors'); ?>
        </div>
    </div>
    <?php get_template_part('snippets/homepage_work'); ?>
</section>

<section class="homepage-section team">
    <div class="section-heading scroll2me" id="team">
        <div class="heading-areas">
            <div class="section-title">
                <h2><?php echo $title_section_2; ?></h2>
            </div>
            <?php get_template_part('snippets/homepage-anchors'); ?>
        </div>
    </div>
    <?php get_template_part('snippets/homepage_team'); ?>
</section>

<section class="homepage-section contact">
    <div class="section-heading scroll2me" id="contact">
        <div class="heading-areas">
            <div class="section-title">
                <h2><?php echo $title_section_3; ?></h2>
            </div>
            <?php get_template_part('snippets/homepage-anchors'); ?>
        </div>
    </div>
    <?php get_template_part('snippets/homepage_contact'); ?>
</section>