<?php

if (!defined('ABSPATH')) {
    exit;
}

function filmworld_generate_movie_slug($post_id)
{
    // فقط برای فیلم
    if (get_post_type($post_id) !== 'movie') {
        return;
    }

    // جلوگیری از ذخیره خودکار و بازبینی‌ها
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    $post = get_post($post_id);

    if (!$post) {
        return;
    }

    // اگر قبلاً اسلاگ توسط مدیر تغییر کرده باشد، دیگر کاری نکن
    if ($post->post_name !== sanitize_title($post->post_title)) {
        return;
    }

    $english_name = get_field('english_name', $post_id);

    if (empty($english_name)) {
        return;
    }

    remove_action('acf/save_post', 'filmworld_generate_movie_slug', 20);

    wp_update_post(array(
        'ID'        => $post_id,
        'post_name' => sanitize_title($english_name),
    ));

    add_action('acf/save_post', 'filmworld_generate_movie_slug', 20);
}

add_action('acf/save_post', 'filmworld_generate_movie_slug', 20);