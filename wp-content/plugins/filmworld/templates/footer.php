<?php
if (!defined('ABSPATH')) exit;
?>
</main>

<footer class="fw-footer">
    <div class="fw-footer-inner">
        <div class="fw-footer-grid">
            <div class="fw-footer-col">
                <a href="<?php echo home_url('/'); ?>" class="fw-footer-logo">Film<span>World</span></a>
                <p class="fw-footer-desc">مرجع دانلود و تماشای فیلم و سریال با زیرنویس فارسی. بهترین کیفیت و سریع‌ترین سرورها.</p>
            </div>
            <div class="fw-footer-col">
                <h4>دسترسی سریع</h4>
                <a href="<?php echo home_url('/'); ?>">صفحه اصلی</a>
                <a href="<?php echo esc_url(get_post_type_archive_link('movie')); ?>">فیلم‌ها</a>
                <a href="<?php echo esc_url(get_post_type_archive_link('series')); ?>">سریال‌ها</a>
            </div>
            <div class="fw-footer-col">
                <h4>ژانرها</h4>
                <?php
                $fw_footer_genres = get_terms(['taxonomy' => 'genre', 'hide_empty' => true, 'parent' => 0, 'number' => 5]);
                if (!is_wp_error($fw_footer_genres)) :
                    foreach ($fw_footer_genres as $term) :
                ?>
                    <a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo esc_html($term->name); ?></a>
                <?php endforeach; endif; ?>
            </div>
            <div class="fw-footer-col">
                <h4>حساب کاربری</h4>
                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo esc_url(home_url('/account/')); ?>">داشبورد</a>
                    <a href="<?php echo esc_url(home_url('/account/?tab=favorites')); ?>">علاقه‌مندی‌ها</a>
                    <a href="<?php echo esc_url(home_url('/account/?tab=membership')); ?>">عضویت ویژه</a>
                    <a href="<?php echo wp_logout_url(home_url()); ?>">خروج</a>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/login/')); ?>">ورود</a>
                    <a href="<?php echo esc_url(home_url('/register/')); ?>">ثبت‌نام</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="fw-footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> FilmWorld. تمامی حقوق محفوظ است.</p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>