<?php

if (!defined('ABSPATH')) exit;

/*
|--------------------------------------------------------------------------
| Admin: User Membership Management (Day-Based v6)
|--------------------------------------------------------------------------
*/

function filmworld_admin_members_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $admin_msg     = '';
    $admin_msg_type = '';

    // ---- Set Expiry (replace) ----
    if (isset($_POST['filmworld_set_days']) && check_admin_referer('filmworld_admin_members')) {
        $user_id = intval($_POST['user_id'] ?? 0);
        $days    = intval($_POST['days'] ?? 0);
        if ($user_id && $days >= 0) {
            filmworld_set_expiry($user_id, $days);
            $admin_msg     = $days > 0 ? 'اشتراک کاربر با موفقیت تنظیم شد.' : 'اشتراک کاربر حذف شد.';
            $admin_msg_type = 'success';
        } else {
            $admin_msg = 'مقادیر نامعتبر.'; $admin_msg_type = 'error';
        }
    }

    // ---- Add Days to One User ----
    if (isset($_POST['filmworld_add_days']) && check_admin_referer('filmworld_admin_members')) {
        $user_id  = intval($_POST['user_id_add'] ?? 0);
        $add_days = intval($_POST['add_days'] ?? 0);
        if ($user_id && $add_days > 0) {
            filmworld_add_days($user_id, $add_days);
            $admin_msg = $add_days . ' روز به اشتراک کاربر اضافه شد.'; $admin_msg_type = 'success';
        } else {
            $admin_msg = 'مقادیر نامعتبر.'; $admin_msg_type = 'error';
        }
    }

    // ---- Add Days to ALL Users ----
    if (isset($_POST['filmworld_add_days_all']) && check_admin_referer('filmworld_admin_members')) {
        $all_days = intval($_POST['all_days'] ?? 0);
        if ($all_days > 0) {
            $count = filmworld_add_days_to_all($all_days);
            $admin_msg = $all_days . ' روز به ' . $count . ' کاربر اضافه شد.'; $admin_msg_type = 'success';
        } else {
            $admin_msg = 'تعداد روز باید بیشتر از صفر باشد.'; $admin_msg_type = 'error';
        }
    }

    // ---- Remove Membership ----
    if (isset($_POST['filmworld_remove']) && check_admin_referer('filmworld_admin_members')) {
        $user_id = intval($_POST['user_id_remove'] ?? 0);
        if ($user_id) {
            filmworld_set_expiry($user_id, 0);
            $admin_msg = 'اشتراک کاربر حذف شد.'; $admin_msg_type = 'success';
        }
    }

    // ---- User List Search ----
    $search   = sanitize_text_field($_GET['s'] ?? '');
    $paged    = max(1, intval($_GET['paged'] ?? 1));
    $per_page = 20;

    $user_args = [
        'number'     => $per_page,
        'paged'      => $paged,
        'orderby'    => 'registered',
        'order'      => 'DESC',
    ];

    if (!empty($search)) {
        $user_args['search']         = '*' . $search . '*';
        $user_args['search_columns'] = ['user_login', 'user_email', 'display_name'];
    }

    // Filter: only non-admin users by default
    if (!isset($_GET['role']) || $_GET['role'] !== 'all') {
        $user_args['role__not_in'] = ['administrator'];
    }

    $user_query = new WP_User_Query($user_args);
    $total_users = $user_query->get_total();
    $total_pages = ceil($total_users / $per_page);

    // Count active members
    $active_count = 0;
    if ($user_query->get_results()) {
        foreach ($user_query->get_results() as $u) {
            if (filmworld_has_access($u->ID)) $active_count++;
        }
    }
    ?>
    <div class="wrap">
        <h1>مدیریت اعضا و اشتراک‌ها <span style="font-size:0.5em;color:#888;">v6</span></h1>

        <?php if (!empty($admin_msg)) : ?>
            <div class="notice notice-<?php echo $admin_msg_type === 'success' ? 'success' : 'error'; ?> is-dismissible">
                <p><strong><?php echo esc_html($admin_msg); ?></strong></p>
            </div>
        <?php endif; ?>

        <!-- ====== Add Days to All Users ====== -->
        <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:6px;padding:20px;margin:20px 0;">
            <h3 style="margin-top:0;color:#856404;">⏰ افزودن روز به همه کاربران</h3>
            <p style="color:#856404;margin-bottom:12px;">تعداد روز مشخص‌شده به اشتراک <strong>تمام کاربران غیرمدیر</strong> اضافه می‌شود. اگر کاربری اشتراک فعال داشته باشد، به زمان باقیمانده‌اش اضافه می‌شود.</p>
            <form method="post" style="display:flex;gap:10px;align-items:flex-end;" onsubmit="return confirm('آیا مطمئنید؟ این عمل روی تمام کاربران اعمال می‌شود.');">
                <?php wp_nonce_field('filmworld_admin_members'); ?>
                <div>
                    <label><strong>تعداد روز:</strong></label><br>
                    <input type="number" name="all_days" value="7" min="1" class="regular-text" style="width:120px;">
                </div>
                <button type="submit" name="filmworld_add_days_all" class="button" style="background:#856404;color:#fff;border-color:#856404;">افزودن به همه</button>
            </form>
        </div>

        <!-- ====== Quick Set by ID ====== -->
        <div style="background:#fff;border:1px solid #ccd0d4;border-radius:6px;padding:20px;margin-bottom:20px;">
            <h3 style="margin-top:0;">تنظیم سریع (با شناسه کاربر)</h3>
            <form method="post" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                <?php wp_nonce_field('filmworld_admin_members'); ?>
                <div>
                    <label><strong>شناسه کاربر (ID):</strong></label><br>
                    <input type="number" name="user_id" placeholder="12" min="1" class="regular-text" style="width:120px;">
                </div>
                <div>
                    <label><strong>تعداد روز (از الان):</strong></label><br>
                    <input type="number" name="days" value="30" min="0" class="regular-text" style="width:100px;">
                    <small style="color:#999;">0 = حذف اشتراک</small>
                </div>
                <button type="submit" name="filmworld_set_days" class="button button-primary">تنظیم</button>
            </form>
        </div>

        <!-- ====== User List ====== -->
        <div style="background:#fff;border:1px solid #ccd0d4;border-radius:6px;padding:20px;overflow-x:auto;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;flex-wrap:wrap;gap:10px;">
                <h3 style="margin:0;">لیست کاربران <span style="font-weight:normal;color:#666;">
                    (<?php echo $total_users; ?> کاربر — <?php echo $active_count; ?> فعال)
                </span></h3>
                <form method="get" style="display:flex;gap:8px;align-items:center;">
                    <input type="hidden" name="page" value="filmworld-members">
                    <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="جستجو..." class="regular-text" style="width:200px;">
                    <select name="role" style="vertical-align:middle;">
                        <option value="" <?php selected(!isset($_GET['role']) || $_GET['role'] !== 'all'); ?>>بدون مدیر</option>
                        <option value="all" <?php selected($_GET['role'] ?? '' === 'all'); ?>>همه</option>
                    </select>
                    <button type="submit" class="button">جستجو</button>
                    <?php if (!empty($search) || isset($_GET['role'])) : ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=filmworld-members')); ?>" class="button">پاک</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if ($user_query->get_results()) : ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th style="width:50px;">ID</th>
                        <th>نام کاربری</th>
                        <th>ایمیل</th>
                        <th style="width:100px;">روز باقیمانده</th>
                        <th style="width:140px;">تاریخ انقضا</th>
                        <th style="width:80px;">وضعیت</th>
                        <th style="width:140px;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($user_query->get_results() as $u) :
                        $u_active = filmworld_has_access($u->ID);
                        $u_remaining = filmworld_get_remaining_days($u->ID);
                        $u_expire = get_user_meta($u->ID, 'filmworld_expire', true);
                        $u_expire_str = $u_expire ? date_i18n('Y/m/d H:i', intval($u_expire)) : '—';
                        $is_admin = user_can($u->ID, 'manage_options');
                    ?>
                    <tr style="<?php echo !$u_active && !$is_admin ? 'color:#999;' : ''; ?>">
                        <td><?php echo $u->ID; ?></td>
                        <td>
                            <strong><?php echo esc_html($u->user_login); ?></strong>
                            <?php if ($is_admin) : ?> <span style="color:#2271b1;">[مدیر]</span> <?php endif; ?>
                        </td>
                        <td dir="ltr" style="font-size:0.85rem;"><?php echo esc_html($u->user_email); ?></td>
                        <td>
                            <?php if ($is_admin) : ?>
                                <span style="color:#2271b1;">∞</span>
                            <?php else : ?>
                                <strong style="color:<?php echo $u_active ? '#00a32a' : '#999'; ?>;">
                                    <?php echo $u_remaining > 0 ? $u_remaining . ' روز' : '۰'; ?>
                                </strong>
                            <?php endif; ?>
                        </td>
                        <td dir="ltr" style="font-size:0.85rem;"><?php echo esc_html($u_expire_str); ?></td>
                        <td>
                            <?php if ($is_admin) : ?>
                                <span style="color:#2271b1;">مدیر</span>
                            <?php elseif ($u_active) : ?>
                                <span style="color:#00a32a;font-weight:bold;">فعال</span>
                            <?php else : ?>
                                <span>غیرفعال</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$is_admin) : ?>
                            <button type="button" class="button button-small"
                                onclick="document.getElementById('fw-row-<?php echo $u->ID; ?>').style.display = document.getElementById('fw-row-<?php echo $u->ID; ?>').style.display === 'none' ? 'table-row' : 'none'">
                                ویرایش
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <!-- Inline Edit -->
                    <tr id="fw-row-<?php echo $u->ID; ?>" style="display:none;">
                        <td colspan="7" style="background:#f0f6fc;padding:16px;">
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;max-width:900px;">
                                <!-- Set Days -->
                                <div style="background:#fff;border:1px solid #ccd0d4;border-radius:6px;padding:14px;">
                                    <h4 style="margin:0 0 10px;color:#2271b1;">تنظیم روز (جایگزین)</h4>
                                    <form method="post">
                                        <?php wp_nonce_field('filmworld_admin_members'); ?>
                                        <input type="hidden" name="user_id" value="<?php echo $u->ID; ?>">
                                        <p><label>تعداد روز (از الان):</label><br>
                                        <input type="number" name="days" value="<?php echo max($u_remaining, 30); ?>" min="0" style="width:100%;">
                                        <small style="color:#999;">0 = حذف اشتراک</small></p>
                                        <button type="submit" name="filmworld_set_days" class="button button-primary" style="width:100%;">اعمال</button>
                                    </form>
                                </div>
                                <!-- Add Days -->
                                <div style="background:#fff;border:1px solid #ccd0d4;border-radius:6px;padding:14px;">
                                    <h4 style="margin:0 0 10px;color:#00a32a;">اضافه کردن روز</h4>
                                    <form method="post">
                                        <?php wp_nonce_field('filmworld_admin_members'); ?>
                                        <input type="hidden" name="user_id_add" value="<?php echo $u->ID; ?>">
                                        <p><label>تعداد روز اضافه:</label><br>
                                        <input type="number" name="add_days" value="30" min="1" style="width:100%;"></p>
                                        <button type="submit" name="filmworld_add_days" class="button" style="width:100%;background:#00a32a;color:#fff;border-color:#00a32a;">اضافه</button>
                                    </form>
                                </div>
                                <!-- Remove -->
                                <div style="background:#fff;border:1px solid #ccd0d4;border-radius:6px;padding:14px;">
                                    <h4 style="margin:0 0 10px;color:#d63638;">حذف اشتراک</h4>
                                    <p style="color:#666;font-size:0.85rem;margin-bottom:10px;">اشتراک کاربر حذف می‌شود.</p>
                                    <form method="post" onsubmit="return confirm('آیا مطمئنید؟');">
                                        <?php wp_nonce_field('filmworld_admin_members'); ?>
                                        <input type="hidden" name="user_id_remove" value="<?php echo $u->ID; ?>">
                                        <button type="submit" name="filmworld_remove" class="button" style="width:100%;background:#d63638;color:#fff;border-color:#d63638;">حذف</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1) : ?>
            <div style="margin-top:15px;display:flex;gap:5px;justify-content:center;">
                <?php
                $base_url = admin_url('admin.php?page=filmworld-members');
                if (!empty($search)) $base_url = add_query_arg('s', $search, $base_url);
                if (isset($_GET['role'])) $base_url = add_query_arg('role', $_GET['role'], $base_url);

                for ($i = 1; $i <= $total_pages; $i++) {
                    $page_url = add_query_arg('paged', $i, $base_url);
                    $active = $i === $paged;
                    echo '<a href="' . esc_url($page_url) . '" class="button ' . ($active ? 'button-primary' : '') . '">' . $i . '</a> ';
                }
                ?>
            </div>
            <?php endif; ?>

            <?php else : ?>
            <p style="color:#999;text-align:center;padding:30px;">هیچ کاربری یافت نشد.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php
}