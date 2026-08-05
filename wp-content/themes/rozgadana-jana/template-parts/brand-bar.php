<?php declare(strict_types=1); ?>
<?php
/**
 * Slim blog identity row shown above the featured post on the front page.
 * Replaces the former gradient hero.
 */
$rj_about     = get_page_by_path('o-mnie');
$rj_about_url = $rj_about instanceof WP_Post ? get_permalink($rj_about) : home_url('/o-mnie/');
?>
<section class="brand-bar" aria-labelledby="brand-bar-name">
    <img class="brand-bar__logo"
         src="<?php echo esc_url(get_theme_file_uri('assets/images/logo-round.jpg')); ?>"
         alt=""
         width="72"
         height="72">
    <div class="brand-bar__text">
        <h1 class="brand-bar__name" id="brand-bar-name"><?php bloginfo('name'); ?></h1>
        <p class="brand-bar__tagline"><?php esc_html_e('O Bogu, o życiu, o rodzinie o sobie.', 'rozgadana-jana'); ?></p>
        <p class="brand-bar__intro"><?php esc_html_e('Piszę o tym, co dzieje się między poranną kawą a wieczorną modlitwą.', 'rozgadana-jana'); ?></p>
    </div>
    <a class="brand-bar__link" href="<?php echo esc_url($rj_about_url); ?>">
        <?php esc_html_e('Poznaj mnie →', 'rozgadana-jana'); ?>
    </a>
</section>
