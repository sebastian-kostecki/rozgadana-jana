<?php declare(strict_types=1); ?>
<article <?php post_class( 'post-card' ); ?>>
    <a href="<?php the_permalink(); ?>"><?php the_title( '<h2 class="post-card__title">', '</h2>' ); ?></a>
</article>
