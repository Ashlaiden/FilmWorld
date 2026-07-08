<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Template Loaders
|--------------------------------------------------------------------------
*/

function filmworld_load_movie_template($template)
{
    if (is_singular('movie')) {
        $custom_template = plugin_dir_path(dirname(__FILE__)) . 'templates/single-movie.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}

function filmworld_load_series_template($template)
{
    if (is_singular('series')) {
        $custom_template = plugin_dir_path(dirname(__FILE__)) . 'templates/single-series.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}

function filmworld_load_movie_archive($template)
{
    if (is_post_type_archive('movie')) {
        $archive_template = plugin_dir_path(dirname(__FILE__)) . 'templates/archive-movie.php';
        if (file_exists($archive_template)) {
            return $archive_template;
        }
    }
    return $template;
}

function filmworld_load_series_archive($template)
{
    if (is_post_type_archive('series')) {
        $archive_template = plugin_dir_path(dirname(__FILE__)) . 'templates/archive-movie.php';
        if (file_exists($archive_template)) {
            return $archive_template;
        }
    }
    return $template;
}

function filmworld_load_front_page($template)
{
    if (is_front_page()) {
        $front_page_template = plugin_dir_path(dirname(__FILE__)) . 'templates/front-page.php';
        if (file_exists($front_page_template)) {
            return $front_page_template;
        }
    }
    return $template;
}

function filmworld_load_search_template($template)
{
    if (is_search()) {
        $search_template = plugin_dir_path(dirname(__FILE__)) . 'templates/search.php';
        if (file_exists($search_template)) {
            return $search_template;
        }
    }
    return $template;
}

function filmworld_load_taxonomy_template($template)
{
    $taxonomies = ['genre', 'country', 'dubbed'];
    if (is_tax($taxonomies)) {
        $archive_template = plugin_dir_path(dirname(__FILE__)) . 'templates/archive-movie.php';
        if (file_exists($archive_template)) {
            return $archive_template;
        }
    }
    return $template;
}

/*
|--------------------------------------------------------------------------
| Asset Enqueuing
|--------------------------------------------------------------------------
*/

function filmworld_enqueue_assets()
{
    $plugin_url = plugin_dir_url(dirname(__FILE__));
    $version = '2.0.0';

    // Common styles (all pages)
    wp_enqueue_style('filmworld-common', $plugin_url . 'assets/css/common.css', [], $version);

    // Dark mode (auto + manual toggle)
    wp_enqueue_style('filmworld-dark', $plugin_url . 'assets/css/dark.css', ['filmworld-common'], $version);

    // Page-specific styles
    if (is_singular('movie') || is_singular('series')) {
        wp_enqueue_style('filmworld-single', $plugin_url . 'assets/css/single.css', ['filmworld-common'], $version);
    }

    if (is_front_page()) {
        wp_enqueue_style('filmworld-front-page', $plugin_url . 'assets/css/front-page.css', ['filmworld-common'], $version);
    }

    if (is_post_type_archive('movie') || is_post_type_archive('series') || is_tax(['genre', 'country', 'dubbed'])) {
        wp_enqueue_style('filmworld-archive', $plugin_url . 'assets/css/archive.css', ['filmworld-common'], $version);
    }

    if (is_search()) {
        wp_enqueue_style('filmworld-search', $plugin_url . 'assets/css/search.css', ['filmworld-common'], $version);
    }

    // Player JS on single pages
    if (is_singular('movie') || is_singular('series')) {
        wp_enqueue_script('filmworld-player', $plugin_url . 'assets/js/player.js', [], $version, true);
    }

    // Main JS (dark mode toggle, favorites, etc.)
    wp_enqueue_script('filmworld-main', $plugin_url . 'assets/js/main.js', [], $version, true);
    wp_localize_script('filmworld-main', 'filmworld_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('filmworld_nonce'),
        'is_user'  => is_user_logged_in() ? '1' : '0',
    ]);
}

/*
|--------------------------------------------------------------------------
| Hooks
|--------------------------------------------------------------------------
*/

add_filter('search_template', 'filmworld_load_search_template');
add_filter('single_template', 'filmworld_load_movie_template');
add_filter('single_template', 'filmworld_load_series_template');
add_filter('archive_template', 'filmworld_load_movie_archive');
add_filter('archive_template', 'filmworld_load_series_archive');
add_filter('frontpage_template', 'filmworld_load_front_page');
add_filter('taxonomy_template', 'filmworld_load_taxonomy_template');

add_action('wp_enqueue_scripts', 'filmworld_enqueue_assets');

/*
|--------------------------------------------------------------------------
| Activation Hook - Flush Rewrite Rules
|--------------------------------------------------------------------------
*/

function filmworld_activate_plugin()
{
    // Post types and taxonomies are registered on init, so we need to trigger them
    filmworld_register_movie_post_type();
    filmworld_register_series_post_type();
    filmworld_register_series_link_post_type();
    filmworld_register_download_links_post_type();
    filmworld_register_genre_taxonomy();
    filmworld_register_country_taxonomy();
    filmworld_register_dubbed_taxonomy();

    flush_rewrite_rules();
}