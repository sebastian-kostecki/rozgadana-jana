<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<div class="reading-progress" aria-hidden="true"><span class="reading-progress__value"></span></div>
<main id="main" class="site-main container">
    <?php while (have_posts()) : the_post(); ?>
        <?php $rj_cat = rj_primary_category(); ?>
        <article <?php post_class('article'); ?>>
            <?php rj_breadcrumb(array(
                array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
                array('label' => $rj_cat instanceof WP_Term ? $rj_cat->name : __('Przemyślenia', 'rozgadana-jana'), 'url' => $rj_cat instanceof WP_Term ? get_category_link($rj_cat) : home_url('/blog/')),
                array('label' => get_the_title(), 'url' => null),
            )); ?>

            <?php if ($rj_cat instanceof WP_Term) : ?>
                <a class="article__cat" href="<?php echo esc_url(get_category_link($rj_cat)); ?>"><?php echo esc_html($rj_cat->name); ?></a>
            <?php endif; ?>

            <h1 class="article__title"><?php the_title(); ?></h1>
            <p class="article__meta"><?php rj_post_meta(); ?></p>

            <div class="article__content article__content--dropcap"><?php the_content(); ?></div>
        </article>

        <nav class="post-nav" aria-label="<?php esc_attr_e('Nawigacja wpisów', 'rozgadana-jana'); ?>">
            <?php $rj_prev = get_previous_post(); $rj_next = get_next_post(); ?>
            <?php if ($rj_prev instanceof WP_Post) : ?>
                <a class="post-nav__link post-nav__link--prev" href="<?php echo esc_url(get_permalink($rj_prev)); ?>">
                    <span class="post-nav__label"><?php esc_html_e('Poprzedni', 'rozgadana-jana'); ?></span>
                    <span class="post-nav__title"><?php echo esc_html(get_the_title($rj_prev)); ?></span>
                </a>
            <?php endif; ?>
            <?php if ($rj_next instanceof WP_Post) : ?>
                <a class="post-nav__link post-nav__link--next" href="<?php echo esc_url(get_permalink($rj_next)); ?>">
                    <span class="post-nav__label"><?php esc_html_e('Następny', 'rozgadana-jana'); ?></span>
                    <span class="post-nav__title"><?php echo esc_html(get_the_title($rj_next)); ?></span>
                </a>
            <?php endif; ?>
        </nav>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
