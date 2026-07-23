document.addEventListener('DOMContentLoaded', () => {

    const container = document.querySelector('.site-main');

    container.addEventListener('click', async (event) => {

        const button = event.target.closest('.photo-like');

        if (!button) {
            return;
        }

        button.classList.add('likes-animation');

        setTimeout(() => {
            button.classList.remove('likes-animation');
        }, 300);

        // Уже лайкал
        if (button.classList.contains('liked')) {
            return;
        }

        // Защита от двойного клика
        if (button.classList.contains('loading')) {
            return;
        }

        button.classList.add('loading');

        const form = new FormData();

        form.append('action', 'photo_like');
        form.append('photo_id', button.dataset.photo);
        form.append('nonce', PhotoLikes.nonce);

        try {

            const response = await fetch(PhotoLikes.ajax, {
                method: 'POST',
                credentials: 'same-origin',
                body: form
            });

            const json = await response.json();

            button.classList.remove('loading');

            if (!json.success) {

                if (json.data?.message === 'already') {
                    button.classList.add('liked');
                }

                return;
            }

            button.classList.add('liked');

            const count = button.querySelector('.count');

            count.textContent = json.data.likes;
            count.classList.add('visible');

        } catch (e) {

            button.classList.remove('loading');

            console.error(e);

        }

    });

});