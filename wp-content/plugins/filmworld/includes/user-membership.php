<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Favorites / Watchlist (AJAX)
|--------------------------------------------------------------------------
*/

function filmworld_toggle_favorite() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['redirect' => wp_login_url()]);
    }

    check_ajax_referer('filmworld_nonce', 'nonce');

    $post_id = intval($_POST['post_id'] ?? 0);
    if (!$post_id) {
        wp_send_json_error(['message' => 'پست نامعتبر.']);
    }

    $user_id   = get_current_user_id();
    $favorites = get_user_meta($user_id, 'filmworld_favorites', true);

    if (!is_array($favorites)) {
        $favorites = [];
    }

    if (in_array($post_id, $favorites)) {
        $favorites = array_diff($favorites, [$post_id]);
        $is_fav = false;
    } else {
        $favorites[] = $post_id;
        $is_fav = true;
    }

    update_user_meta($user_id, 'filmworld_favorites', array_values($favorites));

    wp_send_json_success(['is_favorited' => $is_fav]);
}
add_action('wp_ajax_filmworld_toggle_favorite', 'filmworld_toggle_favorite');

/*
|--------------------------------------------------------------------------
| AJAX: Update User Profile
|--------------------------------------------------------------------------
*/

function filmworld_ajax_update_profile() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'لطفاً وارد شوید.']);
    }

    check_ajax_referer('filmworld_nonce', 'nonce');

    $user_id    = get_current_user_id();
    $first_name = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name  = sanitize_text_field($_POST['last_name'] ?? '');

    $result = wp_update_user([
        'ID'           => $user_id,
        'first_name'   => $first_name,
        'last_name'    => $last_name,
        'display_name' => trim($first_name . ' ' . $last_name) ?: null,
    ]);

    if (is_wp_error($result)) {
        wp_send_json_error(['message' => 'خطا در ذخیره اطلاعات.']);
    }

    wp_send_json_success(['message' => 'اطلاعات با موفقیت ذخیره شد.']);
}
add_action('wp_ajax_filmworld_update_profile', 'filmworld_ajax_update_profile');