<?php declare(strict_types=1); ?>
<section class="hero">
    <img class="hero__logo"
         src="<?php echo esc_url(get_theme_file_uri('assets/images/logo-round.jpg')); ?>"
         alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
    <div class="hero__text">
        <p class="eyebrow"><?php esc_html_e('Witaj u mnie', 'rozgadana-jana'); ?></p>
        <h1><?php esc_html_e('O życiu, o sobie, o Bogu, o rodzinie', 'rozgadana-jana'); ?></h1>
        <p><?php esc_html_e('Żona, mama, katoliczka. Lubię prostotę i autentyczność — dzielę się przemyśleniami o codzienności z Bogiem, macierzyństwie i wartościowej literaturze.', 'rozgadana-jana'); ?></p>
        <div class="hero__actions">
            <?php $rj_about = get_page_by_path('o-mnie'); ?>
            <a class="btn" href="<?php echo esc_url($rj_about ? get_permalink($rj_about) : home_url('/o-mnie/')); ?>">
                <?php esc_html_e('Poznaj mnie →', 'rozgadana-jana'); ?>
            </a>
            <?php rj_social_links_pills(); ?>
        </div>
    </div>
</section>
