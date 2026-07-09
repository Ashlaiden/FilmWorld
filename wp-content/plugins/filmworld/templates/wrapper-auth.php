<?php
/**
 * Wrapper template for auth pages (login, register, account)
 */

if (!defined('ABSPATH')) exit;

$fw_page = get_query_var('filmworld_page');

$file_map = [
    'login'    => FILMWORLD_PLUGIN_PATH . 'templates/login.php',
    'register' => FILMWORLD_PLUGIN_PATH . 'templates/register.php',
    'account'  => FILMWORLD_PLUGIN_PATH . 'templates/account.php',
];

$content_template = $file_map[$fw_page] ?? '';

if (empty($content_template) || !file_exists($content_template)) {
    wp_die('صفحه مورد نظر یافت نشد.');
}

include FILMWORLD_PLUGIN_PATH . 'templates/header.php';
include $content_template;
include FILMWORLD_PLUGIN_PATH . 'templates/footer.php';