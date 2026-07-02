<?php
/**
 * Rozgadana Jana theme bootstrap.
 *
 * @package RozgadanaJana
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

define('RJ_THEME_VERSION', wp_get_theme()->get('Version'));
define('RJ_THEME_DIR', get_template_directory());

require_once RJ_THEME_DIR . '/inc/setup.php';
require_once RJ_THEME_DIR . '/inc/enqueue.php';
require_once RJ_THEME_DIR . '/inc/template-tags.php';
