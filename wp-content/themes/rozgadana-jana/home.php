<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <header class="page-head">
        <?php rj_breadcrumb(array(
            array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
            array('label' => __('Przemyślenia', 'rozgadana-jana'), 'url' => null),
        )); ?>
        <p class="eyebrow"><?php esc_html_e('Przemyślenia', 'rozgadana-jana'); ?></p>
        <h1><?php esc_html_e('Wszystkie wpisy', 'rozgadana-jana'); ?></h1>
        <p class="lead"><?php esc_html_e('Wszystko, co napisałam — od najnowszego. Jeśli szukasz konkretnego tematu, zawęź listę filtrem poniżej.', 'rozgadana-jana'); ?></p>
    </header>

    <?php get_template_part('template-parts/filter-chips', null, array('mode' => 'blog')); ?>

    <?php get_template_part('template-parts/post-list'); ?>
</main>
<?php get_footer(); ?>
