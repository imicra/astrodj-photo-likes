<?php

namespace PhotoLikes;

defined('ABSPATH') || exit;

class Repository
{
    /**
     * Количество лайков
     *
     * @var array<int,int>
     */
    private static array $likes = [];

    /**
     * Какие записи уже лайкнул пользователь
     *
     * @var array<int,bool>
     */
    private static array $liked = [];

    /**
     * Уже загружали данные?
     */
    private static bool $loaded = false;

    /**
     * Получить количество лайков
     */
    public static function likes(int $objectId): int
    {
        self::boot();

        return self::$likes[$objectId] ?? 0;
    }

    /**
     * Пользователь уже лайкал?
     */
    public static function liked(int $objectId): bool
    {
        self::boot();

        return isset(self::$liked[$objectId]);
    }

    /**
     * После успешного AJAX
     * обновляем внутренний кэш
     */
    public static function increment(int $objectId): void
    {
        self::boot();

        self::$likes[$objectId] = (self::$likes[$objectId] ?? 0) + 1;

        self::$liked[$objectId] = true;
    }

    /**
     * Загружаем данные один раз
     */
    private static function boot(): void
    {
        if (self::$loaded) {
            return;
        }

        self::$loaded = true;

        $ids = self::getObjectIds();

        if (empty($ids)) {
            return;
        }

        self::loadLikes($ids);

        self::loadLiked($ids);
    }

    /**
     * Получить все ID объектов
     */
    private static function getObjectIds(): array
    {
        global $wp_query;

        if (empty($wp_query->posts)) {
            return [];
        }

        $ids = [];

        foreach ($wp_query->posts as $post) {

            if (
                in_array(
                    $post->post_type,
                    Config::POST_TYPES,
                    true
                )
            ) {

                $ids[] = (int)$post->ID;

            }

        }

        return array_unique($ids);
    }

    /**
     * Загружаем количество лайков
     */
    private static function loadLikes(array $ids): void
    {
        self::$likes = Database::getLikes($ids);
    }

    /**
     * Загружаем лайки пользователя
     */
    private static function loadLiked(array $ids): void
    {
        self::$liked = Database::getLiked(
            $ids,
            Visitor::hash()
        );
    }

}