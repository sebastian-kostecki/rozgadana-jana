<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <header class="page-head">
        <?php rj_breadcrumb(array(
            array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
            array('label' => wp_strip_all_tags(get_the_archive_title()), 'url' => null),
        )); ?>
        <p class="eyebrow"><?php esc_html_e('Archiwum', 'rozgadana-jana'); ?></p>
        <?php the_archive_title('<h1>', '</h1>'); ?>
        <?php $rj_desc = get_the_archive_description(); if ($rj_desc) : ?>
            <div class="lead"><?php echo wp_kses_post($rj_desc); ?></div>
        <?php endif; ?>
    </header>

    <?php if (have_posts()) : ?>
        <div class="row-list">
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part('template-parts/card', 'post'); ?>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(array('mid_size' => 1, 'prev_text' => '←', 'next_text' => '→')); ?>
    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
