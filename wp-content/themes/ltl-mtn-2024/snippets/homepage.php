<?php
    $theme = get_stylesheet_directory_uri();
    $home_url = get_home_url();
?>

<section class="homepage-section intro">
    <?php if( is_active_sidebar('intro') ) : ?>
        <div class="main">
            <div class="intro-container">
                <?php dynamic_sidebar('intro'); ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<section class="homepage-section work">
    <div class="section-heading">
        <div class="heading-areas">
            <a class="logo" href="<?php echo $home_url; ?>">
                <img src="<?php echo $theme; ?>/assets/images/logo-on-white.svg" alt="LTL Mountain Logo">
            </a>
            <?php get_template_part('snippets/homepage-anchors'); ?>
        </div>
    </div>
</section>

<section class="homepage-section team">
    <div class="section-heading scroll2me">
        <div class="heading-areas">
            <a class="logo" href="<?php echo $home_url; ?>">
                <img src="<?php echo $theme; ?>/assets/images/logo-on-mustard.svg" alt="LTL Mountain Logo">
            </a>
            <?php get_template_part('snippets/homepage-anchors'); ?>
        </div>
    </div>
</section>

<section class="homepage-section contact">
    <div class="section-heading scroll2me">
        <div class="heading-areas">
            <a class="logo" href="<?php echo $home_url; ?>">
                <img src="<?php echo $theme; ?>/assets/images/logo-on-black.svg" alt="LTL Mountain Logo">
            </a>
            <?php get_template_part('snippets/homepage-anchors'); ?>
        </div>
    </div>
</section>