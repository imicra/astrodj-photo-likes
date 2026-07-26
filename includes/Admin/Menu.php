<?php

namespace PhotoLikes\Admin;

use PhotoLikes\Admin\LikesPage;

defined('ABSPATH') || exit;

class Menu
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register']);
    }

    /**
     * Регистрация меню
     */
    public function register(): void
    {
        add_menu_page(
            __('Лайки', 'photo-likes'),
            __('Лайки', 'photo-likes'),
            'manage_options',
            'photo-likes',
            [$this, 'page'],
            'dashicons-heart',
            58
        );
    }

    /**
     * Вывод страницы
     */
    public function page(): void
    {
        $page = new LikesPage();
        $page->render();
    }
}