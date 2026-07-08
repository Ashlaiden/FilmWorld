<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Top-Level Menu: مشخصات فیلم و سریال
|--------------------------------------------------------------------------
*/

function filmworld_admin_menu()
{
    add_menu_page(
        'FilmWorld',
        'FilmWorld',
        'manage_categories',
        'filmworld-dashboard',
        'filmworld_dashboard_page',
        'dashicons-video-alt2',
        25
    );

    // زیرمنوها
    add_submenu_page(
        'filmworld-dashboard',
        'داشبورد',
        'داشبورد',
        'manage_categories',
        'filmworld-dashboard',
        'filmworld_dashboard_page'
    );

    add_submenu_page(
        'filmworld-dashboard',
        'ژانرها',
        'ژانرها',
        'manage_categories',
        'edit-tags.php?taxonomy=genre',
        ''
    );

    add_submenu_page(
        'filmworld-dashboard',
        'کشورها',
        'کشورها',
        'manage_categories',
        'edit-tags.php?taxonomy=country',
        ''
    );

    add_submenu_page(
        'filmworld-dashboard',
        'وضعیت دوبله',
        'وضعیت دوبله',
        'manage_categories',
        'edit-tags.php?taxonomy=dubbed',
        ''
    );

    add_submenu_page(
        'filmworld-dashboard',
        'تنظیمات پرداخت',
        'تنظیمات پرداخت',
        'manage_options',
        'filmworld-payment',
        'filmworld_payment_settings_page'
    );
}

add_action('admin_menu', 'filmworld_admin_menu');

/*
|--------------------------------------------------------------------------
| Dashboard Page (simple welcome)
|--------------------------------------------------------------------------
*/

function filmworld_dashboard_page()
{
    ?>
    <div class="wrap">
        <h1>FilmWorld <span style="font-size:0.7em;color:#888;">v2.0.0</span></h1>
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
                <h3 style="margin-top:0;">تنظیمات پرداخت</h3>
                <p><a href="<?php echo admin_url('admin.php?page=filmworld-payment'); ?>" class="button">درگاه زرین‌پال</a></p>
            </div>
        </div>
    </div>
    <?php
}