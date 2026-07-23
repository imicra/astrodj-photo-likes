<?php

namespace PhotoLikes;

defined('ABSPATH') || exit;

class Database
{
    /**
     * Имя таблицы
     */
    private static function table(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'photo_likes';
    }

    /**
     * Получить количество лайков
     *
     * @return array<int,int>
     */
    public static function getLikes(array $ids): array
    {
        global $wpdb;

        if (empty($ids)) {
            return [];
        }

        $placeholders = implode( ',', array_fill(0, count($ids), '%d') );

        $sql = $wpdb->prepare(
            "
            SELECT
                photo_id,
                COUNT(*) likes
            FROM " . self::table() . "
            WHERE photo_id IN ($placeholders)
            GROUP BY photo_id
            ",
            ...$ids
        );

        $rows = $wpdb->get_results($sql);

        $result = [];

        foreach ($rows as $row) {

            $result[(int)$row->photo_id] = (int)$row->likes;

        }

        return $result;
    }

    /**
     * Получить лайки пользователя
     *
     * @return array<int,bool>
     */
    public static function getLiked(
        array $ids,
        string $visitor
    ): array {

        global $wpdb;

        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(
            ',',
            array_fill(0, count($ids), '%d')
        );

        $params = $ids;

        $params[] = $visitor;

        $sql = $wpdb->prepare(
            "
            SELECT photo_id
            FROM " . self::table() . "
            WHERE photo_id IN ($placeholders)
            AND visitor_hash=%s
            ",
            ...$params
        );

        $rows = $wpdb->get_col($sql);

        $result = [];

        foreach ($rows as $id) {

            $result[(int)$id] = true;

        }

        return $result;
    }

    /**
     * Добавить лайк
     */
    public static function addLike(
        int $photo,
        string $visitor
    ): bool {

        global $wpdb;

        return (bool)$wpdb->insert(

            self::table(),

            [

                'photo_id'=>$photo,

                'visitor_hash'=>$visitor,

                'created_at'=>current_time('mysql')

            ],

            [

                '%d',

                '%s',

                '%s'

            ]

        );
    }

}