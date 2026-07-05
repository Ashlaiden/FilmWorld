<?php

if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="filmworld-downloads">

    <h2>کیفیت‌های موجود</h2>

    <?php foreach ($qualities as $quality => $links) : ?>

        <?php
        if (empty($links['download']) && empty($links['stream'])) {
            continue;
        }
        ?>

        <div class="filmworld-quality">

            <h3><?php echo esc_html($quality); ?></h3>

            <?php if (!empty($links['download'])) : ?>

                <a href="<?php echo esc_url($links['download']); ?>" target="_blank" rel="noopener">
                    دانلود
                </a>

            <?php endif; ?>

            <?php if (!empty($links['stream'])) : ?>

                <a href="<?php echo esc_url($links['stream']); ?>" target="_blank" rel="noopener">
                    پخش آنلاین
                </a>

            <?php endif; ?>

        </div>

    <?php endforeach; ?>

</div>