<?php
declare(strict_types=1);
/**
 * Category filter chips.
 *
 * @var array{mode?: string, active_slug?: string} $args
 */
$rj_mode        = (string) ($args['mode'] ?? 'blog');
$rj_active_slug = (string) ($args['active_slug'] ?? '');
$rj_chips       = rj_thought_category_chips();

$rj_all_active = ($rj_mode === 'front' || $rj_mode === 'blog');
$rj_all_url    = home_url('/blog/');
?>
<div class="filter">
    <a class="filter__chip<?php echo $rj_all_active ? ' is-active' : ''; ?>"
       href="<?php echo esc_url($rj_all_url); ?>"
       <?php echo $rj_mode === 'front' ? ' data-filter="*"' : ''; ?>
       <?php echo $rj_all_active ? ' aria-current="true"' : ''; ?>><?php esc_html_e('Wszystko', 'rozgadana-jana'); ?></a>
    <?php foreach ($rj_chips as $rj_slug => $rj_label) :
        $rj_term = get_category_by_slug($rj_slug);
        if (!$rj_term instanceof WP_Term) {
            continue;
        }
        $rj_is_active = ($rj_mode === 'category' && $rj_active_slug === $rj_slug);
        ?>
        <a class="filter__chip<?php echo $rj_is_active ? ' is-active' : ''; ?>"
           href="<?php echo esc_url(get_category_link($rj_term)); ?>"
           <?php echo $rj_mode === 'front' ? ' data-filter="' . esc_attr($rj_slug) . '"' : ''; ?>
           <?php echo $rj_is_active ? ' aria-current="true"' : ''; ?>><?php echo esc_html($rj_label); ?></a>
    <?php endforeach; ?>
</div>
