<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <div class="empty">
        <p class="eyebrow"><?php esc_html_e('Błąd 404', 'rozgadana-jana'); ?></p>
        <h1 class="article__title"><?php esc_html_e('Nie znaleziono strony', 'rozgadana-jana'); ?></h1>
        <p class="empty__lead"><?php esc_html_e('Ta strona nie istnieje lub została przeniesiona. Może zaczniesz od najnowszych wpisów?', 'rozgadana-jana'); ?></p>
        <p>
            <a class="btn" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Wróć na stronę główną', 'rozgadana-jana'); ?></a>
            <a class="pill" href="<?php echo esc_url(home_url('/blog/')); ?>"><?php esc_html_e('Wszystkie wpisy', 'rozgadana-jana'); ?></a>
        </p>
    </div>
</main>
<?php get_footer(); ?>
