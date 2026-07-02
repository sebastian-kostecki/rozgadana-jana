<?php declare(strict_types=1); ?>
<article <?php post_class('review-card'); ?>>
    <div class="review-card__cover">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('rj-cover', array('alt' => esc_attr(get_the_title()))); ?>
        <?php else : ?>
            <span><?php the_title(); ?></span>
        <?php endif; ?>
    </div>
    <div class="review-card__body">
        <h3 class="review-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <?php $rj_author = rj_review_book_author(get_the_ID()); ?>
        <?php if ($rj_author !== '') : ?>
            <div class="review-card__by"><?php echo esc_html(sprintf(__('aut. %s', 'rozgadana-jana'), $rj_author)); ?></div>
        <?php endif; ?>
        <p class="review-card__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18, '…')); ?></p>
    </div>
</article>
