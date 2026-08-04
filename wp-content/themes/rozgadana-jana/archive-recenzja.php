<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <header class="page-head">
        <?php rj_breadcrumb(array(
            array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
            array('label' => __('Książki', 'rozgadana-jana'), 'url' => null),
        )); ?>
        <p class="eyebrow"><?php esc_html_e('Recenzje', 'rozgadana-jana'); ?></p>
        <h1><?php esc_html_e('Wartościowe książki', 'rozgadana-jana'); ?></h1>
        <p class="lead"><?php esc_html_e('Książki, które coś we mnie zostawiły. Nie recenzuję wszystkiego, co przeczytam — tylko to, do czego chcę wracać.', 'rozgadana-jana'); ?></p>
    </header>

    <?php if (have_posts()) : ?>
        <div class="cover-grid">
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part('template-parts/review-cover', null, array('variant' => 'grid')); ?>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(array('mid_size' => 1, 'prev_text' => '←', 'next_text' => '→')); ?>
    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
