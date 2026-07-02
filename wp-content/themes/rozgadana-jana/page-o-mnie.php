<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <?php while (have_posts()) : the_post(); ?>
        <header class="page-head">
            <p class="eyebrow"><?php esc_html_e('O mnie', 'rozgadana-jana'); ?></p>
            <h1><?php the_title(); ?></h1>
        </header>
        <div class="about">
            <img class="about__photo"
                 src="<?php echo esc_url(get_theme_file_uri('assets/images/author.jpg')); ?>"
                 alt="<?php esc_attr_e('Autorka bloga Rozgadana Jana', 'rozgadana-jana'); ?>">
            <div class="about__text article__content">
                <?php the_content(); ?>
                <div class="site-footer__social" style="margin-top:18px"><?php rj_social_links(); ?></div>
            </div>
        </div>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
