<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <div class="empty">
        <p class="eyebrow"><?php esc_html_e('Błąd 404', 'rozgadana-jana'); ?></p>
        <h1><?php esc_html_e('Nie znaleziono strony', 'rozgadana-jana'); ?></h1>
        <p><?php esc_html_e('Ta strona nie istnieje lub została przeniesiona.', 'rozgadana-jana'); ?></p>
        <p><a class="btn" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Wróć na stronę główną', 'rozgadana-jana'); ?></a></p>
    </div>
</main>
<?php get_footer(); ?>
