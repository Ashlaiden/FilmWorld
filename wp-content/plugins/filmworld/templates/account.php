<?php
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$user_id    = get_current_user_id();
$user       = get_userdata($user_id);
$packages   = filmworld_get_day_packages();
$tab        = sanitize_text_field($_GET['tab'] ?? 'dashboard');
$has_access = filmworld_has_access($user_id);
$remaining  = filmworld_get_remaining_days($user_id);
$expire_raw = get_user_meta($user_id, 'filmworld_expire', true);
$expire_str = $expire_raw ? date_i18n('Y/m/d H:i', intval($expire_raw)) : '—';

// Payment result messages
$payment_msg = '';
$payment_type = '';
if (isset($_GET['payment'])) {
    if ($_GET['payment'] === 'success') {
        $payment_type = 'success';
        $ref = sanitize_text_field($_GET['ref'] ?? '');
        $payment_msg = 'پرداخت با موفقیت انجام شد! کد پیگیری: ' . esc_html($ref);
    } elseif ($_GET['payment'] === 'cancelled') {
        $payment_type = 'error';
        $payment_msg = 'پرداخت لغو شد.';
    } elseif ($_GET['payment'] === 'failed') {
        $payment_type = 'error';
        $payment_msg = 'پرداخت ناموفق بود. ' . sanitize_text_field(urldecode($_GET['msg'] ?? ''));
    }
}

$payments = get_user_meta($user_id, 'filmworld_payments', true);
if (!is_array($payments)) $payments = [];

$favorites = get_user_meta($user_id, 'filmworld_favorites', true);
if (!is_array($favorites)) $favorites = [];

$tabs = [
    'dashboard'  => 'داشبورد',
    'membership' => 'خرید اشتراک',
    'favorites'  => 'علاقه‌مندی‌ها',
    'history'    => 'تاریخچه پرداخت',
    'profile'    => 'پروفایل',
];
?>

<div class="fw-account-page">

    <?php if (!empty($payment_msg)) : ?>
        <div class="fw-notice fw-notice-<?php echo $payment_type === 'success' ? 'success' : 'error'; ?>">
            <?php echo esc_html($payment_msg); ?>
        </div>
    <?php endif; ?>

    <div class="fw-account-tabs">
        <?php foreach ($tabs as $key => $label) : ?>
            <a href="?tab=<?php echo esc_attr($key); ?>" class="fw-account-tab <?php echo $tab === $key ? 'active' : ''; ?>">
                <?php echo esc_html($label); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Dashboard -->
    <?php if ($tab === 'dashboard') : ?>
    <div class="fw-account-card">
        <h2>خوش آمدید، <?php echo esc_html($user->display_name); ?></h2>
        <div class="fw-account-stats">
            <div class="fw-stat">
                <span class="fw-stat-value"><?php echo $has_access ? 'فعال' : 'غیرفعال'; ?></span>
                <span class="fw-stat-label">وضعیت اشتراک</span>
            </div>
            <div class="fw-stat">
                <span class="fw-stat-value"><?php echo $remaining > 0 ? $remaining . ' روز' : '—'; ?></span>
                <span class="fw-stat-label">روز باقیمانده</span>
            </div>
            <div class="fw-stat">
                <span class="fw-stat-value"><?php echo esc_html($expire_str); ?></span>
                <span class="fw-stat-label">تاریخ انقضا</span>
            </div>
            <div class="fw-stat">
                <span class="fw-stat-value"><?php echo count($favorites); ?></span>
                <span class="fw-stat-label">علاقه‌مندی‌ها</span>
            </div>
        </div>
        <?php if (!$has_access) : ?>
            <a href="?tab=membership" class="fw-lock-btn">خرید اشتراک</a>
        <?php endif; ?>
    </div>

    <!-- Membership (Day Packages) -->
    <?php elseif ($tab === 'membership') : ?>
    <div class="fw-plans-grid">
        <?php foreach ($packages as $key => $pkg) : ?>
        <div class="fw-plan-card">
            <h3><?php echo esc_html($pkg['days']); ?> روز اشتراک</h3>
            <div class="fw-plan-price">
                <?php echo number_format_i18n($pkg['price']); ?>
                <small>تومان</small>
            </div>
            <p class="fw-plan-duration">دسترسی به تمام فیلم‌ها و سریال‌ها</p>
            <button class="fw-lock-btn fw-buy-plan-btn" data-plan="<?php echo esc_attr($key); ?>">خرید</button>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Gateway Selection Modal -->
    <div id="fw-gateway-modal" class="fw-gateway-modal" style="display:none;">
        <div class="fw-gateway-modal-overlay"></div>
        <div class="fw-gateway-modal-content">
            <div class="fw-gateway-modal-header">
                <h3>انتخاب درگاه پرداخت</h3>
                <button type="button" class="fw-gateway-modal-close">&times;</button>
            </div>
            <div class="fw-gateway-modal-body" id="fw-gateway-list">
                <p style="color:var(--fw-text-muted);text-align:center;padding:20px;">در حال بارگذاری...</p>
            </div>
        </div>
    </div>

    <!-- Favorites -->
    <?php elseif ($tab === 'favorites') : ?>
    <?php if (!empty($favorites)) : ?>
        <div class="fw-grid">
            <?php
            $fav_query = new WP_Query([
                'post_type'      => ['movie', 'series'],
                'posts_per_page' => 20,
                'post__in'       => $favorites,
                'orderby'        => 'post__in',
            ]);
            while ($fav_query->have_posts()) : $fav_query->the_post();
                include __DIR__ . '/parts/media-card.php';
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    <?php else : ?>
        <div class="fw-locked"><p>هنوز فیلم یا سریالی به علاقه‌مندی‌ها اضافه نکرده‌اید.</p></div>
    <?php endif; ?>

    <!-- Payment History -->
    <?php elseif ($tab === 'history') : ?>
    <?php if (!empty($payments)) : ?>
        <div class="fw-single-section" style="margin-top:0;">
            <h2>تاریخچه پرداخت</h2>
            <table class="fw-download-table">
                <thead>
                    <tr><th>تاریخ</th><th>تعداد روز</th><th>مبلغ (تومان)</th><th>کد پیگیری</th><th>وضعیت</th></tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($payments) as $p) : ?>
                    <tr>
                        <td><?php echo esc_html($p['date']); ?></td>
                        <td><?php echo esc_html($p['plan_name'] ?? '—'); ?></td>
                        <td><?php echo number_format_i18n($p['amount']); ?></td>
                        <td dir="ltr"><?php echo esc_html($p['ref_id']); ?></td>
                        <td style="color:var(--fw-success);">موفق</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <div class="fw-locked"><p>هنوز پرداختی انجام نداده‌اید.</p></div>
    <?php endif; ?>

    <!-- Profile -->
    <?php elseif ($tab === 'profile') : ?>
    <div class="fw-account-card">
        <h2>ویرایش پروفایل</h2>
        <form id="filmworld-profile-form" class="fw-profile-form">
            <div class="fw-form-group">
                <label for="fw-first-name">نام</label>
                <input type="text" id="fw-first-name" name="first_name" value="<?php echo esc_attr($user->first_name); ?>" class="fw-form-input">
            </div>
            <div class="fw-form-group">
                <label for="fw-last-name">نام خانوادگی</label>
                <input type="text" id="fw-last-name" name="last_name" value="<?php echo esc_attr($user->last_name); ?>" class="fw-form-input">
            </div>
            <div class="fw-form-group">
                <label>ایمیل</label>
                <input type="email" value="<?php echo esc_attr($user->user_email); ?>" disabled class="fw-form-input">
                <small>ایمیل قابل تغییر نیست.</small>
            </div>
            <div class="fw-form-group">
                <label>نام کاربری</label>
                <input type="text" value="<?php echo esc_attr($user->user_login); ?>" disabled class="fw-form-input">
                <small>نام کاربری قابل تغییر نیست.</small>
            </div>
            <button type="submit" class="fw-lock-btn">ذخیره تغییرات</button>
            <div id="filmworld-profile-msg" style="margin-top:10px;"></div>
        </form>
        <hr style="margin:30px 0;border-color:var(--fw-border);">
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="fw-lock-btn" style="background:#555;">خروج از حساب</a>
    </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    var selectedPlan = null;
    var gatewayModal = document.getElementById("fw-gateway-modal");
    var gatewayList  = document.getElementById("fw-gateway-list");
    var gatewayClose = document.querySelector(".fw-gateway-modal-close");
    var gatewayOverlay = document.querySelector(".fw-gateway-modal-overlay");

    function openGatewayModal(planKey) {
        selectedPlan = planKey;
        gatewayModal.style.display = "flex";
        document.body.style.overflow = "hidden";

        var fd = new FormData();
        fd.append("action", "filmworld_get_enabled_gateways");
        fd.append("nonce", filmworld_ajax.nonce);
        fetch(filmworld_ajax.ajax_url, { method: "POST", body: fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success && data.data.length > 0) {
                var html = '<div class="fw-gateway-options">';
                data.data.forEach(function (gw) {
                    html += '<button type="button" class="fw-gateway-option" data-gateway="' + gw.id + '">';
                    html += '<span class="fw-gateway-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></span>';
                    html += '<span class="fw-gateway-name">' + gw.name + '</span>';
                    html += '<svg class="fw-gateway-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>';
                    html += '</button>';
                });
                html += '</div>';
                gatewayList.innerHTML = html;

                gatewayList.querySelectorAll(".fw-gateway-option").forEach(function (opt) {
                    opt.addEventListener("click", function () {
                        proceedPayment(selectedPlan, this.dataset.gateway);
                    });
                });
            } else {
                gatewayList.innerHTML = '<p style="color:var(--fw-error);text-align:center;padding:30px;">هیچ درگاه پرداختی فعال و تنظیم‌شده‌ای وجود ندارد.<br><small>لطفاً با مدیریت سایت تماس بگیرید.</small></p>';
            }
        })
        .catch(function () {
            gatewayList.innerHTML = '<p style="color:var(--fw-error);text-align:center;padding:30px;">خطا در بارگذاری لیست درگاه‌ها.</p>';
        });
    }

    function closeGatewayModal() {
        gatewayModal.style.display = "none";
        document.body.style.overflow = "";
        selectedPlan = null;
    }

    if (gatewayClose) gatewayClose.addEventListener("click", closeGatewayModal);
    if (gatewayOverlay) gatewayOverlay.addEventListener("click", closeGatewayModal);

    function proceedPayment(plan, gatewayId) {
        var allBtns = gatewayList.querySelectorAll(".fw-gateway-option");
        allBtns.forEach(function (b) { b.disabled = true; b.style.opacity = "0.5"; });

        var fd = new FormData();
        fd.append("action", "filmworld_init_payment");
        fd.append("plan", plan);
        fd.append("gateway", gatewayId);
        fd.append("nonce", filmworld_ajax.nonce);
        fetch(filmworld_ajax.ajax_url, { method: "POST", body: fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                window.location.href = data.data.url;
            } else {
                alert(data.data.message || "خطایی رخ داد.");
                allBtns.forEach(function (b) { b.disabled = false; b.style.opacity = "1"; });
            }
        })
        .catch(function () {
            alert("خطا در اتصال به سرور.");
            allBtns.forEach(function (b) { b.disabled = false; b.style.opacity = "1"; });
        });
    }

    // Buy Package — opens gateway modal
    document.querySelectorAll(".fw-buy-plan-btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
            openGatewayModal(this.dataset.plan);
        });
    });

    // Profile
    var form = document.getElementById("filmworld-profile-form");
    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            var btn = form.querySelector("button[type=submit]");
            var msg = document.getElementById("filmworld-profile-msg");
            btn.disabled = true; btn.textContent = "ذخیره...";
            var fd = new FormData(form);
            fd.append("action", "filmworld_update_profile");
            fd.append("nonce", filmworld_ajax.nonce);
            fetch(filmworld_ajax.ajax_url, { method: "POST", body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.disabled = false; btn.textContent = "ذخیره تغییرات";
                msg.innerHTML = '<div class="fw-notice fw-notice-' + (data.success ? 'success' : 'error') + '">' + (data.data.message) + '</div>';
            });
        });
    }
});
</script>