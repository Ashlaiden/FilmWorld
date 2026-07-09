<?php
if (!defined('ABSPATH')) exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body <?php body_class('filmworld-body'); ?>>
<?php wp_body_open(); ?>

<header class="fw-header" id="fw-header">
    <div class="fw-header-inner">
        <!-- Logo -->
        <a href="<?php echo home_url('/'); ?>" class="fw-logo">
            <svg class="fw-logo-icon" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="2" y1="7" x2="7" y2="7"/><line x1="2" y1="17" x2="7" y2="17"/><line x1="17" y1="7" x2="22" y2="7"/><line x1="17" y1="17" x2="22" y2="17"/></svg>
            Film<span>World</span>
        </a>

        <!-- Desktop Navigation -->
        <nav class="fw-nav" id="fw-nav">
            <a href="<?php echo home_url('/'); ?>" class="fw-nav-link <?php echo is_front_page() ? 'active' : ''; ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span>خانه</span>
            </a>
            <a href="<?php echo esc_url(get_post_type_archive_link('movie')); ?>" class="fw-nav-link <?php echo is_post_type_archive('movie') ? 'active' : ''; ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
                <span>فیلم‌ها</span>
            </a>
            <a href="<?php echo esc_url(get_post_type_archive_link('series')); ?>" class="fw-nav-link <?php echo is_post_type_archive('series') ? 'active' : ''; ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2" ry="2"/><polyline points="17 2 12 7 7 2"/></svg>
                <span>سریال‌ها</span>
            </a>
            <?php
            $fw_header_genres = get_terms(['taxonomy' => 'genre', 'hide_empty' => true, 'parent' => 0, 'number' => 6]);
            if (!is_wp_error($fw_header_genres) && !empty($fw_header_genres)) :
            ?>
            <div class="fw-nav-dropdown">
                <button class="fw-nav-link fw-dropdown-toggle">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/></svg>
                    <span>ژانرها</span>
                    <svg class="fw-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="fw-dropdown-menu">
                    <?php foreach ($fw_header_genres as $term) : ?>
                        <a href="<?php echo esc_url(get_term_link($term)); ?>" class="fw-dropdown-item"><?php echo esc_html($term->name); ?></a>
                    <?php endforeach; ?>
                    <a href="<?php echo esc_url(get_post_type_archive_link('movie')); ?>" class="fw-dropdown-item fw-dropdown-all">همه ژانرها</a>
                </div>
            </div>
            <?php endif; ?>
        </nav>

        <!-- Header Right -->
        <div class="fw-header-right">
            <button class="fw-search-toggle" id="fw-search-toggle" aria-label="جستجو">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>

            <?php if (is_user_logged_in()) : ?>
                <div class="fw-user-menu">
                    <button class="fw-user-btn" id="fw-user-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span class="fw-user-name"><?php echo esc_html(wp_get_current_user()->display_name); ?></span>
                    </button>
                    <div class="fw-dropdown-menu fw-user-dropdown" id="fw-user-dropdown">
                        <a href="<?php echo esc_url(home_url('/account/')); ?>" class="fw-dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                            داشبورد
                        </a>
                        <a href="<?php echo esc_url(home_url('/account/?tab=favorites')); ?>" class="fw-dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                            علاقه‌مندی‌ها
                        </a>
                        <a href="<?php echo esc_url(home_url('/account/?tab=membership')); ?>" class="fw-dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            عضویت ویژه
                        </a>
                        <div class="fw-dropdown-divider"></div>
                        <a href="<?php echo wp_logout_url(home_url()); ?>" class="fw-dropdown-item fw-dropdown-logout">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            خروج
                        </a>
                    </div>
                </div>
            <?php else : ?>
                <a href="<?php echo esc_url(home_url('/login/')); ?>" class="fw-btn fw-btn-ghost">ورود</a>
                <a href="<?php echo esc_url(home_url('/register/')); ?>" class="fw-btn fw-btn-primary">ثبت‌نام</a>
            <?php endif; ?>

            <button class="fw-mobile-toggle" id="fw-mobile-toggle" aria-label="منو">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

    <!-- Search Overlay -->
    <div class="fw-search-overlay" id="fw-search-overlay">
        <form method="get" action="<?php echo esc_url(home_url('/')); ?>" class="fw-search-form">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="search" name="s" placeholder="نام فیلم یا سریال را جستجو کنید..." value="<?php echo esc_attr(get_search_query()); ?>" autocomplete="off" id="fw-search-input">
            <button type="button" class="fw-search-close" id="fw-search-close">&times;</button>
        </form>
    </div>

    <!-- Mobile Nav -->
    <div class="fw-mobile-nav" id="fw-mobile-nav">
        <nav class="fw-mobile-nav-inner">
            <a href="<?php echo home_url('/'); ?>" class="fw-mobile-link <?php echo is_front_page() ? 'active' : ''; ?>">خانه</a>
            <a href="<?php echo esc_url(get_post_type_archive_link('movie')); ?>" class="fw-mobile-link <?php echo is_post_type_archive('movie') ? 'active' : ''; ?>">فیلم‌ها</a>
            <a href="<?php echo esc_url(get_post_type_archive_link('series')); ?>" class="fw-mobile-link <?php echo is_post_type_archive('series') ? 'active' : ''; ?>">سریال‌ها</a>
            <?php if (!is_wp_error($fw_header_genres) && !empty($fw_header_genres)) : ?>
                <div class="fw-mobile-section-title">ژانرها</div>
                <?php foreach ($fw_header_genres as $term) : ?>
                    <a href="<?php echo esc_url(get_term_link($term)); ?>" class="fw-mobile-link fw-mobile-sub"><?php echo esc_html($term->name); ?></a>
                <?php endforeach; ?>
            <?php endif; ?>
            <div class="fw-mobile-divider"></div>
            <?php if (is_user_logged_in()) : ?>
                <a href="<?php echo esc_url(home_url('/account/')); ?>" class="fw-mobile-link">حساب کاربری</a>
                <a href="<?php echo esc_url(home_url('/account/?tab=favorites')); ?>" class="fw-mobile-link">علاقه‌مندی‌ها</a>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="fw-mobile-link fw-mobile-logout">خروج</a>
            <?php else : ?>
                <a href="<?php echo esc_url(home_url('/login/')); ?>" class="fw-mobile-link">ورود</a>
                <a href="<?php echo esc_url(home_url('/register/')); ?>" class="fw-mobile-link">ثبت‌نام</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="fw-main">