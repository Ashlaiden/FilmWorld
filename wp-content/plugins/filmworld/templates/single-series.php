<?php

get_header();

while (have_posts()) :
    the_post();

    $english_name = get_field('english_name');
    $year         = get_field('year');

    $genres    = get_the_terms(get_the_ID(), 'genre');
    $countries = get_the_terms(get_the_ID(), 'country');
    $dubbed    = get_the_terms(get_the_ID(), 'dubbed');

    /*
    |--------------------------------------------------------------------------
    | Get Episodes (FIXED QUERY)
    |--------------------------------------------------------------------------
    */

    $episodes = new WP_Query([
        'post_type'      => 'series_link',
        'posts_per_page' => -1,
        'post_status'    => 'publish',

        // مهم: فقط سریال مربوطه
        'meta_query' => [
            [
                'key'   => 'series',
                'value' => get_the_ID(),
                'compare' => '='
            ]
        ],

        'orderby' => [
            'meta_value_num' => 'ASC'
        ],
        'meta_key' => 'episode',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Group Episodes by Season
    |--------------------------------------------------------------------------
    */

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

            if (!isset($seasons[$season])) {
                $seasons[$season] = [];
            }

            if (!isset($seasons[$season][$quality])) {
                $seasons[$season][$quality] = [];
            }

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

            <?php if ($year) : ?>
                <p><strong>سال انتشار:</strong> <?php echo esc_html($year); ?></p>
            <?php endif; ?>

        </div>

    </div>

    <?php if (trim(get_the_content()) !== '') : ?>
        <div class="filmworld-description">
            <?php the_content(); ?>
        </div>
    <?php endif; ?>

    <div class="filmworld-seasons">

        <h2>قسمت‌ها</h2>

        <?php if (!empty($seasons)) : ?>

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

                                        <?php if (!empty($ep['url'])) : ?>
                                            <a href="<?php echo esc_url($ep['url']); ?>" target="_blank">
                                                دانلود
                                            </a>
                                        <?php endif; ?>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endforeach; ?>

        <?php else : ?>

            <p>هنوز قسمتی ثبت نشده است.</p>

        <?php endif; ?>

    </div>

</div>

<?php

endwhile;
get_footer();