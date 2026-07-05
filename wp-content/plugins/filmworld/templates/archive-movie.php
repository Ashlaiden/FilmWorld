<?php

get_header();

$post_type = get_query_var('post_type');

if (is_array($post_type)) {
    $post_type = reset($post_type);
}

switch ($post_type) {

    case 'series':
        $archive_title = 'سریال‌ها';
        break;

    case 'movie':
        $archive_title = 'فیلم‌ها';
        break;

    default:
        $archive_title = post_type_archive_title('', false);

        if (empty($archive_title)) {
            $archive_title = 'آرشیو';
        }

        break;
}

?>

<div class="filmworld-archive">

    <h1><?php echo esc_html($archive_title); ?></h1>

    <?php if (have_posts()) : ?>

        <div class="filmworld-movies-grid">

            <?php while (have_posts()) : the_post(); ?>

                <?php include __DIR__ . '/parts/media-card.php'; ?>

            <?php endwhile; ?>

        </div>

        <?php the_posts_pagination([
            'mid_size'  => 2,
            'prev_text' => '« قبلی',
            'next_text' => 'بعدی »',
        ]); ?>

    <?php else : ?>

        <p>موردی یافت نشد.</p>

    <?php endif; ?>

</div>

<?php

get_footer();