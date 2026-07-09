<?php

if (!defined('ABSPATH')) {
    exit;
}


/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

require_once plugin_dir_path(__FILE__) . 'admin-payments.php';
require_once plugin_dir_path(__FILE__) . 'admin-members.php';
require_once plugin_dir_path(__FILE__) . 'admin-menu.php';


/*
|--------------------------------------------------------------------------
| Core
|--------------------------------------------------------------------------
*/

require_once plugin_dir_path(__FILE__) . 'post-types.php';
require_once plugin_dir_path(__FILE__) . 'taxonomies.php';
require_once plugin_dir_path(__FILE__) . 'template-loader.php';
require_once plugin_dir_path(__FILE__) . 'post-slug.php';
require_once plugin_dir_path(__FILE__) . 'acf-fields.php';

/*
|--------------------------------------------------------------------------
| Features
|--------------------------------------------------------------------------
*/

require_once plugin_dir_path(__FILE__) . 'auth.php';
require_once plugin_dir_path(__FILE__) . 'search.php';
require_once plugin_dir_path(__FILE__) . 'subscription.php';
require_once plugin_dir_path(__FILE__) . 'user-membership.php';
require_once plugin_dir_path(__FILE__) . 'payment.php';
require_once plugin_dir_path(__FILE__) . 'download-links.php';

/*
|--------------------------------------------------------------------------
| Series Bulk Import
|--------------------------------------------------------------------------
*/

require_once plugin_dir_path(__FILE__) . 'series-bulk-import-helper.php';
require_once plugin_dir_path(__FILE__) . 'series-bulk-import-handler.php';
require_once plugin_dir_path(__FILE__) . 'series-bulk-import.php';
