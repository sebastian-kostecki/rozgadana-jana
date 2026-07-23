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
                       'macierzynstwo-i-rodzina' => __('Macierzyństwo i rodzina', 'rozgadana-jana')) as $slug => $label) :
            $term = get_category_by_slug($slug);
            if (!$term) { continue; }
            $active = ($rj_current instanceof WP_Term && $rj_current->slug === $slug) ? ' is-active' : '';
        ?>
            <a class="filter__chip<?php echo esc_attr($active); ?>" href="<?php echo esc_url(get_category_link($term)); ?>"><?php echo esc_html($label); ?></a>
        <?php endforeach; ?>
    </div>

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
