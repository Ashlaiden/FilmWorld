<?php

get_header();

global $wp_query;
$total_results = $wp_query->found_posts;

?>

<div class="filmworld-search">

    <h1>نتایج جستجو</h1>

    <p class="filmworld-search-count">
        <?php echo esc_html($total_results); ?> نتیجه برای "<?php echo esc_html(get_search_query()); ?>"
    </p>

    <?php include __DIR__ . '/searchform.php'; ?>

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

        <p>هیچ فیلم یا سریالی پیدا نشد.</p>

    <?php endif; ?>

</div>

<?php

get_footer(); ?>