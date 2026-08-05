<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<?php $rj_current = get_queried_object(); ?>
<main id="main" class="site-main container">
    <header class="page-head">
        <?php rj_breadcrumb(array(
            array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
            array('label' => __('Przemyślenia', 'rozgadana-jana'), 'url' => home_url('/blog/')),
            array('label' => single_cat_title('', false), 'url' => null),
        )); ?>
        <p class="eyebrow"><?php esc_html_e('Kategoria', 'rozgadana-jana'); ?></p>
        <h1><?php single_cat_title(); ?></h1>
        <?php if (category_description()) : ?>
            <div class="lead"><?php echo wp_kses_post(category_description()); ?></div>
        <?php endif; ?>
    </header>

    <?php
    get_template_part('template-parts/filter-chips', null, array(
        'mode'        => 'category',
        'active_slug' => ($rj_current instanceof WP_Term) ? (string) $rj_current->slug : '',
    ));
    ?>

    <?php get_template_part('template-parts/post-list'); ?>
</main>
<?php get_footer(); ?>
