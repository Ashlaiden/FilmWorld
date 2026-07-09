<?php
nocache_headers();
?>
<div class="fw-auth-page">
    <div class="fw-auth-card">
        <div class="fw-auth-header">
            <a href="<?php echo home_url('/'); ?>" class="fw-auth-logo">Film<span>World</span></a>
            <p>وارد حساب کاربری خود شوید</p>
        </div>
        <form id="filmworld-login-form" class="fw-auth-form">
            <div class="fw-form-group">
                <label for="fw-login-user">نام کاربری یا ایمیل</label>
                <input type="text" id="fw-login-user" name="username" required autocomplete="username" placeholder="نام کاربری یا ایمیل">
            </div>
            <div class="fw-form-group">
                <label for="fw-login-pass">رمز عبور</label>
                <input type="password" id="fw-login-pass" name="password" required autocomplete="current-password" placeholder="رمز عبور">
            </div>
            <div class="fw-auth-row">
                <label class="fw-checkbox">
                    <input type="checkbox" name="remember" value="1">
                    <span>مرا به خاطر بسپار</span>
                </label>
                <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">فراموشی رمز عبور</a>
            </div>
            <button type="submit" class="fw-auth-btn">ورود</button>
            <div id="filmworld-login-msg"></div>
        </form>
        <div class="fw-auth-footer">
            حساب کاربری ندارید؟
            <a href="<?php echo esc_url(home_url('/register/')); ?>">ثبت‌نام کنید</a>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    var form = document.getElementById("filmworld-login-form");
    if (!form) return;
    form.addEventListener("submit", function (e) {
        e.preventDefault();
        var btn = form.querySelector(".fw-auth-btn");
        var msg = document.getElementById("filmworld-login-msg");
        btn.disabled = true; btn.textContent = "لطفاً صبر کنید..."; msg.innerHTML = "";
        var formData = new FormData(form);
        formData.append("action", "filmworld_login");
        formData.append("nonce", filmworld_ajax.nonce);
        fetch(filmworld_ajax.ajax_url, { method: "POST", body: formData })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            btn.disabled = false; btn.textContent = "ورود";
            if (data.success) {
                msg.innerHTML = '<div class="fw-auth-notice fw-auth-notice-success">ورود موفق! در حال انتقال...</div>';
                setTimeout(function () { window.location.href = data.data.redirect; }, 700);
            } else {
                msg.innerHTML = '<div class="fw-auth-notice fw-auth-notice-error">' + (data.data.message || "خطایی رخ داد.") + '</div>';
            }
        })
        .catch(function () {
            btn.disabled = false; btn.textContent = "ورود";
            msg.innerHTML = '<div class="fw-auth-notice fw-auth-notice-error">خطا در اتصال.</div>';
        });
    });
});
</script>