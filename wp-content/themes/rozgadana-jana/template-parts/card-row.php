<?php declare(strict_types=1); ?>
<?php
/**
 * Shared row card for list views.
 *
 * Args:
 * - variant: 'thought'|'review' (default thought)
 *
 * @var array{variant?: string}|null $args
 */
$args    = is_array($args ?? null) ? $args : array();
$variant = (string) ($args['variant'] ?? 'thought');
$is_review = $variant === 'review';
$post_id = (int) get_the_ID();

$rj_cat = null;
$data_category = '';
if (!$is_review) {
    $rj_cat = rj_primary_category($post_id);
    $data_category = rj_category_filter_slug($rj_cat);
}

$modifier = $is_review ? 'row-card--review' : 'row-card--thought';
?>
<article
    <?php post_class('row-card ' . $modifier); ?>
    <?php if (!$is_review) : ?>
        data-category="<?php echo esc_attr($data_category); ?>"
    <?php endif; ?>
>
    <a class="row-card__thumb" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('rj-cover', array('alt' => esc_attr(get_the_title()))); ?>
        <?php else : ?>
            <span class="row-card__thumb-placeholder"><?php echo esc_html(get_the_title()); ?></span>
        <?php endif; ?>
    </a>

    <div class="row-card__body">
        <div class="row-card__title-row">
            <h3 class="row-card__title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h3>
            <?php if (!$is_review && $rj_cat instanceof WP_Term) : ?>
                <span class="row-card__chip"><?php echo esc_html($rj_cat->name); ?></span>
            <?php endif; ?>
        </div>

        <div class="row-card__by">
            <?php if ($is_review) : ?>
                <?php $rj_author = rj_review_book_author($post_id); ?>
                <?php if ($rj_author !== '') : ?>
                    <?php echo esc_html(sprintf(__('aut. %s', 'rozgadana-jana'), $rj_author)); ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <p class="row-card__excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>

        <div class="row-card__meta">
            <span><?php echo esc_html(get_the_date()); ?></span>
            <span>
                <?php
                $minutes = rj_reading_time_minutes((string) get_the_content());
                echo esc_html(sprintf(_n('%d min', '%d min', $minutes, 'rozgadana-jana'), $minutes));
                ?>
            </span>
            <a class="rm" href="<?php the_permalink(); ?>"><?php esc_html_e('Czytaj dalej →', 'rozgadana-jana'); ?></a>
        </div>
    </div>
</article>
