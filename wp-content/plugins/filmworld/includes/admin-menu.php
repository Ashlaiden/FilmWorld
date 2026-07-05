<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Movie & Series Taxonomies Menu
|--------------------------------------------------------------------------
*/

function filmworld_taxonomy_admin_menu()
{
    add_menu_page(
        'مشخصات فیلم و سریال',
        'مشخصات فیلم و سریال',
        'manage_categories',
        'filmworld-taxonomies',
        '__return_null',
        'dashicons-category',
        25
    );

    add_submenu_page(
        'filmworld-taxonomies',
        'ژانرها',
        'ژانرها',
        'manage_categories',
        'edit-tags.php?taxonomy=genre'
    );

    add_submenu_page(
        'filmworld-taxonomies',
        'کشورها',
        'کشورها',
        'manage_categories',
        'edit-tags.php?taxonomy=country'
    );

    add_submenu_page(
        'filmworld-taxonomies',
        'وضعیت دوبله',
        'وضعیت دوبله',
        'manage_categories',
        'edit-tags.php?taxonomy=dubbed'
    );
}

add_action('admin_menu', 'filmworld_taxonomy_admin_menu');