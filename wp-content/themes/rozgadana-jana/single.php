<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<div class="reading-progress" aria-hidden="true"><span class="reading-progress__value"></span></div>
<main id="main" class="site-main container">
    <?php while (have_posts()) : the_post(); ?>
        <?php $rj_cat = rj_primary_category(); ?>
        <article <?php post_class('article'); ?>>
            <?php
            $rj_crumbs = array(
                array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
                array('label' => __('Przemyślenia', 'rozgadana-jana'), 'url' => home_url('/blog/')),
            );
            if ($rj_cat instanceof WP_Term) {
                $rj_crumbs[] = array(
                    'label' => $rj_cat->name,
                    'url'   => get_category_link($rj_cat),
                );
            }
            $rj_crumbs[] = array('label' => get_the_title(), 'url' => null);
            rj_breadcrumb($rj_crumbs);
            ?>

            <?php if ($rj_cat instanceof WP_Term) : ?>
                <a class="article__cat" href="<?php echo esc_url(get_category_link($rj_cat)); ?>"><?php echo esc_html($rj_cat->name); ?></a>
            <?php endif; ?>

            <h1 class="article__title"><?php the_title(); ?></h1>
            <p class="article__meta"><?php rj_post_meta(); ?></p>

            <div class="article__content"><?php the_content(); ?></div>
        </article>

        <?php
        get_template_part('template-parts/post-nav', null, array(
            'aria_label' => __('Nawigacja wpisów', 'rozgadana-jana'),
            'prev_label' => __('Poprzedni', 'rozgadana-jana'),
            'next_label' => __('Następny', 'rozgadana-jana'),
        ));
        ?>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
