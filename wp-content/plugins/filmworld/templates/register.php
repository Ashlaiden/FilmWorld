<?php
nocache_headers();
?>
<div class="fw-auth-page">
    <div class="fw-auth-card">
        <div class="fw-auth-header">
            <a href="<?php echo home_url('/'); ?>" class="fw-auth-logo">Film<span>World</span></a>
            <p>حساب کاربری جدید بسازید</p>
        </div>
        <form id="filmworld-register-form" class="fw-auth-form">
            <div class="fw-form-group">
                <label for="fw-reg-user">نام کاربری</label>
                <input type="text" id="fw-reg-user" name="username" required autocomplete="username" placeholder="مثال: ali123" minlength="3">
            </div>
            <div class="fw-form-group">
                <label for="fw-reg-email">ایمیل</label>
                <input type="email" id="fw-reg-email" name="email" required autocomplete="email" placeholder="example@mail.com" dir="ltr" style="text-align:left;">
            </div>
            <div class="fw-form-group">
                <label for="fw-reg-pass">رمز عبور</label>
                <input type="password" id="fw-reg-pass" name="password" required autocomplete="new-password" placeholder="حداقل ۶ کاراکتر" minlength="6">
            </div>
            <div class="fw-form-group">
                <label for="fw-reg-confirm">تکرار رمز عبور</label>
                <input type="password" id="fw-reg-confirm" name="confirm_password" required autocomplete="new-password" placeholder="رمز عبور را دوباره وارد کنید">
            </div>
            <button type="submit" class="fw-auth-btn">ثبت‌نام</button>
            <div id="filmworld-register-msg"></div>
        </form>
        <div class="fw-auth-footer">
            قبلاً ثبت‌نام کرده‌اید؟
            <a href="<?php echo esc_url(home_url('/login/')); ?>">وارد شوید</a>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    var form = document.getElementById("filmworld-register-form");
    if (!form) return;
    form.addEventListener("submit", function (e) {
        e.preventDefault();
        var btn = form.querySelector(".fw-auth-btn");
        var msg = document.getElementById("filmworld-register-msg");
        btn.disabled = true; btn.textContent = "لطفاً صبر کنید..."; msg.innerHTML = "";
        var formData = new FormData(form);
        formData.append("action", "filmworld_register");
        formData.append("nonce", filmworld_ajax.nonce);
        fetch(filmworld_ajax.ajax_url, { method: "POST", body: formData })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            btn.disabled = false; btn.textContent = "ثبت‌نام";
            if (data.success) {
                msg.innerHTML = '<div class="fw-auth-notice fw-auth-notice-success">ثبت‌نام موفق! در حال انتقال...</div>';
                setTimeout(function () { window.location.href = data.data.redirect; }, 700);
            } else {
                msg.innerHTML = '<div class="fw-auth-notice fw-auth-notice-error">' + (data.data.message || "خطایی رخ داد.") + '</div>';
            }
        })
        .catch(function () {
            btn.disabled = false; btn.textContent = "ثبت‌نام";
            msg.innerHTML = '<div class="fw-auth-notice fw-auth-notice-error">خطا در اتصال.</div>';
        });
    });
});
</script>