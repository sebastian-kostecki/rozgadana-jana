<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('article'); ?>>
            <?php $rj_cat = rj_primary_category(); ?>
            <?php if ($rj_cat) : ?>
                <a class="article__cat" href="<?php echo esc_url(get_category_link($rj_cat)); ?>"><?php echo esc_html($rj_cat->name); ?></a>
            <?php endif; ?>
            <h1><?php the_title(); ?></h1>
            <div class="article__meta"><?php rj_post_meta(); ?></div>
            <div class="article__content"><?php the_content(); ?></div>
        </article>

        <nav class="post-nav" aria-label="<?php esc_attr_e('Nawigacja wpisów', 'rozgadana-jana'); ?>">
            <?php
            $prev = get_previous_post();
            $next = get_next_post();
            if ($prev) :
            ?>
                <a class="prev" href="<?php echo esc_url(get_permalink($prev)); ?>">
                    <span class="s"><?php esc_html_e('← Poprzedni', 'rozgadana-jana'); ?></span>
                    <?php echo esc_html(get_the_title($prev)); ?>
                </a>
            <?php endif; ?>
            <?php if ($next) : ?>
                <a class="next" href="<?php echo esc_url(get_permalink($next)); ?>">
                    <span class="s"><?php esc_html_e('Następny →', 'rozgadana-jana'); ?></span>
                    <?php echo esc_html(get_the_title($next)); ?>
                </a>
            <?php endif; ?>
        </nav>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
