<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Rewrite Rules
|--------------------------------------------------------------------------
*/

function filmworld_auth_rewrites()
{
    add_rewrite_rule('^login/?$', 'index.php?filmworld_page=login', 'top');
    add_rewrite_rule('^register/?$', 'index.php?filmworld_page=register', 'top');
    add_rewrite_rule('^account/?$', 'index.php?filmworld_page=account', 'top');
    add_rewrite_tag('%filmworld_page%', '([^?]+)');
}
add_action('init', 'filmworld_auth_rewrites');

/*
|--------------------------------------------------------------------------
| Force flush rewrite rules if needed (fixes blank pages)
|--------------------------------------------------------------------------
*/

add_action('wp_loaded', function() {
    $rules = get_option('rewrite_rules');
    if (!isset($rules['^login/?$']) || !isset($rules['^register/?$']) || !isset($rules['^account/?$'])) {
        flush_rewrite_rules();
    }
});

/*
|--------------------------------------------------------------------------
| Template Redirect
|--------------------------------------------------------------------------
*/

function filmworld_page_template_redirect($template)
{
    $page = get_query_var('filmworld_page');

    if (empty($page)) {
        return $template;
    }

    // If logged in and visiting login/register, redirect to account
    if (is_user_logged_in() && in_array($page, ['login', 'register'])) {
        wp_redirect(home_url('/account/'));
        exit;
    }

    // If not logged in and visiting account, redirect to login
    if (!is_user_logged_in() && $page === 'account') {
        wp_redirect(home_url('/login/'));
        exit;
    }

    $file_map = [
        'login'    => 'login.php',
        'register' => 'register.php',
        'account'  => 'account.php',
    ];

    if (isset($file_map[$page])) {
        $path = FILMWORLD_PLUGIN_PATH . 'templates/' . $file_map[$page];
        if (file_exists($path)) {
            return $path;
        }
    }

    return $template;
}
add_filter('template_include', 'filmworld_page_template_redirect');

/*
|--------------------------------------------------------------------------
| AJAX Login
|--------------------------------------------------------------------------
*/

function filmworld_ajax_login()
{
    check_ajax_referer('filmworld_nonce', 'nonce');

    $username = sanitize_text_field($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember']);

    if (empty($username) || empty($password)) {
        wp_send_json_error(['message' => 'نام کاربری و رمز عبور را وارد کنید.']);
    }

    $user = wp_signon([
        'user_login'    => $username,
        'user_password' => $password,
        'remember'      => $remember,
    ]);

    if (is_wp_error($user)) {
        wp_send_json_error(['message' => 'نام کاربری یا رمز عبور اشتباه است.']);
    }

    wp_send_json_success(['redirect' => home_url('/account/')]);
}
add_action('wp_ajax_nopriv_filmworld_login', 'filmworld_ajax_login');
add_action('wp_ajax_filmworld_login', 'filmworld_ajax_login');

/*
|--------------------------------------------------------------------------
| AJAX Register
|--------------------------------------------------------------------------
*/

function filmworld_ajax_register()
{
    check_ajax_referer('filmworld_nonce', 'nonce');

    $username = sanitize_text_field($_POST['username'] ?? '');
    $email    = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        wp_send_json_error(['message' => 'تمام فیلدها الزامی هستند.']);
    }
    if (strlen($username) < 3) {
        wp_send_json_error(['message' => 'نام کاربری باید حداقل ۳ کاراکتر باشد.']);
    }
    if (!is_email($email)) {
        wp_send_json_error(['message' => 'ایمیل معتبر وارد کنید.']);
    }
    if (strlen($password) < 6) {
        wp_send_json_error(['message' => 'رمز عبور باید حداقل ۶ کاراکتر باشد.']);
    }
    if ($password !== $confirm) {
        wp_send_json_error(['message' => 'رمز عبور و تکرار آن مطابقت ندارند.']);
    }
    if (username_exists($username)) {
        wp_send_json_error(['message' => 'این نام کاربری قبلاً ثبت شده است.']);
    }
    if (email_exists($email)) {
        wp_send_json_error(['message' => 'این ایمیل قبلاً ثبت شده است.']);
    }

    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        wp_send_json_error(['message' => 'خطا در ثبت‌نام. دوباره تلاش کنید.']);
    }

    wp_set_auth_cookie($user_id, true);
    wp_send_json_success(['redirect' => home_url('/account/')]);
}
add_action('wp_ajax_nopriv_filmworld_register', 'filmworld_ajax_register');
add_action('wp_ajax_filmworld_register', 'filmworld_ajax_register');

/*
|--------------------------------------------------------------------------
| Redirect default wp-login.php
|--------------------------------------------------------------------------
*/

function filmworld_redirect_login()
{
    global $pagenow;

    if ($pagenow !== 'wp-login.php') return;
    if (isset($_REQUEST['action']) && in_array($_REQUEST['action'], ['logout', 'lostpassword', 'rp', 'resetpass'])) return;
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') return;
    if (is_user_logged_in()) return;

    wp_redirect(home_url('/login/'));
    exit;
}
add_action('init', 'filmworld_redirect_login');

function filmworld_logout_redirect($redirect, $requested, $user)
{
    return home_url('/login/');
}
add_filter('logout_redirect', 'filmworld_logout_redirect', 10, 3);