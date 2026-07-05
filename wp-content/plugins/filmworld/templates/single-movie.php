<?php

get_header();

while (have_posts()) :
    the_post();

    $english_name = get_field('english_name');
    $year         = get_field('year');
    $duration     = get_field('duration');

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

?>

<div class="filmworld-movie">

    <div class="filmworld-poster">

        <?php if (has_post_thumbnail()) : ?>

            <?php the_post_thumbnail('large'); ?>

        <?php else : ?>

            <p>تصویری برای این فیلم ثبت نشده است.</p>

        <?php endif; ?>

    </div>

    <div class="filmworld-info">

        <h1><?php the_title(); ?></h1>

        <?php if (!empty($english_name)) : ?>

            <h3><?php echo esc_html($english_name); ?></h3>

        <?php endif; ?>

        <?php if (!empty($year)) : ?>

            <p>
                <strong>سال ساخت:</strong>
                <?php echo esc_html($year); ?>
            </p>

        <?php endif; ?>

        <?php if (!empty($duration)) : ?>

            <p>
                <strong>مدت زمان:</strong>
                <?php echo esc_html($duration); ?>
            </p>

        <?php endif; ?>

        <?php if ($genres && !is_wp_error($genres)) : ?>

            <p>

                <strong>ژانر:</strong>

                <?php

                $genre_links = [];

                foreach ($genres as $genre) {

                    $genre_links[] =
                        '<a href="' . esc_url(get_term_link($genre)) . '">' .
                        esc_html($genre->name) .
                        '</a>';

                }

                echo implode(' | ', $genre_links);

                ?>

            </p>

        <?php endif; ?>

        <?php if ($countries && !is_wp_error($countries)) : ?>

            <p>

                <strong>کشور:</strong>

                <?php

                $country_links = [];

                foreach ($countries as $country) {

                    $country_links[] =
                        '<a href="' . esc_url(get_term_link($country)) . '">' .
                        esc_html($country->name) .
                        '</a>';

                }

                echo implode(' | ', $country_links);

                ?>

            </p>

        <?php endif; ?>

        <?php if ($dubbed && !is_wp_error($dubbed)) : ?>

            <p>

                <strong>وضعیت:</strong>

                <?php

                $dubbed_links = [];

                foreach ($dubbed as $item) {

                    $dubbed_links[] =
                        '<a href="' . esc_url(get_term_link($item)) . '">' .
                        esc_html($item->name) .
                        '</a>';

                }

                echo implode(' | ', $dubbed_links);

                ?>

            </p>

        <?php endif; ?>

    </div>

</div>

<?php if (trim(get_the_content()) !== '') : ?>

    <div class="filmworld-description">

        <?php the_content(); ?>

    </div>

<?php endif; ?>

<div class="filmworld-downloads">

    <h2>لینک‌های دانلود</h2>

    <?php if ($download_links->have_posts()) : ?>

        <table class="filmworld-download-table">

            <thead>

                <tr>

                    <th>کیفیت</th>
                    <th>حجم</th>
                    <th>انکودر</th>
                    <th>زبان</th>
                    <th>زیرنویس</th>
                    <th>دانلود</th>

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

                    case 'original':
                    default:
                        $language_text = 'زبان اصلی';
                        break;

                }

                ?>

                <tr>

                    <td><?php echo esc_html($quality); ?></td>

                    <td><?php echo esc_html($size); ?></td>

                    <td><?php echo esc_html($encoder); ?></td>

                    <td><?php echo esc_html($language_text); ?></td>

                    <td>

                        <?php echo $subtitle ? '✅' : '❌'; ?>

                    </td>

                    <td>

                        <?php if (!empty($downloadUrl)) : ?>

                            <a
                                class="filmworld-download-btn"
                                href="<?php echo esc_url($downloadUrl); ?>"
                                target="_blank"
                                rel="noopener">

                                دانلود

                            </a>

                        <?php else : ?>

                            —

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endwhile; ?>

            </tbody>

        </table>

        <?php wp_reset_postdata(); ?>

    <?php else : ?>

        <p>هنوز لینک دانلودی برای این فیلم ثبت نشده است.</p>

    <?php endif; ?>

</div>

<?php

endwhile;


$genre_ids = [];

if ($genres && !is_wp_error($genres)) {

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

        <?php while ($related_movies->have_posts()) : $related_movies->the_post();

            $related_year = get_field('year');

        ?>

            <div class="filmworld-related-card">

                <a href="<?php the_permalink(); ?>">

                    <?php if (has_post_thumbnail()) : ?>

                        <?php the_post_thumbnail('medium'); ?>

                    <?php endif; ?>

                </a>

                <h3>

                    <a href="<?php the_permalink(); ?>">

                        <?php the_title(); ?>

                    </a>

                </h3>

                <?php if (!empty($related_year)) : ?>

                    <p>

                        <?php echo esc_html($related_year); ?>

                    </p>

                <?php endif; ?>

            </div>

        <?php endwhile; ?>

    </div>

</div>

<?php

        wp_reset_postdata();

    endif;

endif;

?>

<?php

get_footer();
