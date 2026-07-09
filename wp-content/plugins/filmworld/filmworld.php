<?php
/**
 * Plugin Name: FilmWorld
 * Plugin URI: https://github.com/Ashlaiden/FilmWorld
 * Description: FilmWorld - Movie & Series Download and Streaming Platform
 * Version: 6.0.0
 * Author: Ashtin
 * License: GPL2
 * Text Domain: filmworld
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FILMWORLD_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FILMWORLD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FILMWORLD_VERSION', '6.0.0');

// Activation & Deactivation
register_activation_hook(__FILE__, 'filmworld_activate_plugin');

// Load includes
require_once FILMWORLD_PLUGIN_PATH . 'includes/loader.php';