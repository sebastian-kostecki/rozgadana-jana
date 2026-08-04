<?php declare(strict_types=1); ?>
<?php
/**
 * Slim blog identity row shown above the featured post on the front page.
 * Replaces the former gradient hero.
 */
$rj_about     = get_page_by_path('o-mnie');
$rj_about_url = $rj_about instanceof WP_Post ? get_permalink($rj_about) : home_url('/o-mnie/');
?>
<section class="brand-bar" aria-label="<?php esc_attr_e('O blogu', 'rozgadana-jana'); ?>">
    <img class="brand-bar__logo"
         src="<?php echo esc_url(get_theme_file_uri('assets/images/logo-round.jpg')); ?>"
         alt=""
         width="44"
         height="44">
    <div class="brand-bar__text">
        <p class="brand-bar__tagline"><?php esc_html_e('O życiu, o sobie, o Bogu, o rodzinie.', 'rozgadana-jana'); ?></p>
        <p class="brand-bar__intro"><?php esc_html_e('Piszę o tym, co dzieje się między poranną kawą a wieczorną modlitwą.', 'rozgadana-jana'); ?></p>
    </div>
    <a class="brand-bar__link" href="<?php echo esc_url($rj_about_url); ?>">
        <?php esc_html_e('Poznaj mnie →', 'rozgadana-jana'); ?>
    </a>
</section>
