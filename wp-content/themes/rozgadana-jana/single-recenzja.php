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

        <?php
        get_template_part('template-parts/post-nav', null, array(
            'aria_label' => __('Nawigacja recenzji', 'rozgadana-jana'),
            'prev_label' => __('Poprzednia recenzja', 'rozgadana-jana'),
            'next_label' => __('Następna recenzja', 'rozgadana-jana'),
        ));
        ?>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
