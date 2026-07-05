<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Genre
|--------------------------------------------------------------------------
*/

function filmworld_register_genre_taxonomy()
{
    register_taxonomy(
        'genre',
        ['movie', 'series'],
        [
            'labels' => [
                'name'          => 'ژانرها',
                'singular_name' => 'ژانر',
                'menu_name'     => 'ژانرها',
            ],

            'public'              => true,
            'hierarchical'        => true,

            'show_ui'             => true,
            'show_admin_column'   => true,
            'show_in_menu'        => false,

            'show_in_rest'        => true,

            'rewrite' => [
                'slug' => 'genre',
            ],
        ]
    );
}

/*
|--------------------------------------------------------------------------
| Country
|--------------------------------------------------------------------------
*/

function filmworld_register_country_taxonomy()
{
    register_taxonomy(
        'country',
        ['movie', 'series'],
        [
            'labels' => [
                'name'          => 'کشورها',
                'singular_name' => 'کشور',
                'menu_name'     => 'کشورها',
            ],

            'public'              => true,
            'hierarchical'        => false,

            'show_ui'             => true,
            'show_admin_column'   => true,
            'show_in_menu'        => false,

            'show_in_rest'        => true,

            'rewrite' => [
                'slug' => 'country',
            ],
        ]
    );
}

/*
|--------------------------------------------------------------------------
| Dubbed
|--------------------------------------------------------------------------
*/

function filmworld_register_dubbed_taxonomy()
{
    register_taxonomy(
        'dubbed',
        ['movie', 'series'],
        [
            'labels' => [
                'name'          => 'وضعیت دوبله',
                'singular_name' => 'وضعیت دوبله',
                'menu_name'     => 'وضعیت دوبله',
            ],

            'public'              => true,
            'hierarchical'        => false,

            'show_ui'             => true,
            'show_admin_column'   => true,
            'show_in_menu'        => false,

            'show_in_rest'        => true,

            'rewrite' => [
                'slug' => 'dubbed',
            ],
        ]
    );
}

add_action('init', 'filmworld_register_genre_taxonomy');
add_action('init', 'filmworld_register_country_taxonomy');
add_action('init', 'filmworld_register_dubbed_taxonomy');