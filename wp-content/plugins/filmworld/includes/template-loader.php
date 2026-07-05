<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Single Movie Template
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

/*
|--------------------------------------------------------------------------
| Single Series Template
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| Movie Archive Template
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| Front Page Template
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| ُSearch Page Template
|--------------------------------------------------------------------------
*/
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


/*
|--------------------------------------------------------------------------
| Assets
|--------------------------------------------------------------------------
*/

function filmworld_enqueue_assets()
{
    if (is_singular('movie')) {

        wp_enqueue_style(
            'filmworld-single-movie',
            plugins_url('../assets/css/single-movie.css', __FILE__),
            [],
            '1.0.0'
        );
    }

    if (is_post_type_archive('movie')) {

        wp_enqueue_style(
            'filmworld-archive-movie',
            plugins_url('../assets/css/archive.css', __FILE__),
            [],
            '1.0.0'
        );
    }

    if (is_front_page()) {

        wp_enqueue_style(
            'filmworld-front-page',
            plugins_url('../assets/css/front-page.css', __FILE__),
            [],
            '1.0.0'
        );
    }
    
}

add_filter('search_template', 'filmworld_load_search_template');
add_filter('single_template', 'filmworld_load_movie_template');
add_filter('single_template', 'filmworld_load_series_template');
add_filter('archive_template', 'filmworld_load_movie_archive');
add_filter('frontpage_template', 'filmworld_load_front_page');

add_action('wp_enqueue_scripts', 'filmworld_enqueue_assets');