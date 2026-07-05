<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Clean Links
|--------------------------------------------------------------------------
*/

function filmworld_clean_links($text)
{
    $text = str_replace("\r", '', $text);

    $lines = explode("\n", $text);

    $links = [];

    foreach ($lines as $line) {

        $line = trim($line);

        if (empty($line)) {
            continue;
        }

        if (!filter_var($line, FILTER_VALIDATE_URL)) {
            continue;
        }

        $links[] = $line;
    }

    return array_values(array_unique($links));
}

/*
|--------------------------------------------------------------------------
| Generate Episode Title
|--------------------------------------------------------------------------
*/

function filmworld_generate_episode_title(
    $series_title,
    $season,
    $episode,
    $quality
) {

    return sprintf(
        '%s - S%02dE%02d - %s',
        $series_title,
        $season,
        $episode,
        $quality
    );

}

/*
|--------------------------------------------------------------------------
| Episode Exists?
|--------------------------------------------------------------------------
*/

function filmworld_episode_exists(
    $series_id,
    $season,
    $episode,
    $quality
) {

    $query = new WP_Query([
        'post_type'      => 'series_link',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [
            [
                'key'   => 'series',
                'value' => $series_id,
            ],
            [
                'key'   => 'season',
                'value' => $season,
            ],
            [
                'key'   => 'episode',
                'value' => $episode,
            ],
            [
                'key'   => 'quality',
                'value' => $quality,
            ],
        ],
    ]);

    return $query->have_posts();
}

/*
|--------------------------------------------------------------------------
| Language Label
|--------------------------------------------------------------------------
*/

function filmworld_language_label($language)
{
    switch ($language) {

        case 'dubbed':
            return 'Dubbed';

        case 'dual':
            return 'Dual Audio';

        default:
            return 'Original';
    }
}

/*
|--------------------------------------------------------------------------
| Subtitle Label
|--------------------------------------------------------------------------
*/

function filmworld_subtitle_label($subtitle)
{
    return $subtitle ? 'Yes' : 'No';
}

/*
|--------------------------------------------------------------------------
| Format Season
|--------------------------------------------------------------------------
*/

function filmworld_format_season($season)
{
    return sprintf('S%02d', intval($season));
}

/*
|--------------------------------------------------------------------------
| Format Episode
|--------------------------------------------------------------------------
*/

function filmworld_format_episode($episode)
{
    return sprintf('E%02d', intval($episode));
}