<?php

if (!defined('ABSPATH')) {
    exit;
}

$post_type = get_post_type();
$is_movie  = ($post_type === 'movie');
$is_series = ($post_type === 'series');

$english_name = get_field('english_name');
$year         = get_field('year');
$genres       = get_the_terms(get_the_ID(), 'genre');

// Check if favorited
$is_favorited = false;
if (is_user_logged_in()) {
    $favorites = get_user_meta(get_current_user_id(), 'filmworld_favorites', true);
    if (!is_array($favorites)) $favorites = [];
    $is_favorited = in_array(get_the_ID(), $favorites);
}

?>

<div class="filmworld-media-item">

    <?php if (is_user_logged_in()) : ?>

        <button class="filmworld-fav-btn <?php echo $is_favorited ? 'active' : ''; ?>"
                data-post-id="<?php echo get_the_ID(); ?>"
                aria-label="افزودن به علاقه‌مندی‌ها">
            <?php echo $is_favorited ? '&#10084;' : '&#9825;'; ?>
        </button>

    <?php endif; ?>

    <a class="filmworld-media-poster" href="<?php the_permalink(); ?>">

        <?php if (has_post_thumbnail()) : ?>

            <?php the_post_thumbnail('medium'); ?>

        <?php else : ?>

            <div style="width:100%;height:100%;background:var(--fw-bg);display:flex;align-items:center;justify-content:center;color:var(--fw-text-muted);font-size:3rem;">
                <?php echo $is_movie ? '&#127916;' : '&#128250;'; ?>
            </div>

        <?php endif; ?>

        <span class="filmworld-media-type">

            <?php echo $is_movie ? 'فیلم' : 'سریال'; ?>

        </span>

    </a>

    <div class="filmworld-media-content">

        <h2>
            <a href="<?php the_permalink(); ?>">
                <?php the_title(); ?>
            </a>
        </h2>

        <?php if (!empty($english_name)) : ?>

            <p class="filmworld-english-title">
                <?php echo esc_html($english_name); ?>
            </p>

        <?php endif; ?>

        <?php if (!empty($year)) : ?>

            <p>
                <strong>سال:</strong>
                <?php echo esc_html($year); ?>
            </p>

        <?php endif; ?>

        <?php if ($genres && !is_wp_error($genres) && !empty($genres)) : ?>

            <p>
                <?php
                $genre_names = array_map(function($g) { return $g->name; }, array_slice($genres, 0, 2));
                echo esc_html(implode(' / ', $genre_names));
                if (count($genres) > 2) echo ' ...';
                ?>
            </p>

        <?php endif; ?>

        <a class="filmworld-view-btn" href="<?php the_permalink(); ?>">
            <?php echo $is_movie ? 'مشاهده فیلم' : 'مشاهده سریال'; ?>
        </a>

    </div>

</div>