<?php

get_header();

while (have_posts()) :
    the_post();

    $english_name = get_field('english_name');
    $year         = get_field('year');
    $stream_url   = get_field('stream_url');
    $imdb_rating  = get_field('imdb_rating');
    $imdb_link    = get_field('imdb_link');
    $director     = get_field('director');

    $genres    = get_the_terms(get_the_ID(), 'genre');
    $countries = get_the_terms(get_the_ID(), 'country');
    $dubbed    = get_the_terms(get_the_ID(), 'dubbed');

    $has_access = function_exists('filmworld_has_access') ? filmworld_has_access() : true;

    // Get Episodes
    $episodes = new WP_Query([
        'post_type'      => 'series_link',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => [
            [
                'key'   => 'series',
                'value' => get_the_ID(),
            ]
        ],
        'orderby'  => 'menu_order',
        'order'    => 'ASC',
    ]);

    // Group by Season > Quality
    $seasons = [];
    if ($episodes->have_posts()) {
        while ($episodes->have_posts()) {
            $episodes->the_post();

            $season   = get_field('season');
            $episode  = get_field('episode');
            $quality  = get_field('quality');
            $size     = get_field('size');
            $encoder  = get_field('encoder');
            $language = get_field('language');
            $subtitle = get_field('subtitle');
            $url      = get_field('download_url');

            if (!isset($seasons[$season])) $seasons[$season] = [];
            if (!isset($seasons[$season][$quality])) $seasons[$season][$quality] = [];

            $seasons[$season][$quality][] = [
                'episode' => $episode,
                'size'    => $size,
                'encoder' => $encoder,
                'lang'    => $language,
                'sub'     => $subtitle,
                'url'     => $url,
            ];
        }
        wp_reset_postdata();
    }

?>

<div class="filmworld-series">

    <div class="filmworld-header">

        <?php if (has_post_thumbnail()) : ?>

            <div class="filmworld-poster">
                <?php the_post_thumbnail('large'); ?>
            </div>

        <?php endif; ?>

        <div class="filmworld-info">

            <h1><?php the_title(); ?></h1>

            <?php if ($english_name) : ?>
                <h3><?php echo esc_html($english_name); ?></h3>
            <?php endif; ?>

            <div class="filmworld-info-meta">

                <?php if (!empty($year)) : ?>
                    <div class="filmworld-info-meta-item">
                        <span>سال انتشار</span>
                        <strong><?php echo esc_html($year); ?></strong>
                    </div>
                <?php endif; ?>

                <?php if (!empty($seasons)) : ?>
                    <div class="filmworld-info-meta-item">
                        <span>تعداد فصل</span>
                        <strong><?php echo count($seasons); ?> فصل</strong>
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

                <div class="filmworld-meta-tags" style="margin-top: 12px;">
                    <?php foreach ($genres as $genre) : ?>
                        <a href="<?php echo esc_url(get_term_link($genre)); ?>" class="filmworld-meta-tag">
                            <?php echo esc_html($genre->name); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

            <?php if (!empty($imdb_rating)) : ?>
                <div class="filmworld-imdb-badge" style="margin-top: 12px;">
                    IMDB: <?php echo esc_html($imdb_rating); ?>
                    <?php if (!empty($imdb_link)) : ?>
                        / <a href="<?php echo esc_url($imdb_link); ?>" target="_blank">مشاهده</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($stream_url)) : ?>
                <div class="filmworld-actions" style="margin-top: 16px;">
                    <button class="filmworld-stream-btn" data-video="<?php echo esc_url($stream_url); ?>">
                        &#9654; پخش آنلاین
                    </button>
                </div>
            <?php endif; ?>

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

    <div class="filmworld-seasons">

        <h2>قسمت‌ها</h2>

        <?php if (!$has_access) : ?>

            <div class="filmworld-locked">
                <div class="filmworld-locked-icon">&#128274;</div>
                <p>برای دسترسی به لینک‌های دانلود، ابتدا باید عضو ویژه شوید.</p>
                <a href="<?php echo esc_url(home_url('/account/?tab=membership')); ?>" class="filmworld-lock-btn">
                    عضویت ویژه
                </a>
            </div>

        <?php elseif (!empty($seasons)) : ?>

            <?php foreach ($seasons as $season_number => $qualities) : ?>

                <div class="filmworld-season">

                    <h3>فصل <?php echo esc_html($season_number); ?></h3>

                    <?php foreach ($qualities as $quality => $episodes_list) : ?>

                        <div class="filmworld-quality-group">

                            <h4><?php echo esc_html($quality); ?></h4>

                            <div class="filmworld-episodes">

                                <?php foreach ($episodes_list as $ep) : ?>

                                    <div class="filmworld-episode">

                                        <span>قسمت <?php echo esc_html($ep['episode']); ?></span>

                                        <div style="display:flex;gap:8px;align-items:center;">
                                            <?php if (!empty($ep['size'])) : ?>
                                                <small style="color:var(--fw-text-muted);"><?php echo esc_html($ep['size']); ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($ep['url'])) : ?>
                                                <a href="<?php echo esc_url($ep['url']); ?>" target="_blank">&#11015; دانلود</a>
                                            <?php endif; ?>
                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endforeach; ?>

        <?php else : ?>

            <div class="filmworld-locked">
                <p>هنوز قسمتی ثبت نشده است.</p>
            </div>

        <?php endif; ?>

    </div>

</div>

<?php
endwhile;
get_footer(); ?>