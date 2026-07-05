<?php

get_header();

?>

<div class="filmworld-search">

    <h1>

        نتایج جستجو برای:

        "<?php echo esc_html(get_search_query()); ?>"

    </h1>

    <?php include __DIR__ . '/searchform.php'; ?>

    <?php if (have_posts()) : ?>

        <div class="filmworld-movies-grid">

            <?php while (have_posts()) : the_post(); ?>

                <?php include __DIR__ . '/parts/movie-card.php'; ?>

            <?php endwhile; ?>

        </div>

    <?php else : ?>

        <p>هیچ فیلمی پیدا نشد.</p>

    <?php endif; ?>

</div>

<?php

get_footer();