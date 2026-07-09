<?php
/**
 * FilmWorld Front Page — zarfilm-style with hero banner + horizontal sliders
 */

// Featured movie (latest published)
$featured = new WP_Query([
    'post_type'      => ['movie', 'series'],
    'posts_per_page' => 1,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => [[
        'key'     => 'stream_url',
        'value'   => '',
        'compare' => '!=',
    ]],
]);

// Fallback: any latest
if (!$featured->have_posts()) {
    $featured = new WP_Query([
        'post_type'      => ['movie', 'series'],
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
}

// Sections data
$sections = [
    [
        'title'    => 'جدیدترین فیلم‌ها',
        'more_url' => get_post_type_archive_link('movie'),
        'query'    => new WP_Query([
            'post_type'      => 'movie',
            'posts_per_page' => 15,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]),
    ],
    [
        'title'    => 'جدیدترین سریال‌ها',
        'more_url' => get_post_type_archive_link('series'),
        'query'    => new WP_Query([
            'post_type'      => 'series',
            'posts_per_page' => 15,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]),
    ],
    [
        'title'    => 'فیلم‌های دوبله فارسی',
        'more_url' => get_term_link(get_term_by('slug', 'dubbed', 'dubbed')),
        'query'    => new WP_Query([
            'post_type'      => 'movie',
            'posts_per_page' => 15,
            'post_status'    => 'publish',
            'tax_query'      => [[
                'taxonomy' => 'dubbed',
                'field'    => 'slug',
                'terms'    => 'dubbed',
            ]],
        ]),
    ],
];
?>

<!-- Hero Banner -->
<?php if ($featured->have_posts()) : $featured->the_post();
    $fw_en = get_field('english_name');
    $fw_year = get_field('year');
    $fw_imdb = get_field('imdb_rating');
    $fw_genres = get_the_terms(get_the_ID(), 'genre');
    $fw_stream = get_field('stream_url');
?>
<div class="fw-hero">
    <?php if (has_post_thumbnail()) : ?>
    <div class="fw-hero-bg">
        <?php the_post_thumbnail('large'); ?>
    </div>
    <?php endif; ?>
    <div class="fw-hero-gradient"></div>
    <div class="fw-hero-content">
        <?php
        $fw_type = get_post_type() === 'series' ? 'سریال' : 'فیلم';
        ?>
        <div class="fw-hero-badge"><?php echo esc_html($fw_type); ?> پیشنهادی</div>
        <h1 class="fw-hero-title"><?php the_title(); ?></h1>
        <?php if ($fw_en) : ?>
            <p class="fw-hero-subtitle"><?php echo esc_html($fw_en); ?></p>
        <?php endif; ?>
        <div class="fw-hero-meta">
            <?php if ($fw_year) : ?><span><?php echo esc_html($fw_year); ?></span><?php endif; ?>
            <?php
            if ($fw_genres && !is_wp_error($fw_genres)) {
                $gn = array_map(function($g) { return $g->name; }, array_slice($fw_genres, 0, 3));
                echo '<span>' . esc_html(implode(' / ', $gn)) . '</span>';
            }
            ?>
            <?php if ($fw_imdb) : ?>
                <span class="fw-hero-imdb">IMDb <?php echo esc_html($fw_imdb); ?></span>
            <?php endif; ?>
        </div>
        <?php if (trim(get_the_content()) !== '') : ?>
            <p class="fw-hero-desc"><?php echo wp_trim_words(get_the_content(), 40, '...'); ?></p>
        <?php endif; ?>
        <div class="fw-hero-actions">
            <?php if ($fw_stream) : ?>
                <button class="fw-hero-btn fw-hero-btn--primary" data-video="<?php echo esc_url($fw_stream); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    پخش آنلاین
                </button>
            <?php endif; ?>
            <a href="<?php the_permalink(); ?>" class="fw-hero-btn fw-hero-btn--ghost">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                جزئیات و دانلود
            </a>
        </div>
    </div>
</div>
<?php wp_reset_postdata(); endif; ?>

<!-- Sections with Horizontal Sliders -->
<div class="fw-home-sections">
    <?php foreach ($sections as $section) : ?>
    <?php if ($section['query']->have_posts()) : ?>
    <div class="fw-slider-section">
        <div class="fw-section-title">
            <h2><?php echo esc_html($section['title']); ?></h2>
            <?php if ($section['more_url']) : ?>
                <a href="<?php echo esc_url($section['more_url']); ?>">مشاهده همه</a>
            <?php endif; ?>
        </div>
        <div class="fw-slider-wrapper">
            <button class="fw-slider-arrow fw-slider-arrow--prev" type="button" aria-label="قبلی">
                <svg viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <div class="fw-slider">
                <?php while ($section['query']->have_posts()) : $section['query']->the_post(); ?>
                    <?php include __DIR__ . '/parts/media-card.php'; ?>
                <?php endwhile; ?>
            </div>
            <button class="fw-slider-arrow fw-slider-arrow--next" type="button" aria-label="بعدی">
                <svg viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </div>
    <?php wp_reset_postdata(); endif; endforeach; ?>
</div>

<!-- Player Modal -->
<div id="filmworld-player-modal">
    <button id="filmworld-player-close">&times;</button>
    <video id="filmworld-video-player" controls playsinline></video>
</div>