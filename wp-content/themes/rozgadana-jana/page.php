<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('article'); ?>>
            <h1 class="article__title"><?php the_title(); ?></h1>
            <div class="article__content"><?php the_content(); ?></div>
        </article>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
