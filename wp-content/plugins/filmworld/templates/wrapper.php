<?php
/**
 * Wrapper template for main FilmWorld pages (front, archive, single, search)
 * This replaces get_header()/get_footer() with our own header/footer
 */

if (!defined('ABSPATH')) exit;

// Determine content template
$content_template = '';
if (is_front_page()) {
    $content_template = FILMWORLD_PLUGIN_PATH . 'templates/front-page.php';
} elseif (is_singular('movie')) {
    $content_template = FILMWORLD_PLUGIN_PATH . 'templates/single-movie.php';
} elseif (is_singular('series')) {
    $content_template = FILMWORLD_PLUGIN_PATH . 'templates/single-series.php';
} elseif (is_post_type_archive('movie') || is_post_type_archive('series') || is_tax(['genre', 'country', 'dubbed'])) {
    $content_template = FILMWORLD_PLUGIN_PATH . 'templates/archive-movie.php';
} elseif (is_search()) {
    $content_template = FILMWORLD_PLUGIN_PATH . 'templates/search.php';
}

if (empty($content_template) || !file_exists($content_template)) {
    wp_die('قالب مورد نظر یافت نشد.');
}

include FILMWORLD_PLUGIN_PATH . 'templates/header.php';
include $content_template;
include FILMWORLD_PLUGIN_PATH . 'templates/footer.php';