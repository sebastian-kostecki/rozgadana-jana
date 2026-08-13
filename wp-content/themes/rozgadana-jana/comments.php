<?php
/**
 * Comments template for posts and reviews.
 *
 * @package RozgadanaJana
 */
declare(strict_types=1);

defined('ABSPATH') || exit;

if (post_password_required()) {
    return;
}

$rj_count = (int) get_comments_number();
$rj_have  = have_comments();
if ($rj_count < 1 && !$rj_have && !comments_open()) {
    return;
}
?>
<section class="comments" id="comments">
    <?php if ($rj_have) : ?>
        <?php if ($rj_count > 0) : ?>
            <h2 class="comments__title">
                <?php
                echo esc_html(
                    sprintf(
                        /* translators: %s: number of comments */
                        _n('%s komentarz', '%s komentarzy', $rj_count, 'rozgadana-jana'),
                        number_format_i18n($rj_count)
                    )
                );
                ?>
            </h2>
        <?php endif; ?>
        <ol class="comments__list">
            <?php
            wp_list_comments(array(
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 0,
            ));
            ?>
        </ol>
        <?php
        the_comments_pagination(array(
            'prev_text' => __('Poprzednie', 'rozgadana-jana'),
            'next_text' => __('Następne', 'rozgadana-jana'),
        ));
        ?>
    <?php endif; ?>

    <?php if (comments_open()) : ?>
        <?php
        comment_form(array(
            'title_reply'          => __('Zostaw komentarz', 'rozgadana-jana'),
            'title_reply_to'       => __('Odpowiedź dla %s', 'rozgadana-jana'),
            'label_submit'         => __('Opublikuj komentarz', 'rozgadana-jana'),
            'class_submit'         => 'btn',
            'class_container'      => 'comments__form',
            'comment_notes_before' => '',
        ));
        ?>
    <?php else : ?>
        <p class="comments__closed"><?php esc_html_e('Komentarze są zamknięte.', 'rozgadana-jana'); ?></p>
    <?php endif; ?>
</section>
