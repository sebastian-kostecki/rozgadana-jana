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
            $rj_q_common = array(
                'posts_per_page'      => 6,
                'ignore_sticky_posts' => true,
                'no_found_rows'       => true,
            );

            $rj_codz = new WP_Query(array_merge($rj_q_common, array(
                'category_name' => 'codziennosc-z-bogiem',
            )));

            $rj_fam = new WP_Query(array_merge($rj_q_common, array(
                'category_name' => 'macierzynstwo-i-rodzina',
            )));

            /** @var array<int, WP_Post> $rj_posts */
            $rj_posts = array();
            foreach (array($rj_codz->posts, $rj_fam->posts) as $rj_list) {
                foreach ($rj_list as $rj_post) {
                    if (!$rj_post instanceof WP_Post) {
                        continue;
                    }
                    $rj_posts[$rj_post->ID] = $rj_post; // dedupe by ID
                }
            }

            $rj_posts = array_values($rj_posts);
            usort($rj_posts, static function (WP_Post $a, WP_Post $b): int {
                return strcmp((string) $b->post_date_gmt, (string) $a->post_date_gmt);
            });

            if ($rj_posts !== array()) :
                global $post;
                foreach ($rj_posts as $post) :
                    setup_postdata($post);
                    get_template_part('template-parts/card', 'post');
                endforeach;
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
