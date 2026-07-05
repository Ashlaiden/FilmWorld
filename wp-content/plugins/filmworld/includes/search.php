<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Search Movies & Series
|--------------------------------------------------------------------------
*/

function filmworld_search_content($query)
{
    if (
        !is_admin() &&
        $query->is_main_query() &&
        $query->is_search()
    ) {

        $query->set('post_type', [
            'movie',
            'series'
        ]);

    }
}

add_action('pre_get_posts', 'filmworld_search_content');