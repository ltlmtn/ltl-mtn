<!-- FOOTER.PHP ++++++++++++++++++++++ -->
<?php
    $site_name = get_bloginfo('name');
    $theme = get_stylesheet_directory_uri();
?>

<footer id="site-footer">
    <div class="footer-content content-width">
        <div class="footer-column-1">
            <div class="footer-contact"><a href="mailto:info@ltl-mtn.com">info@ltl-mtn.com</a></div>
            <div class="footer-location">Kingston, NY</div>
        </div>
        <div class="footer-column-2">
            <div class="footer-company-name"><?= $site_name; ?></div>
            <div class="footer-icon">
                <img src="<?= $theme; ?>/assets/images/footer-icon.svg" alt="Footer Icon" />
            </div>
            <div class="footer-copyright">&copy; <?= date('Y'); ?> and still human.</div>
        </div>
    </div>
</footer>


<div class="back-to-top">
    <a href="#top" class="back-to-top-link">
        <img src="<?= $theme; ?>/assets/images/back-to-top.svg" alt="Back to top" />
    </a>
</div>

<?php get_template_part('assets/scripts'); ?>

<?php /* Include this so the admin bar is visible. */ wp_footer(); ?>

</body>
</html>