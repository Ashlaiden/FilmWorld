<?php

/**
 * FilmWorld Plugin Uninstall
 * Cleans up all plugin data on deletion
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Delete custom post types and their meta
$post_types = ['movie', 'series', 'movie_link', 'series_link'];
foreach ($post_types as $pt) {
    $posts = get_posts([
        'post_type'   => $pt,
        'numberposts' => -1,
        'post_status' => 'any',
    ]);
    foreach ($posts as $post) {
        wp_delete_post($post->ID, true);
    }
}

// Delete taxonomy terms
$taxonomies = ['genre', 'country', 'dubbed'];
foreach ($taxonomies as $tax) {
    $terms = get_terms([
        'taxonomy'   => $tax,
        'hide_empty' => false,
    ]);
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            wp_delete_term($term->term_id, $tax);
        }
    }
}

// Delete user meta for all users
$meta_keys = [
    'filmworld_plan',
    'filmworld_expire',
    'filmworld_favorites',
    'filmworld_payments',
    'filmworld_pending_payment',
];

foreach ($meta_keys as $key) {
    $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key = '{$key}'");
}

// Delete options
$options = [
    'filmworld_zarinpal_merchant',
    'filmworld_zarinpal_sandbox',
    'filmworld_plans',
];

foreach ($options as $option) {
    delete_option($option);
}

// Flush rewrite rules
flush_rewrite_rules();