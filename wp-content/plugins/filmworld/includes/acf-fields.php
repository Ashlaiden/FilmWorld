<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| ACF Field Groups - PHP Registration
| All field groups are defined here for portability
|--------------------------------------------------------------------------
*/

function filmworld_register_acf_fields()
{
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Movie / Series Info Fields
    |--------------------------------------------------------------------------
    */

    acf_add_local_field_group([
        'key'                   => 'group_filmworld_media_info',
        'title'                 => 'اطلاعات فیلم / سریال',
        'fields'                => [
            [
                'key'           => 'field_english_name',
                'label'         => 'نام انگلیسی',
                'name'          => 'english_name',
                'type'          => 'text',
                'instructions'  => 'نام انگلیسی فیلم یا سریال (برای ساخت لینک و اسلاگ)',
                'required'      => 1,
                'wrapper'       => ['width' => '50'],
            ],
            [
                'key'           => 'field_year',
                'label'         => 'سال ساخت / انتشار',
                'name'          => 'year',
                'type'          => 'text',
                'wrapper'       => ['width' => '25'],
            ],
            [
                'key'           => 'field_duration',
                'label'         => 'مدت زمان',
                'name'          => 'duration',
                'type'          => 'text',
                'instructions'  => 'مثال: 1:45:00 یا 105 دقیقه',
                'wrapper'       => ['width' => '25'],
            ],
            [
                'key'           => 'field_stream_url',
                'label'         => 'لینک استریم',
                'name'          => 'stream_url',
                'type'          => 'url',
                'instructions'  => 'لینک مستقیم ویدیو برای پخش آنلاین (mp4, m3u8)',
            ],
            [
                'key'           => 'field_imdb_rating',
                'label'         => 'امتیاز IMDB',
                'name'          => 'imdb_rating',
                'type'          => 'text',
                'wrapper'       => ['width' => '25'],
            ],
            [
                'key'           => 'field_imdb_link',
                'label'         => 'لینک IMDB',
                'name'          => 'imdb_link',
                'type'          => 'url',
                'wrapper'       => ['width' => '25'],
            ],
            [
                'key'           => 'field_director',
                'label'         => 'کارگردان',
                'name'          => 'director',
                'type'          => 'text',
                'wrapper'       => ['width' => '25'],
            ],
            [
                'key'           => 'field_actors',
                'label'         => 'بازیگران',
                'name'          => 'actors',
                'type'          => 'textarea',
                'instructions'  => 'هر بازیگر در یک خط',
                'wrapper'       => ['width' => '25'],
            ],
        ],
        'location'              => [
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'movie',
                ],
            ],
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'series',
                ],
            ],
        ],
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
    ]);

    /*
    |--------------------------------------------------------------------------
    | Movie Download Link Fields
    |--------------------------------------------------------------------------
    */

    acf_add_local_field_group([
        'key'                   => 'group_filmworld_movie_link',
        'title'                 => 'جزئیات لینک دانلود فیلم',
        'fields'                => [
            [
                'key'           => 'field_ml_movie',
                'label'         => 'فیلم',
                'name'          => 'movie',
                'type'          => 'post_object',
                'post_type'     => ['movie'],
                'required'      => 1,
                'return_format' => 'id',
                'wrapper'       => ['width' => '50'],
            ],
            [
                'key'           => 'field_ml_quality',
                'label'         => 'کیفیت',
                'name'          => 'quality',
                'type'          => 'select',
                'choices'       => [
                    '480p'   => '480p',
                    '720p'   => '720p',
                    '1080p'  => '1080p',
                    '2160p'  => '2160p (4K)',
                ],
                'default_value' => '1080p',
                'wrapper'       => ['width' => '25'],
            ],
            [
                'key'           => 'field_ml_size',
                'label'         => 'حجم',
                'name'          => 'size',
                'type'          => 'text',
                'instructions'  => 'مثال: 1.8 GB',
                'wrapper'       => ['width' => '25'],
            ],
            [
                'key'           => 'field_ml_encoder',
                'label'         => 'انکودر',
                'name'          => 'encoder',
                'type'          => 'text',
                'wrapper'       => ['width' => '33'],
            ],
            [
                'key'           => 'field_ml_language',
                'label'         => 'زبان',
                'name'          => 'language',
                'type'          => 'select',
                'choices'       => [
                    'original' => 'زبان اصلی',
                    'dubbed'   => 'دوبله فارسی',
                    'dual'     => 'دو زبانه',
                ],
                'default_value' => 'original',
                'wrapper'       => ['width' => '33'],
            ],
            [
                'key'           => 'field_ml_subtitle',
                'label'         => 'زیرنویس فارسی',
                'name'          => 'subtitle',
                'type'          => 'true_false',
                'default_value' => 0,
                'wrapper'       => ['width' => '34'],
            ],
            [
                'key'           => 'field_ml_download_url',
                'label'         => 'لینک دانلود',
                'name'          => 'download_url',
                'type'          => 'url',
                'required'      => 1,
            ],
        ],
        'location'              => [
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'movie_link',
                ],
            ],
        ],
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'active'                => true,
    ]);

    /*
    |--------------------------------------------------------------------------
    | Series Download Link Fields
    |--------------------------------------------------------------------------
    */

    acf_add_local_field_group([
        'key'                   => 'group_filmworld_series_link',
        'title'                 => 'جزئیات لینک دانلود سریال',
        'fields'                => [
            [
                'key'           => 'field_sl_series',
                'label'         => 'سریال',
                'name'          => 'series',
                'type'          => 'post_object',
                'post_type'     => ['series'],
                'required'      => 1,
                'return_format' => 'id',
                'wrapper'       => ['width' => '50'],
            ],
            [
                'key'           => 'field_sl_season',
                'label'         => 'شماره فصل',
                'name'          => 'season',
                'type'          => 'number',
                'required'      => 1,
                'min'           => 1,
                'wrapper'       => ['width' => '25'],
            ],
            [
                'key'           => 'field_sl_episode',
                'label'         => 'شماره قسمت',
                'name'          => 'episode',
                'type'          => 'number',
                'required'      => 1,
                'min'           => 1,
                'wrapper'       => ['width' => '25'],
            ],
            [
                'key'           => 'field_sl_quality',
                'label'         => 'کیفیت',
                'name'          => 'quality',
                'type'          => 'select',
                'choices'       => [
                    '480p'   => '480p',
                    '720p'   => '720p',
                    '1080p'  => '1080p',
                    '2160p'  => '2160p (4K)',
                ],
                'default_value' => '1080p',
                'wrapper'       => ['width' => '25'],
            ],
            [
                'key'           => 'field_sl_size',
                'label'         => 'حجم',
                'name'          => 'size',
                'type'          => 'text',
                'wrapper'       => ['width' => '25'],
            ],
            [
                'key'           => 'field_sl_encoder',
                'label'         => 'انکودر',
                'name'          => 'encoder',
                'type'          => 'text',
                'wrapper'       => ['width' => '25'],
            ],
            [
                'key'           => 'field_sl_language',
                'label'         => 'زبان',
                'name'          => 'language',
                'type'          => 'select',
                'choices'       => [
                    'original' => 'زبان اصلی',
                    'dubbed'   => 'دوبله فارسی',
                    'dual'     => 'دو زبانه',
                ],
                'default_value' => 'original',
                'wrapper'       => ['width' => '33'],
            ],
            [
                'key'           => 'field_sl_subtitle',
                'label'         => 'زیرنویس فارسی',
                'name'          => 'subtitle',
                'type'          => 'true_false',
                'default_value' => 0,
                'wrapper'       => ['width' => '34'],
            ],
            [
                'key'           => 'field_sl_download_url',
                'label'         => 'لینک دانلود',
                'name'          => 'download_url',
                'type'          => 'url',
                'required'      => 1,
            ],
        ],
        'location'              => [
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'series_link',
                ],
            ],
        ],
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'active'                => true,
    ]);
}

add_action('acf/init', 'filmworld_register_acf_fields');