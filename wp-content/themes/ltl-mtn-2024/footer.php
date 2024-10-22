<!-- FOOTER.PHP ++++++++++++++++++++++ -->
<?php
    $site_name = get_bloginfo('name');
?>


<footer>
    <div class="copyright content-width">
        &copy; <?= date('Y'); ?> <?= $site_name; ?>
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