<?php

get_header();

$post_type = get_query_var('post_type');
if (is_array($post_type)) {
    $post_type = reset($post_type);
}

// Handle taxonomy pages
if (is_tax()) {
    $queried = get_queried_object();
    $archive_title = $queried->name;

    if (!empty($queried->description)) {
        echo '<div class="filmworld-archive">';
        echo '<div class="filmworld-taxonomy-description">' . esc_html($queried->description) . '</div>';
    }
} else {
    switch ($post_type) {
        case 'series':
            $archive_title = 'سریال‌ها';
            break;
        case 'movie':
            $archive_title = 'فیلم‌ها';
            break;
        default:
            $archive_title = post_type_archive_title('', false);
            if (empty($archive_title)) $archive_title = 'آرشیو';
            break;
    }
    echo '<div class="filmworld-archive">';
}

// Filter Bar
$genre_terms  = get_terms(['taxonomy' => 'genre', 'hide_empty' => true, 'parent' => 0]);
$country_terms = get_terms(['taxonomy' => 'country', 'hide_empty' => true]);

if ((!is_wp_error($genre_terms) && !empty($genre_terms)) || (!is_wp_error($country_terms) && !empty($country_terms))) :
?>

<div class="filmworld-filter-bar">

    <?php if (!is_wp_error($genre_terms) && !empty($genre_terms)) : ?>
        <div class="filmworld-filter-group">
            <label>ژانر</label>
            <select onchange="window.location.href=this.value">
                <option value="">همه ژانرها</option>
                <?php foreach ($genre_terms as $term) : ?>
                    <option value="<?php echo esc_url(get_term_link($term)); ?>" <?php echo (is_tax('genre', $term->slug)) ? 'selected' : ''; ?>>
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <?php if (!is_wp_error($country_terms) && !empty($country_terms)) : ?>
        <div class="filmworld-filter-group">
            <label>کشور</label>
            <select onchange="window.location.href=this.value">
                <option value="">همه کشورها</option>
                <?php foreach ($country_terms as $term) : ?>
                    <option value="<?php echo esc_url(get_term_link($term)); ?>" <?php echo (is_tax('country', $term->slug)) ? 'selected' : ''; ?>>
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <div class="filmworld-filter-group">
        <label>نمایش</label>
        <select onchange="window.location.href=this.value">
            <option value="<?php echo esc_url(get_post_type_archive_link('movie')); ?>" <?php echo ($post_type === 'movie') ? 'selected' : ''; ?>>فیلم‌ها</option>
            <option value="<?php echo esc_url(get_post_type_archive_link('series')); ?>" <?php echo ($post_type === 'series') ? 'selected' : ''; ?>>سریال‌ها</option>
        </select>
    </div>

</div>

<?php endif; ?>

<h1><?php echo esc_html($archive_title); ?></h1>

<?php if (have_posts()) : ?>

    <div class="filmworld-movies-grid">

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

    <p>موردی یافت نشد.</p>

<?php endif; ?>

</div>

<?php get_footer(); ?>