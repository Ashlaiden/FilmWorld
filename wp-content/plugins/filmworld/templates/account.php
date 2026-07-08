<?php

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

get_header();

$user_id   = get_current_user_id();
$user      = get_userdata($user_id);
$plans     = filmworld_get_plans();
$tab       = sanitize_text_field($_GET['tab'] ?? 'dashboard');
$has_access = filmworld_has_access($user_id);

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

// Membership info
$plan_key = get_user_meta($user_id, 'filmworld_plan', true);
$expire   = get_user_meta($user_id, 'filmworld_expire', true);
$plan_name = $plans[$plan_key]['name'] ?? 'ندارد';
$expire_date = $expire ? date_i18n('Y/m/d H:i', intval($expire)) : '—';

// Payment history
$payments = get_user_meta($user_id, 'filmworld_payments', true);
if (!is_array($payments)) $payments = [];

// Favorites
$favorites = get_user_meta($user_id, 'filmworld_favorites', true);
if (!is_array($favorites)) $favorites = [];

$tabs = [
    'dashboard'  => 'داشبورد',
    'membership' => 'عضویت',
    'favorites'  => 'علاقه‌مندی‌ها',
    'history'    => 'تاریخچه پرداخت',
    'profile'    => 'پروفایل',
];

?>

<div class="filmworld-account">

    <!-- Payment Message -->
    <?php if (!empty($payment_msg)) : ?>
        <div class="filmworld-notice filmworld-notice-<?php echo $payment_type === 'success' ? 'success' : 'error'; ?>">
            <?php echo esc_html($payment_msg); ?>
        </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="filmworld-account-tabs">
        <?php foreach ($tabs as $key => $label) : ?>
            <a href="?tab=<?php echo esc_attr($key); ?>" class="filmworld-account-tab <?php echo $tab === $key ? 'active' : ''; ?>">
                <?php echo esc_html($label); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Dashboard -->
    <?php if ($tab === 'dashboard') : ?>

        <div class="filmworld-account-card">
            <h2>خوش آمدید، <?php echo esc_html($user->display_name); ?></h2>

            <div class="filmworld-account-stats">
                <div class="filmworld-stat">
                    <span class="filmworld-stat-value"><?php echo $has_access ? 'فعال' : 'غیرفعال'; ?></span>
                    <span class="filmworld-stat-label">وضعیت عضویت</span>
                </div>
                <div class="filmworld-stat">
                    <span class="filmworld-stat-value"><?php echo esc_html($plan_name); ?></span>
                    <span class="filmworld-stat-label">پلن فعلی</span>
                </div>
                <div class="filmworld-stat">
                    <span class="filmworld-stat-value"><?php echo esc_html($expire_date); ?></span>
                    <span class="filmworld-stat-label">تاریخ انقضا</span>
                </div>
                <div class="filmworld-stat">
                    <span class="filmworld-stat-value"><?php echo count($favorites); ?></span>
                    <span class="filmworld-stat-label">علاقه‌مندی‌ها</span>
                </div>
            </div>

            <?php if (!$has_access) : ?>
                <a href="?tab=membership" class="filmworld-lock-btn">خرید عضویت</a>
            <?php endif; ?>
        </div>

    <!-- Membership / Buy Plans -->
    <?php elseif ($tab === 'membership') : ?>

        <!-- Gateway Selector -->
        <div class="filmworld-gateway-wrapper">
            <label for="filmworld-gateway-select" class="filmworld-gateway-label">درگاه پرداخت:</label>
            <select id="filmworld-gateway-select" class="filmworld-gateway-select">
                <option value="">در حال بارگذاری...</option>
            </select>
        </div>

        <div class="filmworld-plans-grid">
            <?php foreach ($plans as $key => $plan) : ?>
                <div class="filmworld-plan-card">
                    <h3><?php echo esc_html($plan['name']); ?></h3>
                    <div class="filmworld-plan-price">
                        <?php echo number_format_i18n($plan['price']); ?>
                        <small>تومان</small>
                    </div>
                    <p class="filmworld-plan-duration"><?php echo esc_html($plan['days']); ?> روز</p>
                    <p class="filmworld-plan-desc"><?php echo esc_html($plan['description']); ?></p>
                    <button class="filmworld-lock-btn filmworld-buy-plan-btn" data-plan="<?php echo esc_attr($key); ?>">
                        خرید پلن
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

    <!-- Favorites -->
    <?php elseif ($tab === 'favorites') : ?>

        <?php if (!empty($favorites)) : ?>

            <div class="filmworld-movies-grid">

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

            <div class="filmworld-locked">
                <p>هنوز فیلم یا سریالی به علاقه‌مندی‌ها اضافه نکرده‌اید.</p>
            </div>

        <?php endif; ?>

    <!-- Payment History -->
    <?php elseif ($tab === 'history') : ?>

        <?php if (!empty($payments)) : ?>

            <table class="filmworld-download-table">
                <thead>
                    <tr>
                        <th>تاریخ</th>
                        <th>پلن</th>
                        <th>درگاه</th>
                        <th>مبلغ (تومان)</th>
                        <th>کد پیگیری</th>
                        <th>وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($payments) as $p) : ?>
                        <tr>
                            <td><?php echo esc_html($p['date']); ?></td>
                            <td><?php echo esc_html($p['plan_name']); ?></td>
                            <td><?php echo esc_html($p['gateway'] ?? 'زرین‌پال'); ?></td>
                            <td><?php echo number_format_i18n($p['amount']); ?></td>
                            <td dir="ltr"><?php echo esc_html($p['ref_id']); ?></td>
                            <td style="color:<?php echo ($p['status'] ?? '') === 'success' ? 'green' : 'red'; ?>;">
                                <?php echo ($p['status'] ?? '') === 'success' ? 'موفق' : 'ناموفق'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php else : ?>

            <div class="filmworld-locked">
                <p>هنوز پرداختی انجام نداده‌اید.</p>
            </div>

        <?php endif; ?>

    <!-- Profile -->
    <?php elseif ($tab === 'profile') : ?>

        <div class="filmworld-account-card">
            <h2>ویرایش پروفایل</h2>

            <form id="filmworld-profile-form" class="filmworld-profile-form">
                <div class="filmworld-form-group">
                    <label for="fw-first-name">نام</label>
                    <input type="text" id="fw-first-name" name="first_name" value="<?php echo esc_attr($user->first_name); ?>">
                </div>
                <div class="filmworld-form-group">
                    <label for="fw-last-name">نام خانوادگی</label>
                    <input type="text" id="fw-last-name" name="last_name" value="<?php echo esc_attr($user->last_name); ?>">
                </div>
                <div class="filmworld-form-group">
                    <label>ایمیل</label>
                    <input type="email" value="<?php echo esc_attr($user->user_email); ?>" disabled>
                    <small>ایمیل قابل تغییر نیست.</small>
                </div>
                <div class="filmworld-form-group">
                    <label>نام کاربری</label>
                    <input type="text" value="<?php echo esc_attr($user->user_login); ?>" disabled>
                    <small>نام کاربری قابل تغییر نیست.</small>
                </div>
                <button type="submit" class="filmworld-lock-btn">ذخیره تغییرات</button>
                <div id="filmworld-profile-msg" style="margin-top:10px;"></div>
            </form>

            <hr style="margin:30px 0;border-color:var(--fw-border);">

            <a href="<?php echo wp_logout_url(home_url()); ?>" class="filmworld-lock-btn" style="background:#666;">
                خروج از حساب
            </a>
        </div>

    <?php endif; ?>

</div>

<style>
.filmworld-account-tabs {
    display: flex;
    gap: 4px;
    margin-bottom: 30px;
    background: var(--fw-bg-card);
    border-radius: var(--fw-radius);
    padding: 6px;
    box-shadow: var(--fw-shadow);
    overflow-x: auto;
    flex-wrap: wrap;
}
.filmworld-account-tab {
    padding: 10px 20px;
    border-radius: var(--fw-radius-sm);
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--fw-text-secondary);
    transition: all var(--fw-transition);
    white-space: nowrap;
}
.filmworld-account-tab:hover {
    color: var(--fw-primary);
    background: var(--fw-primary-light);
}
.filmworld-account-tab.active {
    background: var(--fw-primary);
    color: #fff;
}
.filmworld-account-card {
    background: var(--fw-bg-card);
    border-radius: var(--fw-radius);
    padding: 30px;
    box-shadow: var(--fw-shadow);
}
.filmworld-account-card h2 {
    margin: 0 0 25px;
    font-size: 1.3rem;
}
.filmworld-account-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}
.filmworld-stat {
    text-align: center;
    padding: 16px;
    background: var(--fw-bg);
    border-radius: var(--fw-radius-sm);
}
.filmworld-stat-value {
    display: block;
    font-size: 1rem;
    font-weight: 700;
    color: var(--fw-text);
}
.filmworld-stat-label {
    display: block;
    font-size: 0.78rem;
    color: var(--fw-text-muted);
    margin-top: 4px;
}

/* Gateway Selector */
.filmworld-gateway-wrapper {
    background: var(--fw-bg-card);
    border-radius: var(--fw-radius);
    padding: 20px 25px;
    box-shadow: var(--fw-shadow);
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}
.filmworld-gateway-label {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--fw-text-secondary);
    white-space: nowrap;
}
.filmworld-gateway-select {
    flex: 1;
    min-width: 220px;
    max-width: 350px;
    padding: 10px 14px;
    border: 2px solid var(--fw-border);
    border-radius: var(--fw-radius-sm);
    background: var(--fw-bg);
    color: var(--fw-text);
    font-family: var(--fw-font);
    font-size: 0.95rem;
    direction: rtl;
    cursor: pointer;
    transition: border-color 0.2s;
}
.filmworld-gateway-select:focus {
    outline: none;
    border-color: var(--fw-primary);
}

.filmworld-plans-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
}
.filmworld-plan-card {
    background: var(--fw-bg-card);
    border-radius: var(--fw-radius);
    padding: 30px;
    text-align: center;
    box-shadow: var(--fw-shadow);
    transition: transform var(--fw-transition), box-shadow var(--fw-transition);
    border: 2px solid transparent;
}
.filmworld-plan-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--fw-shadow-hover);
    border-color: var(--fw-primary);
}
.filmworld-plan-card h3 {
    font-size: 1.2rem;
    margin: 0 0 10px;
}
.filmworld-plan-price {
    font-size: 2rem;
    font-weight: 900;
    color: var(--fw-primary);
    margin-bottom: 4px;
}
.filmworld-plan-price small {
    font-size: 0.9rem;
    font-weight: 400;
    color: var(--fw-text-muted);
}
.filmworld-plan-duration {
    color: var(--fw-text-secondary);
    margin-bottom: 12px;
    font-size: 0.9rem;
}
.filmworld-plan-desc {
    color: var(--fw-text-muted);
    font-size: 0.85rem;
    margin-bottom: 20px;
    line-height: 1.6;
}
.filmworld-form-group {
    margin-bottom: 18px;
}
.filmworld-form-group label {
    display: block;
    font-weight: 700;
    margin-bottom: 6px;
    font-size: 0.9rem;
    color: var(--fw-text-secondary);
}
.filmworld-form-group input {
    width: 100%;
    max-width: 400px;
    padding: 12px 16px;
    border: 2px solid var(--fw-border);
    border-radius: var(--fw-radius-sm);
    background: var(--fw-bg);
    color: var(--fw-text);
    font-family: var(--fw-font);
    font-size: 0.95rem;
    direction: rtl;
}
.filmworld-form-group input:focus {
    outline: none;
    border-color: var(--fw-primary);
}
.filmworld-form-group small {
    display: block;
    margin-top: 4px;
    font-size: 0.78rem;
    color: var(--fw-text-muted);
}
.filmworld-notice {
    padding: 14px 20px;
    border-radius: var(--fw-radius-sm);
    margin-bottom: 20px;
    font-weight: 600;
    font-size: 0.95rem;
}
.filmworld-notice-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.filmworld-notice-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
@media (max-width: 768px) {
    .filmworld-account-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    .filmworld-plans-grid {
        grid-template-columns: 1fr;
    }
    .filmworld-gateway-wrapper {
        flex-direction: column;
        align-items: stretch;
    }
    .filmworld-gateway-select {
        max-width: 100%;
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // ---- Load enabled gateways ----
    var gatewaySelect = document.getElementById("filmworld-gateway-select");
    if (gatewaySelect) {
        var gwForm = new FormData();
        gwForm.append("action", "filmworld_get_enabled_gateways");
        gwForm.append("nonce", filmworld_ajax.nonce);

        fetch(filmworld_ajax.ajax_url, {
            method: "POST",
            body: gwForm
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res.success || !res.data || res.data.length === 0) {
                gatewaySelect.innerHTML = '<option value="">هیچ درگاه فعالی وجود ندارد</option>';
                return;
            }

            var gateways = res.data;

            // اگر فقط یک درگاه فعال باشه، خودکار انتخاب بشه
            if (gateways.length === 1) {
                gatewaySelect.innerHTML = '<option value="' + gateways[0].id + '">' + gateways[0].name + '</option>';
                gatewaySelect.disabled = true;
            } else {
                gatewaySelect.innerHTML = '<option value="">-- درگاه پرداخت را انتخاب کنید --</option>';
                gateways.forEach(function (gw) {
                    var opt = document.createElement("option");
                    opt.value = gw.id;
                    opt.textContent = gw.name;
                    gatewaySelect.appendChild(opt);
                });
            }
        })
        .catch(function () {
            gatewaySelect.innerHTML = '<option value="">خطا در بارگذاری درگاه‌ها</option>';
        });
    }

    // ---- Buy Plan Buttons ----
    document.querySelectorAll(".filmworld-buy-plan-btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
            var plan = this.dataset.plan;

            // بررسی انتخاب درگاه
            if (gatewaySelect && !gatewaySelect.value) {
                alert("لطفاً ابتدا درگاه پرداخت را انتخاب کنید.");
                gatewaySelect.focus();
                return;
            }

            var gateway = gatewaySelect ? gatewaySelect.value : "";
            this.disabled = true;
            this.textContent = "در حال انتقال...";

            var formData = new FormData();
            formData.append("action", "filmworld_init_payment");
            formData.append("plan", plan);
            formData.append("gateway", gateway);
            formData.append("nonce", filmworld_ajax.nonce);

            fetch(filmworld_ajax.ajax_url, {
                method: "POST",
                body: formData,
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    window.location.href = data.data.url;
                } else {
                    alert(data.data.message || "خطایی رخ داد.");
                    btn.disabled = false;
                    btn.textContent = "خرید پلن";
                }
            })
            .catch(function () {
                alert("خطا در اتصال.");
                btn.disabled = false;
                btn.textContent = "خرید پلن";
            });
        });
    });

    // ---- Profile Form ----
    var form = document.getElementById("filmworld-profile-form");
    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            var btn = form.querySelector("button[type=submit]");
            var msg = document.getElementById("filmworld-profile-msg");
            btn.disabled = true;
            btn.textContent = "ذخیره...";

            var formData = new FormData(form);
            formData.append("action", "filmworld_update_profile");
            formData.append("nonce", filmworld_ajax.nonce);

            fetch(filmworld_ajax.ajax_url, {
                method: "POST",
                body: formData,
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                btn.disabled = false;
                btn.textContent = "ذخیره تغییرات";
                msg.innerHTML = '<div class="filmworld-notice filmworld-notice-' + (data.success ? 'success' : 'error') + '">' + (data.success ? data.data.message : data.data.message) + '</div>';
            });
        });
    }
});
</script>

<?php get_footer(); ?>