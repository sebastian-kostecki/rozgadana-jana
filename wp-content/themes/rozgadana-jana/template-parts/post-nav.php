<?php
declare(strict_types=1);
/**
 * Previous / next post navigation.
 *
 * @var array{aria_label?: string, prev_label?: string, next_label?: string} $args
 */
$rj_aria = (string) ($args['aria_label'] ?? __('Nawigacja wpisów', 'rozgadana-jana'));
$rj_prev_label = (string) ($args['prev_label'] ?? __('Poprzedni', 'rozgadana-jana'));
$rj_next_label = (string) ($args['next_label'] ?? __('Następny', 'rozgadana-jana'));
$rj_prev = get_previous_post();
$rj_next = get_next_post();
if (!$rj_prev instanceof WP_Post && !$rj_next instanceof WP_Post) {
    return;
}
?>
<nav class="post-nav" aria-label="<?php echo esc_attr($rj_aria); ?>">
    <?php if ($rj_prev instanceof WP_Post) : ?>
        <a class="post-nav__link post-nav__link--prev" href="<?php echo esc_url(get_permalink($rj_prev)); ?>">
            <span class="post-nav__label"><?php echo esc_html($rj_prev_label); ?></span>
            <span class="post-nav__title"><?php echo esc_html(get_the_title($rj_prev)); ?></span>
        </a>
    <?php endif; ?>
    <?php if ($rj_next instanceof WP_Post) : ?>
        <a class="post-nav__link post-nav__link--next" href="<?php echo esc_url(get_permalink($rj_next)); ?>">
            <span class="post-nav__label"><?php echo esc_html($rj_next_label); ?></span>
            <span class="post-nav__title"><?php echo esc_html(get_the_title($rj_next)); ?></span>
        </a>
    <?php endif; ?>
</nav>
