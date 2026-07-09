<?php
if (!defined('ABSPATH')) exit;

$post_type = get_post_type();
$is_movie  = ($post_type === 'movie');
$is_series = ($post_type === 'series');

$english_name = get_field('english_name');
$year         = get_field('year');
$imdb_rating  = get_field('imdb_rating');
$genres       = get_the_terms(get_the_ID(), 'genre');

$is_favorited = false;
if (is_user_logged_in()) {
    $favorites = get_user_meta(get_current_user_id(), 'filmworld_favorites', true);
    if (!is_array($favorites)) $favorites = [];
    $is_favorited = in_array(get_the_ID(), $favorites);
}

// Check dubbed
$dubbed_terms = get_the_terms(get_the_ID(), 'dubbed');
$is_dubbed = false;
if ($dubbed_terms && !is_wp_error($dubbed_terms)) {
    foreach ($dubbed_terms as $dt) {
        if ($dt->slug === 'dubbed') { $is_dubbed = true; break; }
    }
}
?>

<div class="fw-card">
    <!-- Badges -->
    <div class="fw-card-badges">
        <span class="fw-card-badge fw-card-badge--type"><?php echo $is_movie ? 'فیلم' : 'سریال'; ?></span>
        <?php if (!empty($imdb_rating)) : ?>
            <span class="fw-card-badge fw-card-badge--imdb">IMDb <?php echo esc_html($imdb_rating); ?></span>
        <?php endif; ?>
        <?php if ($is_dubbed) : ?>
            <span class="fw-card-badge fw-card-badge--dubbed">دوبله</span>
        <?php endif; ?>
    </div>

    <?php if (is_user_logged_in()) : ?>
        <button class="fw-card-fav <?php echo $is_favorited ? 'active' : ''; ?>"
                data-post-id="<?php echo get_the_ID(); ?>"
                aria-label="افزودن به علاقه‌مندی‌ها">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
        </button>
    <?php endif; ?>

    <!-- Poster -->
    <a class="fw-card-poster" href="<?php the_permalink(); ?>">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('medium'); ?>
        <?php else : ?>
            <div style="width:100%;height:100%;background:var(--fw-bg-elevated);display:flex;align-items:center;justify-content:center;color:var(--fw-text-muted);font-size:3rem;">
                <?php echo $is_movie ? '&#127916;' : '&#128250;'; ?>
            </div>
        <?php endif; ?>

        <!-- Hover Overlay -->
        <div class="fw-card-overlay">
            <div class="fw-card-play">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            </div>
        </div>
    </a>

    <!-- Info -->
    <div class="fw-card-info">
        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <div class="fw-card-meta">
            <?php if (!empty($year)) : ?>
                <span><?php echo esc_html($year); ?></span>
                <span class="fw-dot"></span>
            <?php endif; ?>
            <?php
            if ($genres && !is_wp_error($genres) && !empty($genres)) {
                $genre_names = array_map(function($g) { return $g->name; }, array_slice($genres, 0, 2));
                echo '<span>' . esc_html(implode(' / ', $genre_names)) . '</span>';
            }
            ?>
        </div>
    </div>
</div>