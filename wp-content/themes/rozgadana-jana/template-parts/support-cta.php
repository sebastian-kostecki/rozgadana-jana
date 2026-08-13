<?php
declare(strict_types=1);
/**
 * Support CTA card under single posts and reviews.
 */
ob_start();
$rj_has_links = rj_support_cta();
$rj_pills     = (string) ob_get_clean();
if (!$rj_has_links) {
    return;
}
?>
<aside class="support-cta" aria-label="<?php echo esc_attr__('Wesprzyj', 'rozgadana-jana'); ?>">
    <p class="eyebrow"><?php esc_html_e('Wesprzyj', 'rozgadana-jana'); ?></p>
    <p class="support-cta__lead"><?php esc_html_e('Jeśli podoba Ci się moja twórczość', 'rozgadana-jana'); ?></p>
    <div class="support-cta__pills"><?php echo $rj_pills; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pills built with esc_url/esc_html + trusted SVG. ?></div>
</aside>
