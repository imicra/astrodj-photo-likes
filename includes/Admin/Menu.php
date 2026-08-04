<?php

namespace PhotoLikes\Admin;

use PhotoLikes\Admin\LikesPage;
use PhotoLikes\Repository;

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
            $this->menuTitle(),
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

    /**
     * Заголовок меню
     */
    private function menuTitle(): string
    {
        $count = Repository::countNewLikes();

        $title = __('Лайки', 'photo-likes');

        if ($count > 0) {

            $title .= sprintf(
                ' <span class="awaiting-mod">%s</span>',
                number_format_i18n($count)
            );

        }

        return $title;
    }
}