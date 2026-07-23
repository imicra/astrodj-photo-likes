<?php

namespace PhotoLikes;

defined('ABSPATH') || exit;

class Loader
{
    public function __construct()
    {
        add_action('wp', [$this, 'boot']);

        new Ajax();
        new Button();
    }

    public function boot()
    {
        if (!is_singular(Config::POST_TYPES) && !is_post_type_archive(Config::POST_TYPES)) {
            return;
        }

        // подключаем JS/CSS
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue()
    {
        wp_enqueue_style(
            'photo-likes',
            PHOTO_LIKES_URL . 'assets/css/photo-likes.css',
            [],
            PHOTO_LIKES_VERSION
        );

        wp_enqueue_script(
            'photo-likes',
            PHOTO_LIKES_URL . 'assets/js/photo-likes.js',
            ['jquery'],
            PHOTO_LIKES_VERSION,
            true
        );

        wp_localize_script(
            'photo-likes',
            'PhotoLikesData',
            [
                'ajax' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('photo_likes'),
            ]
        );
    }
}