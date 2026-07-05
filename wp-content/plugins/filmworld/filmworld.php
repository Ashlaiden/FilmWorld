<?php
/**
 * Plugin Name: FilmWorld
 * Plugin URI: https://localhost
 * Description: FilmWorld Movie & Series Management Plugin
 * Version: 1.0.0
 * Author: Ashtin
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FILMWORLD_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FILMWORLD_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once FILMWORLD_PLUGIN_PATH . 'includes/loader.php';