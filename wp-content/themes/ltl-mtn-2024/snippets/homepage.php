<?php
    $theme = get_stylesheet_directory_uri();
    $home_url = get_home_url();
    $slogan = get_bloginfo('description');
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

<section class="homepage-section heading-section work">
    <div class="section-heading" id="work">
        <div class="heading-areas">
            <a class="logo" href="<?php echo $home_url; ?>">
                <img src="<?php echo $theme; ?>/assets/images/logo-on-white.svg" alt="LTL Mountain Logo">
            </a>
            <div class="slogan">
                <span><h1><?php echo $slogan; ?></h1></span>
            </div>
            <?php get_template_part('snippets/homepage-anchors'); ?>
        </div>
    </div>
    <?php get_template_part('snippets/homepage_work'); ?>
</section>

<section class="homepage-section heading-section team">
    <div class="section-heading scroll2me" id="team">
        <div class="heading-areas">
            <a class="logo" href="<?php echo $home_url; ?>">
                <img src="<?php echo $theme; ?>/assets/images/logo-on-mustard.svg" alt="LTL Mountain Logo">
            </a>
            <div class="slogan">
                <span><?php echo $slogan; ?></span>
            </div>
            <?php get_template_part('snippets/homepage-anchors'); ?>
        </div>
    </div>
    <?php get_template_part('snippets/homepage_team'); ?>
</section>

<section class="homepage-section heading-section contact">
    <div class="section-heading scroll2me" id="contact">
        <div class="heading-areas">
            <a class="logo" href="<?php echo $home_url; ?>">
                <img src="<?php echo $theme; ?>/assets/images/logo-on-black.svg" alt="LTL Mountain Logo">
            </a>
            <div class="slogan">
                <span><?php echo $slogan; ?></span>
            </div>
            <?php get_template_part('snippets/homepage-anchors'); ?>
        </div>
    </div>
    <?php get_template_part('snippets/homepage_contact'); ?>
</section>