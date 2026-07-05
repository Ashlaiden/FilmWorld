<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Movie Post Type
 */
function filmworld_register_movie_post_type()
{
    $labels = array(
        'name'               => 'فیلم‌ها',
        'singular_name'      => 'فیلم',
        'menu_name'          => 'فیلم‌ها',
        'add_new'            => 'افزودن فیلم',
        'add_new_item'       => 'افزودن فیلم جدید',
        'edit_item'          => 'ویرایش فیلم',
        'new_item'           => 'فیلم جدید',
        'view_item'          => 'مشاهده فیلم',
        'search_items'       => 'جستجوی فیلم',
        'not_found'          => 'فیلمی پیدا نشد',
        'not_found_in_trash' => 'فیلمی در زباله‌دان نیست',
    );

    $args = array(
        'labels' => $labels,

        'public' => true,

        'menu_icon' => 'dashicons-video-alt2',

        'supports' => array(
            'title',
            'editor',
            'thumbnail',
            'comments'
        ),

        'has_archive' => true,

        'rewrite' => array(
            'slug' => 'movies'
        ),

        'show_in_rest' => true,
    );

    register_post_type('movie', $args);
}

function filmworld_register_series_post_type()
{
    register_post_type('series', [

        'labels' => [

            'name'               => 'سریال‌ها',
            'singular_name'      => 'سریال',
            'add_new'            => 'افزودن سریال',
            'add_new_item'       => 'افزودن سریال جدید',
            'edit_item'          => 'ویرایش سریال',
            'new_item'           => 'سریال جدید',
            'view_item'          => 'مشاهده سریال',
            'search_items'       => 'جستجوی سریال',
            'not_found'          => 'سریالی پیدا نشد',
            'menu_name'          => 'سریال‌ها',

        ],

        'public' => true,

        'has_archive' => true,

        'rewrite' => [

            'slug' => 'series'

        ],

        'supports' => [

            'title',
            'editor',
            'thumbnail'

        ],

        'menu_icon' => 'dashicons-video-alt3',

        'show_in_rest' => true,

    ]);
}

function filmworld_register_series_link_post_type()
{
    register_post_type('series_link', [

        'labels' => [

            'name' => 'لینک های دانلود سریال',
            'singular_name' => 'لینک دانلود سریال',
            'add_new_item' => 'افزودن لینک سریال',

        ],

        'public' => false,

        'show_ui' => true,

        'show_in_menu' => 'edit.php?post_type=series',

        'supports' => [

            'title'

        ],

        'menu_icon' => 'dashicons-download',

    ]);
}

add_action('init', 'filmworld_register_series_link_post_type');
add_action('init', 'filmworld_register_series_post_type');
add_action('init', 'filmworld_register_movie_post_type');