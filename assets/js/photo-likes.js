class PhotoLikes {

    constructor() {

        this.loadingState = false;
        this.pendingReload = false;

        this.bindEvents();

        this.observe();

        this.loadState();

    }

    observe() {

        const root = document.querySelector(PhotoLikesData.container) || document.body;

        this.observer = new MutationObserver((mutations) => {

            let reload = false;

            for (const mutation of mutations) {

                if (mutation.type !== 'childList') {
                    continue;
                }

                for (const node of mutation.addedNodes) {

                    if (node.nodeType !== 1) {
                        continue;
                    }

                    if (
                        node.matches?.('.photo-like') ||
                        node.querySelector?.('.photo-like')
                    ) {
                        reload = true;
                        break;
                    }

                }

                if (reload) {
                    break;
                }

            }

            if (reload) {
                this.loadState();
            }

        });

        this.observer.observe(root, {

            childList: true,
            subtree: true

        });

    }

    /**
     * Универсальный AJAX-запрос
     */
    async request(action, data = {}) {

        const form = new FormData();

        form.append('action', action);
        form.append('nonce', PhotoLikesData.nonce);

        Object.entries(data).forEach(([key, value]) => {

            if (Array.isArray(value)) {

                value.forEach(item => {
                    form.append(`${key}[]`, item);
                });

            } else {

                form.append(key, value);

            }

        });

        const response = await fetch(PhotoLikesData.ajax, {

            method: 'POST',
            credentials: 'same-origin',
            body: form

        });

        return await response.json();

    }

    /**
     * Делегирование событий
     */
    bindEvents() {

        const container = document.querySelector('.site-main');

        container.addEventListener('click', (event) => {

            const button = event.target.closest('.photo-like');

            if (!button) {
                return;
            }

            this.like(button);

        });

    }

    /**
     * Получить кнопки, которые еще не инициализированы
     */
    getButtons() {

        return document.querySelectorAll('.photo-like:not([data-state])');

    }

    /**
     * Загрузить состояние кнопок
     */
    async loadState() {

        if (this.loadingState) {

            this.pendingReload = true;
            return;

        }

        const buttons = [...this.getButtons()];

        if (!buttons.length) {
            return;
        }

        this.loadingState = true;

        buttons.forEach(button => {
            button.dataset.state = 'loading';
        });

        const ids = buttons.map(button => button.dataset.photo);

        try {

            const json = await this.request(
                'photo_likes_state',
                {
                    ids: ids
                }
            );

            if (json.success) {

                buttons.forEach(button => {

                    const id = button.dataset.photo;

                    const state = json.data[id];

                    if (state) {

                        this.updateButton(
                            button,
                            state
                        );

                    }

                    button.dataset.state = 'loaded';

                });

            } else {

                buttons.forEach(button => {
                    button.removeAttribute('data-state');
                });

            }

        }
        catch (e) {

            buttons.forEach(button => {
                button.removeAttribute('data-state');
            });

            console.error(e);

        }

        this.loadingState = false;

        if (this.pendingReload) {

            this.pendingReload = false;

            this.loadState();

        }

    }

    /**
     * Обновить кнопку
     */
    updateButton(button, state) {

        const count = button.querySelector('.count');

        const likes = parseInt(state.likes, 10) || 0;

        count.textContent = likes;

        count.classList.toggle(
            'visible',
            likes > 0
        );

        button.classList.toggle(
            'liked',
            state.liked
        );

    }

    /**
     * Поставить лайк
     */
    async like(button) {

        button.classList.add('likes-animation');

        setTimeout(() => {
            button.classList.remove('likes-animation');
        }, 300);

        if (button.classList.contains('liked')) {
            return;
        }

        if (button.classList.contains('loading')) {
            return;
        }

        button.classList.add('loading');

        try {

            const json = await this.request(
                'photo_like',
                {
                    photo_id: button.dataset.photo
                }
            );

            button.classList.remove('loading');

            if (!json.success) {

                if (json.data?.message === 'already') {

                    button.classList.add('liked');

                }

                return;

            }

            this.updateButton(button, {

                likes: json.data.likes,
                liked: true

            });

        }
        catch (e) {

            button.classList.remove('loading');

            console.error(e);

        }

    }

}

document.addEventListener('DOMContentLoaded', () => {

    window.photoLikes = new PhotoLikes();

});