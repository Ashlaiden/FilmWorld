<?php
/**
 * FilmWorld Search Page
 */

global $wp_query;
$total_results = $wp_query->found_posts;
?>

<div class="fw-search-page">

    <h1 class="fw-search-title">نتایج جستجو</h1>
    <p class="fw-search-count"><?php echo esc_html($total_results); ?> نتیجه برای "<?php echo esc_html(get_search_query()); ?>"</p>

    <div class="fw-search-inline">
        <form method="get" action="<?php echo esc_url(home_url('/')); ?>" style="width:100%;display:flex;gap:10px;">
            <input type="search" name="s" placeholder="جستجوی فیلم و سریال..." value="<?php echo esc_attr(get_search_query()); ?>" style="flex:1;">
            <button type="submit" class="fw-btn fw-btn-primary" style="flex-shrink:0;">جستجو</button>
        </form>
    </div>

    <?php if (have_posts()) : ?>
        <div class="fw-grid">
            <?php while (have_posts()) : the_post(); ?>
                <?php include __DIR__ . '/parts/media-card.php'; ?>
            <?php endwhile; ?>
        </div>

        <?php the_posts_pagination([
            'mid_size'  => 2,
            'prev_text' => '&laquo; قبلی',
            'next_text' => 'بعدی &raquo;',
        ]); ?>
    <?php else : ?>
        <div class="fw-empty">هیچ فیلم یا سریالی پیدا نشد.</div>
    <?php endif; ?>

</div>