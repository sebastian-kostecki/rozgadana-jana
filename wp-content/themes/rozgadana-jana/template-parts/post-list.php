<?php declare(strict_types=1); ?>
<?php
/**
 * Renders the main query as typographic rows, with pagination and an empty state.
 *
 * Callers must not open their own loop — this part runs the main query itself.
 *
 * Args:
 * - group_by_year: bool — print a year heading whenever the year changes. Default true.
 *   Pass false where results are not in chronological order (search).
 *
 * @var array{group_by_year?: bool}|null $args
 */
$args           = is_array($args ?? null) ? $args : array();
$rj_group_years = (bool) ($args['group_by_year'] ?? true);
?>
<?php if (have_posts()) : ?>
    <div class="row-list<?php echo $rj_group_years ? ' row-list--archive' : ''; ?>">
        <?php
        $rj_prev_year = null;
        while (have_posts()) :
            the_post();

            if ($rj_group_years) {
                $rj_year = (int) get_the_date('Y');
                if (rj_needs_year_heading($rj_prev_year, $rj_year)) {
                    printf(
                        '<p class="year-heading"><span class="year-heading__label">%s</span><span class="year-heading__rule" aria-hidden="true"></span></p>',
                        esc_html((string) $rj_year)
                    );
                    $rj_prev_year = $rj_year;
                }
            }

            get_template_part('template-parts/list-item', null, array('variant' => 'archive'));
        endwhile;
        ?>
    </div>
    <?php the_posts_pagination(array('mid_size' => 1, 'prev_text' => '←', 'next_text' => '→')); ?>
<?php else : ?>
    <?php get_template_part('template-parts/content', 'none'); ?>
<?php endif; ?>
