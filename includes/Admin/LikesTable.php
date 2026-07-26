<?php

namespace PhotoLikes\Admin;

use PhotoLikes\Repository;

defined('ABSPATH') || exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class LikesTable extends \WP_List_Table
{
    /**
     * Количество записей на странице
     *
     * @var int
     */
    protected $perPage = 20;

    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'photo',
            'plural'   => 'photos',
            'ajax'     => false,
        ));
    }

    /**
     * Колонки
     */
    public function get_columns()
    {
        return array(

            'thumbnail' => '',

            'title' => __('Фотография', 'photo-likes'),

            'type' => __('Тип', 'photo-likes'),

            'likes' => __('Лайков', 'photo-likes'),

            'last_like' => __('Последний лайк', 'photo-likes'),

        );
    }

    /**
     * Сортируемые колонки
     */
    protected function get_sortable_columns()
    {
        return array(

            'title' => array('title', false),

            'likes' => array('likes', true),

            'last_like' => array('last_like', false),

        );
    }

    /**
     * Подготовка данных
     */
    public function prepare_items()
    {
        $currentPage = $this->get_pagenum();

        $orderby = isset($_GET['orderby'])
            ? sanitize_key($_GET['orderby'])
            : 'last_like';

        $order = isset($_GET['order'])
            ? strtoupper(sanitize_key($_GET['order']))
            : 'DESC';

        $this->items = Repository::getStatistics(

            $this->perPage,

            ($currentPage - 1) * $this->perPage,

            $orderby,

            $order

        );

        $totalItems = Repository::countStatistics();

        $this->_column_headers = array(

            $this->get_columns(),

            array(),

            $this->get_sortable_columns()

        );

        $this->set_pagination_args(array(

            'total_items' => $totalItems,

            'per_page' => $this->perPage,

            'total_pages' => ceil($totalItems / $this->perPage),

        ));
    }

    /**
     * Миниатюра
     */
    public function column_thumbnail($item)
    {
        $thumb = get_the_post_thumbnail(

            $item->ID,

            array(60, 60)

        );

        if (!$thumb) {
            return '—';
        }

        return $thumb;
    }

    /**
     * Заголовок
     */
    public function column_title($item)
    {
        $title = $item->post_title;

        if (empty($title)) {
            $title = __('(Без названия)', 'photo-likes');
        }

        $actions = array(

            'edit' => sprintf(

                '<a href="%s">%s</a>',

                esc_url(get_edit_post_link($item->ID)),

                __('Редактировать')

            ),

            'view' => sprintf(

                '<a href="%s" target="_blank">%s</a>',

                esc_url(get_permalink($item->ID)),

                __('Просмотреть')

            ),

        );

        return sprintf(

            '<strong><a href="%s">%s</a></strong>%s',

            esc_url(get_edit_post_link($item->ID)),

            esc_html($title),

            $this->row_actions($actions)

        );
    }

    /**
     * Тип записи
     */
    public function column_type($item)
    {
        $postType = get_post_type_object($item->post_type);

        if ($postType) {
            return esc_html($postType->labels->singular_name);
        }

        return esc_html($item->post_type);
    }

    /**
     * Лайки
     */
    public function column_likes($item)
    {
        return number_format_i18n($item->likes);
    }

    /**
     * Последний лайк
     */
    public function column_last_like($item)
    {
        if (empty($item->last_like)) {
            return '—';
        }

        return wp_date(

            get_option('date_format')
            . ' ' .
            get_option('time_format'),

            strtotime($item->last_like)

        );
    }

    /**
     * По умолчанию
     */
    public function column_default($item, $column_name)
    {
        if (isset($item->$column_name)) {
            return $item->$column_name;
        }

        return '';
    }

    /**
     * Если нет данных
     */
    public function no_items()
    {
        esc_html_e(
            'Фотографий с лайками пока нет.',
            'photo-likes'
        );
    }
}