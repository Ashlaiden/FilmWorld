<?php

// جلوگیری از کش
nocache_headers();

get_header();

?>

<div class="filmworld-auth-page">

    <div class="filmworld-auth-card">

        <div class="filmworld-auth-header">
            <h1>Film<span>World</span></h1>
            <p>وارد حساب کاربری خود شوید</p>
        </div>

        <form id="filmworld-login-form" class="filmworld-auth-form">

            <div class="filmworld-form-group">
                <label for="fw-login-user">نام کاربری یا ایمیل</label>
                <input type="text" id="fw-login-user" name="username" required autocomplete="username" placeholder="نام کاربری یا ایمیل">
            </div>

            <div class="filmworld-form-group">
                <label for="fw-login-pass">رمز عبور</label>
                <input type="password" id="fw-login-pass" name="password" required autocomplete="current-password" placeholder="رمز عبور">
            </div>

            <div class="filmworld-form-row">
                <label class="filmworld-checkbox">
                    <input type="checkbox" name="remember" value="1">
                    <span>مرا به خاطر بسپار</span>
                </label>
                <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">فراموشی رمز عبور</a>
            </div>

            <button type="submit" class="filmworld-auth-btn">ورود</button>

            <div id="filmworld-login-msg"></div>

        </form>

        <div class="filmworld-auth-footer">
            حساب کاربری ندارید؟
            <a href="<?php echo esc_url(home_url('/register/')); ?>">ثبت‌نام کنید</a>
        </div>

    </div>

</div>

<style>
.filmworld-auth-page {
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
}
.filmworld-auth-card {
    width: 100%;
    max-width: 420px;
    background: var(--fw-bg-card, #fff);
    border-radius: 16px;
    padding: 40px 35px;
    box-shadow: 0 4px 30px rgba(0,0,0,0.1);
}
.filmworld-auth-header {
    text-align: center;
    margin-bottom: 30px;
}
.filmworld-auth-header h1 {
    font-size: 2rem;
    font-weight: 900;
    margin: 0 0 8px;
    color: var(--fw-text, #1a1a2e);
}
.filmworld-auth-header h1 span {
    color: #e50914;
}
.filmworld-auth-header p {
    color: var(--fw-text-muted, #888);
    margin: 0;
    font-size: 0.95rem;
}
.filmworld-auth-form .filmworld-form-group {
    margin-bottom: 18px;
}
.filmworld-auth-form label {
    display: block;
    font-weight: 700;
    margin-bottom: 6px;
    font-size: 0.88rem;
    color: var(--fw-text-secondary, #555);
}
.filmworld-auth-form input[type="text"],
.filmworld-auth-form input[type="password"],
.filmworld-auth-form input[type="email"] {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--fw-border, #e0e0e0);
    border-radius: 10px;
    background: var(--fw-bg, #f5f5f5);
    color: var(--fw-text, #1a1a2e);
    font-family: var(--fw-font, Tahoma, sans-serif);
    font-size: 0.95rem;
    direction: rtl;
    transition: border-color 0.3s;
    box-sizing: border-box;
}
.filmworld-auth-form input:focus {
    outline: none;
    border-color: #e50914;
}
.filmworld-form-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
    font-size: 0.85rem;
}
.filmworld-form-row a {
    color: #e50914;
    font-weight: 600;
}
.filmworld-checkbox {
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    color: var(--fw-text-secondary, #555);
}
.filmworld-checkbox input {
    width: 16px;
    height: 16px;
    accent-color: #e50914;
}
.filmworld-auth-btn {
    width: 100%;
    padding: 14px;
    background: #e50914;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 700;
    font-family: var(--fw-font, Tahoma, sans-serif);
    cursor: pointer;
    transition: background 0.3s, transform 0.2s;
}
.filmworld-auth-btn:hover {
    background: #f40612;
    transform: translateY(-1px);
}
.filmworld-auth-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
.filmworld-auth-footer {
    text-align: center;
    margin-top: 22px;
    padding-top: 20px;
    border-top: 1px solid var(--fw-border, #e0e0e0);
    font-size: 0.9rem;
    color: var(--fw-text-muted, #888);
}
.filmworld-auth-footer a {
    color: #e50914;
    font-weight: 700;
}
.filmworld-auth-notice {
    padding: 12px 16px;
    border-radius: 8px;
    margin-top: 12px;
    font-size: 0.9rem;
    font-weight: 600;
    text-align: center;
}
.filmworld-auth-notice-error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
.filmworld-auth-notice-success {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}

/* Dark mode */
body.filmworld-dark .filmworld-auth-card {
    background: var(--fw-bg-card, #1a1a2e);
}
body.filmworld-dark .filmworld-auth-footer {
    border-color: var(--fw-border, #2a2a3e);
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {

    var form = document.getElementById("filmworld-login-form");
    if (!form) return;

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        var btn = form.querySelector(".filmworld-auth-btn");
        var msg = document.getElementById("filmworld-login-msg");
        btn.disabled = true;
        btn.textContent = "لطفاً صبر کنید...";
        msg.innerHTML = "";

        var formData = new FormData(form);
        formData.append("action", "filmworld_login");
        formData.append("nonce", filmworld_ajax.nonce);

        fetch(filmworld_ajax.ajax_url, {
            method: "POST",
            body: formData,
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            btn.disabled = false;
            btn.textContent = "ورود";

            if (data.success) {
                msg.innerHTML = '<div class="filmworld-auth-notice filmworld-auth-notice-success">ورود موفق! در حال انتقال...</div>';
                setTimeout(function () {
                    window.location.href = data.data.redirect;
                }, 800);
            } else {
                msg.innerHTML = '<div class="filmworld-auth-notice filmworld-auth-notice-error">' + (data.data.message || "خطایی رخ داد.") + '</div>';
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.textContent = "ورود";
            msg.innerHTML = '<div class="filmworld-auth-notice filmworld-auth-notice-error">خطا در اتصال.</div>';
        });
    });

});
</script>

<?php get_footer(); ?>