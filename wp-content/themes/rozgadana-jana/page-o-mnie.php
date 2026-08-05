<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <?php while (have_posts()) : the_post(); ?>
        <header class="about-head">
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('rj-cover', array('class' => 'about-head__photo', 'alt' => esc_attr__('Autorka bloga Rozgadana Jana', 'rozgadana-jana'))); ?>
            <?php else : ?>
                <img class="about-head__photo"
                     src="<?php echo esc_url(get_theme_file_uri('assets/images/author.jpg')); ?>"
                     alt="<?php esc_attr_e('Autorka bloga Rozgadana Jana', 'rozgadana-jana'); ?>">
            <?php endif; ?>
            <div class="about-head__text">
                <p class="eyebrow"><?php esc_html_e('Poznaj mnie', 'rozgadana-jana'); ?></p>
                <h1 class="article__title"><?php the_title(); ?></h1>
                <div class="about-head__bio"><?php the_content(); ?></div>
            </div>
        </header>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
