<?php declare(strict_types=1); ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e('Przejdź do treści', 'rozgadana-jana'); ?></a>
<header class="site-header">
    <div class="container">
        <a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>">
            <img class="site-brand__logo"
                 src="<?php echo esc_url(get_theme_file_uri('assets/images/logo-round.jpg')); ?>"
                 alt=""
                 width="40"
                 height="40">
            <span class="site-brand__name"><?php bloginfo('name'); ?></span>
        </a>
        <button class="nav-toggle" aria-expanded="false" aria-controls="primary-menu">
            <?php esc_html_e('Menu', 'rozgadana-jana'); ?>
        </button>
        <?php
        wp_nav_menu(array(
            'theme_location' => 'primary',
            'container'      => 'nav',
            'container_class'=> 'main-nav',
            'menu_id'        => 'primary-menu',
            'fallback_cb'    => false,
            'depth'          => 1,
        ));
        ?>
    </div>
</header>
