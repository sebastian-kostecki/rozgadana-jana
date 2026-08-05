<?php declare(strict_types=1); ?>
<?php
/**
 * Slim blog identity row shown above the featured post on the front page.
 * Replaces the former gradient hero.
 */
?>
<section class="brand-bar" aria-labelledby="brand-bar-name">
    <img class="brand-bar__logo"
         src="<?php echo esc_url(rj_brand_logo_url()); ?>"
         alt=""
         width="72"
         height="72"
         fetchpriority="high"
         decoding="async">
    <div class="brand-bar__text">
        <h1 class="brand-bar__name" id="brand-bar-name"><?php bloginfo('name'); ?></h1>
        <p class="brand-bar__tagline"><?php echo esc_html(rj_short_tagline()); ?></p>
        <p class="brand-bar__intro"><?php esc_html_e('Piszę o tym, co dzieje się między poranną kawą a wieczorną modlitwą.', 'rozgadana-jana'); ?></p>
    </div>
    <a class="brand-bar__link" href="#kto-tu-pisze">
        <?php esc_html_e('Poznaj mnie →', 'rozgadana-jana'); ?>
    </a>
</section>
