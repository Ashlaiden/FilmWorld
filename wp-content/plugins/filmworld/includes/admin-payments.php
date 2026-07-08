<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Admin Payments List Page
|--------------------------------------------------------------------------
*/

function filmworld_admin_payments_page() {
    global $wpdb;

    if (!current_user_can('manage_options')) {
        return;
    }

    $table = $wpdb->prefix . 'filmworld_payments';

    // Ensure table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
        echo '<div class="wrap"><h1>مدیریت پرداخت‌ها</h1><div class="notice notice-warning"><p>جدول پرداخت‌ها هنوز ایجاد نشده. یک بار به تنظیمات پرداخت بروید تا جدول ساخته شود.</p></div></div>';
        return;
    }

    // ---- Filters ----
    $where  = ['1=1'];
    $values = [];

    if (!empty($_GET['status']) && $_GET['status'] !== 'all') {
        $where[]  = 'p.status = %s';
        $values[] = sanitize_text_field($_GET['status']);
    }

    if (!empty($_GET['gateway']) && $_GET['gateway'] !== 'all') {
        $where[]  = 'p.gateway = %s';
        $values[] = sanitize_text_field($_GET['gateway']);
    }

    if (!empty($_GET['user_search'])) {
        $where[]   = '(u.user_login LIKE %s OR u.user_email LIKE %s OR u.display_name LIKE %s)';
        $search    = '%' . $wpdb->esc_like(sanitize_text_field($_GET['user_search'])) . '%';
        $values[]  = $search;
        $values[]  = $search;
        $values[]  = $search;
    }

    $where_sql = implode(' AND ', $where);

    // ---- Pagination ----
    $per_page     = 20;
    $current_page = max(1, intval($_GET['paged'] ?? 1));
    $offset       = ($current_page - 1) * $per_page;

    // Total count
    if (!empty($values)) {
        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table p LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID WHERE $where_sql",
            $values
        ));
    } else {
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table p LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID WHERE $where_sql");
    }

    // Fetch payments
    if (!empty($values)) {
        $payments = $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, u.user_login, u.display_name, u.user_email
             FROM $table p
             LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID
             WHERE $where_sql
             ORDER BY p.created_at DESC
             LIMIT %d OFFSET %d",
            array_merge($values, [$per_page, $offset])
        ));
    } else {
        $payments = $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, u.user_login, u.display_name, u.user_email
             FROM $table p
             LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID
             WHERE $where_sql
             ORDER BY p.created_at DESC
             LIMIT %d OFFSET %d",
            $per_page, $offset
        ));
    }

    // ---- Stats ----
    $stats = $wpdb->get_results("SELECT status, COUNT(*) as cnt, SUM(amount) as total FROM $table GROUP BY status");

    $status_colors = [
        'success'   => '#00a32a',
        'pending'   => '#dba617',
        'failed'    => '#d63638',
        'cancelled' => '#787c82',
    ];
    $status_labels = [
        'success'   => 'موفق',
        'pending'   => 'در انتظار',
        'failed'    => 'ناموفق',
        'cancelled' => 'لغو شده',
    ];

    $all_gateways = filmworld_get_all_gateways();
    ?>

    <div class="wrap">
        <h1>مدیریت پرداخت‌ها</h1>

        <!-- Stats Cards -->
        <div style="display:flex;gap:15px;margin:20px 0;flex-wrap:wrap;">
            <?php foreach ($stats as $s) :
                $color = $status_colors[$s->status] ?? '#666';
                $label = $status_labels[$s->status] ?? $s->status;
            ?>
            <div style="background:#fff;border:1px solid #c3c4c7;border-left:4px solid <?php echo $color; ?>;padding:12px 18px;border-radius:4px;min-width:170px;">
                <div style="font-size:24px;font-weight:700;color:<?php echo $color; ?>;">
                    <?php echo number_format_i18n($s->cnt); ?>
                </div>
                <div style="color:#646970;font-size:13px;"><?php echo esc_html($label); ?></div>
                <?php if ($s->total > 0) : ?>
                <div style="color:#1d2327;font-size:12px;margin-top:3px;">
                    <?php echo number_format_i18n($s->total); ?> تومان
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Filters -->
        <form method="get" style="margin-bottom:15px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <input type="hidden" name="page" value="filmworld-payments">

            <select name="status">
                <option value="all" <?php selected($_GET['status'] ?? 'all', 'all'); ?>>همه وضعیت‌ها</option>
                <option value="success" <?php selected($_GET['status'] ?? '', 'success'); ?>>موفق</option>
                <option value="pending" <?php selected($_GET['status'] ?? '', 'pending'); ?>>در انتظار</option>
                <option value="failed" <?php selected($_GET['status'] ?? '', 'failed'); ?>>ناموفق</option>
                <option value="cancelled" <?php selected($_GET['status'] ?? '', 'cancelled'); ?>>لغو شده</option>
            </select>

            <select name="gateway">
                <option value="all">همه درگاه‌ها</option>
                <?php foreach ($all_gateways as $id => $gw) : ?>
                <option value="<?php echo esc_attr($id); ?>" <?php selected($_GET['gateway'] ?? '', $id); ?>>
                    <?php echo esc_html($gw->get_name()); ?>
                </option>
                <?php endforeach; ?>
            </select>

            <input type="text" name="user_search" placeholder="جستجوی کاربر (نام / ایمیل)..."
                   value="<?php echo esc_attr($_GET['user_search'] ?? ''); ?>"
                   style="min-width:220px;">

            <button type="submit" class="button">فیلتر</button>
            <a href="<?php echo admin_url('admin.php?page=filmworld-payments'); ?>" class="button">پاک کردن</a>
        </form>

        <!-- Payments Table -->
        <table class="widefat striped fixed">
            <thead>
                <tr>
                    <th style="width:45px;">#</th>
                    <th style="width:160px;">کاربر</th>
                    <th style="width:100px;">درگاه</th>
                    <th>پلن</th>
                    <th style="width:110px;">مبلغ (تومان)</th>
                    <th style="width:130px;">شماره پیگیری</th>
                    <th style="width:85px;">وضعیت</th>
                    <th style="width:140px;">تاریخ ایجاد</th>
                    <th style="width:140px;">تاریخ پرداخت</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)) : ?>
                <tr>
                    <td colspan="9" style="text-align:center;padding:40px;color:#646970;">
                        هیچ پرداختی یافت نشد.
                    </td>
                </tr>
                <?php else :
                    $counter = $offset;
                    foreach ($payments as $p) :
                        $counter++;
                        $color   = $status_colors[$p->status] ?? '#666';
                        $label   = $status_labels[$p->status] ?? $p->status;
                        $gw_obj  = $all_gateways[$p->gateway] ?? null;
                        $gw_name = $gw_obj ? $gw_obj->get_name() : $p->gateway;
                ?>
                <tr>
                    <td><?php echo $counter; ?></td>
                    <td>
                        <?php if ($p->user_id) : ?>
                        <a href="<?php echo admin_url('user-edit.php?user_id=' . $p->user_id); ?>" target="_blank">
                            <strong><?php echo esc_html($p->display_name ?: $p->user_login); ?></strong>
                        </a>
                        <br><span style="color:#646970;font-size:11px;"><?php echo esc_html($p->user_email); ?></span>
                        <?php else : ?>
                        <span style="color:#999;">کاربر حذف شده</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;"><?php echo esc_html($gw_name); ?></td>
                    <td><?php echo esc_html($p->plan_name); ?></td>
                    <td style="font-weight:600;"><?php echo number_format_i18n($p->amount); ?></td>
                    <td>
                        <?php if ($p->ref_id) : ?>
                        <code style="font-size:11px;"><?php echo esc_html($p->ref_id); ?></code>
                        <?php else : ?>
                        <span style="color:#999;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="color:<?php echo $color; ?>;font-weight:600;font-size:12px;">
                            <?php echo esc_html($label); ?>
                        </span>
                    </td>
                    <td style="font-size:11px;"><?php echo esc_html($p->created_at); ?></td>
                    <td style="font-size:11px;">
                        <?php if ($p->paid_at && $p->paid_at !== '0000-00-00 00:00:00') : ?>
                            <?php echo esc_html($p->paid_at); ?>
                        <?php else : ?>
                            <span style="color:#999;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php
        $total_pages = ceil($total / $per_page);
        if ($total > $per_page) :
            $base_url = admin_url('admin.php?page=filmworld-payments'
                . '&status=' . urlencode($_GET['status'] ?? 'all')
                . '&gateway=' . urlencode($_GET['gateway'] ?? 'all')
                . '&user_search=' . urlencode($_GET['user_search'] ?? ''));
        ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num"><?php echo number_format_i18n($total); ?> مورد</span>
                <span class="pagination-links">
                    <?php if ($current_page > 1) : ?>
                    <a class="button" href="<?php echo add_query_arg('paged', $current_page - 1, $base_url); ?>">&#8249;</a>
                    <?php endif; ?>
                    <span class="paging-input"><?php echo $current_page; ?> از <?php echo $total_pages; ?></span>
                    <?php if ($current_page < $total_pages) : ?>
                    <a class="button" href="<?php echo add_query_arg('paged', $current_page + 1, $base_url); ?>">&#8250;</a>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

/*
|--------------------------------------------------------------------------
| Admin Subscriptions List Page
|--------------------------------------------------------------------------
*/

function filmworld_admin_subscriptions_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Get users who have a plan set
    $args = [
        'meta_key'     => 'filmworld_plan',
        'meta_compare' => 'EXISTS',
        'number'       => 200,
        'orderby'      => 'ID',
        'order'        => 'DESC',
    ];

    if (!empty($_GET['sub_search'])) {
        $args['search'] = '*' . sanitize_text_field($_GET['sub_search']) . '*';
    }

    $users       = get_users($args);
    $plans       = filmworld_get_plans();
    $plan_labels = [];
    foreach ($plans as $key => $p) {
        $plan_labels[$key] = $p['name'];
    }

    // Count stats
    $active_count  = 0;
    $expired_count = 0;
    foreach ($users as $u) {
        $expire = get_user_meta($u->ID, 'filmworld_expire', true);
        if (!empty($expire) && intval($expire) > time()) {
            $active_count++;
        } else {
            $expired_count++;
        }
    }
    ?>

    <div class="wrap">
        <h1>مدیریت اشتراک‌ها</h1>

        <!-- Stats -->
        <div style="display:flex;gap:15px;margin:20px 0;flex-wrap:wrap;">
            <div style="background:#fff;border:1px solid #c3c4c7;border-left:4px solid #00a32a;padding:12px 18px;border-radius:4px;min-width:150px;">
                <div style="font-size:24px;font-weight:700;color:#00a32a;"><?php echo number_format_i18n($active_count); ?></div>
                <div style="color:#646970;font-size:13px;">اشتراک فعال</div>
            </div>
            <div style="background:#fff;border:1px solid #c3c4c7;border-left:4px solid #d63638;padding:12px 18px;border-radius:4px;min-width:150px;">
                <div style="font-size:24px;font-weight:700;color:#d63638;"><?php echo number_format_i18n($expired_count); ?></div>
                <div style="color:#646970;font-size:13px;">منقضی شده</div>
            </div>
            <div style="background:#fff;border:1px solid #c3c4c7;border-left:4px solid #2271b1;padding:12px 18px;border-radius:4px;min-width:150px;">
                <div style="font-size:24px;font-weight:700;color:#2271b1;"><?php echo number_format_i18n(count($users)); ?></div>
                <div style="color:#646970;font-size:13px;">کل خریداران</div>
            </div>
        </div>

        <!-- Search -->
        <form method="get" style="margin-bottom:15px;display:flex;align-items:center;gap:8px;">
            <input type="hidden" name="page" value="filmworld-subscriptions">
            <input type="text" name="sub_search" placeholder="جستجوی کاربر (نام / ایمیل)..."
                   value="<?php echo esc_attr($_GET['sub_search'] ?? ''); ?>"
                   style="min-width:250px;">
            <button type="submit" class="button">جستجو</button>
            <a href="<?php echo admin_url('admin.php?page=filmworld-subscriptions'); ?>" class="button">پاک کردن</a>
        </form>

        <!-- Subscriptions Table -->
        <table class="widefat striped fixed">
            <thead>
                <tr>
                    <th style="width:45px;">#</th>
                    <th style="width:180px;">کاربر</th>
                    <th style="width:120px;">پلن فعلی</th>
                    <th style="width:150px;">تاریخ انقضا</th>
                    <th style="width:80px;">وضعیت</th>
                    <th style="width:100px;">روز باقیمانده</th>
                    <th style="width:150px;">تاریخ ثبت‌نام</th>
                    <th style="width:90px;">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)) : ?>
                <tr>
                    <td colspan="8" style="text-align:center;padding:40px;color:#646970;">
                        هیچ اشتراکی یافت نشد.
                    </td>
                </tr>
                <?php else :
                    $counter = 0;
                    foreach ($users as $u) :
                        $counter++;
                        $plan       = get_user_meta($u->ID, 'filmworld_plan', true);
                        $expire     = get_user_meta($u->ID, 'filmworld_expire', true);
                        $is_active  = !empty($expire) && intval($expire) > time();
                        $days_left  = $is_active ? ceil(($expire - time()) / 86400) : 0;
                        $expire_str = !empty($expire) ? date_i18n('Y/m/d H:i', intval($expire)) : '—';

                        // Color for days remaining
                        if ($days_left <= 3 && $is_active) {
                            $days_color = '#d63638';
                        } elseif ($days_left <= 7 && $is_active) {
                            $days_color = '#dba617';
                        } else {
                            $days_color = '#00a32a';
                        }
                ?>
                <tr>
                    <td><?php echo $counter; ?></td>
                    <td>
                        <a href="<?php echo admin_url('user-edit.php?user_id=' . $u->ID); ?>" target="_blank">
                            <strong><?php echo esc_html($u->display_name); ?></strong>
                        </a>
                        <br><span style="color:#646970;font-size:11px;"><?php echo esc_html($u->user_email); ?></span>
                    </td>
                    <td><?php echo esc_html($plan_labels[$plan] ?? $plan); ?></td>
                    <td style="font-size:12px;"><?php echo esc_html($expire_str); ?></td>
                    <td>
                        <?php if ($is_active) : ?>
                        <span style="color:#00a32a;font-weight:600;font-size:12px;">فعال</span>
                        <?php else : ?>
                        <span style="color:#d63638;font-weight:600;font-size:12px;">منقضی</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;font-weight:700;color:<?php echo $days_color; ?>;">
                        <?php echo $is_active ? $days_left : '—'; ?>
                    </td>
                    <td style="font-size:11px;"><?php echo esc_html($u->user_registered); ?></td>
                    <td>
                        <?php if ($is_active) : ?>
                        <button type="button" class="button button-small"
                                onclick="filmworld_revoke_sub(this, <?php echo $u->ID; ?>)"
                                style="color:#d63638;border-color:#d63638;">
                            لغو
                        </button>
                        <?php else : ?>
                        <button type="button" class="button button-small"
                                onclick="filmworld_extend_sub(this, <?php echo $u->ID; ?>)"
                                style="color:#00a32a;border-color:#00a32a;">
                            تمدید
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    function filmworld_revoke_sub(btn, userId) {
        if (!confirm('آیا از لغو اشتراک این کاربر مطمئن هستید؟')) return;

        jQuery.post(ajaxurl, {
            action:  'filmworld_admin_revoke_subscription',
            user_id: userId,
            nonce:   '<?php echo wp_create_nonce("filmworld_admin_nonce"); ?>'
        }, function(res) {
            if (res.success) {
                location.reload();
            } else {
                alert(res.data || 'خطا در لغو اشتراک.');
            }
        });
    }

    function filmworld_extend_sub(btn, userId) {
        var days = prompt('تعداد روز برای تمدید اشتراک را وارد کنید:', '30');
        if (!days || isNaN(days) || parseInt(days) < 1) return;

        jQuery.post(ajaxurl, {
            action:  'filmworld_admin_extend_subscription',
            user_id: userId,
            days:    parseInt(days),
            nonce:   '<?php echo wp_create_nonce("filmworld_admin_nonce"); ?>'
        }, function(res) {
            if (res.success) {
                location.reload();
            } else {
                alert(res.data || 'خطا در تمدید اشتراک.');
            }
        });
    }
    </script>
    <?php
}

/*
|--------------------------------------------------------------------------
| AJAX: Admin Revoke Subscription
|--------------------------------------------------------------------------
*/

function filmworld_ajax_admin_revoke_subscription() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('دسترسی غیرمجاز.');
    }

    check_ajax_referer('filmworld_admin_nonce', 'nonce');

    $user_id = intval($_POST['user_id'] ?? 0);
    if (!$user_id) {
        wp_send_json_error('کاربر نامعتبر.');
    }

    update_user_meta($user_id, 'filmworld_plan', 'none');
    update_user_meta($user_id, 'filmworld_expire', 0);

    wp_send_json_success('اشتراک لغو شد.');
}
add_action('wp_ajax_filmworld_admin_revoke_subscription', 'filmworld_ajax_admin_revoke_subscription');

/*
|--------------------------------------------------------------------------
| AJAX: Admin Extend Subscription
|--------------------------------------------------------------------------
*/

function filmworld_ajax_admin_extend_subscription() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('دسترسی غیرمجاز.');
    }

    check_ajax_referer('filmworld_admin_nonce', 'nonce');

    $user_id = intval($_POST['user_id'] ?? 0);
    $days    = intval($_POST['days'] ?? 0);

    if (!$user_id || !$days) {
        wp_send_json_error('پارامترها نامعتبر هستند.');
    }

    // Ensure user has a plan
    $plan = get_user_meta($user_id, 'filmworld_plan', true);
    if (empty($plan) || $plan === 'none') {
        $plan = 'monthly';
        update_user_meta($user_id, 'filmworld_plan', $plan);
    }

    // Extend from current expiry (if still active) or from now
    $current_expire = get_user_meta($user_id, 'filmworld_expire', true);
    $base           = (!empty($current_expire) && intval($current_expire) > time())
        ? intval($current_expire)
        : time();
    $new_expire = $base + ($days * 86400);

    update_user_meta($user_id, 'filmworld_expire', $new_expire);

    wp_send_json_success('اشتراک برای ' . $days . ' روز تمدید شد.');
}
add_action('wp_ajax_filmworld_admin_extend_subscription', 'filmworld_ajax_extend_subscription');

/*
|--------------------------------------------------------------------------
| Admin Menu: Payments & Subscriptions
|--------------------------------------------------------------------------
*/

function filmworld_payments_admin_menu() {
    add_submenu_page(
        'filmworld-taxonomies',
        'پرداخت‌ها',
        'پرداخت‌ها',
        'manage_options',
        'filmworld-payments',
        'filmworld_admin_payments_page'
    );

    add_submenu_page(
        'filmworld-taxonomies',
        'اشتراک‌ها',
        'اشتراک‌ها',
        'manage_options',
        'filmworld-subscriptions',
        'filmworld_admin_subscriptions_page'
    );
}
add_action('admin_menu', 'filmworld_payments_admin_menu');