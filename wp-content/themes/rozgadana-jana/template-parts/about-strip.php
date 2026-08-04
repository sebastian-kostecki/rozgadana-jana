<?php declare(strict_types=1); ?>
<?php
/**
 * "Kto tu pisze" strip on the front page.
 *
 * Content comes from the page with slug `o-mnie` — its featured image and excerpt — so the
 * author can edit it from the page editor without the theme growing a settings field.
 */
$rj_about = get_page_by_path('o-mnie');
$rj_url   = $rj_about instanceof WP_Post ? get_permalink($rj_about) : home_url('/o-mnie/');

$rj_photo = '';
if ($rj_about instanceof WP_Post && has_post_thumbnail($rj_about)) {
    $rj_photo = (string) get_the_post_thumbnail_url($rj_about, 'rj-cover');
}
if ($rj_photo === '') {
    $rj_photo = (string) get_theme_file_uri('assets/images/author.jpg');
}

$rj_bio = '';
if ($rj_about instanceof WP_Post && $rj_about->post_excerpt !== '') {
    $rj_bio = $rj_about->post_excerpt;
}
if ($rj_bio === '') {
    $rj_bio = __('Żona, mama, katoliczka, która nie udaje, że ma wszystko poukładane. Piszę o wierze bez patosu i o rodzinie bez filtra.', 'rozgadana-jana');
}
?>
<section class="about-strip" aria-labelledby="about-strip-title">
    <img class="about-strip__photo"
         src="<?php echo esc_url($rj_photo); ?>"
         alt="<?php esc_attr_e('Autorka bloga Rozgadana Jana', 'rozgadana-jana'); ?>"
         width="92"
         height="92"
         loading="lazy">
    <div class="about-strip__text">
        <p class="eyebrow"><?php esc_html_e('Kto tu pisze', 'rozgadana-jana'); ?></p>
        <h2 class="about-strip__title" id="about-strip-title"><?php esc_html_e('Cześć, jestem Jana', 'rozgadana-jana'); ?></h2>
        <p class="about-strip__bio"><?php echo esc_html($rj_bio); ?></p>
        <a class="about-strip__link" href="<?php echo esc_url($rj_url); ?>">
            <?php esc_html_e('Przeczytaj całą historię →', 'rozgadana-jana'); ?>
        </a>
    </div>
</section>
