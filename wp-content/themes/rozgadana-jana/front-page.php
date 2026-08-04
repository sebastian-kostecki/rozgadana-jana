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

        <div class="filter">
            <a class="filter__chip is-active" href="<?php echo esc_url(home_url('/blog/')); ?>" data-filter="*" aria-current="true"><?php esc_html_e('Wszystko', 'rozgadana-jana'); ?></a>
            <?php
            $rj_chips = array(
                'codziennosc-z-bogiem'    => __('Codzienność z Bogiem', 'rozgadana-jana'),
                'macierzynstwo-i-rodzina' => __('Macierzyństwo i rodzina', 'rozgadana-jana'),
            );
            foreach ($rj_chips as $rj_slug => $rj_label) :
                $rj_term = get_category_by_slug($rj_slug);
                if (!$rj_term instanceof WP_Term) {
                    continue;
                }
                ?>
                <a class="filter__chip"
                   href="<?php echo esc_url(get_category_link($rj_term)); ?>"
                   data-filter="<?php echo esc_attr($rj_slug); ?>"><?php echo esc_html($rj_label); ?></a>
            <?php endforeach; ?>
        </div>

        <div class="row-list" id="rj-thoughts">
            <?php
            $rj_thoughts = new WP_Query(array(
                'posts_per_page'      => 5,
                'ignore_sticky_posts' => true,
                'no_found_rows'       => true,
                'post__not_in'        => $rj_featured_id > 0 ? array($rj_featured_id) : array(),
            ));
            if ($rj_thoughts->have_posts()) :
                $rj_i = 0;
                while ($rj_thoughts->have_posts()) :
                    $rj_thoughts->the_post();
                    $rj_i++;
                    get_template_part('template-parts/list-item', null, array(
                        'variant' => 'home',
                        'index'   => $rj_i,
                    ));
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

</main>
<?php get_footer(); ?>
