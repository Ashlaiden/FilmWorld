<?php
/**
 * FilmWorld Archive Page — Movies, Series, Taxonomy
 */

$post_type = get_query_var('post_type');
if (is_array($post_type)) {
    $post_type = reset($post_type);
}

if (is_tax()) {
    $queried = get_queried_object();
    $archive_title = $queried->name;
    $tax_desc = !empty($queried->description) ? $queried->description : '';
} else {
    $tax_desc = '';
    switch ($post_type) {
        case 'series': $archive_title = 'سریال‌ها'; break;
        case 'movie':  $archive_title = 'فیلم‌ها'; break;
        default:
            $archive_title = post_type_archive_title('', false);
            if (empty($archive_title)) $archive_title = 'آرشیو';
            break;
    }
}

$genre_terms   = get_terms(['taxonomy' => 'genre', 'hide_empty' => true, 'parent' => 0]);
$country_terms = get_terms(['taxonomy' => 'country', 'hide_empty' => true]);
$has_filters   = (!is_wp_error($genre_terms) && !empty($genre_terms)) || (!is_wp_error($country_terms) && !empty($country_terms));
?>

<div class="fw-archive-page">

    <h1 class="fw-page-title"><?php echo esc_html($archive_title); ?></h1>

    <?php if (!empty($tax_desc)) : ?>
        <div class="fw-tax-desc"><?php echo esc_html($tax_desc); ?></div>
    <?php endif; ?>

    <?php if ($has_filters) : ?>
    <div class="fw-filter-bar">
        <?php if (!is_wp_error($genre_terms) && !empty($genre_terms)) : ?>
        <div class="fw-filter-group">
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
        <div class="fw-filter-group">
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

        <div class="fw-filter-group">
            <label>نوع</label>
            <select onchange="window.location.href=this.value">
                <option value="<?php echo esc_url(get_post_type_archive_link('movie')); ?>" <?php echo ($post_type === 'movie') ? 'selected' : ''; ?>>فیلم‌ها</option>
                <option value="<?php echo esc_url(get_post_type_archive_link('series')); ?>" <?php echo ($post_type === 'series') ? 'selected' : ''; ?>>سریال‌ها</option>
            </select>
        </div>
    </div>
    <?php endif; ?>

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
        <div class="fw-empty">موردی یافت نشد.</div>
    <?php endif; ?>

</div>