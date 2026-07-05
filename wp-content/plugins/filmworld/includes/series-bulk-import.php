<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Bulk Import Menu
|--------------------------------------------------------------------------
*/

function filmworld_series_import_menu()
{
    add_submenu_page(
        'edit.php?post_type=series',
        'افزودن فصل',
        'افزودن فصل',
        'manage_options',
        'filmworld-import-season',
        'filmworld_series_import_page'
    );
}

add_action('admin_menu', 'filmworld_series_import_menu');

/*
|--------------------------------------------------------------------------
| Bulk Import Page
|--------------------------------------------------------------------------
*/

function filmworld_series_import_page()
{
?>

<div class="wrap">

    <h1>درون‌ریزی فصل سریال</h1>

    <?php settings_errors('filmworld_bulk_import'); ?>

    <form method="post">

        <?php wp_nonce_field('filmworld_bulk_import'); ?>

        <input
            type="hidden"
            name="filmworld_bulk_import"
            value="1">

        <table class="form-table">

            <tr>

                <th>سریال</th>

                <td>

                    <?php

                    $series = get_posts([
                        'post_type'      => 'series',
                        'posts_per_page' => -1,
                        'orderby'        => 'title',
                        'order'          => 'ASC'
                    ]);

                    ?>

                    <select name="series" required>

                        <option value="">انتخاب کنید...</option>

                        <?php foreach ($series as $item) : ?>

                            <option value="<?php echo esc_attr($item->ID); ?>">

                                <?php echo esc_html($item->post_title); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </td>

            </tr>

            <tr>

                <th>فصل</th>

                <td>

                    <input
                        type="number"
                        name="season"
                        min="1"
                        value="1"
                        required>

                </td>

            </tr>

            <tr>

                <th>شروع شماره قسمت</th>

                <td>

                    <input
                        type="number"
                        name="episode_start"
                        min="1"
                        value="1"
                        required>

                </td>

            </tr>

            <tr>

                <th>کیفیت</th>

                <td>

                    <select name="quality">

                        <option value="480p">480p</option>
                        <option value="720p">720p</option>
                        <option value="1080p" selected>1080p</option>
                        <option value="2160p">2160p (4K)</option>

                    </select>

                </td>

            </tr>

            <tr>

                <th>حجم</th>

                <td>

                    <input
                        type="text"
                        name="size"
                        placeholder="مثلاً 1.8 GB">

                </td>

            </tr>

            <tr>

                <th>انکودر</th>

                <td>

                    <input
                        type="text"
                        name="encoder"
                        placeholder="PSA / x265 / YIFY">

                </td>

            </tr>

            <tr>

                <th>زبان</th>

                <td>

                    <select name="language">

                        <option value="original">زبان اصلی</option>

                        <option value="dubbed">دوبله فارسی</option>

                        <option value="dual">دو زبانه</option>

                    </select>

                </td>

            </tr>

            <tr>

                <th>زیرنویس فارسی</th>

                <td>

                    <label>

                        <input
                            type="checkbox"
                            name="subtitle"
                            value="1">

                        دارد

                    </label>

                </td>

            </tr>

            <tr>

                <th>لینک قسمت‌ها</th>

                <td>

                    <textarea
                        name="links"
                        rows="18"
                        style="width:100%;font-family:monospace;"
                        placeholder="هر لینک را در یک خط وارد کنید"
                        required></textarea>

                    <p class="description">

                        هر لینک را در یک خط وارد کنید.<br>
                        لینک اول = قسمت ۱<br>
                        لینک دوم = قسمت ۲<br>
                        لینک سوم = قسمت ۳<br>
                        ...

                    </p>

                </td>

            </tr>

        </table>

        <input
        type="hidden"
        name="filmworld_bulk_import"
        value="1">

        <?php submit_button('افزودن قسمت‌ها'); ?>

    </form>

</div>

<?php
}