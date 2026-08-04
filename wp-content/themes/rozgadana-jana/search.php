<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <header class="page-head">
        <p class="eyebrow"><?php esc_html_e('Wyniki wyszukiwania', 'rozgadana-jana'); ?></p>
        <h1><?php echo esc_html(sprintf(__('Szukasz: %s', 'rozgadana-jana'), get_search_query())); ?></h1>
    </header>
    <?php get_template_part('template-parts/post-list', null, array('group_by_year' => false)); ?>
</main>
<?php get_footer(); ?>
