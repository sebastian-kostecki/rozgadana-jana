<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <header class="page-head">
        <?php rj_breadcrumb(array(
            array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
            array('label' => __('Przemyślenia', 'rozgadana-jana'), 'url' => null),
        )); ?>
        <p class="eyebrow"><?php esc_html_e('Przemyślenia', 'rozgadana-jana'); ?></p>
        <h1><?php esc_html_e('Wszystkie wpisy', 'rozgadana-jana'); ?></h1>
        <p class="lead"><?php esc_html_e('Wszystko, co napisałam — od najnowszego. Jeśli szukasz konkretnego tematu, zawęź listę filtrem poniżej.', 'rozgadana-jana'); ?></p>
    </header>

    <div class="filter">
        <a class="filter__chip is-active" href="<?php echo esc_url(home_url('/blog/')); ?>" aria-current="true"><?php esc_html_e('Wszystko', 'rozgadana-jana'); ?></a>
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
            <a class="filter__chip" href="<?php echo esc_url(get_category_link($rj_term)); ?>"><?php echo esc_html($rj_label); ?></a>
        <?php endforeach; ?>
    </div>

    <?php get_template_part('template-parts/post-list'); ?>
</main>
<?php get_footer(); ?>
