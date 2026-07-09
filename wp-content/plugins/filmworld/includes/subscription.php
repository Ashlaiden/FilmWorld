<?php

if (!defined('ABSPATH')) exit;

/*
|--------------------------------------------------------------------------
| Check Active Subscription (day-based — no plan concept)
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

    $expire = get_user_meta($user_id, 'filmworld_expire', true);
    if (empty($expire) || intval($expire) < time()) {
        return false;
    }

    return true;
}

/*
|--------------------------------------------------------------------------
| Get Remaining Days
|--------------------------------------------------------------------------
*/
function filmworld_get_remaining_days($user_id = null)
{
    $user_id = $user_id ?: get_current_user_id();
    if (!$user_id) return 0;

    $expire = intval(get_user_meta($user_id, 'filmworld_expire', true));
    if ($expire <= time()) return 0;

    $remaining = ceil(($expire - time()) / 86400);
    return max(0, $remaining);
}

/*
|--------------------------------------------------------------------------
| Add Days to User (extends if active, starts fresh if expired)
|--------------------------------------------------------------------------
*/
function filmworld_add_days($user_id, $days)
{
    $user_id = intval($user_id);
    $days    = max(1, intval($days));

    $current_expire = intval(get_user_meta($user_id, 'filmworld_expire', true));
    $now = time();

    if ($current_expire > $now) {
        $new_expire = $current_expire + ($days * 86400);
    } else {
        $new_expire = $now + ($days * 86400);
    }

    update_user_meta($user_id, 'filmworld_expire', $new_expire);
    return $new_expire;
}

/*
|--------------------------------------------------------------------------
| Set Expiry (admin — replaces current expiry)
|--------------------------------------------------------------------------
*/
function filmworld_set_expiry($user_id, $days)
{
    $user_id = intval($user_id);
    $days    = max(0, intval($days));

    if ($days === 0) {
        update_user_meta($user_id, 'filmworld_expire', '');
        return 0;
    }

    $new_expire = time() + ($days * 86400);
    update_user_meta($user_id, 'filmworld_expire', $new_expire);
    return $new_expire;
}

/*
|--------------------------------------------------------------------------
| Add Days to ALL Non-Admin Users
|--------------------------------------------------------------------------
*/
function filmworld_add_days_to_all($days)
{
    $days = max(1, intval($days));
    $now  = time();

    $users = get_users([
        'role__not_in' => ['administrator'],
        'number'       => 0, // all
    ]);

    $count = 0;
    foreach ($users as $user) {
        $current = intval(get_user_meta($user->ID, 'filmworld_expire', true));
        if ($current > $now) {
            $new = $current + ($days * 86400);
        } else {
            $new = $now + ($days * 86400);
        }
        update_user_meta($user->ID, 'filmworld_expire', $new);
        $count++;
    }

    return $count;
}

/*
|--------------------------------------------------------------------------
| Day Packages (replaces old plans)
|--------------------------------------------------------------------------
*/
function filmworld_get_day_packages()
{
    $packages = get_option('filmworld_day_packages', []);

    if (empty($packages)) {
        $packages = [
            '30'  => ['days' => 30,  'price' => 49000],
            '90'  => ['days' => 90,  'price' => 119000],
            '365' => ['days' => 365, 'price' => 389000],
        ];
    }

    return $packages;
}

/*
|--------------------------------------------------------------------------
| Backward compat wrapper — old code may call filmworld_get_plans()
|--------------------------------------------------------------------------
*/
function filmworld_get_plans()
{
    $packages = filmworld_get_day_packages();
    $plans = [];
    foreach ($packages as $key => $pkg) {
        $plans[$key] = [
            'name'        => $pkg['days'] . ' روز',
            'price'       => $pkg['price'],
            'days'        => $pkg['days'],
            'description' => $pkg['days'] . ' روز دسترسی به تمام فیلم‌ها و سریال‌ها',
        ];
    }
    return $plans;
}