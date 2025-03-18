<!-- FOOTER.PHP ++++++++++++++++++++++ -->
<?php
    $site_name = get_bloginfo('name');
?>


<footer>
    <?php if( is_front_page() ) : ?>
        <div class="footer-logo">
            <?php
                $logo_url = get_stylesheet_directory_uri() . '/assets/images/logo-on-white.svg';
            ?>
            <div class="content-width footer-logo-container">
                <img src="<?= $logo_url; ?>" alt="Ltl Mtn Logo" />
            </div>
        </div>
    <?php endif; ?>
    <div class="copyright content-width">
        &copy; <?= date('Y'); ?> Ltl Mtn Inc.
    </div>
</footer>


<div class="back-to-top">
    <a href="#top" class="back-to-top-link">
        <img src="<?= get_stylesheet_directory_uri(); ?>/assets/images/back-to-top.svg" alt="Back to top" />
    </a>
</div>

<?php get_template_part('assets/scripts'); ?>

<?php /* Include this so the admin bar is visible. */ wp_footer(); ?>

</body>
</html>