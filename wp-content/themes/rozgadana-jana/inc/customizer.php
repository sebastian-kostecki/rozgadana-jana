<?php
/**
 * Theme Customizer: social profile URLs.
 *
 * @package RozgadanaJana
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

add_action('customize_register', static function (WP_Customize_Manager $wp_customize): void {
    $wp_customize->add_section(
        'rj_social',
        array(
            'title'    => __('Media społecznościowe', 'rozgadana-jana'),
            'priority' => 40,
        )
    );

    $settings = array(
        'rj_facebook_url'  => array(
            'label'   => __('Facebook URL', 'rozgadana-jana'),
            'default' => 'https://www.facebook.com/rozgadanajana/',
        ),
        'rj_instagram_url' => array(
            'label'   => __('Instagram URL', 'rozgadana-jana'),
            'default' => 'https://www.instagram.com/rozgadana_jana/',
        ),
    );

    foreach ($settings as $id => $args) {
        $wp_customize->add_setting(
            $id,
            array(
                'default'           => $args['default'],
                'sanitize_callback' => 'esc_url_raw',
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            $id,
            array(
                'label'   => $args['label'],
                'section' => 'rj_social',
                'type'    => 'url',
            )
        );
    }
});
