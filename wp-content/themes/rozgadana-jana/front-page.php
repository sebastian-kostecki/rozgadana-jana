<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">

    <?php get_template_part('template-parts/brand-bar'); ?>

    <?php
    $rj_featured_id = 0;
    $rj_featured = new WP_Query(array(
        'posts_per_page'      => 1,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ));
    if ($rj_featured->have_posts()) :
        while ($rj_featured->have_posts()) :
            $rj_featured->the_post();
            $rj_featured_id = (int) get_the_ID();
            get_template_part('template-parts/featured-post');
        endwhile;
        wp_reset_postdata();
    endif;
    ?>

    <section class="section" aria-labelledby="thoughts-h">
        <div class="section__head">
            <h2 id="thoughts-h"><?php esc_html_e('Wcześniej pisałam', 'rozgadana-jana'); ?></h2>
            <a class="more" href="<?php echo esc_url(home_url('/blog/')); ?>"><?php esc_html_e('Wszystkie wpisy →', 'rozgadana-jana'); ?></a>
        </div>

        <?php get_template_part('template-parts/filter-chips', null, array('mode' => 'front')); ?>

        <div class="row-list" id="rj-thoughts">
            <?php
            $rj_chips   = rj_thought_category_chips();
            $rj_pools   = array_merge(array('*'), array_keys($rj_chips));
            $rj_any     = false;
            foreach ($rj_pools as $rj_pool) :
                if ($rj_pool !== '*' && !isset($rj_chips[$rj_pool])) {
                    continue;
                }
                if ($rj_pool !== '*' && !get_category_by_slug($rj_pool) instanceof WP_Term) {
                    continue;
                }
                $rj_thoughts = rj_home_thoughts_query($rj_pool, $rj_featured_id);
                if (!$rj_thoughts->have_posts()) {
                    wp_reset_postdata();
                    continue;
                }
                $rj_any = true;
                $rj_i   = 0;
                while ($rj_thoughts->have_posts()) :
                    $rj_thoughts->the_post();
                    $rj_i++;
                    get_template_part('template-parts/list-item', null, array(
                        'variant'    => 'home',
                        'index'      => $rj_i,
                        'filter_for' => $rj_pool,
                        'hidden'     => $rj_pool !== '*',
                    ));
                endwhile;
                wp_reset_postdata();
            endforeach;
            if (!$rj_any) :
                get_template_part('template-parts/content', 'none');
            endif;
            ?>
        </div>
    </section>

    <section class="section" aria-labelledby="reviews-h">
        <div class="section__head">
            <h2 id="reviews-h"><?php esc_html_e('Wartościowe książki', 'rozgadana-jana'); ?></h2>
            <a class="more" href="<?php echo esc_url(home_url('/ksiazki/')); ?>"><?php esc_html_e('Wszystkie recenzje →', 'rozgadana-jana'); ?></a>
        </div>
        <div class="cover-shelf">
            <?php
            $rj_reviews = new WP_Query(array(
                'post_type'      => 'recenzja',
                'posts_per_page' => 4,
                'no_found_rows'  => true,
            ));
            if ($rj_reviews->have_posts()) :
                while ($rj_reviews->have_posts()) :
                    $rj_reviews->the_post();
                    get_template_part('template-parts/review-cover', null, array('variant' => 'shelf'));
                endwhile;
                wp_reset_postdata();
            else :
                get_template_part('template-parts/content', 'none');
            endif;
            ?>
        </div>
    </section>

    <?php get_template_part('template-parts/about-strip'); ?>

</main>
<?php get_footer(); ?>
