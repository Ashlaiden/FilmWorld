<?php
/**
 * FilmWorld Single Movie — Cinema-style detail page
 */

while (have_posts()) :
    the_post();

    $english_name = get_field('english_name');
    $year         = get_field('year');
    $duration     = get_field('duration');
    $stream_url   = get_field('stream_url');
    $imdb_rating  = get_field('imdb_rating');
    $imdb_link    = get_field('imdb_link');
    $director     = get_field('director');

    $genres    = get_the_terms(get_the_ID(), 'genre');
    $countries = get_the_terms(get_the_ID(), 'country');
    $dubbed    = get_the_terms(get_the_ID(), 'dubbed');

    $download_links = new WP_Query([
        'post_type'      => 'movie_link',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_key'       => 'movie',
        'meta_value'     => get_the_ID(),
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ]);

    $has_access = function_exists('filmworld_has_access') ? filmworld_has_access() : true;

    // Get backdrop (try large thumbnail, fall back to medium)
    $backdrop_url = '';
    if (has_post_thumbnail()) {
        $backdrop_url = get_the_post_thumbnail_url(get_the_ID(), 'large') ?: get_the_post_thumbnail_url(get_the_ID(), 'medium');
    }
?>

<!-- Hero with Backdrop -->
<div class="fw-single-hero">
    <?php if ($backdrop_url) : ?>
    <div class="fw-single-backdrop">
        <img src="<?php echo esc_url($backdrop_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
    </div>
    <?php endif; ?>
    <div class="fw-single-gradient"></div>
    <div class="fw-single-info-wrapper">
        <div class="fw-single-poster">
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('medium'); ?>
            <?php else : ?>
                <div style="width:100%;aspect-ratio:2/3;background:var(--fw-bg-elevated);display:flex;align-items:center;justify-content:center;color:var(--fw-text-muted);font-size:4rem;">&#127916;</div>
            <?php endif; ?>
        </div>
        <div class="fw-single-info">
            <h1><?php the_title(); ?></h1>
            <?php if (!empty($english_name)) : ?>
                <p class="fw-english-name"><?php echo esc_html($english_name); ?></p>
            <?php endif; ?>

            <div class="fw-single-meta-row">
                <?php if (!empty($year)) : ?>
                    <span class="fw-single-meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <?php echo esc_html($year); ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($duration)) : ?>
                    <span class="fw-single-meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <?php echo esc_html($duration); ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($director)) : ?>
                    <span class="fw-single-meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        <?php echo esc_html($director); ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($imdb_rating)) : ?>
                    <span class="fw-single-imdb">
                        IMDb <?php echo esc_html($imdb_rating); ?>
                        <?php if (!empty($imdb_link)) : ?>
                            <a href="<?php echo esc_url($imdb_link); ?>" target="_blank">مشاهده</a>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php if ($genres && !is_wp_error($genres)) : ?>
                <div class="fw-meta-tags">
                    <?php foreach ($genres as $genre) : ?>
                        <a href="<?php echo esc_url(get_term_link($genre)); ?>" class="fw-meta-tag"><?php echo esc_html($genre->name); ?></a>
                    <?php endforeach; ?>
                    <?php if ($countries && !is_wp_error($countries)) : ?>
                        <?php foreach ($countries as $country) : ?>
                            <a href="<?php echo esc_url(get_term_link($country)); ?>" class="fw-meta-tag"><?php echo esc_html($country->name); ?></a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if ($dubbed && !is_wp_error($dubbed)) : ?>
                        <?php foreach ($dubbed as $item) : ?>
                            <a href="<?php echo esc_url(get_term_link($item)); ?>" class="fw-meta-tag"><?php echo esc_html($item->name); ?></a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="fw-single-actions">
                <?php if (!empty($stream_url)) : ?>
                    <button class="fw-single-btn fw-single-btn--play" data-video="<?php echo esc_url($stream_url); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        پخش آنلاین
                    </button>
                <?php endif; ?>
                <a href="#downloads" class="fw-single-btn fw-single-btn--download">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    دانلود فیلم
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Player Modal -->
<div id="filmworld-player-modal">
    <button id="filmworld-player-close">&times;</button>
    <video id="filmworld-video-player" controls playsinline></video>
</div>

<!-- Content -->
<div class="fw-single-content">

    <!-- Description -->
    <?php if (trim(get_the_content()) !== '') : ?>
    <div class="fw-single-section">
        <h2>خلاصه داستان</h2>
        <div class="fw-description"><?php the_content(); ?></div>
    </div>
    <?php endif; ?>

    <!-- Downloads -->
    <div class="fw-single-section" id="downloads">
        <h2>لینک‌های دانلود</h2>

        <?php if (!$has_access) : ?>
            <div class="fw-locked">
                <div class="fw-locked-icon">&#128274;</div>
                <p>برای دسترسی به لینک‌های دانلود، ابتدا باید عضو ویژه شوید.</p>
                <a href="<?php echo esc_url(home_url('/account/?tab=membership')); ?>" class="fw-lock-btn">عضویت ویژه</a>
            </div>

        <?php elseif ($download_links->have_posts()) : ?>
            <table class="fw-download-table">
                <thead>
                    <tr>
                        <th>کیفیت</th>
                        <th>حجم</th>
                        <th>انکودر</th>
                        <th>زبان</th>
                        <th>زیرنویس</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($download_links->have_posts()) : $download_links->the_post();
                    $quality     = get_field('quality');
                    $size        = get_field('size');
                    $encoder     = get_field('encoder');
                    $language    = get_field('language');
                    $subtitle    = get_field('subtitle');
                    $downloadUrl = get_field('download_url');

                    switch ($language) {
                        case 'dubbed': $language_text = 'دوبله فارسی'; break;
                        case 'dual':   $language_text = 'دو زبانه'; break;
                        default:       $language_text = 'زبان اصلی'; break;
                    }
                ?>
                    <tr>
                        <td><strong style="color:var(--fw-text)"><?php echo esc_html($quality); ?></strong></td>
                        <td><?php echo esc_html($size); ?></td>
                        <td><?php echo esc_html($encoder); ?></td>
                        <td><?php echo esc_html($language_text); ?></td>
                        <td><?php echo $subtitle ? '&#9989;' : '&#10060;'; ?></td>
                        <td>
                            <?php if (!empty($downloadUrl)) : ?>
                                <a class="fw-download-btn" href="<?php echo esc_url($downloadUrl); ?>" target="_blank" rel="noopener">دانلود</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php wp_reset_postdata(); ?>

        <?php else : ?>
            <div class="fw-locked">
                <p>هنوز لینک دانلودی برای این فیلم ثبت نشده است.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
endwhile;

// Related Movies
$genre_ids = [];
if (isset($genres) && $genres && !is_wp_error($genres)) {
    $genre_ids = wp_list_pluck($genres, 'term_id');
}

if (!empty($genre_ids)) :
    $related_movies = new WP_Query([
        'post_type'      => 'movie',
        'posts_per_page' => 15,
        'post__not_in'   => [get_the_ID()],
        'tax_query'      => [[
            'taxonomy' => 'genre',
            'field'    => 'term_id',
            'terms'    => $genre_ids,
        ]],
    ]);

    if ($related_movies->have_posts()) :
?>
<div class="fw-related-section">
    <div class="fw-section-title">
        <h2>فیلم‌های مشابه</h2>
    </div>
    <div class="fw-slider-wrapper">
        <button class="fw-slider-arrow fw-slider-arrow--prev" type="button" aria-label="قبلی">
            <svg viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="fw-slider">
            <?php while ($related_movies->have_posts()) : $related_movies->the_post(); ?>
                <?php include __DIR__ . '/parts/media-card.php'; ?>
            <?php endwhile; ?>
        </div>
        <button class="fw-slider-arrow fw-slider-arrow--next" type="button" aria-label="بعدی">
            <svg viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </div>
</div>
<?php
        wp_reset_postdata();
    endif;
endif;
?>