<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Auto Slug for Movie & Series
|--------------------------------------------------------------------------
*/

function filmworld_generate_movie_slug($post_id)
{
    $post_type = get_post_type($post_id);
    if (!in_array($post_type, ['movie', 'series'])) {
        return;
    }

    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    $post = get_post($post_id);
    if (!$post) return;

    if ($post->post_name !== sanitize_title($post->post_title)) {
        return;
    }

    $english_name = get_field('english_name', $post_id);
    if (empty($english_name)) return;

    remove_action('acf/save_post', 'filmworld_generate_movie_slug', 20);

    wp_update_post([
        'ID'        => $post_id,
        'post_name' => sanitize_title($english_name),
    ]);

    add_action('acf/save_post', 'filmworld_generate_movie_slug', 20);
}

add_action('acf/save_post', 'filmworld_generate_movie_slug', 20);

/*
|--------------------------------------------------------------------------
| Auto Title for Movie Download Links
| Format: "Movie Name - 1080p"
|--------------------------------------------------------------------------
*/

function filmworld_auto_title_movie_link($post_id, $post, $update)
{
    if ($post->post_type !== 'movie_link') return;
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;
    if ($update) return;

    $movie_id = get_field('movie', $post_id);
    if (!$movie_id) return;

    $movie   = get_post($movie_id);
    $quality = get_field('quality', $post_id);
    if (!$movie) return;

    $title = $movie->post_title;
    if (!empty($quality)) {
        $title .= ' - ' . $quality;
    }

    remove_action('save_post', 'filmworld_auto_title_movie_link', 20);

    wp_update_post([
        'ID'         => $post_id,
        'post_title' => $title,
    ]);

    add_action('save_post', 'filmworld_auto_title_movie_link', 20, 3);
}

add_action('save_post', 'filmworld_auto_title_movie_link', 20, 3);

/*
|--------------------------------------------------------------------------
| Auto Title for Series Download Links
| Format: "Series Name - S01E05 - 1080p"
|--------------------------------------------------------------------------
*/

function filmworld_auto_title_series_link($post_id, $post, $update)
{
    if ($post->post_type !== 'series_link') return;
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;
    if ($update) return;

    $series_id = get_field('series', $post_id);
    if (!$series_id) return;

    $series  = get_post($series_id);
    $season  = get_field('season', $post_id);
    $episode = get_field('episode', $post_id);
    $quality = get_field('quality', $post_id);
    if (!$series) return;

    $title = $series->post_title;

    if (!empty($season)) {
        $title .= ' - ' . filmworld_format_season($season);
    }
    if (!empty($episode)) {
        $title .= filmworld_format_episode($episode);
    }
    if (!empty($quality)) {
        $title .= ' - ' . $quality;
    }

    remove_action('save_post', 'filmworld_auto_title_series_link', 20);

    wp_update_post([
        'ID'         => $post_id,
        'post_title' => $title,
    ]);

    add_action('save_post', 'filmworld_auto_title_series_link', 20, 3);
}

add_action('save_post', 'filmworld_auto_title_series_link', 20, 3);