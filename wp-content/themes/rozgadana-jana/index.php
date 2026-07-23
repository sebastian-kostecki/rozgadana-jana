<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <?php if ( have_posts() ) : ?>
        <div class="row-list">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part( 'template-parts/card', 'post' ); ?>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
    <?php else : ?>
        <?php get_template_part( 'template-parts/content', 'none' ); ?>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
