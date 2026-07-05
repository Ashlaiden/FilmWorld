<?php

if (!defined('ABSPATH')) {
    exit;
}

$post_type = get_post_type();

$is_movie  = ($post_type === 'movie');
$is_series = ($post_type === 'series');

$english_name = get_field('english_name');
$year         = get_field('year');

$genres    = get_the_terms(get_the_ID(), 'genre');
$countries = get_the_terms(get_the_ID(), 'country');
$dubbed    = get_the_terms(get_the_ID(), 'dubbed');

?>

<div class="filmworld-media-item">

    <a class="filmworld-media-poster" href="<?php the_permalink(); ?>">

        <?php if (has_post_thumbnail()) : ?>

            <?php the_post_thumbnail('medium'); ?>

        <?php endif; ?>

        <span class="filmworld-media-type">

            <?php echo $is_movie ? '🎬 فیلم' : '📺 سریال'; ?>

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

                <strong>سال ساخت:</strong>

                <?php echo esc_html($year); ?>

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

        <p>

            <a class="filmworld-view-btn" href="<?php the_permalink(); ?>">

                <?php echo $is_movie ? 'مشاهده فیلم' : 'مشاهده سریال'; ?>

            </a>

        </p>

    </div>

</div>