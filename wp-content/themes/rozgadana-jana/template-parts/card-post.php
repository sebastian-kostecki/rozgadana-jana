<?php declare(strict_types=1); ?>
<?php $rj_cat = rj_primary_category(); ?>
<article <?php post_class('post-card ' . rj_post_card_modifier(get_the_ID())); ?>
         data-category="<?php echo esc_attr(rj_category_filter_slug($rj_cat)); ?>">
    <?php if ($rj_cat) : ?>
        <span class="post-card__cat"><?php echo esc_html($rj_cat->name); ?></span>
    <?php endif; ?>
    <h2 class="post-card__title">
        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </h2>
    <p class="post-card__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 26, '…')); ?></p>
    <div class="post-card__meta">
        <span><?php echo esc_html(get_the_date()); ?></span>
        <span><?php echo esc_html(sprintf(
            _n('%d min', '%d min', rj_reading_time_minutes((string) get_the_content()), 'rozgadana-jana'),
            rj_reading_time_minutes((string) get_the_content())
        )); ?></span>
        <a class="rm" href="<?php the_permalink(); ?>"><?php esc_html_e('Czytaj dalej →', 'rozgadana-jana'); ?></a>
    </div>
</article>
