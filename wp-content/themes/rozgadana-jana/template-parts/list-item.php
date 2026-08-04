<?php declare(strict_types=1); ?>
<?php
/**
 * One row in a post list. Must be called inside a loop.
 *
 * Args:
 * - variant: 'home' (two-digit ordinal) | 'archive' (date column). Default 'home'.
 * - index:   1-based position, used by the 'home' variant only. Default 0.
 *
 * @var array{variant?: string, index?: int}|null $args
 */
$args        = is_array($args ?? null) ? $args : array();
$rj_variant  = (string) ($args['variant'] ?? 'home');
$rj_index    = (int) ($args['index'] ?? 0);
$rj_post_id  = (int) get_the_ID();
$rj_minutes  = rj_reading_time_minutes((string) get_the_content());
$rj_is_review = get_post_type() === 'recenzja';

// Reviews have no category, so in mixed lists (search) they carry a type label instead.
if ($rj_is_review) {
    $rj_label     = __('Recenzja', 'rozgadana-jana');
    $rj_label_url = (string) get_post_type_archive_link('recenzja');
    $rj_filter    = '';
} else {
    $rj_cat       = rj_primary_category($rj_post_id);
    $rj_label     = $rj_cat instanceof WP_Term ? $rj_cat->name : '';
    $rj_label_url = $rj_cat instanceof WP_Term ? (string) get_category_link($rj_cat) : '';
    $rj_filter    = rj_category_filter_slug($rj_cat);
}
?>
<article <?php post_class('row-item'); ?><?php echo $rj_is_review ? '' : ' data-category="' . esc_attr($rj_filter) . '"'; ?>>
    <?php if ($rj_variant === 'archive') : ?>
        <span class="row-item__date"><?php echo esc_html(get_the_date('j M')); ?></span>
    <?php else : ?>
        <span class="row-item__num" aria-hidden="true"><?php echo esc_html(str_pad((string) $rj_index, 2, '0', STR_PAD_LEFT)); ?></span>
    <?php endif; ?>

    <div class="row-item__body">
        <h3 class="row-item__title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>
        <p class="row-item__meta">
            <?php if ($rj_label !== '' && $rj_label_url !== '') : ?>
                <a class="row-item__cat" href="<?php echo esc_url($rj_label_url); ?>"><?php echo esc_html($rj_label); ?></a>
                <span aria-hidden="true"> · </span>
            <?php endif; ?>
            <span class="row-item__date-inline"><?php echo esc_html(get_the_date()); ?><span aria-hidden="true"> · </span></span>
            <?php
            echo esc_html(sprintf(
                /* translators: %d: reading time in minutes */
                __('%d min', 'rozgadana-jana'),
                $rj_minutes
            ));
            ?>
        </p>
    </div>
</article>
