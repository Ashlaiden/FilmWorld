<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Admin Menu Structure
|--------------------------------------------------------------------------
|
| FilmWorld          → داشبورد، مدیریت اعضا، تنظیمات پرداخت
| دسته‌بندی‌ها        → ژانرها، کشورها، وضعیت دوبله
|
*/

function filmworld_admin_menu()
{
    /*
    |==============================
    | FilmWorld Menu
    |==============================
    */

    add_menu_page(
        'FilmWorld',
        'FilmWorld',
        'manage_options',
        'filmworld-dashboard',
        'filmworld_dashboard_page',
        'dashicons-video-alt2',
        25
    );

    // 1. داشبورد (اولین زیرمنو — همیشه اول باشد)
    add_submenu_page(
        'filmworld-dashboard',
        'داشبورد',
        'داشبورد',
        'manage_options',
        'filmworld-dashboard',
        'filmworld_dashboard_page'
    );

    // 2. مدیریت اعضا
    add_submenu_page(
        'filmworld-dashboard',
        'مدیریت اعضا',
        'مدیریت اعضا',
        'manage_options',
        'filmworld-members',
        'filmworld_admin_members_page'
    );

    // 3. تنظیمات پرداخت
    add_submenu_page(
        'filmworld-dashboard',
        'تنظیمات پرداخت',
        'تنظیمات پرداخت',
        'manage_options',
        'filmworld-payment',
        'filmworld_payment_settings_page'
    );

    // 4. پرداخت‌ها
    add_submenu_page(
        'filmworld-dashboard',
        'پرداخت‌ها',
        'پرداخت‌ها',
        'manage_options',
        'filmworld-payments',
        'filmworld_admin_payments_page'
    );

    // 5. اشتراک‌ها
    add_submenu_page(
        'filmworld-dashboard',
        'اشتراک‌ها',
        'اشتراک‌ها',
        'manage_options',
        'filmworld-subscriptions',
        'filmworld_admin_subscriptions_page'
    );

    /*
    |==============================
    | دسته‌بندی‌ها (منوی جداگانه)
    |==============================
    */

    add_menu_page(
        'دسته‌بندی‌ها',
        'دسته‌بندی‌ها',
        'manage_categories',
        'filmworld-categories',
        'filmworld_categories_parent_redirect',
        'dashicons-tag',
        25
    );

    // زیرمنوها — مستقیماً به صفحه مدیریت تاکسونومی لینک می‌شوند
    add_submenu_page(
        'filmworld-categories',
        'ژانرها',
        'ژانرها',
        'manage_categories',
        'edit-tags.php?taxonomy=genre',
        ''
    );

    add_submenu_page(
        'filmworld-categories',
        'کشورها',
        'کشورها',
        'manage_categories',
        'edit-tags.php?taxonomy=country',
        ''
    );

    add_submenu_page(
        'filmworld-categories',
        'وضعیت دوبله',
        'وضعیت دوبله',
        'manage_categories',
        'edit-tags.php?taxonomy=dubbed',
        ''
    );
}

add_action('admin_menu', 'filmworld_admin_menu');

/*
|--------------------------------------------------------------------------
| Redirect: دسته‌بندی‌ها parent → first submenu (ژانرها)
|--------------------------------------------------------------------------
*/

function filmworld_categories_parent_redirect()
{
    wp_redirect(admin_url('edit-tags.php?taxonomy=genre'));
    exit;
}

/*
|--------------------------------------------------------------------------
| Dashboard Page
|--------------------------------------------------------------------------
*/

function filmworld_dashboard_page()
{
    ?>
    <div class="wrap">
        <h1>FilmWorld <span style="font-size:0.7em;color:#888;">v6.0.0</span></h1>
        <p>به پنل مدیریت FilmWorld خوش آمدید.</p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;margin-top:20px;">
            <div style="background:#fff;border:1px solid #ccd0d4;border-radius:8px;padding:20px;">
                <h3 style="margin-top:0;">افزودن فیلم</h3>
                <p><a href="<?php echo admin_url('post-new.php?post_type=movie'); ?>" class="button button-primary">فیلم جدید</a></p>
            </div>
            <div style="background:#fff;border:1px solid #ccd0d4;border-radius:8px;padding:20px;">
                <h3 style="margin-top:0;">افزودن سریال</h3>
                <p><a href="<?php echo admin_url('post-new.php?post_type=series'); ?>" class="button button-primary">سریال جدید</a></p>
            </div>
            <div style="background:#fff;border:1px solid #ccd0d4;border-radius:8px;padding:20px;">
                <h3 style="margin-top:0;">مدیریت اعضا</h3>
                <p><a href="<?php echo admin_url('admin.php?page=filmworld-members'); ?>" class="button">تنظیم اشتراک کاربران</a></p>
            </div>
            <div style="background:#fff;border:1px solid #ccd0d4;border-radius:8px;padding:20px;">
                <h3 style="margin-top:0;">تنظیمات پرداخت</h3>
                <p><a href="<?php echo admin_url('admin.php?page=filmworld-payment'); ?>" class="button">درگاه‌ها و پلن‌ها</a></p>
            </div>
        </div>
    </div>
    <?php
}