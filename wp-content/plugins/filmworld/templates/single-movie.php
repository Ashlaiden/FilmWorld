<?php

get_header();

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

?>

<div class="filmworld-movie">

    <div class="filmworld-movie-header">

        <div class="filmworld-poster">

            <?php if (has_post_thumbnail()) : ?>

                <?php the_post_thumbnail('large'); ?>

            <?php else : ?>

                <div style="width:100%;aspect-ratio:2/3;background:var(--fw-bg);border-radius:var(--fw-radius-sm);display:flex;align-items:center;justify-content:center;color:var(--fw-text-muted);font-size:3rem;">&#127916;</div>

            <?php endif; ?>

        </div>

        <div class="filmworld-info">

            <h1><?php the_title(); ?></h1>

            <?php if (!empty($english_name)) : ?>

                <h3><?php echo esc_html($english_name); ?></h3>

            <?php endif; ?>

            <div class="filmworld-info-meta">

                <?php if (!empty($year)) : ?>
                    <div class="filmworld-info-meta-item">
                        <span>سال ساخت</span>
                        <strong><?php echo esc_html($year); ?></strong>
                    </div>
                <?php endif; ?>

                <?php if (!empty($duration)) : ?>
                    <div class="filmworld-info-meta-item">
                        <span>مدت زمان</span>
                        <strong><?php echo esc_html($duration); ?></strong>
                    </div>
                <?php endif; ?>

                <?php if (!empty($director)) : ?>
                    <div class="filmworld-info-meta-item">
                        <span>کارگردان</span>
                        <strong><?php echo esc_html($director); ?></strong>
                    </div>
                <?php endif; ?>

            </div>

            <?php if ($genres && !is_wp_error($genres)) : ?>

                <div class="filmworld-meta-tags">

                    <?php foreach ($genres as $genre) : ?>

                        <a href="<?php echo esc_url(get_term_link($genre)); ?>" class="filmworld-meta-tag">
                            <?php echo esc_html($genre->name); ?>
                        </a>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

            <?php if ($countries && !is_wp_error($countries)) : ?>

                <div class="filmworld-meta-tags" style="margin-top: 8px;">

                    <?php foreach ($countries as $country) : ?>

                        <a href="<?php echo esc_url(get_term_link($country)); ?>" class="filmworld-meta-tag">
                            <?php echo esc_html($country->name); ?>
                        </a>

                    <?php endforeach; ?>

                    <?php if ($dubbed && !is_wp_error($dubbed)) : ?>

                        <?php foreach ($dubbed as $item) : ?>

                            <a href="<?php echo esc_url(get_term_link($item)); ?>" class="filmworld-meta-tag">
                                <?php echo esc_html($item->name); ?>
                            </a>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

            <?php if (!empty($imdb_rating)) : ?>

                <div class="filmworld-imdb-badge">
                    IMDB: <?php echo esc_html($imdb_rating); ?>
                    <?php if (!empty($imdb_link)) : ?>
                        / <a href="<?php echo esc_url($imdb_link); ?>" target="_blank">مشاهده</a>
                    <?php endif; ?>
                </div>

            <?php endif; ?>

            <div class="filmworld-actions">

                <?php if (!empty($stream_url)) : ?>

                    <button class="filmworld-stream-btn" data-video="<?php echo esc_url($stream_url); ?>">
                        &#9654; پخش آنلاین
                    </button>

                <?php endif; ?>

            </div>

        </div>

    </div>

    <!-- Player Modal -->
    <div id="filmworld-player-modal">
        <button id="filmworld-player-close">&times;</button>
        <video id="filmworld-video-player" controls playsinline></video>
    </div>

    <?php if (trim(get_the_content()) !== '') : ?>

        <div class="filmworld-description">

            <?php the_content(); ?>

        </div>

    <?php endif; ?>

    <div class="filmworld-downloads">

        <h2>لینک‌های دانلود</h2>

        <?php if (!$has_access) : ?>

            <div class="filmworld-locked">
                <div class="filmworld-locked-icon">&#128274;</div>
                <p>برای دسترسی به لینک‌های دانلود، ابتدا باید عضو ویژه شوید.</p>
                <a href="<?php echo esc_url(home_url('/account/?tab=membership')); ?>" class="filmworld-lock-btn">
                    عضویت ویژه
                </a>
            </div>

        <?php elseif ($download_links->have_posts()) : ?>

            <table class="filmworld-download-table">

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

                <?php while ($download_links->have_posts()) : $download_links->the_post(); ?>

                    <?php

                    $quality     = get_field('quality');
                    $size        = get_field('size');
                    $encoder     = get_field('encoder');
                    $language    = get_field('language');
                    $subtitle    = get_field('subtitle');
                    $downloadUrl = get_field('download_url');

                    switch ($language) {
                        case 'dubbed':
                            $language_text = 'دوبله فارسی';
                            break;
                        case 'dual':
                            $language_text = 'دو زبانه';
                            break;
                        default:
                            $language_text = 'زبان اصلی';
                            break;
                    }

                    ?>

                    <tr>

                        <td><strong><?php echo esc_html($quality); ?></strong></td>

                        <td><?php echo esc_html($size); ?></td>

                        <td><?php echo esc_html($encoder); ?></td>

                        <td><?php echo esc_html($language_text); ?></td>

                        <td><?php echo $subtitle ? '&#9989;' : '&#10060;'; ?></td>

                        <td>
                            <div class="filmworld-download-actions">

                                <?php if (!empty($downloadUrl)) : ?>

                                    <a class="filmworld-download-btn" href="<?php echo esc_url($downloadUrl); ?>" target="_blank" rel="noopener">
                                        &#11015; دانلود
                                    </a>

                                <?php endif; ?>

                            </div>
                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

            <?php wp_reset_postdata(); ?>

        <?php else : ?>

            <div class="filmworld-locked">
                <p>هنوز لینک دانلودی برای این فیلم ثبت نشده است.</p>
            </div>

        <?php endif; ?>

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
            'posts_per_page' => 6,
            'post__not_in'   => [get_the_ID()],
            'tax_query'      => [
                [
                    'taxonomy' => 'genre',
                    'field'    => 'term_id',
                    'terms'    => $genre_ids,
                ],
            ],
        ]);

        if ($related_movies->have_posts()) :
    ?>

    <div class="filmworld-related">

        <h2>فیلم‌های مشابه</h2>

        <div class="filmworld-related-grid">

            <?php while ($related_movies->have_posts()) : $related_movies->the_post(); ?>

                <div class="filmworld-related-card">

                    <a href="<?php the_permalink(); ?>">

                        <?php if (has_post_thumbnail()) : ?>

                            <?php the_post_thumbnail('medium'); ?>

                        <?php endif; ?>

                    </a>

                    <div class="card-body">

                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

                        <?php
                        $related_year = get_field('year');
                        if (!empty($related_year)) :
                        ?>
                            <p><?php echo esc_html($related_year); ?></p>
                        <?php endif; ?>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    </div>

    <?php
            wp_reset_postdata();
        endif;
    endif;
    ?>

</div>

<?php get_footer(); ?>