<?php

if (!defined('ABSPATH')) {
    exit;
}

function filmworld_register_download_links_post_type()
{
    $labels = [
        'name'               => 'لینک‌های دانلود',
        'singular_name'      => 'لینک دانلود',
        'menu_name'          => 'لینک‌های دانلود',
        'add_new'            => 'افزودن لینک',
        'add_new_item'       => 'افزودن لینک دانلود',
        'edit_item'          => 'ویرایش لینک دانلود',
        'new_item'           => 'لینک جدید',
        'view_item'          => 'مشاهده لینک',
        'search_items'       => 'جستجوی لینک',
        'not_found'          => 'لینکی پیدا نشد',
    ];

    register_post_type('movie_link', [

        'labels' => $labels,

        'public' => false,

        'show_ui' => true,

        'show_in_menu' => 'edit.php?post_type=movie',

        'supports' => [
            'title'
        ],

        'menu_icon' => 'dashicons-download',

        'show_in_rest' => false,
    ]);
}

add_action('init', 'filmworld_register_download_links_post_type');