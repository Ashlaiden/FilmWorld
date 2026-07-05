<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Handle Bulk Import
|--------------------------------------------------------------------------
*/

function filmworld_handle_series_bulk_import()
{
    if (!isset($_POST['filmworld_bulk_import'])) {
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_die('Access denied.');
    }

    check_admin_referer('filmworld_bulk_import');

    $series_id = isset($_POST['series']) ? intval($_POST['series']) : 0;
    $season    = isset($_POST['season']) ? intval($_POST['season']) : 1;
    $episode   = isset($_POST['episode_start']) ? intval($_POST['episode_start']) : 1;

    $quality   = sanitize_text_field($_POST['quality'] ?? '');
    $size      = sanitize_text_field($_POST['size'] ?? '');
    $encoder   = sanitize_text_field($_POST['encoder'] ?? '');
    $language  = sanitize_text_field($_POST['language'] ?? '');
    $subtitle  = isset($_POST['subtitle']) ? 1 : 0;

    $links_text = $_POST['links'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | Validate
    |--------------------------------------------------------------------------
    */

    if (!$series_id) {

        add_settings_error(
            'filmworld_bulk_import',
            'series',
            'لطفا یک سریال انتخاب کنید.',
            'error'
        );

        return;
    }

    $series = get_post($series_id);

    if (!$series) {

        add_settings_error(
            'filmworld_bulk_import',
            'series',
            'سریال انتخاب شده وجود ندارد.',
            'error'
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Clean Links
    |--------------------------------------------------------------------------
    */

    $links = filmworld_clean_links($links_text);

    if (empty($links)) {

        add_settings_error(
            'filmworld_bulk_import',
            'links',
            'هیچ لینک معتبری پیدا نشد.',
            'error'
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Prepare Result
    |--------------------------------------------------------------------------
    */

    $result = [

        'created' => 0,
        'exists'  => 0,
        'failed'  => 0,

    ];

    $series_title = $series->post_title;

    /*
    |--------------------------------------------------------------------------
    | Start Import
    |--------------------------------------------------------------------------
    */

    foreach ($links as $download_url) {

        /*
        |--------------------------------------------------------------------------
        | Duplicate Check
        |--------------------------------------------------------------------------
        */

        if (
            filmworld_episode_exists(
                $series_id,
                $season,
                $episode,
                $quality
            )
        ) {

            $result['exists']++;

            $episode++;

            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Generate Title
        |--------------------------------------------------------------------------
        */

        $title = filmworld_generate_episode_title(

            $series_title,
            $season,
            $episode,
            $quality

        );

        /*
        |--------------------------------------------------------------------------
        | Create Post
        |--------------------------------------------------------------------------
        */

        $post_id = wp_insert_post([
            'post_type'   => 'series_link',
            'post_status' => 'publish',
            'post_title'  => $title,
        ], true);

        /*
        |--------------------------------------------------------------------------
        | Insert Failed
        |--------------------------------------------------------------------------
        */

        if (is_wp_error($post_id)) {

            $result['failed']++;

            $episode++;

            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Save ACF Fields
        |--------------------------------------------------------------------------
        */

        update_field('series', $series_id, $post_id);
        update_field('season', $season, $post_id);
        update_field('episode', $episode, $post_id);

        update_field('quality', $quality, $post_id);
        update_field('size', $size, $post_id);
        update_field('encoder', $encoder, $post_id);

        update_field('language', $language, $post_id);
        update_field('subtitle', $subtitle, $post_id);

        update_field('download_url', esc_url_raw($download_url), $post_id);

        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        $result['created']++;

        $episode++;

    }

    /*
    |--------------------------------------------------------------------------
    | Show Result
    |--------------------------------------------------------------------------
    */

    $message = sprintf(

        'عملیات پایان یافت.<br><br>
        ایجاد شده: <strong>%d</strong><br>
        تکراری: <strong>%d</strong><br>
        خطا: <strong>%d</strong>',

        $result['created'],
        $result['exists'],
        $result['failed']

    );

    add_settings_error(
        'filmworld_bulk_import',
        'success',
        $message,
        'updated'
    );
}
/*
|--------------------------------------------------------------------------
| Register Handler
|--------------------------------------------------------------------------
*/

add_action(
    'admin_init',
    'filmworld_handle_series_bulk_import'
);