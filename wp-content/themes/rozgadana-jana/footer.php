<?php declare(strict_types=1); ?>
<footer class="site-footer">
    <div class="container">
        <?php
        wp_nav_menu(array(
            'theme_location' => 'footer',
            'container'      => 'nav',
            'fallback_cb'    => false,
            'depth'          => 1,
        ));
        ?>
        <div class="site-footer__bar">
            <div class="site-footer__copy">
                <?php echo esc_html(sprintf('© %s #rozgadanajana', date('Y'))); ?>
                · <?php esc_html_e('O życiu, o sobie, o Bogu, o rodzinie', 'rozgadana-jana'); ?>
            </div>
            <div class="site-footer__social"><?php rj_social_links(); ?></div>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
