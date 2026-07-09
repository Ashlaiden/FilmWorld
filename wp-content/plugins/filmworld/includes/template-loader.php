<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Hide WordPress Admin Bar on Frontend
|--------------------------------------------------------------------------
*/

add_filter('show_admin_bar', function() {
    return current_user_can('manage_options');
});

/*
|--------------------------------------------------------------------------
| Template Wrapper System
| All FilmWorld pages use our own header/footer instead of the theme's
|--------------------------------------------------------------------------
*/

function filmworld_is_plugin_page()
{
    return is_front_page()
        || is_singular('movie')
        || is_singular('series')
        || is_post_type_archive('movie')
        || is_post_type_archive('series')
        || is_tax(['genre', 'country', 'dubbed'])
        || is_search()
        || get_query_var('filmworld_page');
}

function filmworld_template_wrapper($template)
{
    if (!filmworld_is_plugin_page()) {
        return $template;
    }

    // Check if this is an auth page (login/register/account)
    $fw_page = get_query_var('filmworld_page');
    if (!empty($fw_page)) {
        return FILMWORLD_PLUGIN_PATH . 'templates/wrapper-auth.php';
    }

    return FILMWORLD_PLUGIN_PATH . 'templates/wrapper.php';
}
add_filter('template_include', 'filmworld_template_wrapper', 999);

/*
|--------------------------------------------------------------------------
| Asset Enqueuing
|--------------------------------------------------------------------------
*/

function filmworld_enqueue_assets()
{
    if (!filmworld_is_plugin_page()) {
        return;
    }

    $plugin_url = plugin_dir_url(dirname(__FILE__));
    $version = '3.0.0';

    // Main stylesheet (dark theme by default)
    wp_enqueue_style('filmworld-common', $plugin_url . 'assets/css/common.css', [], $version);

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

    if (get_query_var('filmworld_page')) {
        wp_enqueue_style('filmworld-auth', $plugin_url . 'assets/css/auth.css', ['filmworld-common'], $version);
    }

    // Player JS on single pages
    if (is_singular('movie') || is_singular('series')) {
        wp_enqueue_script('filmworld-player', $plugin_url . 'assets/js/player.js', [], $version, true);
    }

    // Main JS (footer)
    wp_enqueue_script('filmworld-main', $plugin_url . 'assets/js/main.js', [], $version, true);
}

add_action('wp_enqueue_scripts', 'filmworld_enqueue_assets');

/*
|--------------------------------------------------------------------------
| Localize filmworld_ajax in wp_head so inline scripts can use it
| (main.js is loaded in footer, but inline scripts in templates run before footer)
|--------------------------------------------------------------------------
*/

add_action('wp_head', function() {
    if (!filmworld_is_plugin_page()) {
        return;
    }
    ?>
    <script type="text/javascript">
    var filmworld_ajax = <?php echo wp_json_encode([
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('filmworld_nonce'),
        'is_user'  => is_user_logged_in() ? '1' : '0',
    ]); ?>;
    </script>
    <?php
}, 1);

/*
|--------------------------------------------------------------------------
| Activation Hook
|--------------------------------------------------------------------------
*/

function filmworld_activate_plugin()
{
    filmworld_register_movie_post_type();
    filmworld_register_series_post_type();
    filmworld_register_series_link_post_type();
    filmworld_register_download_links_post_type();
    filmworld_register_genre_taxonomy();
    filmworld_register_country_taxonomy();
    filmworld_register_dubbed_taxonomy();
    filmworld_auth_rewrites();

    flush_rewrite_rules();
}