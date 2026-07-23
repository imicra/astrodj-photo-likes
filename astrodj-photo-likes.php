<?php
/**
 * Plugin Name: Photo Likes
 * Plugin URI: https://github.com/imicra
 * Description: AJAX лайки фотографий.
 * Version: 1.0.0
 * Author: Astrodj
 * License: GPL2
 */

defined('ABSPATH') || exit;

define('PHOTO_LIKES_VERSION', '1.0.0');
define('PHOTO_LIKES_PATH', plugin_dir_path(__FILE__));
define('PHOTO_LIKES_URL', plugin_dir_url(__FILE__));

require_once PHOTO_LIKES_PATH . 'includes/Install.php';
require_once PHOTO_LIKES_PATH . 'includes/Loader.php';
require_once PHOTO_LIKES_PATH . 'includes/Ajax.php';
require_once PHOTO_LIKES_PATH . 'includes/Button.php';
require_once PHOTO_LIKES_PATH . 'includes/Visitor.php';

register_activation_hook(__FILE__, ['PhotoLikes\\Install', 'activate']);

add_action('plugins_loaded', function () {
    new PhotoLikes\Loader();
});