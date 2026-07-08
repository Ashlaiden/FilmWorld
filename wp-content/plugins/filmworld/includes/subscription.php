<?php

if (!defined('ABSPATH')) exit;

/*
|--------------------------------------------------------------------------
| Check Active Subscription
|--------------------------------------------------------------------------
*/
function filmworld_has_access($user_id = null)
{
    $user_id = $user_id ?: get_current_user_id();

    if (!$user_id) return false;

    // ادمین‌ها و ویرایشگرها همیشه دسترسی دارند
    if (user_can($user_id, 'manage_options') || user_can($user_id, 'edit_others_posts')) {
        return true;
    }

    $plan   = get_user_meta($user_id, 'filmworld_plan', true);
    $expire = get_user_meta($user_id, 'filmworld_expire', true);

    if (empty($plan) || $plan === 'none') {
        return false;
    }

    if (!empty($expire) && intval($expire) < time()) {
        return false;
    }

    return true;
}