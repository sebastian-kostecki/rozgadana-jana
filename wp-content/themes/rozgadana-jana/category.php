<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<?php $rj_current = get_queried_object(); ?>
<main id="main" class="site-main container">
    <header class="page-head">
        <?php rj_breadcrumb(array(
            array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
            array('label' => single_cat_title('', false), 'url' => null),
        )); ?>
        <p class="eyebrow"><?php esc_html_e('Kategoria', 'rozgadana-jana'); ?></p>
        <h1><?php single_cat_title(); ?></h1>
        <?php if (category_description()) : ?>
            <div class="lead"><?php echo wp_kses_post(category_description()); ?></div>
        <?php endif; ?>
    </header>

    <div class="filter">
        <a class="filter__chip" href="<?php echo esc_url(home_url('/blog/')); ?>"><?php esc_html_e('Wszystko', 'rozgadana-jana'); ?></a>
        <?php
        foreach (array('codziennosc-z-bogiem' => __('Codzienność z Bogiem', 'rozgadana-jana'),
                       'macierzynstwo-i-rodzina' => __('Macierzyństwo i rodzina', 'rozgadana-jana')) as $rj_slug => $rj_label) :
            $rj_term = get_category_by_slug($rj_slug);
            if (!($rj_term instanceof WP_Term)) { continue; }
            $rj_is_active = ($rj_current instanceof WP_Term && $rj_current->slug === $rj_slug);
            $rj_class = $rj_is_active ? ' is-active' : '';
            $rj_aria = $rj_is_active ? ' aria-current="true"' : '';
        ?>
            <a class="filter__chip<?php echo esc_attr($rj_class); ?>"<?php echo $rj_aria; ?> href="<?php echo esc_url(get_category_link($rj_term)); ?>"><?php echo esc_html($rj_label); ?></a>
        <?php endforeach; ?>
    </div>

    <?php get_template_part('template-parts/post-list'); ?>
</main>
<?php get_footer(); ?>
