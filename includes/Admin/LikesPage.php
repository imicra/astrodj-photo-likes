<?php

namespace PhotoLikes\Admin;

use PhotoLikes\Repository;

defined('ABSPATH') || exit;

class LikesPage
{
    /**
     * Отображение страницы
     */
    public function render(): void
    {
        $table = new LikesTable();

        $table->prepare_items();

        $summary = Repository::getSummary();

        $topPhotos = Repository::getTopPhotos();

        ?>

        <div class="wrap">

            <h1 class="wp-heading-inline">
                <?php esc_html_e('Статистика лайков', 'photo-likes'); ?>
            </h1>

            <hr class="wp-header-end">

            <div class="photo-likes-summary">

                <div class="photo-likes-card">

                    <span class="photo-likes-card__label">
                        <?php esc_html_e('Фотографий с лайками', 'photo-likes'); ?>
                    </span>

                    <span class="photo-likes-card__value">
                        <?php echo number_format_i18n($summary->photos); ?>
                    </span>

                </div>

                <div class="photo-likes-card">

                    <span class="photo-likes-card__label">
                        <?php esc_html_e('Всего лайков', 'photo-likes'); ?>
                    </span>

                    <span class="photo-likes-card__value">
                        <?php echo number_format_i18n($summary->likes); ?>
                    </span>

                </div>

                <div class="photo-likes-card">

                    <span class="photo-likes-card__label">
                        <?php esc_html_e('Среднее лайков на фото', 'photo-likes'); ?>
                    </span>

                    <span class="photo-likes-card__value">
                        <?php echo esc_html($summary->average); ?>
                    </span>

                </div>

                <div class="photo-likes-card">

                    <span class="photo-likes-card__label">
                        <?php esc_html_e('Последний лайк', 'photo-likes'); ?>
                    </span>

                    <span class="photo-likes-card__value">

                        <?php

                        if (!empty($summary->last_like)) {

                            echo esc_html(
                                wp_date(
                                    get_option('date_format') . ' ' . get_option('time_format'),
                                    strtotime($summary->last_like)
                                )
                            );

                        } else {

                            echo '—';

                        }

                        ?>

                    </span>

                </div>

            </div>

            <?php if (!empty($topPhotos)) : ?>

                <div class="photo-likes-top">

                    <h2>
                        <?php esc_html_e('ТОП-5 фотографий', 'photo-likes'); ?>
                    </h2>

                    <div class="photo-likes-top-list">

                        <?php foreach ($topPhotos as $index => $photo) : ?>

                            <?php

                            switch ($index) {

                                case 0:
                                    $place = '🥇';
                                    break;

                                case 1:
                                    $place = '🥈';
                                    break;

                                case 2:
                                    $place = '🥉';
                                    break;

                                default:
                                    $place = $index + 1;
                                    break;

                            }

                            ?>

                            <div class="photo-likes-top-item">

                                <div class="photo-likes-top-item__place">
                                    <?php echo $place; ?>
                                </div>

                                <div class="photo-likes-top-item__thumb">

                                    <?php
                                    echo get_the_post_thumbnail(
                                        $photo->ID,
                                        array(60, 60)
                                    );
                                    ?>

                                </div>

                                <div class="photo-likes-top-item__content">

                                    <a
                                        href="<?php echo esc_url(get_edit_post_link($photo->ID)); ?>"
                                        class="photo-likes-top-item__title"
                                    >

                                        <?php echo esc_html($photo->post_title); ?>

                                    </a>

                                    <div class="photo-likes-top-item__meta">

                                        <?php

                                        $type = get_post_type_object($photo->post_type);

                                        if ($type) {

                                            echo esc_html($type->labels->singular_name);

                                        } else {

                                            echo esc_html($photo->post_type);

                                        }

                                        ?>

                                    </div>

                                </div>

                                <div class="photo-likes-top-item__likes">

                                    ❤️
                                    <?php echo number_format_i18n($photo->likes); ?>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>

            <?php endif; ?>

            <form method="get">

                <input
                    type="hidden"
                    name="page"
                    value="photo-likes"
                >

                <?php $table->display(); ?>

            </form>

        </div>

        <?php
    }
}