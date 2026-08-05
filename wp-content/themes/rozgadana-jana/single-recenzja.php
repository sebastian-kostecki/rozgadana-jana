<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<div class="reading-progress" aria-hidden="true"><span class="reading-progress__value"></span></div>
<main id="main" class="site-main container">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('article'); ?>>
            <?php rj_breadcrumb(array(
                array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
                array('label' => __('Książki', 'rozgadana-jana'), 'url' => home_url('/ksiazki/')),
                array('label' => get_the_title(), 'url' => null),
            )); ?>

            <header class="review-head">
                <div class="review-head__cover">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('rj-cover', array('alt' => '')); ?>
                    <?php endif; ?>
                </div>
                <div class="review-head__meta">
                    <p class="article__cat"><?php esc_html_e('Recenzja', 'rozgadana-jana'); ?></p>
                    <h1 class="article__title"><?php the_title(); ?></h1>
                    <?php $rj_author = rj_review_book_author((int) get_the_ID()); ?>
                    <?php if ($rj_author !== '') : ?>
                        <p class="review-head__by"><?php echo esc_html($rj_author); ?></p>
                    <?php endif; ?>
                    <p class="article__meta"><?php rj_post_meta(); ?></p>
                </div>
            </header>

            <div class="article__content"><?php the_content(); ?></div>
        </article>

        <nav class="post-nav" aria-label="<?php esc_attr_e('Nawigacja recenzji', 'rozgadana-jana'); ?>">
            <?php $rj_prev = get_previous_post(); $rj_next = get_next_post(); ?>
            <?php if ($rj_prev instanceof WP_Post) : ?>
                <a class="post-nav__link post-nav__link--prev" href="<?php echo esc_url(get_permalink($rj_prev)); ?>">
                    <span class="post-nav__label"><?php esc_html_e('Poprzednia recenzja', 'rozgadana-jana'); ?></span>
                    <span class="post-nav__title"><?php echo esc_html(get_the_title($rj_prev)); ?></span>
                </a>
            <?php endif; ?>
            <?php if ($rj_next instanceof WP_Post) : ?>
                <a class="post-nav__link post-nav__link--next" href="<?php echo esc_url(get_permalink($rj_next)); ?>">
                    <span class="post-nav__label"><?php esc_html_e('Następna recenzja', 'rozgadana-jana'); ?></span>
                    <span class="post-nav__title"><?php echo esc_html(get_the_title($rj_next)); ?></span>
                </a>
            <?php endif; ?>
        </nav>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
