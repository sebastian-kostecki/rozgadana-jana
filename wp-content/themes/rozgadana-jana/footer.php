<?php declare(strict_types=1); ?>
<footer class="site-footer">
    <div class="container">
        <img class="site-footer__wordmark"
             src="<?php echo esc_url(get_theme_file_uri('assets/images/wordmark.jpg')); ?>"
             alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
        <?php
        wp_nav_menu(array(
            'theme_location' => 'footer',
            'container'      => 'nav',
            'fallback_cb'    => false,
            'depth'          => 1,
        ));
        ?>
        <div class="site-footer__social"><?php rj_social_links(); ?></div>
        <div class="site-footer__copy">
            <?php echo esc_html(sprintf('© %s %s', date('Y'), get_bloginfo('name'))); ?>
            · <?php esc_html_e('O życiu, o sobie, o Bogu, o rodzinie', 'rozgadana-jana'); ?>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
