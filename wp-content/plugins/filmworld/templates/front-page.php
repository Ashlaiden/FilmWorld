<?php

get_header();

?>

<div class="filmworld-home">

    <section class="filmworld-hero">

        <h1>FilmWorld</h1>

        <p>مرجع دانلود و مشاهده فیلم و سریال</p>

        <?php include __DIR__ . '/searchform.php'; ?>

    </section>

    <!-- جدیدترین فیلم‌ها -->

    <section class="filmworld-section">

        <h2>🎬 جدیدترین فیلم‌ها</h2>

        <?php

        $latest_movies = new WP_Query([
            'post_type'      => 'movie',
            'posts_per_page' => 8,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC'
        ]);

        ?>

        <?php if ($latest_movies->have_posts()) : ?>

            <div class="filmworld-movies-grid">

                <?php while ($latest_movies->have_posts()) : $latest_movies->the_post(); ?>

                    <?php include __DIR__ . '/parts/media-card.php'; ?>

                <?php endwhile; ?>

            </div>

            <?php wp_reset_postdata(); ?>

        <?php else : ?>

            <p>فیلمی ثبت نشده است.</p>

        <?php endif; ?>

    </section>

    <!-- جدیدترین سریال‌ها -->

    <section class="filmworld-section">

        <h2>📺 جدیدترین سریال‌ها</h2>

        <?php

        $latest_series = new WP_Query([
            'post_type'      => 'series',
            'posts_per_page' => 8,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC'
        ]);

        ?>

        <?php if ($latest_series->have_posts()) : ?>

            <div class="filmworld-movies-grid">

                <?php while ($latest_series->have_posts()) : $latest_series->the_post(); ?>

                    <?php include __DIR__ . '/parts/media-card.php'; ?>

                <?php endwhile; ?>

            </div>

            <?php wp_reset_postdata(); ?>

        <?php else : ?>

            <p>سریالی ثبت نشده است.</p>

        <?php endif; ?>

    </section>

    <!-- فیلم‌های دوبله فارسی -->

    <section class="filmworld-section">

        <h2>🎭 فیلم‌های دوبله فارسی</h2>

        <?php

        $dubbed_movies = new WP_Query([
            'post_type'      => 'movie',
            'posts_per_page' => 8,
            'post_status'    => 'publish',
            'tax_query'      => [
                [
                    'taxonomy' => 'dubbed',
                    'field'    => 'slug',
                    'terms'    => 'dubbed'
                ]
            ]
        ]);

        ?>

        <?php if ($dubbed_movies->have_posts()) : ?>

            <div class="filmworld-movies-grid">

                <?php while ($dubbed_movies->have_posts()) : $dubbed_movies->the_post(); ?>

                    <?php include __DIR__ . '/parts/media-card.php'; ?>

                <?php endwhile; ?>

            </div>

            <?php wp_reset_postdata(); ?>

        <?php else : ?>

            <p>فیلم دوبله‌ای ثبت نشده است.</p>

        <?php endif; ?>

    </section>

</div>

<?php

get_footer();