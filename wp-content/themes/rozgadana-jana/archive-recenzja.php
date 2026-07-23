<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <header class="page-head">
        <?php rj_breadcrumb(array(
            array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
            array('label' => __('Wartościowe książki', 'rozgadana-jana'), 'url' => null),
        )); ?>
        <p class="eyebrow"><?php esc_html_e('Recenzje', 'rozgadana-jana'); ?></p>
        <h1><?php esc_html_e('Wartościowe książki', 'rozgadana-jana'); ?></h1>
        <p class="lead"><?php esc_html_e('Subiektywne recenzje lektur, które poruszają serce i przybliżają do Boga.', 'rozgadana-jana'); ?></p>
    </header>

    <?php if (have_posts()) : ?>
        <div class="row-list">
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part('template-parts/card', 'review'); ?>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(array('mid_size' => 1, 'prev_text' => '←', 'next_text' => '→')); ?>
    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
