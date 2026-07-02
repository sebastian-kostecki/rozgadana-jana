<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <?php while (have_posts()) : the_post(); ?>
        <header class="page-head">
            <?php rj_breadcrumb(array(
                array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
                array('label' => __('Wartościowe książki', 'rozgadana-jana'), 'url' => home_url('/ksiazki/')),
                array('label' => get_the_title(), 'url' => null),
            )); ?>
        </header>
        <article <?php post_class('review-single'); ?>>
            <div class="review-single__cover">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('rj-cover', array('alt' => esc_attr(get_the_title()))); ?>
                <?php endif; ?>
            </div>
            <div class="review-single__body">
                <p class="eyebrow"><?php esc_html_e('Recenzja', 'rozgadana-jana'); ?></p>
                <h1><?php the_title(); ?></h1>
                <?php $rj_author = rj_review_book_author(get_the_ID()); ?>
                <?php if ($rj_author !== '') : ?>
                    <div class="review-single__by"><?php echo esc_html(sprintf(__('aut. %s', 'rozgadana-jana'), $rj_author)); ?></div>
                <?php endif; ?>
                <div class="article__content"><?php the_content(); ?></div>
            </div>
        </article>

        <nav class="post-nav" aria-label="<?php esc_attr_e('Nawigacja recenzji', 'rozgadana-jana'); ?>">
            <?php $prev = get_previous_post(); $next = get_next_post(); ?>
            <?php if ($prev) : ?>
                <a class="prev" href="<?php echo esc_url(get_permalink($prev)); ?>">
                    <span class="s"><?php esc_html_e('← Poprzednia', 'rozgadana-jana'); ?></span>
                    <?php echo esc_html(get_the_title($prev)); ?>
                </a>
            <?php endif; ?>
            <?php if ($next) : ?>
                <a class="next" href="<?php echo esc_url(get_permalink($next)); ?>">
                    <span class="s"><?php esc_html_e('Następna →', 'rozgadana-jana'); ?></span>
                    <?php echo esc_html(get_the_title($next)); ?>
                </a>
            <?php endif; ?>
        </nav>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
