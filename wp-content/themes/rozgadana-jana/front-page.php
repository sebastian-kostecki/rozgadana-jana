<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">

    <?php get_template_part('template-parts/hero'); ?>

    <section class="section" aria-labelledby="thoughts-h">
        <div class="section__head">
            <h2 id="thoughts-h"><?php esc_html_e('Przemyślenia', 'rozgadana-jana'); ?></h2>
            <a class="more" href="<?php echo esc_url(home_url('/blog/')); ?>"><?php esc_html_e('Wszystkie wpisy →', 'rozgadana-jana'); ?></a>
        </div>

        <div class="filter" role="tablist" aria-label="<?php esc_attr_e('Filtr kategorii', 'rozgadana-jana'); ?>">
            <a class="filter__chip is-active" href="#" data-filter="*"><?php esc_html_e('Wszystko', 'rozgadana-jana'); ?></a>
            <a class="filter__chip" href="<?php echo esc_url(get_category_link(get_category_by_slug_id('codziennosc-z-bogiem'))); ?>" data-filter="codziennosc-z-bogiem"><?php esc_html_e('Codzienność z Bogiem', 'rozgadana-jana'); ?></a>
            <a class="filter__chip" href="<?php echo esc_url(get_category_link(get_category_by_slug_id('macierzynstwo-i-rodzina'))); ?>" data-filter="macierzynstwo-i-rodzina"><?php esc_html_e('Macierzyństwo i rodzina', 'rozgadana-jana'); ?></a>
        </div>

        <div class="post-grid" id="rj-thoughts">
            <?php
            $rj_thoughts = new WP_Query(array(
                'posts_per_page'      => 6,
                'ignore_sticky_posts' => true,
                'no_found_rows'       => true,
            ));
            if ($rj_thoughts->have_posts()) :
                while ($rj_thoughts->have_posts()) : $rj_thoughts->the_post();
                    get_template_part('template-parts/card', 'post');
                endwhile;
                wp_reset_postdata();
            else :
                get_template_part('template-parts/content', 'none');
            endif;
            ?>
        </div>
    </section>

    <section class="section" aria-labelledby="reviews-h">
        <div class="section__head">
            <h2 id="reviews-h"><?php esc_html_e('Recenzje książek', 'rozgadana-jana'); ?></h2>
            <a class="more" href="<?php echo esc_url(home_url('/ksiazki/')); ?>"><?php esc_html_e('Wszystkie recenzje →', 'rozgadana-jana'); ?></a>
        </div>
        <div class="review-grid review-grid--home">
            <?php
            $rj_reviews = new WP_Query(array(
                'post_type'           => 'recenzja',
                'posts_per_page'      => 4,
                'no_found_rows'       => true,
            ));
            if ($rj_reviews->have_posts()) :
                while ($rj_reviews->have_posts()) : $rj_reviews->the_post();
                    get_template_part('template-parts/card', 'review');
                endwhile;
                wp_reset_postdata();
            else :
                get_template_part('template-parts/content', 'none');
            endif;
            ?>
        </div>
    </section>

</main>
<?php get_footer(); ?>
