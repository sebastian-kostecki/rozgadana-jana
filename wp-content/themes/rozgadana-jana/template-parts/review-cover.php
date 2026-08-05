<?php declare(strict_types=1); ?>
<?php
/**
 * One book cover cell. Must be called inside a loop over the `recenzja` post type.
 *
 * Args:
 * - variant: 'shelf' (cover, title, author) | 'grid' (adds excerpt). Default 'shelf'.
 *
 * @var array{variant?: string}|null $args
 */
$args       = is_array($args ?? null) ? $args : array();
$rj_variant = (string) ($args['variant'] ?? 'shelf');
$rj_post_id = (int) get_the_ID();
$rj_author  = rj_review_book_author($rj_post_id);
?>
<article <?php post_class('cover-item'); ?>>
    <a class="cover-item__art" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('rj-cover', array('alt' => '')); ?>
        <?php endif; ?>
    </a>
    <h3 class="cover-item__title">
        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </h3>
    <?php if ($rj_author !== '') : ?>
        <p class="cover-item__by"><?php echo esc_html($rj_author); ?></p>
    <?php endif; ?>
    <?php if ($rj_variant === 'grid' && has_excerpt()) : ?>
        <p class="cover-item__excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
    <?php endif; ?>
</article>
