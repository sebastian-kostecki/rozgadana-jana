<?php declare(strict_types=1); ?>
<?php
/**
 * Newest post presented on a deep-purple stage. Must be called inside a loop.
 */
$rj_minutes = rj_reading_time_minutes((string) get_the_content());
?>
<section class="featured" aria-labelledby="featured-title">
    <p class="featured__eyebrow"><?php esc_html_e('Najnowszy wpis', 'rozgadana-jana'); ?></p>
    <h2 class="featured__title" id="featured-title">
        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </h2>
    <p class="featured__excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
    <p class="featured__actions">
        <a class="featured__cta" href="<?php the_permalink(); ?>"><?php esc_html_e('Czytaj →', 'rozgadana-jana'); ?></a>
        <span class="featured__meta">
            <?php
            echo esc_html(sprintf(
                /* translators: 1: publication date, 2: reading time in minutes */
                __('%1$s · %2$d min', 'rozgadana-jana'),
                get_the_date(),
                $rj_minutes
            ));
            ?>
        </span>
    </p>
</section>
