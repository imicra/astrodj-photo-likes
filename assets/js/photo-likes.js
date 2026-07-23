class PhotoLikes {

    constructor() {

        this.bindEvents();

        this.loadState();

    }

    /**
     * Делегирование событий
     */
    bindEvents() {

        document.addEventListener('click', (event) => {

            const button = event.target.closest('.photo-like');

            if (!button) {
                return;
            }

            this.like(button);

        });

    }

    /**
     * Все ещё не инициализированные кнопки
     */
    getButtons() {
        return document.querySelectorAll('.photo-like:not([data-loaded])');
    }

    /**
     * Получение состояния кнопок
     */
    async loadState() {

        const buttons = this.getButtons();

        if (!buttons.length) {
            return;
        }

        const ids = [];

        buttons.forEach(button => {

            ids.push(button.dataset.photo);

        });

        const form = new FormData();

        form.append('action', 'photo_likes_state');
        form.append('nonce', PhotoLikesData.nonce);

        ids.forEach(id => {

            form.append('ids[]', id);

        });

        try {

            const response = await fetch(PhotoLikesData.ajax, {

                method: 'POST',

                credentials: 'same-origin',

                body: form

            });

            const json = await response.json();

            if (!json.success) {
                return;
            }

            buttons.forEach(button => {

                const id = button.dataset.photo;

                if (json.data[id]) {

                    this.updateButton(
                        button,
                        json.data[id]
                    );

                } else {

                    button.setAttribute('data-loaded', '');

                }

            });

        }

        catch (e) {

            console.error(e);

        }

    }

    /**
     * Обновление DOM
     */
    updateButton(button, state) {

        const count = button.querySelector('.count');

        count.textContent = state.likes;

        if (state.likes > 0) {

            count.classList.add('visible');

        } else {

            count.classList.remove('visible');

        }

        if (state.liked) {

            button.classList.add('liked');

        } else {

            button.classList.remove('liked');

        }

        button.setAttribute('data-loaded', '');

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

        const form = new FormData();

        form.append('action', 'photo_like');
        form.append('photo_id', button.dataset.photo);
        form.append('nonce', PhotoLikesData.nonce);

        try {

            const response = await fetch(PhotoLikesData.ajax, {

                method: 'POST',

                credentials: 'same-origin',

                body: form

            });

            const json = await response.json();

            button.classList.remove('loading');

            if (!json.success) {

                if (json.data?.message === 'already') {

                    this.updateButton(button, {

                        likes: parseInt(
                            button.querySelector('.count').textContent || 0
                        ),

                        liked: true

                    });

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